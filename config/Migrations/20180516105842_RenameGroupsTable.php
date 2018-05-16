<?php
use Migrations\AbstractMigration;

class RenameGroupsTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $this->table('groups')
            ->rename('qobo_groups');
    }
}
