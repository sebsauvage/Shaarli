<?php

namespace Shaarli\Api\Controllers;

use Shaarli\Config\ConfigManager;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class GetLinkIdTest
 *
 * Test getLink by ID API service.
 *
 * @see http://shaarli.github.io/api-documentation/#links-link-get
 *
 * @package Shaarli\Api\Controllers
 */
class GetLinkIdTest extends \PHPUnit\Framework\TestCase
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
     * @var Links controller instance.
     */
    protected $controller;

    /**
     * Number of JSON fields per link.
     */
    const NB_FIELDS_LINK = 9;

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
        $this->container['db'] = new \Shaarli\Bookmark\LinkDB(self::$testDatastore, true, false);
        $this->container['history'] = null;

        $this->controller = new Links($this->container);
    }

    /**
     * After each test, remove the test datastore.
     */
    public function tearDown()
    {
        @unlink(self::$testDatastore);
    }

    /**
     * Test basic getLink service: return link ID=41.
     */
    public function testGetLinkId()
    {
        // Used by index_url().
        $_SERVER['SERVER_NAME'] = 'domain.tld';
        $_SERVER['SERVER_PORT'] = 80;
        $_SERVER['SCRIPT_NAME'] = '/';

        $id = 41;
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
        ]);
        $request = Request::createFromEnvironment($env);

        $response = $this->controller->getLink($request, new Response(), ['id' => $id]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(self::NB_FIELDS_LINK, count($data));
        $this->assertEquals($id, $data['id']);

        // Check link elements
        $this->assertEquals('http://domain.tld/?WDWyig', $data['url']);
        $this->assertEquals('WDWyig', $data['shorturl']);
        $this->assertEquals('Link title: @website', $data['title']);
        $this->assertEquals(
            'Stallman has a beard and is part of the Free Software Foundation (or not). Seriously, read this. #hashtag',
            $data['description']
        );
        $this->assertEquals('sTuff', $data['tags'][0]);
        $this->assertEquals(false, $data['private']);
        $this->assertEquals(
            \DateTime::createFromFormat(\Shaarli\Bookmark\LinkDB::LINK_DATE_FORMAT, '20150310_114651')->format(\DateTime::ATOM),
            $data['created']
        );
        $this->assertEmpty($data['updated']);
    }

    /**
     * Test basic getLink service: get non existent link => ApiLinkNotFoundException.
     *
     * @expectedException Shaarli\Api\Exceptions\ApiLinkNotFoundException
     * @expectedExceptionMessage Link not found
     */
    public function testGetLink404()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
        ]);
        $request = Request::createFromEnvironment($env);

        $this->controller->getLink($request, new Response(), ['id' => -1]);
    }
}
