<?php

require_once 'application/NetscapeBookmarkUtils.php';


/**
 * Utility function to load a file's metadata in a $_FILES-like array
 *
 * @param string $filename Basename of the file
 *
 * @return array A $_FILES-like array
 */
function file2array($filename)
{
    return array(
        'filetoupload' => array(
            'name'     => $filename,
            'tmp_name' => __DIR__ . '/input/' . $filename,
            'size'     => filesize(__DIR__ . '/input/' . $filename)
        )
    );
}


/**
 * Netscape bookmark import
 */
class BookmarkImportTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string datastore to test write operations
     */
    protected static $testDatastore = 'sandbox/datastore.php';

    /**
     * @var LinkDB private LinkDB instance
     */
    protected $linkDb = null;

    /**
     * @var string Dummy page cache
     */
    protected $pagecache = 'tests';

    /**
     * Resets test data before each test
     */
    protected function setUp()
    {
        if (file_exists(self::$testDatastore)) {
            unlink(self::$testDatastore);
        }
        // start with an empty datastore
        file_put_contents(self::$testDatastore, '<?php /* S7QysKquBQA= */ ?>');
        $this->linkDb = new LinkDB(self::$testDatastore, true, false);
    }

    /**
     * Attempt to import bookmarks from an empty file
     */
    public function testImportEmptyData()
    {
        $files = file2array('empty.htm');
        $this->assertEquals(
            'File empty.htm (0 bytes) has an unknown file format.'
            .' Nothing was imported.',
            NetscapeBookmarkUtils::import(NULL, $files, NULL, NULL)
        );
        $this->assertEquals(0, count($this->linkDb));
    }

    /**
     * Attempt to import bookmarks from a file with no Doctype
     */
    public function testImportNoDoctype()
    {
        $files = file2array('no_doctype.htm');
        $this->assertEquals(
            'File no_doctype.htm (350 bytes) has an unknown file format. Nothing was imported.',
            NetscapeBookmarkUtils::import(NULL, $files, NULL, NULL)
        );
        $this->assertEquals(0, count($this->linkDb));
    }

    /**
     * Import bookmarks nested in a folder hierarchy
     */
    public function testImportNested()
    {
        $files = file2array('netscape_nested.htm');
        $this->assertEquals(
            'File netscape_nested.htm (1337 bytes) was successfully processed:'
            .' 8 links imported, 0 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import(array(), $files, $this->linkDb, $this->pagecache)
        );
        $this->assertEquals(8, count($this->linkDb));
        $this->assertEquals(2, count_private($this->linkDb));

        $this->assertEquals(
            array(
                'linkdate' => '20160225_205541',
                'title' => 'Nested 1',
                'url' => 'http://nest.ed/1',
                'description' => '',
                'private' => 0,
                'tags' => 'tag1 tag2'
            ),
            $this->linkDb->getLinkFromUrl('http://nest.ed/1')
        );
        $this->assertEquals(
            array(
                'linkdate' => '20160225_205542',
                'title' => 'Nested 1-1',
                'url' => 'http://nest.ed/1-1',
                'description' => '',
                'private' => 0,
                'tags' => 'folder1 tag1 tag2'
            ),
            $this->linkDb->getLinkFromUrl('http://nest.ed/1-1')
        );
        $this->assertEquals(
            array(
                'linkdate' => '20160225_205547',
                'title' => 'Nested 1-2',
                'url' => 'http://nest.ed/1-2',
                'description' => '',
                'private' => 0,
                'tags' => 'folder1 tag3 tag4'
            ),
            $this->linkDb->getLinkFromUrl('http://nest.ed/1-2')
        );
        $this->assertEquals(
            array(
                'linkdate' => '20160202_172222',
                'title' => 'Nested 2-1',
                'url' => 'http://nest.ed/2-1',
                'description' => 'First link of the second section',
                'private' => 1,
                'tags' => 'folder2'
            ),
            $this->linkDb->getLinkFromUrl('http://nest.ed/2-1')
        );
        $this->assertEquals(
            array(
                'linkdate' => '20160119_200227',
                'title' => 'Nested 2-2',
                'url' => 'http://nest.ed/2-2',
                'description' => 'Second link of the second section',
                'private' => 1,
                'tags' => 'folder2'
            ),
            $this->linkDb->getLinkFromUrl('http://nest.ed/2-2')
        );
        $this->assertEquals(
            array(
                'linkdate' => '20160202_172223',
                'title' => 'Nested 3-1',
                'url' => 'http://nest.ed/3-1',
                'description' => '',
                'private' => 0,
                'tags' => 'folder3 folder3-1 tag3'
            ),
            $this->linkDb->getLinkFromUrl('http://nest.ed/3-1')
        );
        $this->assertEquals(
            array(
                'linkdate' => '20160119_200228',
                'title' => 'Nested 3-2',
                'url' => 'http://nest.ed/3-2',
                'description' => '',
                'private' => 0,
                'tags' => 'folder3 folder3-1'
            ),
            $this->linkDb->getLinkFromUrl('http://nest.ed/3-2')
        );
        $this->assertEquals(
            array(
                'linkdate' => '20160229_081541',
                'title' => 'Nested 2',
                'url' => 'http://nest.ed/2',
                'description' => '',
                'private' => 0,
                'tags' => 'tag4'
            ),
            $this->linkDb->getLinkFromUrl('http://nest.ed/2')
        );
    }

    /**
     * Import bookmarks with the default privacy setting (reuse from file)
     *
     * The $_POST array is not set.
     */
    public function testImportDefaultPrivacyNoPost()
    {
        $files = file2array('netscape_basic.htm');
        $this->assertEquals(
            'File netscape_basic.htm (482 bytes) was successfully processed:'
            .' 2 links imported, 0 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import(array(), $files, $this->linkDb, $this->pagecache)
        );
        $this->assertEquals(2, count($this->linkDb));
        $this->assertEquals(1, count_private($this->linkDb));

        $this->assertEquals(
            array(
                'linkdate' => '20001010_105536',
                'title' => 'Secret stuff',
                'url' => 'https://private.tld',
                'description' => "Super-secret stuff you're not supposed to know about",
                'private' => 1,
                'tags' => 'private secret'
            ),
            $this->linkDb->getLinkFromUrl('https://private.tld')
        );
        $this->assertEquals(
            array(
                'linkdate' => '20160225_205548',
                'title' => 'Public stuff',
                'url' => 'http://public.tld',
                'description' => '',
                'private' => 0,
                'tags' => 'public hello world'
            ),
            $this->linkDb->getLinkFromUrl('http://public.tld')
        );
    }

    /**
     * Import bookmarks with the default privacy setting (reuse from file)
     */
    public function testImportKeepPrivacy()
    {
        $post = array('privacy' => 'default');
        $files = file2array('netscape_basic.htm');
        $this->assertEquals(
            'File netscape_basic.htm (482 bytes) was successfully processed:'
            .' 2 links imported, 0 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import($post, $files, $this->linkDb, $this->pagecache)
        );
        $this->assertEquals(2, count($this->linkDb));
        $this->assertEquals(1, count_private($this->linkDb));

        $this->assertEquals(
            array(
                'linkdate' => '20001010_105536',
                'title' => 'Secret stuff',
                'url' => 'https://private.tld',
                'description' => "Super-secret stuff you're not supposed to know about",
                'private' => 1,
                'tags' => 'private secret'
            ),
            $this->linkDb->getLinkFromUrl('https://private.tld')
        );
        $this->assertEquals(
            array(
                'linkdate' => '20160225_205548',
                'title' => 'Public stuff',
                'url' => 'http://public.tld',
                'description' => '',
                'private' => 0,
                'tags' => 'public hello world'
            ),
            $this->linkDb->getLinkFromUrl('http://public.tld')
        );
    }

    /**
     * Import links as public
     */
    public function testImportAsPublic()
    {
        $post = array('privacy' => 'public');
        $files = file2array('netscape_basic.htm');
        $this->assertEquals(
            'File netscape_basic.htm (482 bytes) was successfully processed:'
            .' 2 links imported, 0 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import($post, $files, $this->linkDb, $this->pagecache)
        );
        $this->assertEquals(2, count($this->linkDb));
        $this->assertEquals(0, count_private($this->linkDb));
        $this->assertEquals(
            0,
            $this->linkDb['20001010_105536']['private']
        );
        $this->assertEquals(
            0,
            $this->linkDb['20160225_205548']['private']
        );
    }

    /**
     * Import links as private
     */
    public function testImportAsPrivate()
    {
        $post = array('privacy' => 'private');
        $files = file2array('netscape_basic.htm');
        $this->assertEquals(
            'File netscape_basic.htm (482 bytes) was successfully processed:'
            .' 2 links imported, 0 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import($post, $files, $this->linkDb, $this->pagecache)
        );
        $this->assertEquals(2, count($this->linkDb));
        $this->assertEquals(2, count_private($this->linkDb));
        $this->assertEquals(
            1,
            $this->linkDb['20001010_105536']['private']
        );
        $this->assertEquals(
            1,
            $this->linkDb['20160225_205548']['private']
        );
    }

    /**
     * Overwrite private links so they become public
     */
    public function testOverwriteAsPublic()
    {
        $files = file2array('netscape_basic.htm');

        // import links as private
        $post = array('privacy' => 'private');
        $this->assertEquals(
            'File netscape_basic.htm (482 bytes) was successfully processed:'
            .' 2 links imported, 0 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import($post, $files, $this->linkDb, $this->pagecache)
        );
        $this->assertEquals(2, count($this->linkDb));
        $this->assertEquals(2, count_private($this->linkDb));
        $this->assertEquals(
            1,
            $this->linkDb['20001010_105536']['private']
        );
        $this->assertEquals(
            1,
            $this->linkDb['20160225_205548']['private']
        );

        // re-import as public, enable overwriting
        $post = array(
            'privacy' => 'public',
            'overwrite' => 'true'
        );
        $this->assertEquals(
            'File netscape_basic.htm (482 bytes) was successfully processed:'
            .' 2 links imported, 2 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import($post, $files, $this->linkDb, $this->pagecache)
        );
        $this->assertEquals(2, count($this->linkDb));
        $this->assertEquals(0, count_private($this->linkDb));
        $this->assertEquals(
            0,
            $this->linkDb['20001010_105536']['private']
        );
        $this->assertEquals(
            0,
            $this->linkDb['20160225_205548']['private']
        );
    }

    /**
     * Overwrite public links so they become private
     */
    public function testOverwriteAsPrivate()
    {
        $files = file2array('netscape_basic.htm');

        // import links as public
        $post = array('privacy' => 'public');
        $this->assertEquals(
            'File netscape_basic.htm (482 bytes) was successfully processed:'
            .' 2 links imported, 0 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import($post, $files, $this->linkDb, $this->pagecache)
        );
        $this->assertEquals(2, count($this->linkDb));
        $this->assertEquals(0, count_private($this->linkDb));
        $this->assertEquals(
            0,
            $this->linkDb['20001010_105536']['private']
        );
        $this->assertEquals(
            0,
            $this->linkDb['20160225_205548']['private']
        );

        // re-import as private, enable overwriting
        $post = array(
            'privacy' => 'private',
            'overwrite' => 'true'
        );
        $this->assertEquals(
            'File netscape_basic.htm (482 bytes) was successfully processed:'
            .' 2 links imported, 2 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import($post, $files, $this->linkDb, $this->pagecache)
        );
        $this->assertEquals(2, count($this->linkDb));
        $this->assertEquals(2, count_private($this->linkDb));
        $this->assertEquals(
            1,
            $this->linkDb['20001010_105536']['private']
        );
        $this->assertEquals(
            1,
            $this->linkDb['20160225_205548']['private']
        );
    }

    /**
     * Attept to import the same links twice without enabling overwriting
     */
    public function testSkipOverwrite()
    {
        $post = array('privacy' => 'public');
        $files = file2array('netscape_basic.htm');
        $this->assertEquals(
            'File netscape_basic.htm (482 bytes) was successfully processed:'
            .' 2 links imported, 0 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import($post, $files, $this->linkDb, $this->pagecache)
        );
        $this->assertEquals(2, count($this->linkDb));
        $this->assertEquals(0, count_private($this->linkDb));

        // re-import as private, DO NOT enable overwriting
        $post = array('privacy' => 'private');
        $this->assertEquals(
            'File netscape_basic.htm (482 bytes) was successfully processed:'
            .' 0 links imported, 0 links overwritten, 2 links skipped.',
            NetscapeBookmarkUtils::import($post, $files, $this->linkDb, $this->pagecache)
        );
        $this->assertEquals(2, count($this->linkDb));
        $this->assertEquals(0, count_private($this->linkDb));
    }

    /**
     * Add user-specified tags to all imported bookmarks
     */
    public function testSetDefaultTags()
    {
        $post = array(
            'privacy' => 'public',
            'default_tags' => 'tag1,tag2 tag3'
        );
        $files = file2array('netscape_basic.htm');
        $this->assertEquals(
            'File netscape_basic.htm (482 bytes) was successfully processed:'
            .' 2 links imported, 0 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import($post, $files, $this->linkDb, $this->pagecache)
        );
        $this->assertEquals(2, count($this->linkDb));
        $this->assertEquals(0, count_private($this->linkDb));
        $this->assertEquals(
            'tag1 tag2 tag3 private secret',
            $this->linkDb['20001010_105536']['tags']
        );
        $this->assertEquals(
            'tag1 tag2 tag3 public hello world',
            $this->linkDb['20160225_205548']['tags']
        );
    }

    /**
     * The user-specified tags contain characters to be escaped
     */
    public function testSanitizeDefaultTags()
    {
        $post = array(
            'privacy' => 'public',
            'default_tags' => 'tag1&,tag2 "tag3"'
        );
        $files = file2array('netscape_basic.htm');
        $this->assertEquals(
            'File netscape_basic.htm (482 bytes) was successfully processed:'
            .' 2 links imported, 0 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import($post, $files, $this->linkDb, $this->pagecache)
        );
        $this->assertEquals(2, count($this->linkDb));
        $this->assertEquals(0, count_private($this->linkDb));
        $this->assertEquals(
            'tag1&amp; tag2 &quot;tag3&quot; private secret',
            $this->linkDb['20001010_105536']['tags']
        );
        $this->assertEquals(
            'tag1&amp; tag2 &quot;tag3&quot; public hello world',
            $this->linkDb['20160225_205548']['tags']
        );
    }

    /**
     * Ensure each imported bookmark has a unique linkdate
     *
     * See https://github.com/shaarli/Shaarli/issues/351
     */
    public function testImportSameDate()
    {
        $files = file2array('same_date.htm');
        $this->assertEquals(
            'File same_date.htm (453 bytes) was successfully processed:'
            .' 3 links imported, 0 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import(array(), $files, $this->linkDb, $this->pagecache)
        );
        $this->assertEquals(3, count($this->linkDb));
        $this->assertEquals(0, count_private($this->linkDb));
        $this->assertEquals(
            '20160225_205548',
            $this->linkDb['20160225_205548']['linkdate']
        );
        $this->assertEquals(
            '20160225_205549',
            $this->linkDb['20160225_205549']['linkdate']
        );
        $this->assertEquals(
            '20160225_205550',
            $this->linkDb['20160225_205550']['linkdate']
        );
    }
}
