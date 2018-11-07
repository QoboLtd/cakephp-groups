<?php
namespace Groups\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Groups\Shell\Task\ImportTask;

/**
 * @property \Groups\Model\Table\GroupsTable $Groups
 */
class ImportTaskTest extends TestCase
{
    public $fixtures = [
        'plugin.groups.groups',
    ];

    public function setUp()
    {
        parent::setUp();

        /**
         * @var \Groups\Model\Table\GroupsTable $table
         */
        $table = TableRegistry::get('Groups.Groups');
        $this->Groups = $table;

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

    public function testMain(): void
    {
        $group = $this->Groups->find()->where(['name' => 'Admins'])->first();
        $this->assertFalse(empty($group), "No Admins group found");
        $this->assertTrue(is_object($group), "Admins group is not an object");
        if (!empty($group) && is_object($group)) {
            $this->Groups->delete($group);
        }

        $group = $this->Groups->find()->where(['name' => 'Everyone'])->first();
        $this->assertFalse(empty($group), "No Everyone group found");
        $this->assertTrue(is_object($group), "Everyone group is not an object");
        if (!empty($group) && is_object($group)) {
            $this->Groups->delete($group);
        }

        $this->Task->main();

        $query = $this->Groups->find()->where(['name' => 'Admins']);
        $this->assertFalse($query->isEmpty());

        $query = $this->Groups->find()->where(['name' => 'Everyone']);
        $this->assertFalse($query->isEmpty());
    }
}
