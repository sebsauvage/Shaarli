<?php

/**
 * Cache tests
 */

namespace Shaarli\Render;

use Shaarli\Security\SessionManager;
use Shaarli\TestCase;

/**
 * Unitary tests for cached pages
 */
class PageCacheManagerTest extends TestCase
{
    // test cache directory
    protected static $testCacheDir = 'sandbox/dummycache';

    // dummy cached file names / content
    protected static $pages = ['a', 'toto', 'd7b59c'];

    /** @var PageCacheManager */
    protected $cacheManager;

    /** @var SessionManager */
    protected $sessionManager;

    /**
     * Populate the cache with dummy files
     */
    protected function setUp(): void
    {
        $this->cacheManager = new PageCacheManager(static::$testCacheDir, true);

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
    protected function tearDown(): void
    {
        array_map('unlink', glob(self::$testCacheDir . '/*'));
        rmdir(self::$testCacheDir);
    }

    /**
     * Purge cached pages
     */
    public function testPurgeCachedPages()
    {
        $this->cacheManager->purgeCachedPages();
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
        $this->cacheManager = new PageCacheManager(self::$testCacheDir . '_missing', true);

        $oldlog = ini_get('error_log');
        ini_set('error_log', '/dev/null');
        $this->assertEquals(
            'Cannot purge sandbox/dummycache_missing: no directory',
            $this->cacheManager->purgeCachedPages()
        );
        ini_set('error_log', $oldlog);
    }
}
