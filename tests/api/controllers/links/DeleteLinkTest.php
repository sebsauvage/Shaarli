<?php


namespace Shaarli\Api\Controllers;

use Shaarli\Bookmark\LinkDB;
use Shaarli\Config\ConfigManager;
use Shaarli\History;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

class DeleteLinkTest extends \PHPUnit\Framework\TestCase
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
     * @var Links controller instance.
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

        $this->controller = new Links($this->container);
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
     * Test DELETE link endpoint: the link should be removed.
     */
    public function testDeleteLinkValid()
    {
        $id = '41';
        $this->assertTrue(isset($this->linkDB[$id]));
        $env = Environment::mock([
            'REQUEST_METHOD' => 'DELETE',
        ]);
        $request = Request::createFromEnvironment($env);

        $response = $this->controller->deleteLink($request, new Response(), ['id' => $id]);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty((string) $response->getBody());

        $this->linkDB = new LinkDB(self::$testDatastore, true, false);
        $this->assertFalse(isset($this->linkDB[$id]));

        $historyEntry = $this->history->getHistory()[0];
        $this->assertEquals(History::DELETED, $historyEntry['event']);
        $this->assertTrue(
            (new \DateTime())->add(\DateInterval::createFromDateString('-5 seconds')) < $historyEntry['datetime']
        );
        $this->assertEquals($id, $historyEntry['id']);
    }

    /**
     * Test DELETE link endpoint: reach not existing ID.
     *
     * @expectedException \Shaarli\Api\Exceptions\ApiLinkNotFoundException
     */
    public function testDeleteLink404()
    {
        $id = -1;
        $this->assertFalse(isset($this->linkDB[$id]));
        $env = Environment::mock([
            'REQUEST_METHOD' => 'DELETE',
        ]);
        $request = Request::createFromEnvironment($env);

        $this->controller->deleteLink($request, new Response(), ['id' => $id]);
    }
}
