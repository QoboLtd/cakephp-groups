<?php
namespace Groups\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Groups\Model\Table\GroupsTable;

/**
 * Groups\Model\Table\GroupsTable Test Case
 */
class GroupsTableTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.groups.groups',
        'plugin.groups.users',
        'plugin.groups.groups_users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Groups') ? [] : ['className' => 'Groups\Model\Table\GroupsTable'];
        $this->Groups = TableRegistry::get('Groups', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Groups);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $validator = new \Cake\Validation\Validator();
        $result = $this->Groups->validationDefault($validator);

        $this->assertInstanceOf('\Cake\Validation\Validator', $result);

        $data = ['name' => 'Foobar'];

        $entity = $this->Groups->newEntity($data);
        $this->assertEmpty($entity->errors());
    }

    public function testSave()
    {
        $data = ['name' => 'Foobar'];

        $entity = $this->Groups->newEntity($data);
        $result = $this->Groups->save($entity);

        $this->assertNotEmpty($result->get('id'));
    }

    public function testGetUserGroups()
    {
        $userId = '00000000-0000-0000-0000-000000000001';
        $result = $this->Groups->getUserGroups($userId);
        $values = array_values($result);

        $this->assertTrue(is_array($result));
        $this->assertNotEmpty($result);
        $this->assertTrue(!is_object($values[0]));
    }

    public function testGetUserGroupsAll()
    {
        $userId = '00000000-0000-0000-0000-000000000001';

        $result = $this->Groups->getUserGroupsAll($userId);
        $values = array_values($result);

        $this->assertNotEmpty($result);
        $this->assertTrue(is_array($result));
        $this->assertTrue(is_object($values[0]));
    }
}
