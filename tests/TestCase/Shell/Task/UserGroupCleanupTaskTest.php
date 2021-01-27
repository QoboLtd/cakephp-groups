<?php
namespace Groups\Test\TestCase\Shell\Task;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Groups\Shell\Task\UserGroupCleanupTask;

/**
 * @property \Groups\Model\Table\GroupsTable $Groups
 * @property \CakeDC\Users\Model\Table\UsersTable $Users
 */
class UserGroupCleanupTaskTest extends TestCase
{
    public $fixtures = [
        'plugin.Groups.Groups',
        'plugin.Groups.GroupsUsers',
        'plugin.CakeDC/Users.Users',
    ];

    /**
     * @var \Groups\Shell\Task\UserGroupCleanupTask
     */
    private $Task;

    public function setUp()
    {
        parent::setUp();

        /**
         * @var \Groups\Model\Table\GroupsTable
         */
        $table = TableRegistry::getTableLocator()->get('Groups.Groups');
        $this->Groups = $table;

        /**
         * @var \CakeDC\Users\Model\Table\UsersTable
         */
        $table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $this->Users = $table;

        /** @var \Cake\Console\ConsoleIo */
        $io = $this->getMockBuilder('Cake\Console\ConsoleIo')->getMock();

        $this->Task = new UserGroupCleanupTask($io);

        Configure::load('Groups.groups');
    }

    public function tearDown()
    {
        unset($this->Groups);
        unset($this->Users);
        unset($this->Task);

        parent::tearDown();
    }

    public function testMain(): void
    {
        $group = $this->Groups->get('00000000-0000-0000-0000-000000000001');
        $user = $this->Users->get('00000000-0000-0000-0000-000000000001');
        // create duplicate many-to-many record between group and user
        $this->Groups->Users->link($group, [$user]);

        // verify user - group links increased
        $result = $this->Groups->get('00000000-0000-0000-0000-000000000001', [
            'contain' => [
                'Users' => function ($q) {
                    return $q->where(['Users.id' => '00000000-0000-0000-0000-000000000001']);
                },
            ],
        ]);
        $this->assertNotEquals(1, count($result->get('users')));

        $this->Task->main();

        // verify duplicated records have been removed
        $result = $this->Groups->get('00000000-0000-0000-0000-000000000001', [
            'contain' => [
                'Users' => function ($q) {
                    return $q->where(['Users.id' => '00000000-0000-0000-0000-000000000001']);
                },
            ],
        ]);
        $this->assertEquals(1, count($result->get('users')));
    }
}
