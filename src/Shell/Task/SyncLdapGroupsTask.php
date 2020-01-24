<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Groups\Shell\Task;

use CakeDC\Users\Controller\Traits\CustomUsersTableTrait;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Exception;

/**
 * Sync LDAP Groups Task
 *
 * Synchronize LDAP groups.
 */
class SyncLdapGroupsTask extends Shell
{
    use CustomUsersTableTrait;

    /**
     * LDAP required parameters.
     *
     * @var array
     */
    protected $ldapRequiredParams = [
        'host',
        'port',
        'version',
        'domain',
        'baseDn',
        'username',
        'password',
        'groupsFilter',
    ];

    /**
     * Main task method
     *
     * @return bool True on success, false otherwise
     */
    public function main()
    {
        $this->out('Sync LDAP Groups');
        $this->hr();

        if (!(bool)Configure::read('Groups.remoteGroups.enabled')) {
            $this->warn('Remote groups functionality is turned off.');

            return true;
        }

        if (!(bool)Configure::read('Groups.remoteGroups.LDAP.enabled')) {
            $this->warn('LDAP functionality is turned off.');

            return true;
        }

        $config = (array)Configure::read('Groups.remoteGroups.LDAP');

        // evaluate LDAP parameters
        $diff = array_diff($this->ldapRequiredParams, array_keys($config));
        if (!empty($diff)) {
            $this->abort('Required parameters are missing: ' . implode(', ', $diff) . '.');
        }

        $groupsTable = TableRegistry::get('Groups.Groups');

        $groups = $this->getGroups($groupsTable);
        if (empty($groups)) {
            $this->warn('No groups are mapped to remote LDAP groups.  Nothing to do.');

            return true;
        }

        $connection = $this->ldapConnect($config);
        if (!is_resource($connection)) {
            $this->abort('Unable to connect to LDAP Server.');
        }

        $domain = $this->getDomain($config);

        foreach ($groups as $group) {
            $filter = substr($config['filter'], 0, -1) . '(memberof=' . $group->remote_group_id . '))';

            $cookie = '';
            $success = true;
            do {
                $result = ldap_control_paged_result($connection, 20, true, $cookie);
                if ($result === false) {
                    $this->abort('Failed to set LDAP paged result');
                }

                $search = ldap_search($connection, $config['baseDn'], $filter, ['userprincipalname']);
                if (!is_resource($search)) {
                    $this->abort('Failed to search LDAP');
                }
                $data = ldap_get_entries($connection, $search);
                if (!is_array($data)) {
                    $this->abort('Failed to get search results from LDAP');
                }

                $users = $this->getUsers($data, $domain);

                if (!$this->syncGroupUsers($groupsTable, $group, $users)) {
                    $success = false;
                }

                ldap_control_paged_result_response($connection, $search, $cookie);
            } while (!empty($cookie));

            if ($success) {
                $this->info('Group ' . $group->name . ' synced successfully.');
            } else {
                $this->warn('Group ' . $group->name . ' failed to sync.');
            }
        }

        $this->success('Synchronization completed.');
    }

    /**
     * Fetch system groups which are mapped to LDAP group.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @return mixed[]
     */
    protected function getGroups(Table $table): array
    {
        /**
         * @var \Cake\ORM\Query $query
         */
        $query = $table->find('all')
            ->where(['remote_group_id IS NOT NULL', 'remote_group_id !=' => ''])
            ->contain(['Users' => function ($q) {
                return $q->select(['Users.username']);
            }]);
        /**
         * @var array $result
         */
        $result = $query->all();

        return $result;
    }

    /**
     * Connect to LDAP server.
     *
     * @param mixed[] $config LDAP configuration
     * @return resource LDAP connection
     */
    protected function ldapConnect(array $config)
    {
        // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
        $connection = @ldap_connect($config['host'], $config['port']);
        if (!is_resource($connection)) {
            $this->abort("Unable to connecto LDAP at [" . $config['host'] . ":" . $config['port'] . "]");
        }

        // set LDAP options
        ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, (int)$config['version']);
        ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($connection, LDAP_OPT_NETWORK_TIMEOUT, 5);

        // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
        $bind = @ldap_bind($connection, $config['domain'] . '\\' . $config['username'], $config['password']);
        if ($bind === false) {
            $this->abort('Cannot bind with user: ' . $config['username'] . '.');
        }

        return $connection;
    }

    /**
     * Get LDAP domain.
     *
     * @param mixed[] $config LDAP configuration
     * @return string
     */
    protected function getDomain(array $config): string
    {
        $result = '';
        $parts = explode(',', $config['baseDn']);
        foreach ($parts as $part) {
            list($key, $value) = explode('=', $part);
            if ('dc' !== strtolower($key)) {
                continue;
            }

            $result .= '.' . $value;
        }

        $result = trim(trim($result, '.'));

        // fallback value
        if (empty($result)) {
            $result = $config['domain'] . '.com';
        }

        return $result;
    }

    /**
     * Fetch system users which are members of LDAP group.
     *
     * @param mixed[] $data LDAP result
     * @param string $domain LDAP domain
     * @return mixed[]
     */
    protected function getUsers(array $data, string $domain): array
    {
        /**
         * @var \CakeDC\Users\Model\Table\UsersTable $table
         */
        $table = $this->getUsersTable();

        $result = [];
        for ($i = 0; $i < $data['count']; $i++) {
            $username = $data[$i]['userprincipalname'][0];
            $username = str_replace('@' . $domain, '', $username);

            $query = $table->find()->where(['username' => $username]);
            if ($query->isEmpty()) {
                continue;
            }

            $result[] = $query->first();
        }

        return $result;
    }

    /**
     * Synchronizes group users based on mapped LDAP group users.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param \Cake\Datasource\EntityInterface $group Group entity
     * @param mixed[] $users Group users
     * @return bool
     */
    protected function syncGroupUsers(Table $table, EntityInterface $group, array $users): bool
    {
        $userIds = [];
        foreach ($users as $user) {
            $userIds[] = $user->id;
        }

        if (!empty($group->users)) {
            foreach ($group->users as $user) {
                $userIds[] = $user->id;
            }
        }

        $data = [
            'users' => [
                '_ids' => $userIds,
            ],
        ];

        $group = $table->patchEntity($group, $data);
        $result = empty($table) ? false : true;

        return $result;
    }
}
