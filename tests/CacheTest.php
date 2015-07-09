<?php
/**
 * Cache tests
 */

// required to access $_SESSION array
session_start();

require_once 'application/Cache.php';

/**
 * Unitary tests for cached pages
 */
class CachedTest extends PHPUnit_Framework_TestCase
{
    // test cache directory
    protected static $testCacheDir = 'tests/dummycache';

    // dummy cached file names / content
    protected static $pages = array('a', 'toto', 'd7b59c');


    /**
     * Populate the cache with dummy files
     */
    public function setUp()
    {
        if (! is_dir(self::$testCacheDir)) {
            mkdir(self::$testCacheDir);
        }
        
        foreach (self::$pages as $page) {
            file_put_contents(self::$testCacheDir.'/'.$page.'.cache', $page);
        }
    }

    /**
     * Purge cached pages
     */
    public function testPurgeCachedPages()
    {
        purgeCachedPages(self::$testCacheDir);
        foreach (self::$pages as $page) {
            $this->assertFileNotExists(self::$testCacheDir.'/'.$page.'.cache');
        }        
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
            $this->assertFileNotExists(self::$testCacheDir.'/'.$page.'.cache');
        }        

        $this->assertArrayNotHasKey('tags', $_SESSION);
    }
}
