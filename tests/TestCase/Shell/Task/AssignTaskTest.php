<?php
namespace Groups\Test\TestCase\Shell\Task;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Groups\Shell\Task\AssignTask;

/**
 * @property \Groups\Model\Table\GroupsTable $Groups
 * @property \CakeDC\Users\Model\Table\UsersTable $Users
 */
class AssignTaskTest extends TestCase
{
    public $fixtures = [
        'plugin.Groups.Groups',
        'plugin.Groups.GroupsUsers',
        'plugin.CakeDC/Users.Users',
    ];

    /**
     * @var \Groups\Shell\Task\AssignTask
     */
    private $Task;

    public function setUp()
    {
        parent::setUp();

        /**
         * @var \Groups\Model\Table\GroupsTable $table
         */
        $table = TableRegistry::getTableLocator()->get('Groups.Groups');
        $this->Groups = $table;

        /**
         * @var \CakeDC\Users\Model\Table\UsersTable $table
         */
        $table = TableRegistry::getTableLocator()->get('CakeDC/Users.Users');
        $this->Users = $table;

        /** @var \Cake\Console\ConsoleIo */
        $io = $this->getMockBuilder('Cake\Console\ConsoleIo')->getMock();

        $this->Task = new AssignTask($io);

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
        $data = ['name' => Configure::read('Groups.defaultGroup')];

        $expected = $this->Users->find()->count();

        $this->Task->main();

        /** @var \Cake\ORM\Query */
        $query = $this->Groups->find()
            ->where($data)
            ->contain('Users');

        $query->enableHydration(true);

        /** @var \Cake\Datasource\EntityInterface */
        $entity = $query->first();

        $this->assertEquals($expected, count($entity->get('users')));
    }
}
