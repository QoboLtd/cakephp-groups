<?php
namespace Groups\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Groups\Shell\Task\ImportTask;

class ImportTaskTest extends TestCase
{
    public $fixtures = [
        'plugin.groups.groups',
    ];

    public function setUp()
    {
        parent::setUp();

        $this->Groups = TableRegistry::get('Groups.Groups');

        $this->io = $this->getMock('Cake\Console\ConsoleIo', [], [], '', false);

        $this->Task = $this->getMock('Groups\Shell\Task\ImportTask', ['in', 'out', 'err', '_stop'], [$this->io]);
    }

    public function tearDown()
    {
        unset($this->Groups);
        unset($this->io);
        unset($this->Task);

        parent::tearDown();
    }

    public function testMain()
    {
        $query = $this->Groups->find()->where(['name' => 'Admins']);
        $this->assertTrue($query->isEmpty());

        $query = $this->Groups->find()->where(['name' => 'Everyone']);
        $this->assertTrue($query->isEmpty());

        $this->Task->main();

        $query = $this->Groups->find()->where(['name' => 'Admins']);
        $this->assertFalse($query->isEmpty());

        $query = $this->Groups->find()->where(['name' => 'Everyone']);
        $this->assertFalse($query->isEmpty());
    }
}
