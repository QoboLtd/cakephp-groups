<?php
namespace Groups\Shell\Task;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

/**
 * Task for assign default group to all users.
 */
class UserGroupCleanupTask extends Shell
{
    /**
     * {@inheritDoc}
     */
    public function main()
    {
        $this->out('Task: user group cleanup');
        $this->hr();

        // get groups table
        $table = TableRegistry::get('Groups.Groups');
        $query = $table->find('all');

        if ($query->isEmpty()) {
            $this->abort('No groups found.');
        }

        $groups = $query->all();
        foreach ($groups as $group) {
            $this->info('Cleaning up ' . $group->name . ' group ..');

            $table = TableRegistry::get('groups_users');
            $query = $table->find('all')
                ->where(['group_id' => $group->id])
                ->select(['user_id'])
                ->distinct(['user_id']);

            if ($query->isEmpty()) {
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

            $this->info('Deleted ' . $count . ' records ..');
        }

        $this->out('<success>User group cleanup task completed</success>');
    }
}
