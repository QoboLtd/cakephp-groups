<?php
namespace Groups\Shell\Task;

use CakeDC\Users\Controller\Traits\CustomUsersTableTrait;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

/**
 * Task for synchronizing ldap groups.
 */
class SyncLdapGroupsTask extends Shell
{
    use CustomUsersTableTrait;

    /**
     * LDAP required parameters.
     *
     * @var array
     */
    protected $_ldapRequiredParams = [
        'host',
        'port',
        'version',
        'domain',
        'baseDn',
        'username',
        'password',
        'groupsFilter'
    ];

    /**
     * {@inheritDoc}
     */
    public function main()
    {
        $this->out('Sync LDAP Groups');
        $this->hr();

        if (!(bool)Configure::read('Groups.remoteGroups.enabled')) {
            $this->abort('Remote groups functionality is turned off.');
        }

        if (!(bool)Configure::read('Groups.remoteGroups.LDAP.enabled')) {
            $this->abort('LDAP functionality is turned off.');
        }

        $config = (array)Configure::read('Groups.remoteGroups.LDAP');

        // evaluate LDAP parameters
        $diff = array_diff($this->_ldapRequiredParams, array_keys($config));
        if (!empty($diff)) {
            $this->abort('Required parameters are missing: ' . implode(', ', $diff) . '.');
        }

        $groupsTable = TableRegistry::get('Groups.Groups');

        $groups = $this->_getGroups($groupsTable);
        if ($groups->isEmpty()) {
            $this->abort('No mapped system groups found.');
        }

        $connection = $this->_ldapConnect($config);
        if (!$connection) {
            $this->abort('Unable to connect to LDAP Server.');
        }

        $domain = $this->_getDomain($config);

        foreach ($groups as $group) {
            $filter = substr($config['filter'], 0, -1) . '(memberof=' . $group->remote_group_id . '))';

            $data = [];
            try {
                $search = ldap_search($connection, $config['baseDn'], $filter, ['userprincipalname']);

                $data = ldap_get_entries($connection, $search);
            } catch (Exception $e) {
                $this->abort('Failed to query AD: ' . $e->getMessage() . '.');
            }

            if (empty($data)) {
                continue;
            }

            $users = $this->_getUsers($data, $domain);

            if (empty($users)) {
                continue;
            }

            $this->_syncGroupUsers($group, $users);
        }

        $this->success('Synchronization completed.');
    }

    /**
     * Fetch system groups which are mapped to LDAP group.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @return array
     */
    protected function _getGroups(Table $table)
    {
        $query = $table->find('all')
            ->where(['remote_group_id IS NOT NULL', 'remote_group_id !=' => ''])
            ->contain(['Users' => function ($q) {
                return $q->select(['Users.username']);
            }]);

        return $query->all();
    }

    /**
     * Connect to LDAP server.
     *
     * @param array $config LDAP configuration
     * @return LDAP connection
     */
    protected function _ldapConnect(array $config)
    {
        try {
            $connection = @ldap_connect($config['host'], $config['port']);

            // set LDAP options
            ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, (int)$config['version']);
            ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);
            ldap_set_option($connection, LDAP_OPT_NETWORK_TIMEOUT, 5);

            $bind = @ldap_bind($connection, $config['domain'] . '\\' . $config['username'], $config['password']);
            if (!$bind) {
                $this->abort('Cannot bind with user: ' . $config['username'] . '.');
            }
        } catch (Exception $e) {
            $this->abort('Unable to connect to specified LDAP Server: ' . $e->getMessage() . '.');
        }

        return $connection;
    }

    /**
     * Get LDAP domain.
     *
     * @param array $config LDAP configuration
     * @return string
     */
    protected function _getDomain(array $config)
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
     * @param array $data LDAP result
     * @param string $domain LDAP domain
     * @return array
     */
    protected function _getUsers($data, $domain)
    {
        $table = $this->getUsersTable();

        $result = [];
        for ($i = 0; $i < $data['count']; $i++) {
            $username = $data[$i]['userprincipalname'][0];
            $username = str_replace('@' . $domain, '', $username);

            $query = $table->findByUsername($username);
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
     * @param array $users Group users
     * @return void
     */
    protected function _syncGroupUsers(Table $table, EntityInterface $group, array $users)
    {
        // unlink existing users
        if (!empty($group->users)) {
            $table->Users->unlink($group, $group->users);
        }

        $userIds = [];
        foreach ($users as $user) {
            $userIds[] = $user->id;
        }

        $data = [
            'users' => [
                '_ids' => $userIds
            ]
        ];

        $group = $table->patchEntity($group, $data);

        if ($table->save($group)) {
            $this->info('Group ' . $group->name . ' synced successfully.');
        } else {
            $this->warn('Group ' . $group->name . ' failed to sync.');
        }
    }
}
