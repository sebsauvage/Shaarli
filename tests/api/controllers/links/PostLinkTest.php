<?php

namespace Shaarli\Api\Controllers;

use malkusch\lock\mutex\NoMutex;
use Shaarli\Bookmark\Bookmark;
use Shaarli\Bookmark\BookmarkFileService;
use Shaarli\Config\ConfigManager;
use Shaarli\History;
use Shaarli\TestCase;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Router;

/**
 * Class PostLinkTest
 *
 * Test POST Link REST API service.
 *
 * @package Shaarli\Api\Controllers
 */
class PostLinkTest extends TestCase
{
    /**
     * @var string datastore to test write operations
     */
    protected static $testDatastore = 'sandbox/datastore.php';

    /**
     * @var string datastore to test write operations
     */
    protected static $testHistory = 'sandbox/history.php';

    /**
     * @var ConfigManager instance
     */
    protected $conf;

    /**
     * @var \ReferenceLinkDB instance.
     */
    protected $refDB = null;

    /**
     * @var BookmarkFileService instance.
     */
    protected $bookmarkService;

    /**
     * @var HistoryController instance.
     */
    protected $history;

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
     * Before every test, instantiate a new Api with its config, plugins and bookmarks.
     */
    protected function setUp(): void
    {
        $mutex = new NoMutex();
        $this->conf = new ConfigManager('tests/utils/config/configJson');
        $this->conf->set('resource.datastore', self::$testDatastore);
        $this->refDB = new \ReferenceLinkDB();
        $this->refDB->write(self::$testDatastore);
        $refHistory = new \ReferenceHistory();
        $refHistory->write(self::$testHistory);
        $this->history = new History(self::$testHistory);
        $this->bookmarkService = new BookmarkFileService($this->conf, $this->history, $mutex, true);

        $this->container = new Container();
        $this->container['conf'] = $this->conf;
        $this->container['db'] = $this->bookmarkService;
        $this->container['history'] = $this->history;

        $this->controller = new Links($this->container);

        $mock = $this->createMock(Router::class);
        $mock->expects($this->any())
             ->method('pathFor')
             ->willReturn('/api/v1/bookmarks/1');

        // affect @property-read... seems to work
        $this->controller->getCi()->router = $mock;

        // Used by index_url().
        $this->controller->getCi()['environment'] = [
            'SERVER_NAME' => 'domain.tld',
            'SERVER_PORT' => 80,
            'SCRIPT_NAME' => '/',
        ];
    }

    /**
     * After every test, remove the test datastore.
     */
    protected function tearDown(): void
    {
        @unlink(self::$testDatastore);
        @unlink(self::$testHistory);
    }

    /**
     * Test link creation without any field: creates a blank note.
     */
    public function testPostLinkMinimal()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'POST',
        ]);

        $request = Request::createFromEnvironment($env);

        $response = $this->controller->postLink($request, new Response());
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('/api/v1/bookmarks/1', $response->getHeader('Location')[0]);
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(self::NB_FIELDS_LINK, count($data));
        $this->assertEquals(43, $data['id']);
        $this->assertRegExp('/[\w_-]{6}/', $data['shorturl']);
        $this->assertEquals('http://domain.tld/shaare/' . $data['shorturl'], $data['url']);
        $this->assertEquals('/shaare/' . $data['shorturl'], $data['title']);
        $this->assertEquals('', $data['description']);
        $this->assertEquals([], $data['tags']);
        $this->assertEquals(true, $data['private']);
        $this->assertTrue(
            new \DateTime('5 seconds ago') < \DateTime::createFromFormat(\DateTime::ATOM, $data['created'])
        );
        $this->assertEquals('', $data['updated']);

        $historyEntry = $this->history->getHistory()[0];
        $this->assertEquals(History::CREATED, $historyEntry['event']);
        $this->assertTrue(
            (new \DateTime())->add(\DateInterval::createFromDateString('-5 seconds')) < $historyEntry['datetime']
        );
        $this->assertEquals(43, $historyEntry['id']);
    }

    /**
     * Test link creation with all available fields.
     */
    public function testPostLinkFull()
    {
        $link = [
            'url' => 'website.tld/test?foo=bar',
            'title' => 'new entry',
            'description' => 'shaare description',
            'tags' => ['one', 'two'],
            'private' => true,
            'created' => '2015-05-05T12:30:00+03:00',
            'updated' => '2016-06-05T14:32:10+03:00',
        ];
        $env = Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'CONTENT_TYPE' => 'application/json'
        ]);

        $request = Request::createFromEnvironment($env);
        $request = $request->withParsedBody($link);
        $response = $this->controller->postLink($request, new Response());

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('/api/v1/bookmarks/1', $response->getHeader('Location')[0]);
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(self::NB_FIELDS_LINK, count($data));
        $this->assertEquals(43, $data['id']);
        $this->assertRegExp('/[\w_-]{6}/', $data['shorturl']);
        $this->assertEquals('http://' . $link['url'], $data['url']);
        $this->assertEquals($link['title'], $data['title']);
        $this->assertEquals($link['description'], $data['description']);
        $this->assertEquals($link['tags'], $data['tags']);
        $this->assertEquals(true, $data['private']);
        $this->assertSame($link['created'], $data['created']);
        $this->assertSame($link['updated'], $data['updated']);
    }

    /**
     * Test link creation with an existing link (duplicate URL). Should return a 409 HTTP error and the existing link.
     */
    public function testPostLinkDuplicate()
    {
        $link = [
            'url' => 'mediagoblin.org/',
            'title' => 'new entry',
            'description' => 'shaare description',
            'tags' => ['one', 'two'],
            'private' => true,
        ];
        $env = Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'CONTENT_TYPE' => 'application/json'
        ]);

        $request = Request::createFromEnvironment($env);
        $request = $request->withParsedBody($link);
        $response = $this->controller->postLink($request, new Response());

        $this->assertEquals(409, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(self::NB_FIELDS_LINK, count($data));
        $this->assertEquals(7, $data['id']);
        $this->assertEquals('IuWvgA', $data['shorturl']);
        $this->assertEquals('http://mediagoblin.org/', $data['url']);
        $this->assertEquals('MediaGoblin', $data['title']);
        $this->assertEquals('A free software media publishing platform #hashtagOther', $data['description']);
        $this->assertEquals(['gnu', 'media', 'web', '.hidden', 'hashtag'], $data['tags']);
        $this->assertEquals(false, $data['private']);
        $this->assertEquals(
            \DateTime::createFromFormat(Bookmark::LINK_DATE_FORMAT, '20130614_184135'),
            \DateTime::createFromFormat(\DateTime::ATOM, $data['created'])
        );
        $this->assertEquals(
            \DateTime::createFromFormat(Bookmark::LINK_DATE_FORMAT, '20130615_184230'),
            \DateTime::createFromFormat(\DateTime::ATOM, $data['updated'])
        );
    }
}
