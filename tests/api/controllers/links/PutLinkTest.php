<?php


namespace Shaarli\Api\Controllers;

use Shaarli\Config\ConfigManager;
use Shaarli\History;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

class PutLinkTest extends \PHPUnit\Framework\TestCase
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
     * Before every test, instantiate a new Api with its config, plugins and links.
     */
    public function setUp()
    {
        $this->conf = new ConfigManager('tests/utils/config/configJson.json.php');
        $this->refDB = new \ReferenceLinkDB();
        $this->refDB->write(self::$testDatastore);

        $refHistory = new \ReferenceHistory();
        $refHistory->write(self::$testHistory);
        $this->history = new History(self::$testHistory);

        $this->container = new Container();
        $this->container['conf'] = $this->conf;
        $this->container['db'] = new \Shaarli\Bookmark\LinkDB(self::$testDatastore, true, false);
        $this->container['history'] = new History(self::$testHistory);

        $this->controller = new Links($this->container);

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
    public function tearDown()
    {
        @unlink(self::$testDatastore);
        @unlink(self::$testHistory);
    }

    /**
     * Test link update without value: reset the link to default values
     */
    public function testPutLinkMinimal()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'PUT',
        ]);
        $id = '41';
        $request = Request::createFromEnvironment($env);

        $response = $this->controller->putLink($request, new Response(), ['id' => $id]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(self::NB_FIELDS_LINK, count($data));
        $this->assertEquals($id, $data['id']);
        $this->assertEquals('WDWyig', $data['shorturl']);
        $this->assertEquals('http://domain.tld/?WDWyig', $data['url']);
        $this->assertEquals('?WDWyig', $data['title']);
        $this->assertEquals('', $data['description']);
        $this->assertEquals([], $data['tags']);
        $this->assertEquals(false, $data['private']);
        $this->assertEquals(
            \DateTime::createFromFormat('Ymd_His', '20150310_114651'),
            \DateTime::createFromFormat(\DateTime::ATOM, $data['created'])
        );
        $this->assertTrue(
            new \DateTime('5 seconds ago') < \DateTime::createFromFormat(\DateTime::ATOM, $data['updated'])
        );

        $historyEntry = $this->history->getHistory()[0];
        $this->assertEquals(History::UPDATED, $historyEntry['event']);
        $this->assertTrue(
            (new \DateTime())->add(\DateInterval::createFromDateString('-5 seconds')) < $historyEntry['datetime']
        );
        $this->assertEquals($id, $historyEntry['id']);
    }

    /**
     * Test link update with new values
     */
    public function testPutLinkWithValues()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'PUT',
            'CONTENT_TYPE' => 'application/json'
        ]);
        $id = 41;
        $update = [
            'url' => 'http://somewhere.else',
            'title' => 'Le Cid',
            'description' => 'Percé jusques au fond du cœur [...]',
            'tags' => ['corneille', 'rodrigue'],
            'private' => true,
        ];
        $request = Request::createFromEnvironment($env);
        $request = $request->withParsedBody($update);

        $response = $this->controller->putLink($request, new Response(), ['id' => $id]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(self::NB_FIELDS_LINK, count($data));
        $this->assertEquals($id, $data['id']);
        $this->assertEquals('WDWyig', $data['shorturl']);
        $this->assertEquals('http://somewhere.else', $data['url']);
        $this->assertEquals('Le Cid', $data['title']);
        $this->assertEquals('Percé jusques au fond du cœur [...]', $data['description']);
        $this->assertEquals(['corneille', 'rodrigue'], $data['tags']);
        $this->assertEquals(true, $data['private']);
        $this->assertEquals(
            \DateTime::createFromFormat('Ymd_His', '20150310_114651'),
            \DateTime::createFromFormat(\DateTime::ATOM, $data['created'])
        );
        $this->assertTrue(
            new \DateTime('5 seconds ago') < \DateTime::createFromFormat(\DateTime::ATOM, $data['updated'])
        );
    }

    /**
     * Test link update with an existing URL: 409 Conflict with the existing link as body
     */
    public function testPutLinkDuplicate()
    {
        $link = [
            'url' => 'mediagoblin.org/',
            'title' => 'new entry',
            'description' => 'shaare description',
            'tags' => ['one', 'two'],
            'private' => true,
        ];
        $env = Environment::mock([
            'REQUEST_METHOD' => 'PUT',
            'CONTENT_TYPE' => 'application/json'
        ]);

        $request = Request::createFromEnvironment($env);
        $request = $request->withParsedBody($link);
        $response = $this->controller->putLink($request, new Response(), ['id' => 41]);

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
            \DateTime::createFromFormat(\Shaarli\Bookmark\LinkDB::LINK_DATE_FORMAT, '20130614_184135'),
            \DateTime::createFromFormat(\DateTime::ATOM, $data['created'])
        );
        $this->assertEquals(
            \DateTime::createFromFormat(\Shaarli\Bookmark\LinkDB::LINK_DATE_FORMAT, '20130615_184230'),
            \DateTime::createFromFormat(\DateTime::ATOM, $data['updated'])
        );
    }

    /**
     * Test link update on non existent link => ApiLinkNotFoundException.
     *
     * @expectedException Shaarli\Api\Exceptions\ApiLinkNotFoundException
     * @expectedExceptionMessage Link not found
     */
    public function testGetLink404()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'PUT',
        ]);
        $request = Request::createFromEnvironment($env);

        $this->controller->putLink($request, new Response(), ['id' => -1]);
    }
}
