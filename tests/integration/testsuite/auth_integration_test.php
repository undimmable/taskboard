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

    public function testSignupMissingPasswordRepeatReturnsBadRequest()
    {
        $credentials = [
            'email' => 'dummy@dummy.com',
            'password' => '123456',
            'csrf_token' => '8',
            'is_customer' => 'on'
        ];
        $response = $this->api->post('auth/signup', ['form_params' => $credentials]);
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "password_repeat", "Password repeat not provided");
    }

    public function testSignupMissingPasswordAndPasswordRepeatReturnsBadRequest()
    {
        $credentials = [
            'email' => 'dummy@dummy.com',
            'csrf_token' => '8',
            'is_customer' => 'on'
        ];
        $response = $this->api->post('auth/signup', ['form_params' => $credentials]);
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "password", "Password not provided");
    }

    public function testSignupMismatchPasswordRepeatReturnsBadRequest()
    {
        $credentials = [
            'email' => 'missing@dummy.com',
            'password' => '123456',
            'password_repeat' => '123451',
            'csrf_token' => '8',
            'is_customer' => 'on'
        ];
        $response = $this->api->post('auth/signup', ['form_params' => $credentials]);
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "password", "Passwords don't match");
    }

    public function testSignupMissingPasswordReturnsBadRequest()
    {
        $credentials = [
            'email' => 'missing@dummy.com',
            'password_repeat' => '123456',
            'csrf_token' => '8',
            'is_customer' => 'on'
        ];
        $response = $this->api->post('auth/signup', ['form_params' => $credentials]);
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "password", "Password not provided");
    }

    public function testSignupInvalidPasswordReturnsBadRequest()
    {
        $credentials = [
            'email' => 'missing@dummy.com',
            'password' => '1234',
            'password_repeat' => '1234',
            'csrf_token' => '8',
            'is_customer' => 'on'
        ];
        $response = $this->api->post('auth/signup', ['form_params' => $credentials]);
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "password", "Password is too short");
    }

    public function testSignupInvalidEmailReturnsBadRequest()
    {
        $credentials = [
            'email' => 'missing@w',
            'password' => '123456',
            'password_repeat' => '123456',
            'csrf_token' => '8',
            'is_customer' => 'on'
        ];
        $response = $this->api->post('auth/signup', ['form_params' => $credentials]);
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "email", "Email is invalid");
    }

    public function testSignupMissingCsrfTokenReturnsUnauthorized()
    {
        $credentials = [
            'email' => 'missing@dummy.com',
            'password' => '123456',
            'password_repeat' => '123456',
            'is_customer' => 'on'
        ];
        $response = $this->api->post('auth/signup', ['form_params' => $credentials]);
        $this->assertResponseUnauthorized($response);
        $this->assertResponseError($response, "reason", "Not authorized");
    }

    public function testSignupMissingUserReturnsOk()
    {
        $credentials = [
            'email' => 'missing@dummy.com',
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

    public function testLoginExistingUserWrongCsrfTokenReturnsUnauthorized()
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

    public function testLoginExistingUserMissingCsrfTokenReturnsUnauthorized()
    {
        $credentials = [
            'email' => 'dummy@dummy.com',
            'password' => '123456'
        ];
        $response = $this->api->post('auth/login', ['form_params' => $credentials]);
        $this->assertResponseUnauthorized($response);
        $this->assertResponseError($response, "reason", "Not authorized");
    }

    public function testLoginNonExistingUserWrongTokenReturnsUnauthorized()
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

    public function testLoginMissingEmailReturnsBadRequest()
    {
        $credentials = [
            'password' => '123456',
            'csrf_token' => '9'
        ];
        $response = $this->api->post('auth/login', ['form_params' => $credentials]);
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "email", "Email not provided");
    }

    public function testLoginMissingPasswordReturnsBadRequest()
    {
        $credentials = [
            'email' => 'missing@dummy.com',
            'csrf_token' => '9'
        ];
        $response = $this->api->post('auth/login', ['form_params' => $credentials]);
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "password", "Password not provided");
    }

    public function testLoginMissingPasswordAndEmailReturnsBadRequest()
    {
        $credentials = [
            'csrf_token' => '9'
        ];
        $response = $this->api->post('auth/login', ['form_params' => $credentials]);
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "password", "Password not provided");
        $this->assertResponseError($response, "email", "Email not provided");
    }

    public function tearDown()
    {
        $this->util->deleteAllCreatedEntities();
        parent::tearDown();
    }
}