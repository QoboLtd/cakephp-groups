<?php
namespace Groups\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
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
     * @param  string $userId user id
     * @return array
     */
    public function getUserGroups($userId)
    {
        $query = $this->find('list', [
            'keyField' => 'id',
            'valueField' => 'name'
        ]);
        $query->matching('Users', function ($q) use ($userId) {
            return $q->where(['Users.id' => $userId]);
        });

        return $query->toArray();
    }
}
