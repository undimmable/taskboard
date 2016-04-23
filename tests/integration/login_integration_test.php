<?php

use \GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use \GuzzleHttp\Psr7\Response;

class LoginIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \GuzzleHttp\Client;
     */
    protected $api;

    public function setUp()
    {
        $this->api = new Client([
            'base_uri' => 'https://taskboard.dev/api/v1/',
            'timeout' => 0.25,
            'cookies' => true,
            'verify' => false
        ]);
    }

    public function tearDown()
    {
        unset($this->api);
    }


    public function testLoginWithUsernamePasswordShouldReturnToken()
    {
        $credentials = ['username' => 'validuser', 'password' => 'validuserpassword'];
        $response = $this->api->post('auth/login', ['form_params' => $credentials]);
        print_r($response->getHeaders());
        $body = $response->getBody();
        if ($body->getSize() > 0) {
            $rspSize = $body->getSize();
            echo $body->read($rspSize);
        } else
            echo "Empty body";
        $this->assertEquals($response->getStatusCode(), 200);
    }
}
