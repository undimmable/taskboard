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

    public function tearDown()
    {
        unset($this->api);
        unset($this->util);
        unset($this->mysqli);
        parent::tearDown();
    }
}
