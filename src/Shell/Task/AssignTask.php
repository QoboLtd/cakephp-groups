<?php
namespace Groups\Shell\Task;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

/**
 * Task for assign group to all users.
 */
class AssignTask extends Shell
{
    /**
     * {@inheritDoc}
     */
    public function main()
    {
        $this->out('Task: assign default group to all users');
        $this->hr();

        // get groups table
        $table = TableRegistry::get('Groups.Groups');

        $defaultGroup = $this->_getDefaultGroupName();
        if ($defaultGroup) {
            $group = $this->_getDefaultGroupEntity($table, $defaultGroup);
            if ($group) {
                $users = $this->_getNonDefaultGroupUsers($group);
                if ($users) {
                    $table->Users->link($group, $users);
                }
            }
        }

        $this->out('<success>Default group assignment task completed</success>');
    }

    /**
     * Get default group name.
     *
     * @return string|null
     */
    protected function _getDefaultGroupName()
    {
        $result = Configure::read('Groups.defaultGroup');
        if (empty($result)) {
            $this->err('Default group is not defined, all following tasks are skipped');
        }

        return $result;
    }

    /**
     * Get default group and associated users.
     *
     * @param  \Cake\ORM\Table $table Table instance
     * @param  string $defaultGroup Default group name
     * @return \Cake\ORM\Entity|null
     */
    protected function _getDefaultGroupEntity(Table $table, $defaultGroup)
    {
        $result = $table
            ->find('all', [
                'conditions' => [
                    'name' => $defaultGroup,
                    'deny_edit' => 1,
                    'deny_delete' => 1
                ]
            ])
            ->contain([
                'Users' => function ($q) {
                    return $q
                        ->select(['id']);
                }
            ])
            ->first();

        if (!$result) {
            $this->err('Default group was not found in the system, all following tasks are skipped');
        }

        return $result;
    }

    /**
     * Get users which are not already assigned to the default group.
     *
     * @param  \Cake\ORM\Entity $group Group entity
     * @return array
     */
    protected function _getNonDefaultGroupUsers(Entity $group)
    {
        $ids = [];
        // get group users ids
        if (!empty($group['users'])) {
            foreach ($group['users'] as $user) {
                $ids[] = $user->id;
            }
        }

        // get users not assigned to the default group
        $result = TableRegistry::get('CakeDC/Users.Users')
            ->find('all', [
                'conditions' => [
                    'id NOT IN' => $ids
                ]
            ])
            ->all();

        if ($result->isEmpty()) {
            $this->err('All users are assigned to the default group, all following tasks are skipped');
        }

        return $result->toArray();
    }
}
