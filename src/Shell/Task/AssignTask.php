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

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * Assign Task
 *
 * Assign all users to a default group, like Everyone.
 */
class AssignTask extends Shell
{
    /**
     * Main task method
     *
     * @return bool True on success, false otherwise
     */
    public function main()
    {
        $this->info('Task: assign all users to the default group');
        $this->hr();

        // Read default group from configuration
        $groupName = (string)Configure::read('Groups.defaultGroup');
        if (empty($groupName)) {
            $this->warn("Default group is not configured.  Nothing to do.");

            return true;
        }

        /**
         * @var \Groups\Model\Table\GroupsTable $table
         */
        $table = TableRegistry::get('Groups.Groups');
        /**
         * Get default group entity.
         *
         * @var \Cake\Datasource\EntityInterface|null
         */
        $group = $table->find()
            ->where(['name' => $groupName])
            ->enableHydration(true)
            ->first();
        if (null === $group) {
            $this->warn("Default group [$groupName] does not exist.  Nothing to do.");

            return true;
        }

        // Get all user IDs
        $users = $table->Users->find('all', [
            'fields' => ['id'],
        ])->toArray();
        if (empty($users)) {
            $this->abort("No users found in the system.  Something is terribly wrong!");
        }

        // Assign all users to the group
        $result = $table->Users->replaceLinks($group, $users);
        if (!$result) {
            $this->abort("Failed to add all users to group [$groupName].");
        }

        $this->success("All users are now assigned to group [$groupName].");
    }
}
