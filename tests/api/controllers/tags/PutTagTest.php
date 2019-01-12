<?php

namespace Shaarli\Api\Controllers;

use Shaarli\Api\Exceptions\ApiBadParametersException;
use Shaarli\Bookmark\LinkDB;
use Shaarli\Config\ConfigManager;
use Shaarli\History;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

class PutTagTest extends \PHPUnit\Framework\TestCase
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
        $this->conf = new ConfigManager('tests/utils/config/configJson.json.php');
        $this->refDB = new \ReferenceLinkDB();
        $this->refDB->write(self::$testDatastore);

        $refHistory = new \ReferenceHistory();
        $refHistory->write(self::$testHistory);
        $this->history = new History(self::$testHistory);

        $this->container = new Container();
        $this->container['conf'] = $this->conf;
        $this->linkDB = new LinkDB(self::$testDatastore, true, false);
        $this->container['db'] = $this->linkDB;
        $this->container['history'] = $this->history;

        $this->controller = new Tags($this->container);
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
     * Test tags update
     */
    public function testPutLinkValid()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'PUT',
        ]);
        $tagName = 'gnu';
        $update = ['name' => $newName = 'newtag'];
        $request = Request::createFromEnvironment($env);
        $request = $request->withParsedBody($update);

        $response = $this->controller->putTag($request, new Response(), ['tagName' => $tagName]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(self::NB_FIELDS_TAG, count($data));
        $this->assertEquals($newName, $data['name']);
        $this->assertEquals(2, $data['occurrences']);

        $tags = $this->linkDB->linksCountPerTag();
        $this->assertNotTrue(isset($tags[$tagName]));
        $this->assertEquals(2, $tags[$newName]);

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
     * Test tag update with an existing tag: they should be merged
     */
    public function testPutTagMerge()
    {
        $tagName = 'gnu';
        $newName = 'w3c';

        $tags = $this->linkDB->linksCountPerTag();
        $this->assertEquals(1, $tags[$newName]);
        $this->assertEquals(2, $tags[$tagName]);

        $env = Environment::mock([
            'REQUEST_METHOD' => 'PUT',
        ]);
        $update = ['name' => $newName];
        $request = Request::createFromEnvironment($env);
        $request = $request->withParsedBody($update);

        $response = $this->controller->putTag($request, new Response(), ['tagName' => $tagName]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(self::NB_FIELDS_TAG, count($data));
        $this->assertEquals($newName, $data['name']);
        $this->assertEquals(3, $data['occurrences']);

        $tags = $this->linkDB->linksCountPerTag();
        $this->assertNotTrue(isset($tags[$tagName]));
        $this->assertEquals(3, $tags[$newName]);
    }

    /**
     * Test tag update with an empty new tag name => ApiBadParametersException
     *
     * @expectedException Shaarli\Api\Exceptions\ApiBadParametersException
     * @expectedExceptionMessage New tag name is required in the request body
     */
    public function testPutTagEmpty()
    {
        $tagName = 'gnu';
        $newName = '';

        $tags = $this->linkDB->linksCountPerTag();
        $this->assertEquals(2, $tags[$tagName]);

        $env = Environment::mock([
            'REQUEST_METHOD' => 'PUT',
        ]);
        $request = Request::createFromEnvironment($env);

        $env = Environment::mock([
            'REQUEST_METHOD' => 'PUT',
        ]);
        $update = ['name' => $newName];
        $request = Request::createFromEnvironment($env);
        $request = $request->withParsedBody($update);

        try {
            $this->controller->putTag($request, new Response(), ['tagName' => $tagName]);
        } catch (ApiBadParametersException $e) {
            $tags = $this->linkDB->linksCountPerTag();
            $this->assertEquals(2, $tags[$tagName]);
            throw $e;
        }
    }

    /**
     * Test tag update on non existent tag => ApiTagNotFoundException.
     *
     * @expectedException Shaarli\Api\Exceptions\ApiTagNotFoundException
     * @expectedExceptionMessage Tag not found
     */
    public function testPutTag404()
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'PUT',
        ]);
        $request = Request::createFromEnvironment($env);

        $this->controller->putTag($request, new Response(), ['tagName' => 'nopenope']);
    }
}
