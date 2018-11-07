<?php
namespace Groups\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Groups\Model\Table\GroupsTable;

/**
 * Groups\Model\Table\GroupsTable Test Case
 *
 * @property \Groups\Model\Table\GroupsTable $Groups
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
        'plugin.groups.groups_users',
        'plugin.CakeDC/Users.users',
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
        /**
         * @var \Groups\Model\Table\GroupsTable $table
         */
        $table = TableRegistry::get('Groups', $config);
        $this->Groups = $table;
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
    public function testInitialize(): void
    {
        $this->assertTrue($this->Groups->hasBehavior('Timestamp'));
        $this->assertTrue($this->Groups->hasBehavior('Trash'));
        $this->assertInstanceOf(BelongsToMany::class, $this->Groups->getAssociation('Users'));
        $this->assertInstanceOf(GroupsTable::class, $this->Groups);
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $validator = new \Cake\Validation\Validator();
        $result = $this->Groups->validationDefault($validator);

        $this->assertInstanceOf('\Cake\Validation\Validator', $result);

        $data = ['name' => 'Foobar'];

        $entity = $this->Groups->newEntity($data);
        $this->assertEmpty($entity->getErrors());
    }

    public function testSave(): void
    {
        $data = ['name' => 'Foobar', 'description' => 'Foobar group', 'deny_edit' => false, 'deny_delete' => false];

        $entity = $this->Groups->newEntity($data);
        $result = $this->Groups->save($entity);
        $this->assertTrue(is_object($result), "Result is not an entity");
        if (is_object($result)) {
            $this->assertNotEmpty($result->get('id'));
        }
    }

    public function testGetUserGroups(): void
    {
        $result = $this->Groups->getUserGroups('00000000-0000-0000-0000-000000000001');

        $this->assertInternalType('array', $result);
        $this->assertEquals(1, count($result));
    }

    public function testGetUserGroupsAll(): void
    {
        $userId = '00000000-0000-0000-0000-000000000001';

        $result = $this->Groups->getUserGroupsAll($userId);

        $this->assertInternalType('array', $result);
        $this->assertEquals(1, count($result));
        $this->assertInstanceOf('Groups\Model\Entity\Group', $result[0]);
    }

    public function testGetRemoteGroupsDummyConfig(): void
    {
        Configure::write('Groups.remoteGroups.enabled', true);
        Configure::write('Groups.remoteGroups.LDAP', [
            'enabled' => true,
            'host' => 'ldaps://127.0.0.1',
            'port' => 987,
            'version' => 3,
            'domain' => 'foobar',
            'baseDn' => '',
            'username' => 'foo',
            'password' => 'foo',
            'groupsFilter' => '(objectclass=group)'
        ]);
        $result = $this->Groups->getRemoteGroups();

        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

    public function testGetRemoteGroupsNotEnabled(): void
    {
        $result = $this->Groups->getRemoteGroups();

        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

    public function testSaveGroupWithExistingName(): void
    {
        $entity = $this->Groups->newEntity();
        $entity = $this->Groups->patchEntity($entity, ['name' => 'Lorem ipsum dolor sit amet']);
        $result = $this->Groups->save($entity);
        $this->assertTrue(is_bool($result), "Result is not a boolean");
        if (is_bool($result)) {
            $this->assertFalse($result);
        }
        $this->assertNotEmpty($entity->getErrors());
    }
}
