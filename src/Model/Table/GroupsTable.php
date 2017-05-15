<?php
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
    protected $_ldapRequiredParams = [
        'host',
        'port',
        'version',
        'domain',
        'baseDn',
        'username',
        'password',
        'groupsAttributes',
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

        $this->table('groups');
        $this->displayField('name');
        $this->primaryKey('id');

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

        $validator
            ->requirePresence('remote_group_id', 'create')
            ->allowEmpty('remote_group_id');

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
            return !$entity->deny_edit;
        }, 'systemCheck');

        // don't allow deletion of non-deletable group(s)
        $rules->addDelete(function ($entity, $options) {
            return !$entity->deny_delete;
        }, 'systemCheck');

        return $rules;
    }

    /**
     * Method that retrieves specified user's groups.
     *
     * @param string $userId user id
     * @param array $options Query options
     * @return array
     */
    public function getUserGroups($userId, array $options = [])
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

    public function getRemoteGroups()
    {
        $result = [];

        if (!(bool)Configure::read('Groups.remoteGroups.enabled')) {
            return $result;
        }

        $ldapConfig = (array)Configure::read('Groups.remoteGroups.LDAP');
        if (empty(array_diff($this->_ldapRequiredParams, array_keys($ldapConfig)))) {
            $connection = $this->_ldapConnect($ldapConfig);
            if (!$connection) {
                return $result;
            }

            $result = $this->_getLdapGroups($connection, $ldapConfig);
        }

        return $result;
    }

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
                Log::critical('Cannot bind with user: ' . $config['username']);
            }
        } catch (Exception $e) {
            Log::critical('Unable to connect to specified LDAP Server: ' . $e->getMessage());
        }

        return $connection;
    }

    protected function _getLdapGroups($connection, array $config)
    {
        $data = [];
        try {
            $search = ldap_search($connection, $config['baseDn'], $config['groupsFilter'], $config['groupsAttributes']);

            $data = ldap_get_entries($connection, $search);
        } catch (Exception $e) {
            Log::critical('Failed to query AD: ' . $e->getMessage());
        }

        if (empty($data)) {
            return $data;
        }

        return $this->_normalizeResult($data, $config);
    }

    protected function _normalizeResult($data, array $config)
    {
        $fields = ['member', 'memberof'];
        $result = [];

        for ($i = 0; $i < $data['count']; $i++) {
            $item = $data[$i];

            $result[$item['dn']] = [];
            foreach ($config['groupsAttributes'] as $attribute) {
                if (in_array($attribute, $fields)) {
                    if (!isset($item[$attribute]) || !isset($item[$attribute]['count'])) {
                        $result[$item['dn']][$attribute] = [];
                        continue;
                    }
                    $result[$item['dn']][$attribute] = [];
                    for ($j = 0; $j < $item[$attribute]['count']; $j++) {
                        $result[$item['dn']][$attribute][] = $item[$attribute][$j];
                    }
                } else {
                    if (!isset($item[$attribute]) || !isset($item[$attribute][0])) {
                        $result[$item['dn']][$field] = null;
                        continue;
                    }
                    $result[$item['dn']][$attribute] = $item[$attribute][0];
                }
            }
        }

        return $result;
    }
}
