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
namespace Groups\Model\Table;

use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Exception;
use Groups\Model\Entity\Group;

/**
 * Groups Model
 *
 * @property \Cake\ORM\Association\BelongsToMany $Phinxlog
 * @property \Cake\ORM\Association\BelongsToMany $Users
 */
class GroupsTable extends Table
{
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
        'groupsFilter'
    ];

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('qobo_groups');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Muffin/Trash.Trash');

        $this->belongsToMany('Users', [
            'foreignKey' => 'group_id',
            'targetForeignKey' => 'user_id',
            'joinTable' => 'groups_users',
            'className' => 'CakeDC/Users.Users'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->add('id', 'valid', ['rule' => 'uuid'])
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name')
            ->add('name', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        return $validator;
    }

    /**
     * {@inheritDoc}
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['name']));

        // don't allow editing of non-editable group(s)
        $rules->addUpdate(function ($entity, $options) {
            return ! $entity->getOriginal('deny_edit');
        }, 'systemCheck');

        // don't allow deletion of non-deletable group(s)
        $rules->addDelete(function ($entity, $options) {
            return !$entity->deny_delete;
        }, 'systemCheck');

        return $rules;
    }

    /**
     * Method that retrieves specified user's groups as list.
     *
     * @param string $userId user id
     * @param mixed[] $options Query options
     * @return mixed[]
     */
    public function getUserGroups(string $userId, array $options = []): array
    {
        $query = $this->find('list', [
            'keyField' => 'id',
            'valueField' => 'name'
        ]);
        $query->matching('Users', function ($q) use ($userId) {
            return $q->where(['Users.id' => $userId]);
        });
        $query->applyOptions($options);

        return $query->toArray();
    }

    /**
     * Method that retrieves specified user's groups.
     *
     * @param string $userId user id
     * @param mixed[] $options Query options
     * @return mixed[]
     */
    public function getUserGroupsAll(string $userId, array $options = []): array
    {
        $query = $this->find('all');

        $query->matching('Users', function ($q) use ($userId) {
            return $q->where(['Users.id' => $userId]);
        });
        $query->applyOptions($options);

        return $query->toArray();
    }

    /**
     * Fetch remote groups.
     *
     * @return mixed[]
     */
    public function getRemoteGroups(): array
    {
        $result = [];

        if (!(bool)Configure::read('Groups.remoteGroups.enabled')) {
            return $result;
        }

        if ((bool)Configure::read('Groups.remoteGroups.LDAP.enabled')) {
            $result = $this->_getLdapGroups();
        }

        return $result;
    }

    /**
     * Fetch LDAP groups.
     *
     * @return mixed[]
     */
    protected function _getLdapGroups(): array
    {
        $result = [];

        $config = (array)Configure::read('Groups.remoteGroups.LDAP');
        if (!empty(array_diff($this->ldapRequiredParams, array_keys($config)))) {
            return $result;
        }

        $connection = $this->_ldapConnect($config);
        if (!is_resource($connection)) {
            return $result;
        }

        $search = ldap_search($connection, $config['baseDn'], $config['groupsFilter'], ['cn']);
        if (!is_resource($search)) {
            Log::critical('Failed to search LDAP');

            return $result;
        }

        $result = ldap_get_entries($connection, $search);

        if (empty($result)) {
            return [];
        }

        return $this->_normalizeResult($result);
    }

    /**
     * Connect to LDAP server.
     *
     * @param mixed[] $config LDAP configuration
     * @return resource|false LDAP connection
     */
    protected function _ldapConnect(array $config)
    {
        $result = false;

        // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
        $connection = @ldap_connect($config['host'], $config['port']);
        if (!is_resource($connection)) {
            Log::critical("Unable to connecto LDAP at [" . $config['host'] . ":" . $config['port'] . "]");

            return $result;
        }

        // set LDAP options
        ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, (int)$config['version']);
        ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($connection, LDAP_OPT_NETWORK_TIMEOUT, 5);

        // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
        $bind = @ldap_bind($connection, $config['domain'] . '\\' . $config['username'], $config['password']);
        if ($bind === false) {
            Log::critical('Cannot bind with user: ' . $config['username']);

            return $result;
        }

        return $connection;
    }

    /**
     * Normalizes LDAP result.
     *
     * @param mixed[] $data LDAP result
     * @return mixed[]
     */
    protected function _normalizeResult(array $data): array
    {
        $result = [];
        for ($i = 0; $i < $data['count']; $i++) {
            $item = $data[$i];

            // construct label
            preg_match('/^.*?,OU=(.*?),/i', $item['dn'], $match);
            $label = !empty($match[1]) ? $match[1] . ' / ' . $item['cn'][0] : $item['cn'][0];

            $result[$item['dn']] = $label;
        }

        asort($result);

        return $result;
    }
}
