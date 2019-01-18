<?php

namespace Shaarli\Api\Controllers;

use Shaarli\Bookmark\LinkDB;
use Shaarli\Config\ConfigManager;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class GetTagNameTest
 *
 * Test getTag by tag name API service.
 *
 * @package Shaarli\Api\Controllers
 */
class GetTagNameTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string datastore to test write operations
     */
    protected static $testDatastore = 'sandbox/datastore.php';

    /**
     * @var ConfigManager instance
     */
    protected $conf;

    /**
     * @var \ReferenceLinkDB instance.
     */
    protected $refDB = null;

    /**
     * @var Container instance.
     */
    protected $container;

    /**
     * @var Tags controller instance.
     */
    protected $controller;

    /**
     * Number of JSON fields per link.
     */
    const NB_FIELDS_TAG = 2;

    /**
     * Before each test, instantiate a new Api with its config, plugins and links.
     */
    public function setUp()
    {
        $this->conf = new ConfigManager('tests/utils/config/configJson');
        $this->refDB = new \ReferenceLinkDB();
        $this->refDB->write(self::$testDatastore);

        $this->container = new Container();
        $this->container['conf'] = $this->conf;
        $this->container['db'] = new LinkDB(self::$testDatastore, true, false);
        $this->container['history'] = null;

        $this->controller = new Tags($this->container);
    }

    /**
     * After each test, remove the test datastore.
     */
    public function tearDown()
    {
        @unlink(self::$testDatastore);
    }

    /**
     * Test basic getTag service: return gnu tag with 2 occurrences.
     */
    public function testGetTag()
    {
        $tagName = 'gnu';
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
        ]);
        $request = Request::createFromEnvironment($env);

        $response = $this->controller->getTag($request, new Response(), ['tagName' => $tagName]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(self::NB_FIELDS_TAG, count($data));
        $this->assertEquals($tagName, $data['name']);
        $this->assertEquals(2, $data['occurrences']);
    }

    /**
     * Test getTag service which is not case sensitive: occurrences with both sTuff and stuff
     */
    public function testGetTagNotCaseSensitive()
    {
        $tagName = 'sTuff';
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
        ]);
        $request = Request::createFromEnvironment($env);

        $response = $this->controller->getTag($request, new Response(), ['tagName' => $tagName]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(self::NB_FIELDS_TAG, count($data));
        $this->assertEquals($tagName, $data['name']);
        $this->assertEquals(2, $data['occurrences']);
    }

    /**
     * Test basic getTag service: get non existent tag => ApiTagNotFoundException.
     *
     * @expectedException Shaarli\Api\Exceptions\ApiTagNotFoundException
     * @expectedExceptionMessage Tag not found
     */
    public function testGetTag404()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
        ]);
        $request = Request::createFromEnvironment($env);

        $this->controller->getTag($request, new Response(), ['tagName' => 'nopenope']);
    }
}
