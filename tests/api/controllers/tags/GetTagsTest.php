<?php
namespace Shaarli\Api\Controllers;

use Shaarli\Bookmark\LinkDB;
use Shaarli\Config\ConfigManager;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class GetTagsTest
 *
 * Test get tag list REST API service.
 *
 * @package Shaarli\Api\Controllers
 */
class GetTagsTest extends \PHPUnit\Framework\TestCase
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
     * @var LinkDB instance.
     */
    protected $linkDB;

    /**
     * @var Tags controller instance.
     */
    protected $controller;

    /**
     * Number of JSON field per link.
     */
    const NB_FIELDS_TAG = 2;

    /**
     * Before every test, instantiate a new Api with its config, plugins and links.
     */
    public function setUp()
    {
        $this->conf = new ConfigManager('tests/utils/config/configJson');
        $this->refDB = new \ReferenceLinkDB();
        $this->refDB->write(self::$testDatastore);

        $this->container = new Container();
        $this->container['conf'] = $this->conf;
        $this->linkDB = new LinkDB(self::$testDatastore, true, false);
        $this->container['db'] = $this->linkDB;
        $this->container['history'] = null;

        $this->controller = new Tags($this->container);
    }

    /**
     * After every test, remove the test datastore.
     */
    public function tearDown()
    {
        @unlink(self::$testDatastore);
    }

    /**
     * Test basic getTags service: returns all tags.
     */
    public function testGetTagsAll()
    {
        $tags = $this->linkDB->linksCountPerTag();
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
        ]);
        $request = Request::createFromEnvironment($env);

        $response = $this->controller->getTags($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(count($tags), count($data));

        // Check order
        $this->assertEquals(self::NB_FIELDS_TAG, count($data[0]));
        $this->assertEquals('web', $data[0]['name']);
        $this->assertEquals(4, $data[0]['occurrences']);
        $this->assertEquals(self::NB_FIELDS_TAG, count($data[1]));
        $this->assertEquals('cartoon', $data[1]['name']);
        $this->assertEquals(3, $data[1]['occurrences']);
        // Case insensitive
        $this->assertEquals(self::NB_FIELDS_TAG, count($data[5]));
        $this->assertEquals('sTuff', $data[5]['name']);
        $this->assertEquals(2, $data[5]['occurrences']);
        // End
        $this->assertEquals(self::NB_FIELDS_TAG, count($data[count($data) - 1]));
        $this->assertEquals('w3c', $data[count($data) - 1]['name']);
        $this->assertEquals(1, $data[count($data) - 1]['occurrences']);
    }

    /**
     * Test getTags service with offset and limit parameter:
     *   limit=1 and offset=1 should return only the second tag, cartoon with 3 occurrences
     */
    public function testGetTagsOffsetLimit()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'offset=1&limit=1'
        ]);
        $request = Request::createFromEnvironment($env);
        $response = $this->controller->getTags($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(1, count($data));
        $this->assertEquals(self::NB_FIELDS_TAG, count($data[0]));
        $this->assertEquals('cartoon', $data[0]['name']);
        $this->assertEquals(3, $data[0]['occurrences']);
    }

    /**
     * Test getTags with limit=all (return all tags).
     */
    public function testGetTagsLimitAll()
    {
        $tags = $this->linkDB->linksCountPerTag();
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'limit=all'
        ]);
        $request = Request::createFromEnvironment($env);
        $response = $this->controller->getTags($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(count($tags), count($data));
    }

    /**
     * Test getTags service with offset and limit parameter:
     *   limit=1 and offset=1 should not return any tag
     */
    public function testGetTagsOffsetTooHigh()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'offset=100'
        ]);
        $request = Request::createFromEnvironment($env);
        $response = $this->controller->getTags($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEmpty(count($data));
    }

    /**
     * Test getTags with visibility parameter set to private
     */
    public function testGetTagsVisibilityPrivate()
    {
        $tags = $this->linkDB->linksCountPerTag([], 'private');
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'visibility=private'
        ]);
        $request = Request::createFromEnvironment($env);
        $response = $this->controller->getTags($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(count($tags), count($data));
        $this->assertEquals(self::NB_FIELDS_TAG, count($data[0]));
        $this->assertEquals('Mercurial', $data[0]['name']);
        $this->assertEquals(1, $data[0]['occurrences']);
    }

    /**
     * Test getTags with visibility parameter set to public
     */
    public function testGetTagsVisibilityPublic()
    {
        $tags = $this->linkDB->linksCountPerTag([], 'public');
        $env = Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'QUERY_STRING' => 'visibility=public'
            ]
        );
        $request = Request::createFromEnvironment($env);
        $response = $this->controller->getTags($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string)$response->getBody(), true);
        $this->assertEquals(count($tags), count($data));
        $this->assertEquals(self::NB_FIELDS_TAG, count($data[0]));
        $this->assertEquals('web', $data[0]['name']);
        $this->assertEquals(3, $data[0]['occurrences']);
    }
}
