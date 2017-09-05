<?php
namespace Groups\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Groups\Controller\Component\GroupComponent;

/**
 * Groups\Controller\Component\GroupComponent Test Case
 */
class GroupComponentTest extends TestCase
{
    public $fixtures = [
        'plugin.groups.groups',
        'plugin.groups.groups_users',
        'plugin.CakeDC/Users.users',
    ];

    public function setUp()
    {
        parent::setUp();

        // Setup our component and fake test controller
        $request = new ServerRequest();
        $response = new Response();
        $controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setConstructorArgs([$request, $response])
            ->setMethods(null)
            ->getMock();
        $registry = new ComponentRegistry($controller);
        $this->GroupComponent = new GroupComponent($registry);
    }

    public function tearDown()
    {
        unset($this->GroupComponent);

        parent::tearDown();
    }

    public function testGetUserGroups()
    {
        $result = $this->GroupComponent->getUserGroups('00000000-0000-0000-0000-000000000001');

        $this->assertInternalType('array', $result);
        $this->assertEquals(1, count($result));
    }

    public function testGetUserGroupsWithoutUserId()
    {
        $this->GroupComponent->Auth->setUser(['id' => '00000000-0000-0000-0000-000000000001']);

        $result = $this->GroupComponent->getUserGroups();

        $this->assertInternalType('array', $result);
        $this->assertEquals(1, count($result));
    }
}
