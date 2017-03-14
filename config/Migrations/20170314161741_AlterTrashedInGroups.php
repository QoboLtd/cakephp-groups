<?php
use Migrations\AbstractMigration;

class AlterTrashedInGroups extends AbstractMigration
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
        $table = $this->table('groups');
        $table->changeColumn('trashed', 'datetime', [
            'default' => null,
            'null' => true,
        ]);
        $table->update();
    }
}
