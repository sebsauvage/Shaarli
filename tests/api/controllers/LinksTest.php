<?php

namespace Shaarli\Api\Controllers;


use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class LinksTest
 *
 * Test Links REST API services.
 * Note that api call results are tightly related to data contained in ReferenceLinkDB.
 *
 * @package Shaarli\Api\Controllers
 */
class LinksTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string datastore to test write operations
     */
    protected static $testDatastore = 'sandbox/datastore.php';

    /**
     * @var \ConfigManager instance
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
     * Number of JSON field per link.
     */
    const NB_FIELDS_LINK = 9;

    /**
     * Before every test, instantiate a new Api with its config, plugins and links.
     */
    public function setUp()
    {
        $this->conf = new \ConfigManager('tests/utils/config/configJson.json.php');
        $this->refDB = new \ReferenceLinkDB();
        $this->refDB->write(self::$testDatastore);

        $this->container = new Container();
        $this->container['conf'] = $this->conf;
        $this->container['db'] = new \LinkDB(self::$testDatastore, true, false);

        $this->controller = new Links($this->container);
    }

    /**
     * After every test, remove the test datastore.
     */
    public function tearDown()
    {
        @unlink(self::$testDatastore);
    }

    /**
     * Test basic getLinks service: returns all links.
     */
    public function testGetLinks()
    {
        // Used by index_url().
        $_SERVER['SERVER_NAME'] = 'domain.tld';
        $_SERVER['SERVER_PORT'] = 80;
        $_SERVER['SCRIPT_NAME'] = '/';

        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
        ]);
        $request = Request::createFromEnvironment($env);

        $response = $this->controller->getLinks($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals($this->refDB->countLinks(), count($data));

        // Check order
        $order = [41, 8, 6, 7, 0, 1, 4, 42];
        $cpt = 0;
        foreach ($data as $link) {
            $this->assertEquals(self::NB_FIELDS_LINK, count($link));
            $this->assertEquals($order[$cpt++], $link['id']);
        }

        // Check first element fields\
        $first = $data[0];
        $this->assertEquals('http://domain.tld/?WDWyig', $first['url']);
        $this->assertEquals('WDWyig', $first['shorturl']);
        $this->assertEquals('Link title: @website', $first['title']);
        $this->assertEquals(
            'Stallman has a beard and is part of the Free Software Foundation (or not). Seriously, read this. #hashtag',
            $first['description']
        );
        $this->assertEquals('sTuff', $first['tags'][0]);
        $this->assertEquals(false, $first['private']);
        $this->assertEquals(
            \DateTime::createFromFormat(\LinkDB::LINK_DATE_FORMAT, '20150310_114651')->format(\DateTime::ATOM),
            $first['created']
        );
        $this->assertEmpty($first['updated']);

        // Multi tags
        $link = $data[1];
        $this->assertEquals(7, count($link['tags']));

        // Update date
        $this->assertEquals(
            \DateTime::createFromFormat(\LinkDB::LINK_DATE_FORMAT, '20160803_093033')->format(\DateTime::ATOM),
            $link['updated']
        );
    }

    /**
     * Test getLinks service with offset and limit parameter:
     *   limit=1 and offset=1 should return only the second link, ID=8 (ordered by creation date DESC).
     */
    public function testGetLinksOffsetLimit()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'offset=1&limit=1'
        ]);
        $request = Request::createFromEnvironment($env);
        $response = $this->controller->getLinks($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(1, count($data));
        $this->assertEquals(8, $data[0]['id']);
        $this->assertEquals(self::NB_FIELDS_LINK, count($data[0]));
    }

    /**
     * Test getLinks with limit=all (return all link).
     */
    public function testGetLinksLimitAll()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'limit=all'
        ]);
        $request = Request::createFromEnvironment($env);
        $response = $this->controller->getLinks($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals($this->refDB->countLinks(), count($data));
        // Check order
        $order = [41, 8, 6, 7, 0, 1, 4, 42];
        $cpt = 0;
        foreach ($data as $link) {
            $this->assertEquals(self::NB_FIELDS_LINK, count($link));
            $this->assertEquals($order[$cpt++], $link['id']);
        }
    }

    /**
     * Test getLinks service with offset and limit parameter:
     *   limit=1 and offset=1 should return only the second link, ID=8 (ordered by creation date DESC).
     */
    public function testGetLinksOffsetTooHigh()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'offset=100'
        ]);
        $request = Request::createFromEnvironment($env);
        $response = $this->controller->getLinks($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEmpty(count($data));
    }

    /**
     * Test getLinks with private attribute to 1 or true.
     */
    public function testGetLinksPrivate()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'private=true'
        ]);
        $request = Request::createFromEnvironment($env);
        $response = $this->controller->getLinks($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals($this->refDB->countPrivateLinks(), count($data));
        $this->assertEquals(6, $data[0]['id']);
        $this->assertEquals(self::NB_FIELDS_LINK, count($data[0]));

        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'private=1'
        ]);
        $request = Request::createFromEnvironment($env);
        $response = $this->controller->getLinks($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals($this->refDB->countPrivateLinks(), count($data));
        $this->assertEquals(6, $data[0]['id']);
        $this->assertEquals(self::NB_FIELDS_LINK, count($data[0]));
    }

    /**
     * Test getLinks with private attribute to false or 0
     */
    public function testGetLinksNotPrivate()
    {
        $env = Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'QUERY_STRING' => 'private=0'
            ]
        );
        $request = Request::createFromEnvironment($env);
        $response = $this->controller->getLinks($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string)$response->getBody(), true);
        $this->assertEquals($this->refDB->countLinks(), count($data));
        $this->assertEquals(41, $data[0]['id']);
        $this->assertEquals(self::NB_FIELDS_LINK, count($data[0]));

        $env = Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'QUERY_STRING' => 'private=false'
            ]
        );
        $request = Request::createFromEnvironment($env);
        $response = $this->controller->getLinks($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string)$response->getBody(), true);
        $this->assertEquals($this->refDB->countLinks(), count($data));
        $this->assertEquals(41, $data[0]['id']);
        $this->assertEquals(self::NB_FIELDS_LINK, count($data[0]));
    }

    /**
     * Test getLinks service with offset and limit parameter:
     *   limit=1 and offset=1 should return only the second link, ID=8 (ordered by creation date DESC).
     */
    public function testGetLinksSearchTerm()
    {
        // Only in description - 1 result
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'searchterm=Tropical'
        ]);
        $request = Request::createFromEnvironment($env);
        $response = $this->controller->getLinks($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(1, count($data));
        $this->assertEquals(1, $data[0]['id']);
        $this->assertEquals(self::NB_FIELDS_LINK, count($data[0]));

        // Only in tags - 1 result
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'searchterm=tag3'
        ]);
        $request = Request::createFromEnvironment($env);
        $response = $this->controller->getLinks($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(1, count($data));
        $this->assertEquals(0, $data[0]['id']);
        $this->assertEquals(self::NB_FIELDS_LINK, count($data[0]));

        // Multiple results (2)
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'searchterm=stallman'
        ]);
        $request = Request::createFromEnvironment($env);
        $response = $this->controller->getLinks($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(2, count($data));
        $this->assertEquals(41, $data[0]['id']);
        $this->assertEquals(self::NB_FIELDS_LINK, count($data[0]));
        $this->assertEquals(8, $data[1]['id']);
        $this->assertEquals(self::NB_FIELDS_LINK, count($data[1]));

        // Multiword - 2 results
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'searchterm=stallman+software'
        ]);
        $request = Request::createFromEnvironment($env);
        $response = $this->controller->getLinks($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(2, count($data));
        $this->assertEquals(41, $data[0]['id']);
        $this->assertEquals(self::NB_FIELDS_LINK, count($data[0]));
        $this->assertEquals(8, $data[1]['id']);
        $this->assertEquals(self::NB_FIELDS_LINK, count($data[1]));

        // URL encoding
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'searchterm='. urlencode('@web')
        ]);
        $request = Request::createFromEnvironment($env);
        $response = $this->controller->getLinks($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(2, count($data));
        $this->assertEquals(41, $data[0]['id']);
        $this->assertEquals(self::NB_FIELDS_LINK, count($data[0]));
        $this->assertEquals(8, $data[1]['id']);
        $this->assertEquals(self::NB_FIELDS_LINK, count($data[1]));
    }

    public function testGetLinksSearchTermNoResult()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'searchterm=nope'
        ]);
        $request = Request::createFromEnvironment($env);
        $response = $this->controller->getLinks($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(0, count($data));
    }

    public function testGetLinksSearchTags()
    {
        // Single tag
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'searchtags=dev',
        ]);
        $request = Request::createFromEnvironment($env);
        $response = $this->controller->getLinks($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(2, count($data));
        $this->assertEquals(0, $data[0]['id']);
        $this->assertEquals(self::NB_FIELDS_LINK, count($data[0]));
        $this->assertEquals(4, $data[1]['id']);
        $this->assertEquals(self::NB_FIELDS_LINK, count($data[1]));

        // Multitag + exclude
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'searchtags=stuff+-gnu',
        ]);
        $request = Request::createFromEnvironment($env);
        $response = $this->controller->getLinks($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(1, count($data));
        $this->assertEquals(41, $data[0]['id']);
        $this->assertEquals(self::NB_FIELDS_LINK, count($data[0]));
    }

    /**
     * Test getLinks service with search tags+terms.
     */
    public function testGetLinksSearchTermsAndTags()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'searchterm=poke&searchtags=dev',
        ]);
        $request = Request::createFromEnvironment($env);
        $response = $this->controller->getLinks($request, new Response());
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(1, count($data));
        $this->assertEquals(0, $data[0]['id']);
        $this->assertEquals(self::NB_FIELDS_LINK, count($data[0]));
    }
}
