<?php
namespace Groups\Test\TestCase\Model\Table;

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
        'plugin.groups.groups',
        'plugin.groups.groups_users',
        'plugin.CakeDC/Users.users',
    ];

    public function setUp()
    {
        parent::setUp();

        /**
         * @var \Groups\Model\Table\GroupsTable $table
         */
        $table = TableRegistry::get('Groups.Groups');
        $this->Groups = $table;

        /**
         * @var \CakeDC\Users\Model\Table\UsersTable $table
         */
        $table = TableRegistry::get('CakeDC/Users.Users');
        $this->Users = $table;

        $this->io = $this->getMockBuilder('Cake\Console\ConsoleIo')
            ->disableOriginalConstructor()
            ->getMock();

        $this->Task = $this->getMockBuilder('Groups\Shell\Task\AssignTask')
            ->setMethods(['in', 'out', 'err', '_stop'])
            ->setConstructorArgs([$this->io])
            ->getMock();

        Configure::load('Groups.groups');
    }

    public function tearDown()
    {
        unset($this->Groups);
        unset($this->Users);
        unset($this->io);
        unset($this->Task);

        parent::tearDown();
    }

    public function testMain(): void
    {
        $data = ['name' => Configure::read('Groups.defaultGroup')];

        $entity = $this->Groups->newEntity();
        $entity = $this->Groups->patchEntity($entity, $data);

        if (!$this->Groups->save($entity)) {
            return;
        }

        $expected = $this->Users->find('all')->count();

        $this->Task->main();

        $groups = $this->Groups->find()->where($data)->contain('Users');
        $this->assertTrue(is_object($groups), "Groups is not an object");
        if (is_object($groups)) {
            $entity = $groups->first();
            $this->assertTrue(is_object($entity), "First user is not an object");
            if (is_object($entity)) {
                $this->assertEquals($expected, count($entity->get('users')));
            }
        }
    }
}
