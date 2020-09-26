<?php


namespace Shaarli\Api\Controllers;

use malkusch\lock\mutex\NoMutex;
use Shaarli\Bookmark\BookmarkFileService;
use Shaarli\Bookmark\LinkDB;
use Shaarli\Config\ConfigManager;
use Shaarli\History;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

class DeleteTagTest extends \Shaarli\TestCase
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
     * @var Tags controller instance.
     */
    protected $controller;

    /** @var NoMutex */
    protected $mutex;

    /**
     * Before each test, instantiate a new Api with its config, plugins and bookmarks.
     */
    protected function setUp(): void
    {
        $this->mutex = new NoMutex();
        $this->conf = new ConfigManager('tests/utils/config/configJson');
        $this->conf->set('resource.datastore', self::$testDatastore);
        $this->refDB = new \ReferenceLinkDB();
        $this->refDB->write(self::$testDatastore);
        $refHistory = new \ReferenceHistory();
        $refHistory->write(self::$testHistory);
        $this->history = new History(self::$testHistory);
        $this->bookmarkService = new BookmarkFileService($this->conf, $this->history, $this->mutex, true);

        $this->container = new Container();
        $this->container['conf'] = $this->conf;
        $this->container['db'] = $this->bookmarkService;
        $this->container['history'] = $this->history;

        $this->controller = new Tags($this->container);
    }

    /**
     * After each test, remove the test datastore.
     */
    protected function tearDown(): void
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
        $tags = $this->bookmarkService->bookmarksCountPerTag();
        $this->assertTrue($tags[$tagName] > 0);
        $env = Environment::mock([
            'REQUEST_METHOD' => 'DELETE',
        ]);
        $request = Request::createFromEnvironment($env);

        $response = $this->controller->deleteTag($request, new Response(), ['tagName' => $tagName]);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty((string) $response->getBody());

        $this->bookmarkService = new BookmarkFileService($this->conf, $this->history, $this->mutex, true);
        $tags = $this->bookmarkService->bookmarksCountPerTag();
        $this->assertFalse(isset($tags[$tagName]));

        // 2 bookmarks affected
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
        $tags = $this->bookmarkService->bookmarksCountPerTag();
        $this->assertTrue($tags[$tagName] > 0);
        $env = Environment::mock([
            'REQUEST_METHOD' => 'DELETE',
        ]);
        $request = Request::createFromEnvironment($env);

        $response = $this->controller->deleteTag($request, new Response(), ['tagName' => $tagName]);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty((string) $response->getBody());

        $this->bookmarkService = new BookmarkFileService($this->conf, $this->history, $this->mutex, true);
        $tags = $this->bookmarkService->bookmarksCountPerTag();
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
     */
    public function testDeleteLink404()
    {
        $this->expectException(\Shaarli\Api\Exceptions\ApiTagNotFoundException::class);
        $this->expectExceptionMessage('Tag not found');

        $tagName = 'nopenope';
        $tags = $this->bookmarkService->bookmarksCountPerTag();
        $this->assertFalse(isset($tags[$tagName]));
        $env = Environment::mock([
            'REQUEST_METHOD' => 'DELETE',
        ]);
        $request = Request::createFromEnvironment($env);

        $this->controller->deleteTag($request, new Response(), ['tagName' => $tagName]);
    }
}
