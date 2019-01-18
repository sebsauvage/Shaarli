<?php


namespace Shaarli\Api\Controllers;

use Shaarli\Bookmark\LinkDB;
use Shaarli\Config\ConfigManager;
use Shaarli\History;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

class DeleteTagTest extends \PHPUnit\Framework\TestCase
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
     * @var LinkDB instance.
     */
    protected $linkDB;

    /**
     * @var HistoryController instance.
     */
    protected $history;

    /**
     * @var Container instance.
     */
    protected $container;

    /**
     * @var Tags controller instance.
     */
    protected $controller;

    /**
     * Before each test, instantiate a new Api with its config, plugins and links.
     */
    public function setUp()
    {
        $this->conf = new ConfigManager('tests/utils/config/configJson');
        $this->refDB = new \ReferenceLinkDB();
        $this->refDB->write(self::$testDatastore);
        $this->linkDB = new LinkDB(self::$testDatastore, true, false);
        $refHistory = new \ReferenceHistory();
        $refHistory->write(self::$testHistory);
        $this->history = new History(self::$testHistory);
        $this->container = new Container();
        $this->container['conf'] = $this->conf;
        $this->container['db'] = $this->linkDB;
        $this->container['history'] = $this->history;

        $this->controller = new Tags($this->container);
    }

    /**
     * After each test, remove the test datastore.
     */
    public function tearDown()
    {
        @unlink(self::$testDatastore);
        @unlink(self::$testHistory);
    }

    /**
     * Test DELETE tag endpoint: the tag should be removed.
     */
    public function testDeleteTagValid()
    {
        $tagName = 'gnu';
        $tags = $this->linkDB->linksCountPerTag();
        $this->assertTrue($tags[$tagName] > 0);
        $env = Environment::mock([
            'REQUEST_METHOD' => 'DELETE',
        ]);
        $request = Request::createFromEnvironment($env);

        $response = $this->controller->deleteTag($request, new Response(), ['tagName' => $tagName]);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty((string) $response->getBody());

        $this->linkDB = new LinkDB(self::$testDatastore, true, false);
        $tags = $this->linkDB->linksCountPerTag();
        $this->assertFalse(isset($tags[$tagName]));

        // 2 links affected
        $historyEntry = $this->history->getHistory()[0];
        $this->assertEquals(History::UPDATED, $historyEntry['event']);
        $this->assertTrue(
            (new \DateTime())->add(\DateInterval::createFromDateString('-5 seconds')) < $historyEntry['datetime']
        );
        $historyEntry = $this->history->getHistory()[1];
        $this->assertEquals(History::UPDATED, $historyEntry['event']);
        $this->assertTrue(
            (new \DateTime())->add(\DateInterval::createFromDateString('-5 seconds')) < $historyEntry['datetime']
        );
    }

    /**
     * Test DELETE tag endpoint: the tag should be removed.
     */
    public function testDeleteTagCaseSensitivity()
    {
        $tagName = 'sTuff';
        $tags = $this->linkDB->linksCountPerTag();
        $this->assertTrue($tags[$tagName] > 0);
        $env = Environment::mock([
            'REQUEST_METHOD' => 'DELETE',
        ]);
        $request = Request::createFromEnvironment($env);

        $response = $this->controller->deleteTag($request, new Response(), ['tagName' => $tagName]);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty((string) $response->getBody());

        $this->linkDB = new LinkDB(self::$testDatastore, true, false);
        $tags = $this->linkDB->linksCountPerTag();
        $this->assertFalse(isset($tags[$tagName]));
        $this->assertTrue($tags[strtolower($tagName)] > 0);

        $historyEntry = $this->history->getHistory()[0];
        $this->assertEquals(History::UPDATED, $historyEntry['event']);
        $this->assertTrue(
            (new \DateTime())->add(\DateInterval::createFromDateString('-5 seconds')) < $historyEntry['datetime']
        );
    }

    /**
     * Test DELETE tag endpoint: reach not existing tag.
     *
     * @expectedException Shaarli\Api\Exceptions\ApiTagNotFoundException
     * @expectedExceptionMessage Tag not found
     */
    public function testDeleteLink404()
    {
        $tagName = 'nopenope';
        $tags = $this->linkDB->linksCountPerTag();
        $this->assertFalse(isset($tags[$tagName]));
        $env = Environment::mock([
            'REQUEST_METHOD' => 'DELETE',
        ]);
        $request = Request::createFromEnvironment($env);

        $this->controller->deleteTag($request, new Response(), ['tagName' => $tagName]);
    }
}
