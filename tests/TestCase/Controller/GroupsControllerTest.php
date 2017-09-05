<?php
namespace Groups\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\ORM\Query;
use Cake\ORM\ResultSet;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;
use Groups\Controller\GroupsController;
use Groups\Model\Entity\Group;

/**
 * Groups\Controller\GroupsController Test Case
 */
class GroupsControllerTest extends IntegrationTestCase
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

        // Run all tests as authenticated user
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000001']);

        // Load default plugin configuration
        Configure::load('Groups.groups');

        $this->enableRetainFlashMessages();
    }

    public function tearDown()
    {
        unset($this->Groups);

        parent::tearDown();
    }

    public function testIndex()
    {
        $this->get('/groups/groups');

        $this->assertResponseOk();

        $groups = $this->viewVariable('groups');
        $this->assertInstanceOf(ResultSet::class, $groups);
        $this->assertEquals(1, $groups->count());
    }

    public function testView()
    {
        $id = '00000000-0000-0000-0000-000000000001';
        $this->get('/groups/groups/view/' . $id);

        $this->assertResponseOk();

        $group = $this->viewVariable('group');
        $this->assertInstanceOf(Group::class, $group);
    }

    public function testAdd()
    {
        $expected = 1 + $this->Groups->find('all')->count();

        $data = ['name' => 'Test group'];

        $this->post('/groups/groups/add', $data);

        $url = [
            'plugin' => 'Groups',
            'controller' => 'Groups',
            'action' => 'index'
        ];
        $this->assertRedirect($url);

        $this->assertEquals($expected, $this->Groups->find('all')->count());
    }

    public function testAddExistingName()
    {
        $data = ['name' => 'Lorem ipsum dolor sit amet'];

        $this->post('/groups/groups/add', $data);

        $this->assertSession('The group could not be saved. Please, try again.', 'Flash.flash.0.message');
    }

    public function testAddGet()
    {
        $this->get('/groups/groups/add');

        $this->assertResponseOk();

        $group = $this->viewVariable('group');
        $this->assertInstanceOf(Group::class, $group);

        $users = $this->viewVariable('users');
        $this->assertInstanceOf(Query::class, $users);
        $this->assertFalse($users->isEmpty());

        $remoteGroups = $this->viewVariable('remoteGroups');
        $this->assertInternalType('array', $remoteGroups);
    }

    public function testEdit()
    {
        $id = '00000000-0000-0000-0000-000000000001';

        $entity = $this->Groups->get($id);

        $data = ['name' => 'Test group'];

        $this->put('/groups/groups/edit/' . $id, $data);

        $url = [
            'plugin' => 'Groups',
            'controller' => 'Groups',
            'action' => 'index'
        ];
        $this->assertRedirect($url);

        $result = $this->Groups->get($id);
        $this->assertEquals($entity->get('id'), $result->get('id'));
        $this->assertNotEquals($entity->get('name'), $result->get('name'));
    }

    public function testEditGet()
    {
        $id = '00000000-0000-0000-0000-000000000001';

        $this->get('/groups/groups/edit/' . $id);

        $this->assertResponseOk();

        $group = $this->viewVariable('group');
        $this->assertInstanceOf(Group::class, $group);

        $users = $this->viewVariable('users');
        $this->assertInstanceOf(Query::class, $users);
        $this->assertFalse($users->isEmpty());

        $remoteGroups = $this->viewVariable('remoteGroups');
        $this->assertInternalType('array', $remoteGroups);
    }

    public function testDelete()
    {
        $expected = 1 - $this->Groups->find('all')->count();

        $id = '00000000-0000-0000-0000-000000000001';

        $this->delete('/groups/groups/delete/' . $id);

        $url = [
            'plugin' => 'Groups',
            'controller' => 'Groups',
            'action' => 'index'
        ];
        $this->assertRedirect($url);

        $this->assertEquals($expected, $this->Groups->find('all')->count());
    }
}
