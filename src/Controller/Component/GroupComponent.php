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
namespace Groups\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Groups Component
 *
 * @property \Cake\Controller\Component\AuthComponent $Auth
 */
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
     * @return mixed[]
     */
    public function getUserGroups(string $userId = ''): array
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
