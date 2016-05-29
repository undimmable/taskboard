<?php


class LoginIntegrationTest extends ApiIntegrationTest
{
    public function setUp()
    {
        parent::setUp();
        $this->util->createUser("dummy@dummy.com", "123456", get_role_key(CUSTOMER));
    }

    public function testLoginExistingUserShouldReturnToken()
    {
        $credentials = [
            'email' => 'dummy@dummy.com',
            'password' => '123456',
            'is_customer' => 'on',
            'csrf_token' => '8'
        ];
        $response = $this->api->post('auth/login', ['http_errors' => false], ['form_params' => $credentials]);
        $this->assertEquals(415, $response->getStatusCode());
    }

    public function tearDown()
    {
        $this->util->deleteAllCreatedEntities();
        parent::tearDown();
    }
}