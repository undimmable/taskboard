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

    public function testSignupAuthorizedUserReturnsForbidden()
    {
        $this->login('dummy@dummy.com', '123456');
        $credentials = [
            'email' => 'dummy@dummy.com',
            'password' => '123456',
            'password_repeat' => '123456',
            'csrf_token' => '8',
            'is_customer' => 'on'
        ];
        $response = $this->api->request(
            'POST',
            'auth/signup',
            [
                'json' => $credentials,
                'headers' => [
                    'X-CSRF-TOKEN' => 8
                ],
                'cookies' => $this->jar
            ]
        );
        $this->assertResponseForbidden($response);
        $this->assertResponseError($response, "unspecified", "forbidden");
    }

    public function testLoginAuthorizedUserReturnsForbidden()
    {
        $this->login('dummy@dummy.com', '123456');
        $credentials = [
            'email' => 'dummy@dummy.com',
            'password' => '123456',
            'csrf_token' => '9',
        ];
        $response = $this->api->request(
            'POST',
            'auth/login',
            [
                'json' => $credentials,
                'headers' => [
                    'X-CSRF-TOKEN' => 9
                ],
                'cookies' => $this->jar
            ]
        );
        $this->assertResponseForbidden($response);
        $this->assertResponseError($response, "unspecified", "forbidden");
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
        $response = $this->api->request(
            'POST',
            'auth/signup?XDEBUG_SESSION_START=PHPStorm_Remote',
            [
                'json' => $credentials,
                'headers' => [
                    'X-CSRF-TOKEN' => 8
                ],
                'cookies' => $this->jar
            ]
        );
        $this->assertResponseConflict($response);
        $this->assertResponseError($response, "email", "already_registered");
        $this->assertNoCookie(ApiIntegrationTest::$authorizationCookieName);
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
        $response = $this->api->request(
            'POST',
            'auth/signup',
            [
                'json' => $credentials,
                'headers' => [
                    'X-CSRF-TOKEN' => 0
                ],
                'cookies' => $this->jar
            ]
        );
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "unspecified", "token_wrong");
        $this->assertNoCookie(ApiIntegrationTest::$authorizationCookieName);
    }

    public function testSignupMissingPasswordRepeatReturnsBadRequest()
    {
        $credentials = [
            'email' => 'dummy@dummy.com',
            'password' => '123456',
            'csrf_token' => '8',
            'is_customer' => 'on'
        ];
        $response = $this->api->request(
            'POST',
            'auth/signup',
            [
                'json' => $credentials,
                'headers' => [
                    'X-CSRF-TOKEN' => 8
                ],
                'cookies' => $this->jar
            ]
        );
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "password_repeat", "not_provided");
        $this->assertNoCookie(ApiIntegrationTest::$authorizationCookieName);
    }

    public function testSignupMissingPasswordAndPasswordRepeatReturnsBadRequest()
    {
        $credentials = [
            'email' => 'dummy@dummy.com',
            'csrf_token' => '8',
            'is_customer' => 'on'
        ];
        $response = $this->api->request(
            'POST',
            'auth/signup',
            [
                'json' => $credentials,
                'headers' => [
                    'X-CSRF-TOKEN' => 8
                ],
                'cookies' => $this->jar
            ]
        );
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "password", "not_provided");
        $this->assertNoCookie(ApiIntegrationTest::$authorizationCookieName);
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
        $response = $this->api->request(
            'POST',
            'auth/signup',
            [
                'json' => $credentials,
                'headers' => [
                    'X-CSRF-TOKEN' => 8
                ],
                'cookies' => $this->jar
            ]
        );
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "password", "mismatch");
        $this->assertNoCookie(ApiIntegrationTest::$authorizationCookieName);
    }

    public function testSignupMissingPasswordReturnsBadRequest()
    {
        $credentials = [
            'email' => 'missing@dummy.com',
            'password_repeat' => '123456',
            'csrf_token' => '8',
            'is_customer' => 'on'
        ];
        $response = $this->api->request(
            'POST', 'auth/signup',
            [
                'json' => $credentials,
                'headers' => [
                    'X-CSRF-TOKEN' => 8
                ],
                'cookies' => $this->jar
            ]
        );
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "password", "not_provided");
        $this->assertNoCookie(ApiIntegrationTest::$authorizationCookieName);
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
        $response = $this->api->request(
            'POST',
            'auth/signup',
            [
                'json' => $credentials, 'headers' => [
                'X-CSRF-TOKEN' => 8
            ],
                'cookies' => $this->jar
            ]
        );
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "password", "is_too_short");
        $this->assertNoCookie(ApiIntegrationTest::$authorizationCookieName);
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
        $response = $this->api->request(
            'POST',
            'auth/signup',
            [
                'json' => $credentials,
                'headers' => [
                    'X-CSRF-TOKEN' => 8
                ],
                'cookies' => $this->jar
            ]
        );
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "email", "is_invalid");
        $this->assertNoCookie(ApiIntegrationTest::$authorizationCookieName);
    }

    public function testSignupMissingCsrfTokenReturnsUnauthorized()
    {
        $credentials = [
            'email' => 'missing@dummy.com',
            'password' => '123456',
            'password_repeat' => '123456',
            'is_customer' => 'on'
        ];
        $response = $this->api->request(
            'POST',
            'auth/signup',
            [
                'json' => $credentials,
                'cookies' => $this->jar
            ]
        );
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "unspecified", "token_wrong");
        $this->assertNoCookie(ApiIntegrationTest::$authorizationCookieName);
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
        $response = $this->api->request(
            'POST',
            'auth/signup',
            [
                'json' => $credentials,
                'headers' => [
                    'X-CSRF-TOKEN' => 8
                ],
                'cookies' => $this->jar
            ]
        );
        $this->assertResponseOk($response);
        $this->assertCookiePresent(ApiIntegrationTest::$authorizationCookieName);
        $this->assertCookieNotDeleted(ApiIntegrationTest::$authorizationCookieName);
    }

    public function testLoginExistingUserReturnsToken()
    {
        $credentials = [
            'email' => 'dummy@dummy.com',
            'password' => '123456',
            'csrf_token' => '9'
        ];
        $response = $this->api->request(
            'POST',
            'auth/login',
            [
                'json' => $credentials,
                'headers' => [
                    'X-CSRF-TOKEN' => 9
                ],
                'cookies' => $this->jar
            ]
        );
        $this->assertResponseOk($response);
        $this->assertCookiePresent(ApiIntegrationTest::$authorizationCookieName);
        $this->assertCookieHttpOnly(ApiIntegrationTest::$authorizationCookieName);
        $this->assertCookieSecure(ApiIntegrationTest::$authorizationCookieName);
        $this->assertCookieHasValue(ApiIntegrationTest::$authorizationCookieName);
    }

    public function testLoginMissingUserReturnsUnauthorized()
    {
        $credentials = [
            'email' => 'missing@dummy.com',
            'password' => '123456',
            'csrf_token' => '9'
        ];
        $response = $this->api->request(
            'POST',
            'auth/login',
            [
                'json' => $credentials,
                'headers' => [
                    'X-CSRF-TOKEN' => 9
                ],
                'cookies' => $this->jar
            ]
        );
        $this->assertResponseUnauthorized($response);
        $this->assertResponseError($response, "email", "no_such_user");
        $this->assertNoCookie(ApiIntegrationTest::$authorizationCookieName);
    }

    public function testLoginExistingUserWrongCsrfTokenReturnsUnauthorized()
    {
        $credentials = [
            'email' => 'dummy@dummy.com',
            'password' => '123456',
            'csrf_token' => '5'
        ];
        $response = $this->api->request(
            'POST',
            'auth/login', [
                'json' => $credentials,
                'headers' => [
                    'X-CSRF-TOKEN' => 5
                ],
                'cookies' => $this->jar
            ]
        );
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "unspecified", "token_wrong");
        $this->assertNoCookie(ApiIntegrationTest::$authorizationCookieName);
    }

    public function testLoginExistingUserMissingCsrfTokenReturnsUnauthorized()
    {
        $credentials = [
            'email' => 'dummy@dummy.com',
            'password' => '123456'
        ];
        $response = $this->api->request(
            'POST',
            'auth/login',
            [
                'json' => $credentials,
                'cookies' => $this->jar
            ]
        );
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "unspecified", "token_wrong");
        $this->assertNoCookie(ApiIntegrationTest::$authorizationCookieName);
    }

    public function testLoginNonExistingUserWrongTokenReturnsUnauthorized()
    {
        $credentials = [
            'email' => 'missing@dummy.com',
            'password' => '123456',
            'csrf_token' => '5'
        ];
        $response = $this->api->request(
            'POST',
            'auth/login',
            [
                'json' => $credentials,
                'headers' => [
                    'X-CSRF-TOKEN' => 5
                ],
                'cookies' => $this->jar
            ]
        );
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "unspecified", "token_wrong");
        $this->assertNoCookie(ApiIntegrationTest::$authorizationCookieName);
    }

    public function testLoginMissingEmailReturnsBadRequest()
    {
        $credentials = [
            'password' => '123456',
            'csrf_token' => '9'
        ];
        $response = $this->api->request(
            'POST',
            'auth/login',
            [
                'json' => $credentials, 'headers' => [
                'X-CSRF-TOKEN' => 9
            ],
                'cookies' => $this->jar
            ]
        );
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "email", "not_provided");
        $this->assertNoCookie(ApiIntegrationTest::$authorizationCookieName);
    }

    public function testLoginMissingPasswordReturnsBadRequest()
    {
        $credentials = [
            'email' => 'missing@dummy.com',
            'csrf_token' => '9'
        ];
        $response = $this->api->request(
            'POST',
            'auth/login',
            [
                'json' => $credentials,
                'headers' => [
                    'X-CSRF-TOKEN' => 9
                ],
                'cookies' => $this->jar
            ]
        );
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "password", "not_provided");
        $this->assertNoCookie(ApiIntegrationTest::$authorizationCookieName);
    }

    public function testLoginMissingPasswordAndEmailReturnsBadRequest()
    {
        $credentials = [
            'csrf_token' => '9'
        ];
        $response = $this->api->request(
            'POST',
            'auth/login',
            [
                'json' => $credentials,
                'headers' => [
                    'X-CSRF-TOKEN' => 9
                ], 'cookies' => $this->jar
            ]
        );
        $this->assertResponseBadRequest($response);
        $this->assertResponseError($response, "password", "not_provided");
        $this->assertResponseError($response, "email", "not_provided");
        $this->assertNoCookie(ApiIntegrationTest::$authorizationCookieName);
    }

    public function testLogoutNotLoggedInReturnUnauthorized()
    {
        $response = $this->api->request(
            'GET',
            'auth/logout',
            [
                'cookies' => $this->jar
            ]
        );
        $this->assertResponseForbidden($response);
        $this->assertResponseError($response, "unspecified", "forbidden");
        $this->assertNoCookie(ApiIntegrationTest::$authorizationCookieName);
    }

    public function testLogoutLoggedInReturnsRedirect()
    {
        $this->login('dummy@dummy.com', '123456');
        $response = $this->api->request(
            'GET',
            'auth/logout',
            [
                'cookies' => $this->jar
            ]
        );
        $this->assertResponseOk($response);
        $this->assertCookiePresent(ApiIntegrationTest::$authorizationCookieName);
        $this->assertCookieHttpOnly(ApiIntegrationTest::$authorizationCookieName);
        $this->assertCookieSecure(ApiIntegrationTest::$authorizationCookieName);
        $this->assertCookieHasValue(ApiIntegrationTest::$authorizationCookieName);
        $this->assertCookieDeleted(ApiIntegrationTest::$authorizationCookieName);
    }

    public function tearDown()
    {
        $this->util->deleteAllCreatedEntities();
        parent::tearDown();
    }
}