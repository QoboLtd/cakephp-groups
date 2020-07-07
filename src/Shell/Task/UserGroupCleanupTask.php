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
use Cake\ORM\TableRegistry;

/**
 * User Group Cleanup Task
 *
 * Remove duplicate links from groups_users table.
 */
class UserGroupCleanupTask extends Shell
{
    /**
     * Main task method
     *
     * @return bool True on success, false otherwise
     */
    public function main()
    {
        $this->info('Task: user group cleanup');
        $this->hr();

        // get groups table
        $table = TableRegistry::getTableLocator()->get('Groups.Groups');
        $query = $table->find('all');

        if ($query->isEmpty()) {
            $this->warn("No groups found in the system.  Nothing to do.");

            return true;
        }

        $groups = $query->all();
        foreach ($groups as $group) {
            $this->info('Cleaning up ' . $group->name . ' group ..');

            $table = TableRegistry::getTableLocator()->get('groups_users');
            $query = $table->find('all')
                ->where(['group_id' => $group->id])
                ->select(['user_id'])
                ->distinct(['user_id']);

            if ($query->isEmpty()) {
                $this->info("No users found in group.  Skipping.");
                continue;
            }

            $users = $query->all();
            $count = 0;
            foreach ($users as $user) {
                $query = $table->find('all')
                    ->where(['user_id' => $user->user_id, 'group_id' => $group->id]);

                if ($query->count() < 2) {
                    continue;
                }

                $count += $query->count() - 1;

                $result = $query->toArray();

                $entity = array_pop($result);

                $query = $table->query();
                $query->delete()
                    ->where(['user_id' => $entity->user_id, 'group_id' => $entity->group_id, 'id !=' => $entity->id])
                    ->execute();
            }

            $this->info('Deleted ' . $count . ' duplicates.');
        }

        $this->success('User group cleanup task completed.');
    }
}
