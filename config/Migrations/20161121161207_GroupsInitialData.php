<?php
use Cake\ORM\TableRegistry;
use Migrations\AbstractMigration;

class GroupsInitialData extends AbstractMigration
{
    /**
     * {@inheritDoc}
     */
    public function up()
    {
        $data = [
            [
                'name' => 'Admins',
                'description' => 'Administrators group',
                'deny_edit' => true,
                'deny_delete' => true
            ],
            [
                'name' => 'Everyone',
                'description' => 'Generic users group',
                'deny_edit' => true,
                'deny_delete' => true
            ]
        ];

        $table = TableRegistry::get('Groups');

        foreach ($data as $group) {
            $entity = $table->newEntity();
            foreach ($group as $k => $v) {
                $entity->{$k} = $v;

            }
            $table->save($entity);
        }
    }
}
