<?php
namespace Shaarli\Updater;

use Exception;
use Shaarli\Bookmark\BookmarkFileService;
use Shaarli\Bookmark\BookmarkServiceInterface;
use Shaarli\Config\ConfigManager;
use Shaarli\History;

require_once 'tests/updater/DummyUpdater.php';
require_once 'tests/utils/ReferenceLinkDB.php';
require_once 'inc/rain.tpl.class.php';

/**
 * Class UpdaterTest.
 * Runs unit tests against the updater class.
 */
class UpdaterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string Path to test datastore.
     */
    protected static $testDatastore = 'sandbox/datastore.php';

    /**
     * @var string Config file path (without extension).
     */
    protected static $configFile = 'sandbox/config';

    /**
     * @var ConfigManager
     */
    protected $conf;

    /** @var BookmarkServiceInterface */
    protected $bookmarkService;

    /** @var Updater */
    protected $updater;

    /**
     * Executed before each test.
     */
    public function setUp()
    {
        copy('tests/utils/config/configJson.json.php', self::$configFile .'.json.php');
        $this->conf = new ConfigManager(self::$configFile);
        $this->bookmarkService = new BookmarkFileService($this->conf, $this->createMock(History::class), true);
        $this->updater = new Updater([], $this->bookmarkService, $this->conf, true);
    }

    /**
     * Test UpdaterUtils::read_updates_file with an empty/missing file.
     */
    public function testReadEmptyUpdatesFile()
    {
        $this->assertEquals(array(), UpdaterUtils::read_updates_file(''));
        $updatesFile = $this->conf->get('resource.data_dir') . '/updates.txt';
        touch($updatesFile);
        $this->assertEquals(array(), UpdaterUtils::read_updates_file($updatesFile));
        unlink($updatesFile);
    }

    /**
     * Test read/write updates file.
     */
    public function testReadWriteUpdatesFile()
    {
        $updatesFile = $this->conf->get('resource.data_dir') . '/updates.txt';
        $updatesMethods = array('m1', 'm2', 'm3');

        UpdaterUtils::write_updates_file($updatesFile, $updatesMethods);
        $readMethods = UpdaterUtils::read_updates_file($updatesFile);
        $this->assertEquals($readMethods, $updatesMethods);

        // Update
        $updatesMethods[] = 'm4';
        UpdaterUtils::write_updates_file($updatesFile, $updatesMethods);
        $readMethods = UpdaterUtils::read_updates_file($updatesFile);
        $this->assertEquals($readMethods, $updatesMethods);
        unlink($updatesFile);
    }

    /**
     * Test errors in UpdaterUtils::write_updates_file(): empty updates file.
     *
     * @expectedException              Exception
     * @expectedExceptionMessageRegExp /Updates file path is not set(.*)/
     */
    public function testWriteEmptyUpdatesFile()
    {
        UpdaterUtils::write_updates_file('', array('test'));
    }

    /**
     * Test errors in UpdaterUtils::write_updates_file(): not writable updates file.
     *
     * @expectedException              Exception
     * @expectedExceptionMessageRegExp /Unable to write(.*)/
     */
    public function testWriteUpdatesFileNotWritable()
    {
        $updatesFile = $this->conf->get('resource.data_dir') . '/updates.txt';
        touch($updatesFile);
        chmod($updatesFile, 0444);
        try {
            @UpdaterUtils::write_updates_file($updatesFile, array('test'));
        } catch (Exception $e) {
            unlink($updatesFile);
            throw $e;
        }
    }

    /**
     * Test the update() method, with no update to run.
     *   1. Everything already run.
     *   2. User is logged out.
     */
    public function testNoUpdates()
    {
        $updates = array(
            'updateMethodDummy1',
            'updateMethodDummy2',
            'updateMethodDummy3',
            'updateMethodException',
        );
        $updater = new DummyUpdater($updates, array(), $this->conf, true);
        $this->assertEquals(array(), $updater->update());

        $updater = new DummyUpdater(array(), array(), $this->conf, false);
        $this->assertEquals(array(), $updater->update());
    }

    /**
     * Test the update() method, with all updates to run (except the failing one).
     */
    public function testUpdatesFirstTime()
    {
        $updates = array('updateMethodException',);
        $expectedUpdates = array(
            'updateMethodDummy1',
            'updateMethodDummy2',
            'updateMethodDummy3',
        );
        $updater = new DummyUpdater($updates, array(), $this->conf, true);
        $this->assertEquals($expectedUpdates, $updater->update());
    }

    /**
     * Test the update() method, only one update to run.
     */
    public function testOneUpdate()
    {
        $updates = array(
            'updateMethodDummy1',
            'updateMethodDummy3',
            'updateMethodException',
        );
        $expectedUpdate = array('updateMethodDummy2');

        $updater = new DummyUpdater($updates, array(), $this->conf, true);
        $this->assertEquals($expectedUpdate, $updater->update());
    }

    /**
     * Test Update failed.
     *
     * @expectedException \Exception
     */
    public function testUpdateFailed()
    {
        $updates = array(
            'updateMethodDummy1',
            'updateMethodDummy2',
            'updateMethodDummy3',
        );

        $updater = new DummyUpdater($updates, array(), $this->conf, true);
        $updater->update();
    }

    public function testUpdateMethodRelativeHomeLinkRename(): void
    {
        $this->conf->set('general.header_link', '?');
        $this->updater->updateMethodRelativeHomeLink();

        static::assertSame();
    }
}
