<?php
namespace Taskboards;

use \GuzzleHttp\Client;

require_once 'util.php';

abstract class ApiIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \GuzzleHttp\Client;
     */
    protected $api;
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
        $this->api = new Client([
            'connect_timeout' => 10,
            'timeout' => 10,
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
     */
    protected function assertResponseUnauthorized($response)
    {
        $this->assertEquals(401, $response->getStatusCode());
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

    public function tearDown()
    {
        unset($this->api);
        unset($this->util);
        unset($this->mysqli);
        parent::tearDown();
    }
}
