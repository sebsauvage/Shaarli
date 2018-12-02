<?php
/**
 * Cache tests
 */
namespace Shaarli\Feed;

// required to access $_SESSION array
session_start();

require_once 'application/feed/Cache.php';

/**
 * Unitary tests for cached pages
 */
class CacheTest extends \PHPUnit\Framework\TestCase
{
    // test cache directory
    protected static $testCacheDir = 'sandbox/dummycache';

    // dummy cached file names / content
    protected static $pages = array('a', 'toto', 'd7b59c');


    /**
     * Populate the cache with dummy files
     */
    public function setUp()
    {
        if (!is_dir(self::$testCacheDir)) {
            mkdir(self::$testCacheDir);
        } else {
            array_map('unlink', glob(self::$testCacheDir . '/*'));
        }

        foreach (self::$pages as $page) {
            file_put_contents(self::$testCacheDir . '/' . $page . '.cache', $page);
        }
        file_put_contents(self::$testCacheDir . '/intru.der', 'ShouldNotBeThere');
    }

    /**
     * Remove dummycache folder after each tests.
     */
    public function tearDown()
    {
        array_map('unlink', glob(self::$testCacheDir . '/*'));
        rmdir(self::$testCacheDir);
    }

    /**
     * Purge cached pages
     */
    public function testPurgeCachedPages()
    {
        purgeCachedPages(self::$testCacheDir);
        foreach (self::$pages as $page) {
            $this->assertFileNotExists(self::$testCacheDir . '/' . $page . '.cache');
        }

        $this->assertFileExists(self::$testCacheDir . '/intru.der');
    }

    /**
     * Purge cached pages - missing directory
     */
    public function testPurgeCachedPagesMissingDir()
    {
        $oldlog = ini_get('error_log');
        ini_set('error_log', '/dev/null');
        $this->assertEquals(
            'Cannot purge sandbox/dummycache_missing: no directory',
            purgeCachedPages(self::$testCacheDir . '_missing')
        );
        ini_set('error_log', $oldlog);
    }

    /**
     * Purge cached pages and session cache
     */
    public function testInvalidateCaches()
    {
        $this->assertArrayNotHasKey('tags', $_SESSION);
        $_SESSION['tags'] = array('goodbye', 'cruel', 'world');

        invalidateCaches(self::$testCacheDir);
        foreach (self::$pages as $page) {
            $this->assertFileNotExists(self::$testCacheDir . '/' . $page . '.cache');
        }

        $this->assertArrayNotHasKey('tags', $_SESSION);
    }
}
