<?php
namespace Taskboards;

/** @noinspection PhpIncludeInspection */
use GuzzleHttp\Cookie\SetCookie;

require 'api_integration_test.php';

class AuthIntegrationTest extends ApiIntegrationTest
{
    private static $authorizationCookieName = 'PRIVATE-TOKEN';

    public function setUp()
    {
        parent::setUp();
        $this->util->createUser("dummy@dummy.com", "123456", 2, false);
    }

    public function testSignupAuthorizedUserReturnsForbidden()
    {
        $this->authorize('dummy@dummy.com', '123456');
        $credentials = [
            'email' => 'dummy@dummy.com',
            'password' => '123456',
            'password_repeat' => '123456',
            'csrf_token' => '8',
            'is_customer' => 'on'
        ];
        $response = $this->api->post('auth/signup?XDEBUG_SESSION_START=PHPStorm_Remote', ['json' => $credentials, 'headers' => [
            'X-CSRF-TOKEN' => 8
        ]]);
        $this->assertResponseForbidden($response);
        $this->assertResponseError($response, "reason", "Forbidden");
    }

    public function testLoginAuthorizedUserReturnsForbidden()
    {
        $this->authorize('dummy@dummy.com', '123456');
        $credentials = [
            'email' => 'dummy@dummy.com',
            'password' => '123456',
            'csrf_token' => '9',
        ];
        $response = $this->api->post('auth/login', ['json' => $credentials, 'headers' => [
            'X-CSRF-TOKEN' => 9
        ]]);
        $this->assertResponseForbidden($response);
        $this->assertResponseError($response, "reason", "Forbidden");
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
        $response = $this->api->post('auth/signup', ['json' => $credentials, 'headers' => [
            'X-CSRF-TOKEN' => 8
        ]]);
        $this->assertResponseConflict($response);
        $this->assertResponseError($response, "email", "User with this email already registered");
        $this->assertNoCookie(AuthIntegrationTest::$authorizationCookieName);
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
        $response = $this->api->post('auth/signup', ['json' => $credentials, 'headers' => [
            'X-CSRF-TOKEN' => 0
        ]]);
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "signup_csrf_token", "wrong");
        $this->assertNoCookie(AuthIntegrationTest::$authorizationCookieName);
    }

    public function testSignupMissingPasswordRepeatReturnsBadRequest()
    {
        $credentials = [
            'email' => 'dummy@dummy.com',
            'password' => '123456',
            'csrf_token' => '8',
            'is_customer' => 'on'
        ];
        $response = $this->api->post('auth/signup', ['json' => $credentials, 'headers' => [
            'X-CSRF-TOKEN' => 8
        ]]);
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "password_repeat", "Password repeat not provided");
        $this->assertNoCookie(AuthIntegrationTest::$authorizationCookieName);
    }

    public function testSignupMissingPasswordAndPasswordRepeatReturnsBadRequest()
    {
        $credentials = [
            'email' => 'dummy@dummy.com',
            'csrf_token' => '8',
            'is_customer' => 'on'
        ];
        $response = $this->api->post('auth/signup', ['json' => $credentials, 'headers' => [
            'X-CSRF-TOKEN' => 8
        ]]);
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "password", "Password not provided");
        $this->assertNoCookie(AuthIntegrationTest::$authorizationCookieName);
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
        $response = $this->api->post('auth/signup', ['json' => $credentials, 'headers' => [
            'X-CSRF-TOKEN' => 8
        ]]);
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "password", "Passwords don't match");
        $this->assertNoCookie(AuthIntegrationTest::$authorizationCookieName);
    }

    public function testSignupMissingPasswordReturnsBadRequest()
    {
        $credentials = [
            'email' => 'missing@dummy.com',
            'password_repeat' => '123456',
            'csrf_token' => '8',
            'is_customer' => 'on'
        ];
        $response = $this->api->post('auth/signup', ['json' => $credentials, 'headers' => [
            'X-CSRF-TOKEN' => 8
        ]]);
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "password", "Password not provided");
        $this->assertNoCookie(AuthIntegrationTest::$authorizationCookieName);
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
        $response = $this->api->post('auth/signup', ['json' => $credentials, 'headers' => [
            'X-CSRF-TOKEN' => 8
        ]]);
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "password", "Password is too short");
        $this->assertNoCookie(AuthIntegrationTest::$authorizationCookieName);
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
        $response = $this->api->post('auth/signup', ['json' => $credentials, 'headers' => [
            'X-CSRF-TOKEN' => 8
        ]]);
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "email", "Email is invalid");
        $this->assertNoCookie(AuthIntegrationTest::$authorizationCookieName);
    }

    public function testSignupMissingCsrfTokenReturnsUnauthorized()
    {
        $credentials = [
            'email' => 'missing@dummy.com',
            'password' => '123456',
            'password_repeat' => '123456',
            'is_customer' => 'on'
        ];
        $response = $this->api->post('auth/signup', ['json' => $credentials]);
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "signup_csrf_token", "wrong");
        $this->assertNoCookie(AuthIntegrationTest::$authorizationCookieName);
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
        $response = $this->api->post('auth/signup', ['json' => $credentials, 'headers' => [
            'X-CSRF-TOKEN' => 8
        ]]);
        $this->assertResponseOk($response);
        $this->assertCookiePresent(AuthIntegrationTest::$authorizationCookieName);
        $this->assertCookieHttpOnly(AuthIntegrationTest::$authorizationCookieName);
        $this->assertCookieSecure(AuthIntegrationTest::$authorizationCookieName);
        $this->assertCookieHasValue(AuthIntegrationTest::$authorizationCookieName);
    }

    public function testLoginExistingUserReturnsToken()
    {
        $credentials = [
            'email' => 'dummy@dummy.com',
            'password' => '123456',
            'csrf_token' => '9'
        ];
        $response = $this->api->post('auth/login', ['json' => $credentials, 'headers' => [
            'X-CSRF-TOKEN' => 9
        ]]);
        $this->assertResponseOk($response);
        $this->assertCookiePresent(AuthIntegrationTest::$authorizationCookieName);
        $this->assertCookieHttpOnly(AuthIntegrationTest::$authorizationCookieName);
        $this->assertCookieSecure(AuthIntegrationTest::$authorizationCookieName);
        $this->assertCookieHasValue(AuthIntegrationTest::$authorizationCookieName);
    }

    public function testLoginMissingUserReturnsUnauthorized()
    {
        $credentials = [
            'email' => 'missing@dummy.com',
            'password' => '123456',
            'csrf_token' => '9'
        ];
        $response = $this->api->post('auth/login', ['json' => $credentials, 'headers' => [
            'X-CSRF-TOKEN' => 9
        ]]);
        $this->assertResponseUnauthorized($response);
        $this->assertResponseError($response, "email", "Wrong username and/or password");
        $this->assertNoCookie(AuthIntegrationTest::$authorizationCookieName);
    }

    public function testLoginExistingUserWrongCsrfTokenReturnsUnauthorized()
    {
        $credentials = [
            'email' => 'dummy@dummy.com',
            'password' => '123456',
            'csrf_token' => '5'
        ];
        $response = $this->api->post('auth/login', ['json' => $credentials, 'headers' => [
            'X-CSRF-TOKEN' => 5
        ]]);
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "login_csrf_token", "wrong");
        $this->assertNoCookie(AuthIntegrationTest::$authorizationCookieName);
    }

    public function testLoginExistingUserMissingCsrfTokenReturnsUnauthorized()
    {
        $credentials = [
            'email' => 'dummy@dummy.com',
            'password' => '123456'
        ];
        $response = $this->api->post('auth/login', ['json' => $credentials]);
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "login_csrf_token", "wrong");
        $this->assertNoCookie(AuthIntegrationTest::$authorizationCookieName);
    }

    public function testLoginNonExistingUserWrongTokenReturnsUnauthorized()
    {
        $credentials = [
            'email' => 'missing@dummy.com',
            'password' => '123456',
            'csrf_token' => '5'
        ];
        $response = $this->api->post('auth/login', ['json' => $credentials, 'headers' => [
            'X-CSRF-TOKEN' => 5
        ]]);
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "login_csrf_token", "wrong");
        $this->assertNoCookie(AuthIntegrationTest::$authorizationCookieName);
    }

    public function testLoginMissingEmailReturnsBadRequest()
    {
        $credentials = [
            'password' => '123456',
            'csrf_token' => '9'
        ];
        $response = $this->api->post('auth/login', ['json' => $credentials, 'headers' => [
            'X-CSRF-TOKEN' => 9
        ]]);
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "email", "Email not provided");
        $this->assertNoCookie(AuthIntegrationTest::$authorizationCookieName);
    }

    public function testLoginMissingPasswordReturnsBadRequest()
    {
        $credentials = [
            'email' => 'missing@dummy.com',
            'csrf_token' => '9'
        ];
        $response = $this->api->post('auth/login', ['json' => $credentials, 'headers' => [
            'X-CSRF-TOKEN' => 9
        ]]);
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "password", "Password not provided");
        $this->assertNoCookie(AuthIntegrationTest::$authorizationCookieName);
    }

    public function testLoginMissingPasswordAndEmailReturnsBadRequest()
    {
        $credentials = [
            'csrf_token' => '9'
        ];
        $response = $this->api->post('auth/login', ['json' => $credentials, 'headers' => [
            'X-CSRF-TOKEN' => 9
        ]]);
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "password", "Password not provided");
        $this->assertResponseError($response, "email", "Email not provided");
        $this->assertNoCookie(AuthIntegrationTest::$authorizationCookieName);
    }

    public function testLogoutNotLoggedInReturnUnauthorized()
    {
        $response = $this->api->get('auth/logout');
        $this->assertResponseUnauthorized($response);
        $this->assertResponseError($response, "reason", "Not authorized");
        $this->assertNoCookie(AuthIntegrationTest::$authorizationCookieName);
    }

    public function testLogoutLoggedInReturnsRedirect()
    {
        $this->authorize('dummy@dummy.com', '123456');
        $response = $this->api->get('auth/logout');
        $this->assertResponseOk($response);
        $this->assertCookiePresent(AuthIntegrationTest::$authorizationCookieName);
        $this->assertCookieHttpOnly(AuthIntegrationTest::$authorizationCookieName);
        $this->assertCookieSecure(AuthIntegrationTest::$authorizationCookieName);
        $this->assertCookieHasValue(AuthIntegrationTest::$authorizationCookieName);
        $this->assertCookieDeleted(AuthIntegrationTest::$authorizationCookieName);
    }

    public function tearDown()
    {
        $this->util->deleteAllCreatedEntities();
        parent::tearDown();
    }
}