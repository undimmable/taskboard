<?php
namespace Taskboards;
/** @noinspection PhpIncludeInspection */
require 'api_integration_test.php';

class AuthIntegrationTest extends ApiIntegrationTest
{
    public function setUp()
    {
        parent::setUp();
        $this->util->createUser("dummy@dummy.com", "123456", 2, 0);
    }

    public function testLoginExistingUserShouldReturnToken()
    {
        $credentials = [
            'email' => 'dummy@dummy.com',
            'password' => '123456',
            'csrf_token' => '9'
        ];
        $response = $this->api->post('auth/login', ['form_params' => $credentials]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testLoginMissingUserShouldReturnUnauthorized()
    {
        $credentials = [
            'email' => 'missing@dummy.com',
            'password' => '123456',
            'csrf_token' => '9'
        ];
        $response = $this->api->post('auth/login', ['form_params' => $credentials]);
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testLoginExistingUserWrongTokenShouldReturnUnauthorized()
    {
        $credentials = [
            'email' => 'dummy@dummy.com',
            'password' => '123456',
            'csrf_token' => '5'
        ];
        $response = $this->api->post('auth/login', ['form_params' => $credentials]);
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testLoginMissingUserWrongTokenShouldReturnUnauthorized()
    {
        $credentials = [
            'email' => 'missing@dummy.com',
            'password' => '123456',
            'csrf_token' => '5'
        ];
        $response = $this->api->post('auth/login', ['form_params' => $credentials]);
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function tearDown()
    {
        $this->util->deleteAllCreatedEntities();
        parent::tearDown();
    }
}