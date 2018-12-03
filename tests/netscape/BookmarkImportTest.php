<?php
namespace Shaarli\Netscape;

use DateTime;
use Shaarli\Bookmark\LinkDB;
use Shaarli\Config\ConfigManager;
use Shaarli\History;

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
class BookmarkImportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string datastore to test write operations
     */
    protected static $testDatastore = 'sandbox/datastore.php';

    /**
     * @var string History file path
     */
    protected static $historyFilePath = 'sandbox/history.php';

    /**
     * @var LinkDB private LinkDB instance
     */
    protected $linkDb = null;

    /**
     * @var string Dummy page cache
     */
    protected $pagecache = 'tests';

    /**
     * @var ConfigManager instance.
     */
    protected $conf;

    /**
     * @var History instance.
     */
    protected $history;

    /**
     * @var string Save the current timezone.
     */
    protected static $defaultTimeZone;

    public static function setUpBeforeClass()
    {
        self::$defaultTimeZone = date_default_timezone_get();
        // Timezone without DST for test consistency
        date_default_timezone_set('Africa/Nairobi');
    }

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
        $this->conf = new ConfigManager('tests/utils/config/configJson');
        $this->conf->set('resource.page_cache', $this->pagecache);
        $this->history = new History(self::$historyFilePath);
    }

    /**
     * Delete history file.
     */
    public function tearDown()
    {
        @unlink(self::$historyFilePath);
    }

    public static function tearDownAfterClass()
    {
        date_default_timezone_set(self::$defaultTimeZone);
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
            NetscapeBookmarkUtils::import(null, $files, null, $this->conf, $this->history)
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
            NetscapeBookmarkUtils::import(null, $files, null, $this->conf, $this->history)
        );
        $this->assertEquals(0, count($this->linkDb));
    }

    /**
     * Attempt to import bookmarks from a file with a lowercase Doctype
     */
    public function testImportLowecaseDoctype()
    {
        $files = file2array('lowercase_doctype.htm');
        $this->assertStringMatchesFormat(
            'File lowercase_doctype.htm (386 bytes) was successfully processed in %d seconds:'
            .' 2 links imported, 0 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import(null, $files, $this->linkDb, $this->conf, $this->history)
        );
        $this->assertEquals(2, count($this->linkDb));
    }


    /**
     * Ensure IE dumps are supported
     */
    public function testImportInternetExplorerEncoding()
    {
        $files = file2array('internet_explorer_encoding.htm');
        $this->assertStringMatchesFormat(
            'File internet_explorer_encoding.htm (356 bytes) was successfully processed in %d seconds:'
            .' 1 links imported, 0 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import([], $files, $this->linkDb, $this->conf, $this->history)
        );
        $this->assertEquals(1, count($this->linkDb));
        $this->assertEquals(0, count_private($this->linkDb));

        $this->assertEquals(
            array(
                'id' => 0,
                'created' => DateTime::createFromFormat(LinkDB::LINK_DATE_FORMAT, '20160618_203944'),
                'title' => 'Hg Init a Mercurial tutorial by Joel Spolsky',
                'url' => 'http://hginit.com/',
                'description' => '',
                'private' => 0,
                'tags' => '',
                'shorturl' => 'La37cg',
            ),
            $this->linkDb->getLinkFromUrl('http://hginit.com/')
        );
    }

    /**
     * Import bookmarks nested in a folder hierarchy
     */
    public function testImportNested()
    {
        $files = file2array('netscape_nested.htm');
        $this->assertStringMatchesFormat(
            'File netscape_nested.htm (1337 bytes) was successfully processed in %d seconds:'
            .' 8 links imported, 0 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import([], $files, $this->linkDb, $this->conf, $this->history)
        );
        $this->assertEquals(8, count($this->linkDb));
        $this->assertEquals(2, count_private($this->linkDb));

        $this->assertEquals(
            array(
                'id' => 0,
                'created' => DateTime::createFromFormat(LinkDB::LINK_DATE_FORMAT, '20160225_235541'),
                'title' => 'Nested 1',
                'url' => 'http://nest.ed/1',
                'description' => '',
                'private' => 0,
                'tags' => 'tag1 tag2',
                'shorturl' => 'KyDNKA',
            ),
            $this->linkDb->getLinkFromUrl('http://nest.ed/1')
        );
        $this->assertEquals(
            array(
                'id' => 1,
                'created' => DateTime::createFromFormat(LinkDB::LINK_DATE_FORMAT, '20160225_235542'),
                'title' => 'Nested 1-1',
                'url' => 'http://nest.ed/1-1',
                'description' => '',
                'private' => 0,
                'tags' => 'folder1 tag1 tag2',
                'shorturl' => 'T2LnXg',
            ),
            $this->linkDb->getLinkFromUrl('http://nest.ed/1-1')
        );
        $this->assertEquals(
            array(
                'id' => 2,
                'created' => DateTime::createFromFormat(LinkDB::LINK_DATE_FORMAT, '20160225_235547'),
                'title' => 'Nested 1-2',
                'url' => 'http://nest.ed/1-2',
                'description' => '',
                'private' => 0,
                'tags' => 'folder1 tag3 tag4',
                'shorturl' => '46SZxA',
            ),
            $this->linkDb->getLinkFromUrl('http://nest.ed/1-2')
        );
        $this->assertEquals(
            array(
                'id' => 3,
                'created' => DateTime::createFromFormat(LinkDB::LINK_DATE_FORMAT, '20160202_202222'),
                'title' => 'Nested 2-1',
                'url' => 'http://nest.ed/2-1',
                'description' => 'First link of the second section',
                'private' => 1,
                'tags' => 'folder2',
                'shorturl' => '4UHOSw',
            ),
            $this->linkDb->getLinkFromUrl('http://nest.ed/2-1')
        );
        $this->assertEquals(
            array(
                'id' => 4,
                'created' => DateTime::createFromFormat(LinkDB::LINK_DATE_FORMAT, '20160119_230227'),
                'title' => 'Nested 2-2',
                'url' => 'http://nest.ed/2-2',
                'description' => 'Second link of the second section',
                'private' => 1,
                'tags' => 'folder2',
                'shorturl' => 'yfzwbw',
            ),
            $this->linkDb->getLinkFromUrl('http://nest.ed/2-2')
        );
        $this->assertEquals(
            array(
                'id' => 5,
                'created' => DateTime::createFromFormat(LinkDB::LINK_DATE_FORMAT, '20160202_202222'),
                'title' => 'Nested 3-1',
                'url' => 'http://nest.ed/3-1',
                'description' => '',
                'private' => 0,
                'tags' => 'folder3 folder3-1 tag3',
                'shorturl' => 'UwxIUQ',
            ),
            $this->linkDb->getLinkFromUrl('http://nest.ed/3-1')
        );
        $this->assertEquals(
            array(
                'id' => 6,
                'created' => DateTime::createFromFormat(LinkDB::LINK_DATE_FORMAT, '20160119_230227'),
                'title' => 'Nested 3-2',
                'url' => 'http://nest.ed/3-2',
                'description' => '',
                'private' => 0,
                'tags' => 'folder3 folder3-1',
                'shorturl' => 'p8dyZg',
            ),
            $this->linkDb->getLinkFromUrl('http://nest.ed/3-2')
        );
        $this->assertEquals(
            array(
                'id' => 7,
                'created' => DateTime::createFromFormat(LinkDB::LINK_DATE_FORMAT, '20160229_111541'),
                'title' => 'Nested 2',
                'url' => 'http://nest.ed/2',
                'description' => '',
                'private' => 0,
                'tags' => 'tag4',
                'shorturl' => 'Gt3Uug',
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
        $this->assertStringMatchesFormat(
            'File netscape_basic.htm (482 bytes) was successfully processed in %d seconds:'
            .' 2 links imported, 0 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import([], $files, $this->linkDb, $this->conf, $this->history)
        );

        $this->assertEquals(2, count($this->linkDb));
        $this->assertEquals(1, count_private($this->linkDb));

        $this->assertEquals(
            array(
                'id' => 0,
                // Old link - UTC+4 (note that TZ in the import file is ignored).
                'created' => DateTime::createFromFormat(LinkDB::LINK_DATE_FORMAT, '20001010_135536'),
                'title' => 'Secret stuff',
                'url' => 'https://private.tld',
                'description' => "Super-secret stuff you're not supposed to know about",
                'private' => 1,
                'tags' => 'private secret',
                'shorturl' => 'EokDtA',
            ),
            $this->linkDb->getLinkFromUrl('https://private.tld')
        );
        $this->assertEquals(
            array(
                'id' => 1,
                'created' => DateTime::createFromFormat(LinkDB::LINK_DATE_FORMAT, '20160225_235548'),
                'title' => 'Public stuff',
                'url' => 'http://public.tld',
                'description' => '',
                'private' => 0,
                'tags' => 'public hello world',
                'shorturl' => 'Er9ddA',
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
        $this->assertStringMatchesFormat(
            'File netscape_basic.htm (482 bytes) was successfully processed in %d seconds:'
            .' 2 links imported, 0 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import($post, $files, $this->linkDb, $this->conf, $this->history)
        );
        $this->assertEquals(2, count($this->linkDb));
        $this->assertEquals(1, count_private($this->linkDb));

        $this->assertEquals(
            array(
                'id' => 0,
                // Note that TZ in the import file is ignored.
                'created' => DateTime::createFromFormat(LinkDB::LINK_DATE_FORMAT, '20001010_135536'),
                'title' => 'Secret stuff',
                'url' => 'https://private.tld',
                'description' => "Super-secret stuff you're not supposed to know about",
                'private' => 1,
                'tags' => 'private secret',
                'shorturl' => 'EokDtA',
            ),
            $this->linkDb->getLinkFromUrl('https://private.tld')
        );
        $this->assertEquals(
            array(
                'id' => 1,
                'created' => DateTime::createFromFormat(LinkDB::LINK_DATE_FORMAT, '20160225_235548'),
                'title' => 'Public stuff',
                'url' => 'http://public.tld',
                'description' => '',
                'private' => 0,
                'tags' => 'public hello world',
                'shorturl' => 'Er9ddA',
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
        $this->assertStringMatchesFormat(
            'File netscape_basic.htm (482 bytes) was successfully processed in %d seconds:'
            .' 2 links imported, 0 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import($post, $files, $this->linkDb, $this->conf, $this->history)
        );
        $this->assertEquals(2, count($this->linkDb));
        $this->assertEquals(0, count_private($this->linkDb));
        $this->assertEquals(
            0,
            $this->linkDb[0]['private']
        );
        $this->assertEquals(
            0,
            $this->linkDb[1]['private']
        );
    }

    /**
     * Import links as private
     */
    public function testImportAsPrivate()
    {
        $post = array('privacy' => 'private');
        $files = file2array('netscape_basic.htm');
        $this->assertStringMatchesFormat(
            'File netscape_basic.htm (482 bytes) was successfully processed in %d seconds:'
            .' 2 links imported, 0 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import($post, $files, $this->linkDb, $this->conf, $this->history)
        );
        $this->assertEquals(2, count($this->linkDb));
        $this->assertEquals(2, count_private($this->linkDb));
        $this->assertEquals(
            1,
            $this->linkDb['0']['private']
        );
        $this->assertEquals(
            1,
            $this->linkDb['1']['private']
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
        $this->assertStringMatchesFormat(
            'File netscape_basic.htm (482 bytes) was successfully processed in %d seconds:'
            .' 2 links imported, 0 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import($post, $files, $this->linkDb, $this->conf, $this->history)
        );
        $this->assertEquals(2, count($this->linkDb));
        $this->assertEquals(2, count_private($this->linkDb));
        $this->assertEquals(
            1,
            $this->linkDb[0]['private']
        );
        $this->assertEquals(
            1,
            $this->linkDb[1]['private']
        );
        // re-import as public, enable overwriting
        $post = array(
            'privacy' => 'public',
            'overwrite' => 'true'
        );
        $this->assertStringMatchesFormat(
            'File netscape_basic.htm (482 bytes) was successfully processed in %d seconds:'
            .' 2 links imported, 2 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import($post, $files, $this->linkDb, $this->conf, $this->history)
        );
        $this->assertEquals(2, count($this->linkDb));
        $this->assertEquals(0, count_private($this->linkDb));
        $this->assertEquals(
            0,
            $this->linkDb[0]['private']
        );
        $this->assertEquals(
            0,
            $this->linkDb[1]['private']
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
        $this->assertStringMatchesFormat(
            'File netscape_basic.htm (482 bytes) was successfully processed in %d seconds:'
            .' 2 links imported, 0 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import($post, $files, $this->linkDb, $this->conf, $this->history)
        );
        $this->assertEquals(2, count($this->linkDb));
        $this->assertEquals(0, count_private($this->linkDb));
        $this->assertEquals(
            0,
            $this->linkDb['0']['private']
        );
        $this->assertEquals(
            0,
            $this->linkDb['1']['private']
        );

        // re-import as private, enable overwriting
        $post = array(
            'privacy' => 'private',
            'overwrite' => 'true'
        );
        $this->assertStringMatchesFormat(
            'File netscape_basic.htm (482 bytes) was successfully processed in %d seconds:'
            .' 2 links imported, 2 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import($post, $files, $this->linkDb, $this->conf, $this->history)
        );
        $this->assertEquals(2, count($this->linkDb));
        $this->assertEquals(2, count_private($this->linkDb));
        $this->assertEquals(
            1,
            $this->linkDb['0']['private']
        );
        $this->assertEquals(
            1,
            $this->linkDb['1']['private']
        );
    }

    /**
     * Attept to import the same links twice without enabling overwriting
     */
    public function testSkipOverwrite()
    {
        $post = array('privacy' => 'public');
        $files = file2array('netscape_basic.htm');
        $this->assertStringMatchesFormat(
            'File netscape_basic.htm (482 bytes) was successfully processed in %d seconds:'
            .' 2 links imported, 0 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import($post, $files, $this->linkDb, $this->conf, $this->history)
        );
        $this->assertEquals(2, count($this->linkDb));
        $this->assertEquals(0, count_private($this->linkDb));

        // re-import as private, DO NOT enable overwriting
        $post = array('privacy' => 'private');
        $this->assertStringMatchesFormat(
            'File netscape_basic.htm (482 bytes) was successfully processed in %d seconds:'
            .' 0 links imported, 0 links overwritten, 2 links skipped.',
            NetscapeBookmarkUtils::import($post, $files, $this->linkDb, $this->conf, $this->history)
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
        $this->assertStringMatchesFormat(
            'File netscape_basic.htm (482 bytes) was successfully processed in %d seconds:'
            .' 2 links imported, 0 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import($post, $files, $this->linkDb, $this->conf, $this->history)
        );
        $this->assertEquals(2, count($this->linkDb));
        $this->assertEquals(0, count_private($this->linkDb));
        $this->assertEquals(
            'tag1 tag2 tag3 private secret',
            $this->linkDb['0']['tags']
        );
        $this->assertEquals(
            'tag1 tag2 tag3 public hello world',
            $this->linkDb['1']['tags']
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
        $this->assertStringMatchesFormat(
            'File netscape_basic.htm (482 bytes) was successfully processed in %d seconds:'
            .' 2 links imported, 0 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import($post, $files, $this->linkDb, $this->conf, $this->history)
        );
        $this->assertEquals(2, count($this->linkDb));
        $this->assertEquals(0, count_private($this->linkDb));
        $this->assertEquals(
            'tag1&amp; tag2 &quot;tag3&quot; private secret',
            $this->linkDb['0']['tags']
        );
        $this->assertEquals(
            'tag1&amp; tag2 &quot;tag3&quot; public hello world',
            $this->linkDb['1']['tags']
        );
    }

    /**
     * Ensure each imported bookmark has a unique id
     *
     * See https://github.com/shaarli/Shaarli/issues/351
     */
    public function testImportSameDate()
    {
        $files = file2array('same_date.htm');
        $this->assertStringMatchesFormat(
            'File same_date.htm (453 bytes) was successfully processed in %d seconds:'
            .' 3 links imported, 0 links overwritten, 0 links skipped.',
            NetscapeBookmarkUtils::import(array(), $files, $this->linkDb, $this->conf, $this->history)
        );
        $this->assertEquals(3, count($this->linkDb));
        $this->assertEquals(0, count_private($this->linkDb));
        $this->assertEquals(
            0,
            $this->linkDb[0]['id']
        );
        $this->assertEquals(
            1,
            $this->linkDb[1]['id']
        );
        $this->assertEquals(
            2,
            $this->linkDb[2]['id']
        );
    }

    public function testImportCreateUpdateHistory()
    {
        $post = [
            'privacy' => 'public',
            'overwrite' => 'true',
        ];
        $files = file2array('netscape_basic.htm');
        NetscapeBookmarkUtils::import($post, $files, $this->linkDb, $this->conf, $this->history);
        $history = $this->history->getHistory();
        $this->assertEquals(1, count($history));
        $this->assertEquals(History::IMPORT, $history[0]['event']);
        $this->assertTrue(new DateTime('-5 seconds') < $history[0]['datetime']);

        // re-import as private, enable overwriting
        NetscapeBookmarkUtils::import($post, $files, $this->linkDb, $this->conf, $this->history);
        $history = $this->history->getHistory();
        $this->assertEquals(2, count($history));
        $this->assertEquals(History::IMPORT, $history[0]['event']);
        $this->assertTrue(new DateTime('-5 seconds') < $history[0]['datetime']);
        $this->assertEquals(History::IMPORT, $history[1]['event']);
        $this->assertTrue(new DateTime('-5 seconds') < $history[1]['datetime']);
    }
}
