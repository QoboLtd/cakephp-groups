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

        $this->io = $this->getMockBuilder('Cake\Console\ConsoleIo')
            ->disableOriginalConstructor()
            ->getMock();

        $this->Task = $this->getMockBuilder('Groups\Shell\Task\ImportTask')
            ->setMethods(['in', 'out', 'err', '_stop'])
            ->setConstructorArgs([$this->io])
            ->getMock();
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
        $group = $this->Groups->find()->where(['name' => 'Admins'])->first();
        $this->Groups->delete($group);

        $group = $this->Groups->find()->where(['name' => 'Everyone'])->first();
        $this->Groups->delete($group);

        $this->Task->main();

        $query = $this->Groups->find()->where(['name' => 'Admins']);
        $this->assertFalse($query->isEmpty());

        $query = $this->Groups->find()->where(['name' => 'Everyone']);
        $this->assertFalse($query->isEmpty());
    }
}
