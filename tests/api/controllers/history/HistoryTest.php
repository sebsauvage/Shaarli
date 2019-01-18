<?php

namespace Shaarli\Api\Controllers;

use Shaarli\Config\ConfigManager;
use Shaarli\History;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

require_once 'tests/utils/ReferenceHistory.php';

class HistoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string datastore to test write operations
     */
    protected static $testHistory = 'sandbox/history.php';

    /**
     * @var ConfigManager instance
     */
    protected $conf;

    /**
     * @var \ReferenceHistory instance.
     */
    protected $refHistory = null;

    /**
     * @var Container instance.
     */
    protected $container;

    /**
     * @var HistoryController controller instance.
     */
    protected $controller;

    /**
     * Before every test, instantiate a new Api with its config, plugins and links.
     */
    public function setUp()
    {
        $this->conf = new ConfigManager('tests/utils/config/configJson.json.php');
        $this->refHistory = new \ReferenceHistory();
        $this->refHistory->write(self::$testHistory);
        $this->container = new Container();
        $this->container['conf'] = $this->conf;
        $this->container['db'] = true;
        $this->container['history'] = new History(self::$testHistory);

        $this->controller = new HistoryController($this->container);
    }

    /**
     * After every test, remove the test datastore.
     */
    public function tearDown()
    {
        @unlink(self::$testHistory);
    }

    /**
     * Test /history service without parameter.
     */
    public function testGetHistory()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
        ]);
        $request = Request::createFromEnvironment($env);

        $response = $this->controller->getHistory($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);

        $this->assertEquals($this->refHistory->count(), count($data));

        $this->assertEquals(History::DELETED, $data[0]['event']);
        $this->assertEquals(
            \DateTime::createFromFormat('Ymd_His', '20170303_121216')->format(\DateTime::ATOM),
            $data[0]['datetime']
        );
        $this->assertEquals(124, $data[0]['id']);

        $this->assertEquals(History::SETTINGS, $data[1]['event']);
        $this->assertEquals(
            \DateTime::createFromFormat('Ymd_His', '20170302_121215')->format(\DateTime::ATOM),
            $data[1]['datetime']
        );
        $this->assertNull($data[1]['id']);

        $this->assertEquals(History::UPDATED, $data[2]['event']);
        $this->assertEquals(
            \DateTime::createFromFormat('Ymd_His', '20170301_121214')->format(\DateTime::ATOM),
            $data[2]['datetime']
        );
        $this->assertEquals(123, $data[2]['id']);

        $this->assertEquals(History::CREATED, $data[3]['event']);
        $this->assertEquals(
            \DateTime::createFromFormat('Ymd_His', '20170201_121214')->format(\DateTime::ATOM),
            $data[3]['datetime']
        );
        $this->assertEquals(124, $data[3]['id']);

        $this->assertEquals(History::CREATED, $data[4]['event']);
        $this->assertEquals(
            \DateTime::createFromFormat('Ymd_His', '20170101_121212')->format(\DateTime::ATOM),
            $data[4]['datetime']
        );
        $this->assertEquals(123, $data[4]['id']);
    }

    /**
     * Test /history service with limit parameter.
     */
    public function testGetHistoryLimit()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'limit=1'
        ]);
        $request = Request::createFromEnvironment($env);

        $response = $this->controller->getHistory($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);

        $this->assertEquals(1, count($data));

        $this->assertEquals(History::DELETED, $data[0]['event']);
        $this->assertEquals(
            \DateTime::createFromFormat('Ymd_His', '20170303_121216')->format(\DateTime::ATOM),
            $data[0]['datetime']
        );
        $this->assertEquals(124, $data[0]['id']);
    }

    /**
     * Test /history service with offset parameter.
     */
    public function testGetHistoryOffset()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'offset=4'
        ]);
        $request = Request::createFromEnvironment($env);

        $response = $this->controller->getHistory($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);

        $this->assertEquals(1, count($data));

        $this->assertEquals(History::CREATED, $data[0]['event']);
        $this->assertEquals(
            \DateTime::createFromFormat('Ymd_His', '20170101_121212')->format(\DateTime::ATOM),
            $data[0]['datetime']
        );
        $this->assertEquals(123, $data[0]['id']);
    }

    /**
     * Test /history service with since parameter.
     */
    public function testGetHistorySince()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'since=2017-03-03T00:00:00%2B00:00'
        ]);
        $request = Request::createFromEnvironment($env);

        $response = $this->controller->getHistory($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);

        $this->assertEquals(1, count($data));

        $this->assertEquals(History::DELETED, $data[0]['event']);
        $this->assertEquals(
            \DateTime::createFromFormat('Ymd_His', '20170303_121216')->format(\DateTime::ATOM),
            $data[0]['datetime']
        );
        $this->assertEquals(124, $data[0]['id']);
    }

    /**
     * Test /history service with since parameter.
     */
    public function testGetHistorySinceOffsetLimit()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'since=2017-02-01T00:00:00%2B00:00&offset=1&limit=1'
        ]);
        $request = Request::createFromEnvironment($env);

        $response = $this->controller->getHistory($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);

        $this->assertEquals(1, count($data));

        $this->assertEquals(History::SETTINGS, $data[0]['event']);
        $this->assertEquals(
            \DateTime::createFromFormat('Ymd_His', '20170302_121215')->format(\DateTime::ATOM),
            $data[0]['datetime']
        );
    }
}
