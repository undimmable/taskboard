<?php
namespace Taskboards;
/** @noinspection PhpIncludeInspection */
require 'api_integration_test.php';

class AuthIntegrationTest extends ApiIntegrationTest
{
    public function setUp()
    {
        parent::setUp();
        $this->util->createUser("dummy@dummy.com", "123456", 2, false);
    }

    public function testSignupExistingUserReturnsConflict()
    {
        $credentials = [
            'email' => 'dummy@dummy.com',
            'password' => '123456',
            'password_repeat' => '123456',
            'csrf_token' => '8',
            'is_customer' => 'on'
        ];
        $response = $this->api->post('auth/signup', ['form_params' => $credentials]);
        $this->assertResponseConflict($response);
        $this->assertResponseError($response, "email", "User with this email already registered");
    }

    public function testSignupWrongCsrfTokenReturnsUnauthorized()
    {
        $credentials = [
            'email' => 'dummy@dummy.com',
            'password' => '123456',
            'password_repeat' => '123456',
            'csrf_token' => '0',
            'is_customer' => 'on'
        ];
        $response = $this->api->post('auth/signup', ['form_params' => $credentials]);
        $this->assertResponseUnauthorized($response);
        $this->assertResponseError($response, "reason", "Not authorized");
    }

    public function testSignupMissingUserReturnsOk()
    {
        $credentials = [
            'email' => 'dummy1@dummy.com',
            'password' => '123456',
            'password_repeat' => '123456',
            'csrf_token' => '8',
            'is_customer' => 'on'
        ];
        $response = $this->api->post('auth/signup', ['form_params' => $credentials]);
        $this->assertResponseOk($response);
    }

    public function testLoginExistingUserReturnsToken()
    {
        $credentials = [
            'email' => 'dummy@dummy.com',
            'password' => '123456',
            'csrf_token' => '9'
        ];
        $response = $this->api->post('auth/login', ['form_params' => $credentials]);
        $this->assertResponseOk($response);
    }

    public function testLoginMissingUserReturnsUnauthorized()
    {
        $credentials = [
            'email' => 'missing@dummy.com',
            'password' => '123456',
            'csrf_token' => '9'
        ];
        $response = $this->api->post('auth/login', ['form_params' => $credentials]);
        $this->assertResponseUnauthorized($response);
        $this->assertResponseError($response, "email", "Wrong username and/or password");
    }

    public function testLoginExistingUserWrongTokenReturnsUnauthorized()
    {
        $credentials = [
            'email' => 'dummy@dummy.com',
            'password' => '123456',
            'csrf_token' => '5'
        ];
        $response = $this->api->post('auth/login', ['form_params' => $credentials]);
        $this->assertResponseUnauthorized($response);
        $this->assertResponseError($response, "reason", "Not authorized");
    }

    public function testLoginMissingUserWrongTokenReturnsUnauthorized()
    {
        $credentials = [
            'email' => 'missing@dummy.com',
            'password' => '123456',
            'csrf_token' => '5'
        ];
        $response = $this->api->post('auth/login', ['form_params' => $credentials]);
        $this->assertResponseUnauthorized($response);
        $this->assertResponseError($response, "reason", "Not authorized");
    }

    public function tearDown()
    {
        $this->util->deleteAllCreatedEntities();
        parent::tearDown();
    }
}