<?php
namespace Groups\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

class GroupComponent extends Component
{
    public $components = ['Auth'];

    /**
     * Initialize method
     * @param  array  $config configuration array
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
    }

    /**
     * Method that retrieves specified user's groups
     * @param  string $userId user id
     * @return array
     */
    public function getUserGroups($userId = '')
    {
        // if not specified, get current user's id
        if (empty($userId)) {
            $userId = $this->Auth->user('id');
        }

        $groups = TableRegistry::get('Groups.Groups');

        $query = $groups->find('list', [
            'keyField' => 'id',
            'valueField' => 'name'
        ]);
        $query->matching('Users', function ($q) use ($userId) {
            return $q->where(['Users.id' => $userId]);
        });

        return $query->toArray();
    }
}
