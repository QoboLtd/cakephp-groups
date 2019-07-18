<?php
namespace Groups\Test\TestCase\Shell\Task;

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

    /**
     * @var \Groups\Shell\Task\ImportTask
     */
    private $Task;

    public function setUp()
    {
        parent::setUp();

        /**
         * @var \Groups\Model\Table\GroupsTable $table
         */
        $table = TableRegistry::get('Groups.Groups');
        $this->Groups = $table;

        /** @var \Cake\Console\ConsoleIo */
        $io = $this->getMockBuilder('Cake\Console\ConsoleIo')->getMock();

        $this->Task = new ImportTask($io);
    }

    public function tearDown()
    {
        unset($this->Groups);
        unset($this->Task);

        parent::tearDown();
    }

    /**
     * @dataProvider groupsProvider
     * @param mixed[] $data Group data
     */
    public function testMain(array $data): void
    {
        $this->Groups->deleteAll([]);


        $this->Task->main();

        $query = $this->Groups->find()->where(['name' => $data['name']]);
        $this->assertSame(1, $query->count());

        $group = $query->firstOrFail()->toArray();
        $this->assertSame([], array_diff_assoc($data, $group));
        $initialModifiedDate = $group['modified'];

        $this->Groups->updateAll(['description' => 'Some random description ' . uniqid()], []);

        // sleeping so we can capture the modified time diff.
        sleep(1);

        $this->Task->main();

        $updated = $this->Groups->find()
            ->where(['name' => $data['name']])
            ->firstOrFail()
            ->toArray();

        $data['deny_edit'] ?
            $this->assertTrue($updated['modified']->getTimestamp() === $initialModifiedDate->getTimestamp()) :
            $this->assertTrue($updated['modified']->getTimestamp() > $initialModifiedDate->getTimestamp());

        $this->assertSame([], array_diff_assoc($data, $updated));
    }

    /**
     * @return mixed[]
     */
    public function groupsProvider() : array
    {
        return [
            [['name' => 'Admins', 'deny_edit' => false, 'deny_delete' => true]],
            [['name' => 'Everyone', 'deny_edit' => true, 'deny_delete' => true]]
        ];
    }
}
