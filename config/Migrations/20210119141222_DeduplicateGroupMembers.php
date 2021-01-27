<?php

use Migrations\AbstractMigration;

class DeduplicateGroupMembers extends AbstractMigration
{

    /**
     * Add defaults to deny_delete and deny_edit
     *
     */
    public function change()
    {
        /* Deduplicate groups_users */
        $sql = 'DELETE t1 FROM groups_users t1' . 
               ' INNER JOIN groups_users t2' . 
               ' WHERE t1.id < t2.id' .
               ' AND t1.user_id = t2.user_id' .
               ' AND t1.group_id = t2.group_id';

        $this->query($sql);

        /* Add unique constraint */
        $this->table('groups_users')
        ->addIndex([
            'user_id',
            'group_id',
        ], [
            'name' => 'UNIQUE_GROUPS_USERS',
            'unique' => true,
        ])->update();
    }
}
