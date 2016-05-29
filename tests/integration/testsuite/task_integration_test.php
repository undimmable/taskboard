<?php
/**
 * @author dimyriy
 * @version 1.0
 */

namespace integration\testsuite;


use Taskboards\ApiIntegrationTest;

class TaskIntegrationTest extends ApiIntegrationTest
{
    /**
     * @var \integer
     */
    private $system_id;
    /**
     * @var \integer
     */
    private $performer_id;
    /**
     * @var \integer
     */
    private $customer_id;

    public function setUp()
    {
        parent::setUp();
        $system_id = $this->util->createUser("system@dummy.com", "123456", 1, false);
        $customer_id = $this->util->createUser("customer@dummy.com", "123456", 2, false);
        $performer_id = $this->util->createUser("performer@dummy.com", "123456", 3, false);
        $this->util->createAccount($system_id, 100.0, 100.0);
        $this->util->createAccount($customer_id, 100.0, 100.0);
        $this->util->createAccount($performer_id, 100.0, 100.0);
    }

    public function testCreateNonAuthorizedReturnsUnauthorized()
    {
        $task = [
            'description' => 'Lorem ipsum',
            'amount' => 10.00,
            'csrf_token' => '10'
        ];
        $response = $this->api->post('task', ['form_params' => $task]);
        $this->assertResponseUnauthorized($response);
        $this->assertResponseError($response, "reason", "Not authorized");
    }

    public function testCreateTaskPerformerReturnsForbidden()
    {
        $this->authorize('performer@dummy.com', '123456');
        $task = [
            'description' => 'Lorem ipsum',
            'amount' => 10.00,
            'csrf_token' => '10'
        ];
        $response = $this->api->post('task', ['form_params' => $task]);
        $this->assertResponseForbidden($response);
        $this->assertResponseError($response, "reason", "Forbidden");
    }

    public function testCreateTaskSystemReturnsForbidden()
    {
        $this->authorize('system@dummy.com', '123456');
        $task = [
            'description' => 'Lorem ipsum',
            'amount' => 10.00,
            'csrf_token' => '10'
        ];
        $response = $this->api->post('task', ['form_params' => $task]);
        $this->assertResponseForbidden($response);
        $this->assertResponseError($response, "reason", "Forbidden");
    }

    public function testCreateTaskNotEnoughMoneyReturnsError()
    {
        $this->authorize('customer@dummy.com', '123456');
        $task = [
            'description' => 'Lorem ipsum',
            'amount' => 1000.00,
            'csrf_token' => '10'
        ];
        $response = $this->api->post('task', ['form_params' => $task]);
        $this->assertResponseConflict($response);
        $this->assertResponseError($response, "amount", "Not enough money");
    }

    public function tearDown()
    {
        $this->util->deleteAllCreatedEntities();
        parent::tearDown();
    }

}