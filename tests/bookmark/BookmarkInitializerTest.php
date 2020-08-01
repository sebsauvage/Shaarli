<?php

namespace Shaarli\Bookmark;

use PHPUnit\Framework\TestCase;
use Shaarli\Config\ConfigManager;
use Shaarli\History;

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

    /**
     * Initialize an empty BookmarkFileService
     */
    public function setUp()
    {
        if (file_exists(self::$testDatastore)) {
            unlink(self::$testDatastore);
        }

        copy('tests/utils/config/configJson.json.php', self::$testConf .'.json.php');
        $this->conf = new ConfigManager(self::$testConf);
        $this->conf->set('resource.datastore', self::$testDatastore);
        $this->history = new History('sandbox/history.php');
        $this->bookmarkService = new BookmarkFileService($this->conf, $this->history, true);

        $this->initializer = new BookmarkInitializer($this->bookmarkService);
    }

    /**
     * Test initialize() with a data store containing bookmarks.
     */
    public function testInitializeNotEmptyDataStore(): void
    {
        $refDB = new \ReferenceLinkDB();
        $refDB->write(self::$testDatastore);
        $this->bookmarkService = new BookmarkFileService($this->conf, $this->history, true);
        $this->initializer = new BookmarkInitializer($this->bookmarkService);

        $this->initializer->initialize();

        $this->assertEquals($refDB->countLinks() + 2, $this->bookmarkService->count());
        $bookmark = $this->bookmarkService->get(43);
        $this->assertEquals(43, $bookmark->getId());
        $this->assertEquals('My secret stuff... - Pastebin.com', $bookmark->getTitle());
        $this->assertTrue($bookmark->isPrivate());

        $bookmark = $this->bookmarkService->get(44);
        $this->assertEquals(44, $bookmark->getId());
        $this->assertEquals(
            'The personal, minimalist, super-fast, database free, bookmarking service',
            $bookmark->getTitle()
        );
        $this->assertFalse($bookmark->isPrivate());

        $this->bookmarkService->save();

        // Reload from file
        $this->bookmarkService = new BookmarkFileService($this->conf, $this->history, true);
        $this->assertEquals($refDB->countLinks() + 2, $this->bookmarkService->count());
        $bookmark = $this->bookmarkService->get(43);
        $this->assertEquals(43, $bookmark->getId());
        $this->assertEquals('My secret stuff... - Pastebin.com', $bookmark->getTitle());
        $this->assertTrue($bookmark->isPrivate());

        $bookmark = $this->bookmarkService->get(44);
        $this->assertEquals(44, $bookmark->getId());
        $this->assertEquals(
            'The personal, minimalist, super-fast, database free, bookmarking service',
            $bookmark->getTitle()
        );
        $this->assertFalse($bookmark->isPrivate());
    }

    /**
     * Test initialize() with an a non existent datastore file .
     */
    public function testInitializeNonExistentDataStore(): void
    {
        $this->conf->set('resource.datastore', static::$testDatastore . '_empty');
        $this->bookmarkService = new BookmarkFileService($this->conf, $this->history, true);

        $this->initializer->initialize();

        $this->assertEquals(2, $this->bookmarkService->count());
        $bookmark = $this->bookmarkService->get(0);
        $this->assertEquals(0, $bookmark->getId());
        $this->assertEquals('My secret stuff... - Pastebin.com', $bookmark->getTitle());
        $this->assertTrue($bookmark->isPrivate());

        $bookmark = $this->bookmarkService->get(1);
        $this->assertEquals(1, $bookmark->getId());
        $this->assertEquals(
            'The personal, minimalist, super-fast, database free, bookmarking service',
            $bookmark->getTitle()
        );
        $this->assertFalse($bookmark->isPrivate());
    }
}
