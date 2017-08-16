<?php
namespace Groups\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Groups\Shell\Task\AssignTask;

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

        $this->Groups = TableRegistry::get('Groups.Groups');
        $this->Users = TableRegistry::get('CakeDC/Users.Users');

        $this->io = $this->getMock('Cake\Console\ConsoleIo', [], [], '', false);

        $this->Task = $this->getMock('Groups\Shell\Task\AssignTask', ['in', 'out', 'err', '_stop'], [$this->io]);

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

    public function testMain()
    {
        $data = ['name' => Configure::read('Groups.defaultGroup')];

        $entity = $this->Groups->newEntity();
        $entity = $this->Groups->patchEntity($entity, $data);

        if (!$this->Groups->save($entity)) {
            return;
        }

        $expected = $this->Users->find('all')->count();

        $this->Task->main();

        $entity = $this->Groups->find()->where($data)->contain('Users')->first();
        $this->assertEquals($expected, count($entity->get('users')));
    }
}
