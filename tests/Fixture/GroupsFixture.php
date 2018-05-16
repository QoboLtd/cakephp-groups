<?php
namespace Groups\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * GroupsFixture
 *
 */
class GroupsFixture extends TestFixture
{
    public $table = 'qobo_groups';
    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'name' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'trashed' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'description' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'deny_edit' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'deny_delete' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'remote_group_id' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
            'UNIQUE_NAME' => ['type' => 'unique', 'columns' => ['name'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => '00000000-0000-0000-0000-000000000001',
            'name' => 'Lorem ipsum dolor sit amet',
            'remote_group_id' => null,
            'created' => '2016-02-04 11:12:29',
            'modified' => '2016-02-04 11:12:29',
            'trashed' => null,
            'description' => 'Lorem ipsum dolor sit amet',
            'deny_edit' => 0,
            'deny_delete' => 0,
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000002',
            'name' => 'Admins',
            'remote_group_id' => null,
            'created' => '2017-09-08 09:58:47',
            'modified' => '2017-09-08 09:58:47',
            'trashed' => null,
            'description' => 'Administrators of the system',
            'deny_edit' => 0,
            'deny_delete' => 1,
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000003',
            'name' => 'Everyone',
            'remote_group_id' => null,
            'created' => '2017-09-08 09:58:47',
            'modified' => '2017-09-08 09:58:47',
            'trashed' => null,
            'description' => 'All users',
            'deny_edit' => 1,
            'deny_delete' => 1,
        ],
    ];
}
