<?php

namespace Shaarli\Bookmark;

use malkusch\lock\mutex\NoMutex;
use Shaarli\Config\ConfigManager;
use Shaarli\History;
use Shaarli\TestCase;

/**
 * Class BookmarkInitializerTest
 * @package Shaarli\Bookmark
 */
class BookmarkInitializerTest extends TestCase
{
    /** @var string Path of test data store */
    protected static $testDatastore = 'sandbox/datastore.php';

    /** @var string Path of test config file */
    protected static $testConf = 'sandbox/config';

    /**
     * @var ConfigManager instance.
     */
    protected $conf;

    /**
     * @var History instance.
     */
    protected $history;

    /** @var BookmarkServiceInterface instance */
    protected $bookmarkService;

    /** @var BookmarkInitializer instance */
    protected $initializer;

    /** @var NoMutex */
    protected $mutex;

    /**
     * Initialize an empty BookmarkFileService
     */
    public function setUp(): void
    {
        $this->mutex = new NoMutex();
        if (file_exists(self::$testDatastore)) {
            unlink(self::$testDatastore);
        }

        copy('tests/utils/config/configJson.json.php', self::$testConf .'.json.php');
        $this->conf = new ConfigManager(self::$testConf);
        $this->conf->set('resource.datastore', self::$testDatastore);
        $this->history = new History('sandbox/history.php');
        $this->bookmarkService = new BookmarkFileService($this->conf, $this->history, $this->mutex, true);

        $this->initializer = new BookmarkInitializer($this->bookmarkService);
    }

    /**
     * Test initialize() with a data store containing bookmarks.
     */
    public function testInitializeNotEmptyDataStore(): void
    {
        $refDB = new \ReferenceLinkDB();
        $refDB->write(self::$testDatastore);
        $this->bookmarkService = new BookmarkFileService($this->conf, $this->history, $this->mutex, true);
        $this->initializer = new BookmarkInitializer($this->bookmarkService);

        $this->initializer->initialize();

        $this->assertEquals($refDB->countLinks() + 3, $this->bookmarkService->count());

        $bookmark = $this->bookmarkService->get(43);
        $this->assertStringStartsWith(
            'Shaarli will automatically pick up the thumbnail for links to a variety of websites.',
            $bookmark->getDescription()
        );
        $this->assertTrue($bookmark->isPrivate());

        $bookmark = $this->bookmarkService->get(44);
        $this->assertStringStartsWith(
            'Adding a shaare without entering a URL creates a text-only "note" post such as this one.',
            $bookmark->getDescription()
        );
        $this->assertTrue($bookmark->isPrivate());

        $bookmark = $this->bookmarkService->get(45);
        $this->assertStringStartsWith(
            'Welcome to Shaarli!',
            $bookmark->getDescription()
        );
        $this->assertFalse($bookmark->isPrivate());

        $this->bookmarkService->save();

        // Reload from file
        $this->bookmarkService = new BookmarkFileService($this->conf, $this->history, $this->mutex, true);
        $this->assertEquals($refDB->countLinks() + 3, $this->bookmarkService->count());

        $bookmark = $this->bookmarkService->get(43);
        $this->assertStringStartsWith(
            'Shaarli will automatically pick up the thumbnail for links to a variety of websites.',
            $bookmark->getDescription()
        );
        $this->assertTrue($bookmark->isPrivate());

        $bookmark = $this->bookmarkService->get(44);
        $this->assertStringStartsWith(
            'Adding a shaare without entering a URL creates a text-only "note" post such as this one.',
            $bookmark->getDescription()
        );
        $this->assertTrue($bookmark->isPrivate());

        $bookmark = $this->bookmarkService->get(45);
        $this->assertStringStartsWith(
            'Welcome to Shaarli!',
            $bookmark->getDescription()
        );
        $this->assertFalse($bookmark->isPrivate());
    }

    /**
     * Test initialize() with an a non existent datastore file .
     */
    public function testInitializeNonExistentDataStore(): void
    {
        $this->conf->set('resource.datastore', static::$testDatastore . '_empty');
        $this->bookmarkService = new BookmarkFileService($this->conf, $this->history, $this->mutex, true);

        $this->initializer->initialize();

        $this->assertEquals(3, $this->bookmarkService->count());
        $bookmark = $this->bookmarkService->get(0);
        $this->assertStringStartsWith(
            'Shaarli will automatically pick up the thumbnail for links to a variety of websites.',
            $bookmark->getDescription()
        );
        $this->assertTrue($bookmark->isPrivate());

        $bookmark = $this->bookmarkService->get(1);
        $this->assertStringStartsWith(
            'Adding a shaare without entering a URL creates a text-only "note" post such as this one.',
            $bookmark->getDescription()
        );
        $this->assertTrue($bookmark->isPrivate());

        $bookmark = $this->bookmarkService->get(2);
        $this->assertStringStartsWith(
            'Welcome to Shaarli!',
            $bookmark->getDescription()
        );
        $this->assertFalse($bookmark->isPrivate());
    }
}
