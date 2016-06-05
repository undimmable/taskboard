<?php
namespace Taskboards;

use \GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;

require_once 'util.php';

abstract class ApiIntegrationTest extends \PHPUnit_Framework_TestCase
{
    protected static $authorizationCookieName = 'PRIVATE-TOKEN';
    /**
     * @var \GuzzleHttp\Client;
     */
    protected $api;
    /**
     * @var \GuzzleHttp\Cookie\CookieJar;
     */
    protected $jar;
    /**
     * @var \mysqli
     */
    protected $mysqli;
    /**
     * @var Util
     */
    protected $util;

    public function setUp()
    {
        parent::setUp();
        $this->mysqli = $this->createMysqlConnection();
        $this->util = new Util($this->mysqli);
        $this->jar = new CookieJar();
        $this->api = new Client([
            'connect_timeout' => 60,
            'timeout' => 60,
            'http_errors' => false,
            'base_uri' => getenv('HOST') . '/api/v1/',
            'cookies' => true,
            'verify' => false
        ]);
    }

    protected function createMysqlConnection()
    {
        return new \mysqli(getenv("MYSQL_CONNECTION_HOST"), getenv("MYSQL_USER"), getenv("MYSQL_PASS"));
    }

    protected function login($email, $password)
    {
        $credentials = [
            'email' => $email,
            'password' => $password,
            'remember_me' => 'on',
            'csrf_token' => '9'
        ];
        $this->api->request(
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
        $this->assertCookiePresent(ApiIntegrationTest::$authorizationCookieName);
        $this->assertCookieNotDeleted(ApiIntegrationTest::$authorizationCookieName);
    }

    /**
     * @param $response \Psr\Http\Message\ResponseInterface
     * @return mixed
     */
    protected function getResponseJson($response)
    {
        $json = \GuzzleHttp\json_decode($response->getBody());
        return $json;
    }

    /**
     * @param $response \Psr\Http\Message\ResponseInterface
     * @param $key \string
     * @param $message \string
     */
    protected function assertResponseError($response, $key, $message)
    {
        $this->assertEquals($message, $this->getResponseJson($response)->error->$key);
    }

    /**
     * @param $response \Psr\Http\Message\ResponseInterface
     * @param $key \string
     * @param $message \string
     */
    protected function assertResponseMessage($response, $key, $message)
    {
        $this->assertEquals($message, $this->getResponseJson($response)->$key);
    }

    /**
     * @param $response \Psr\Http\Message\ResponseInterface
     */
    protected function assertResponseUnauthorized($response)
    {
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @param $response \Psr\Http\Message\ResponseInterface
     */
    protected function assertResponseConflict($response)
    {
        $this->assertEquals(409, $response->getStatusCode());
    }

    /**
     * @param $response \Psr\Http\Message\ResponseInterface
     */
    protected function assertResponseForbidden($response)
    {
        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * @param $response \Psr\Http\Message\ResponseInterface
     */
    protected function assertResponseBadRequest($response)
    {
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * @param $response \Psr\Http\Message\ResponseInterface
     */
    protected function assertResponseOk($response)
    {
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @param $cookieName \string
     */
    protected function assertCookiePresent($cookieName)
    {
        $this->assertNotNull($this->getCookie($cookieName));
    }

    /**
     * @param $cookieName \string
     */
    protected function assertCookieHttpOnly($cookieName)
    {
        $this->assertTrue($this->getCookie($cookieName)->getHttpOnly());
    }

    /**
     * @param $cookieName \string
     */
    protected function assertCookieHasValue($cookieName)
    {
        $this->assertNotNull($this->getCookie($cookieName)->getValue());
    }

    /**
     * @param $cookieName \string
     */
    protected function assertCookieSecure($cookieName)
    {
        $this->assertTrue($this->getCookie($cookieName)->getSecure());
    }

    /**
     * @param $cookieName \string
     */
    protected function assertCookieDeleted($cookieName)
    {
        $this->assertEquals("deleted", $this->getCookie($cookieName)->getValue());
    }

    /**
     * @param $cookieName \string
     */
    protected function assertCookieNotDeleted($cookieName)
    {
        $this->assertNotEquals("deleted", $this->getCookie($cookieName)->getValue());
    }

    /**
     * @param $cookieName \string
     */
    protected function assertNoCookie($cookieName)
    {
        $this->assertNull($this->getCookie($cookieName));
    }

    /**
     * @param $cookieName \string
     * @return SetCookie
     */
    private function getCookie($cookieName)
    {
        $cookie = null;
        for ($iterator = $this->jar->getIterator(); $iterator->valid(); $iterator->next()) {
            $current = $iterator->current();
            if ($current->getName() == $cookieName) {
                $cookie = $current;
            }
        }
        return $cookie;
    }

    public function tearDown()
    {
        unset($this->api);
        unset($this->util);
        unset($this->mysqli);
        parent::tearDown();
    }
}
