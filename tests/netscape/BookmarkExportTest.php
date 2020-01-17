<?php
namespace Shaarli\Netscape;

use Shaarli\Bookmark\BookmarkFileService;
use Shaarli\Bookmark\LinkDB;
use Shaarli\Config\ConfigManager;
use Shaarli\Formatter\FormatterFactory;
use Shaarli\Formatter\BookmarkFormatter;
use Shaarli\History;

require_once 'tests/utils/ReferenceLinkDB.php';

/**
 * Netscape bookmark export
 */
class BookmarkExportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string datastore to test write operations
     */
    protected static $testDatastore = 'sandbox/datastore.php';

    /**
     * @var \ReferenceLinkDB instance.
     */
    protected static $refDb = null;

    /**
     * @var BookmarkFileService private instance.
     */
    protected static $bookmarkService = null;

    /**
     * @var BookmarkFormatter instance
     */
    protected static $formatter;

    /**
     * Instantiate reference data
     */
    public static function setUpBeforeClass()
    {
        $conf = new ConfigManager('tests/utils/config/configJson');
        $conf->set('resource.datastore', self::$testDatastore);
        self::$refDb = new \ReferenceLinkDB();
        self::$refDb->write(self::$testDatastore);
        $history = new History('sandbox/history.php');
        self::$bookmarkService = new BookmarkFileService($conf, $history, true);
        $factory = new FormatterFactory($conf);
        self::$formatter = $factory->getFormatter('raw');
    }

    /**
     * Attempt to export an invalid link selection
     * @expectedException              Exception
     * @expectedExceptionMessageRegExp /Invalid export selection/
     */
    public function testFilterAndFormatInvalid()
    {
        NetscapeBookmarkUtils::filterAndFormat(
            self::$bookmarkService,
            self::$formatter,
            'derp',
            false,
            ''
        );
    }

    /**
     * Prepare all bookmarks for export
     */
    public function testFilterAndFormatAll()
    {
        $links = NetscapeBookmarkUtils::filterAndFormat(
            self::$bookmarkService,
            self::$formatter,
            'all',
            false,
            ''
        );
        $this->assertEquals(self::$refDb->countLinks(), sizeof($links));
        foreach ($links as $link) {
            $date = $link['created'];
            $this->assertEquals(
                $date->getTimestamp(),
                $link['timestamp']
            );
            $this->assertEquals(
                str_replace(' ', ',', $link['tags']),
                $link['taglist']
            );
        }
    }

    /**
     * Prepare private bookmarks for export
     */
    public function testFilterAndFormatPrivate()
    {
        $links = NetscapeBookmarkUtils::filterAndFormat(
            self::$bookmarkService,
            self::$formatter,
            'private',
            false,
            ''
        );
        $this->assertEquals(self::$refDb->countPrivateLinks(), sizeof($links));
        foreach ($links as $link) {
            $date = $link['created'];
            $this->assertEquals(
                $date->getTimestamp(),
                $link['timestamp']
            );
            $this->assertEquals(
                str_replace(' ', ',', $link['tags']),
                $link['taglist']
            );
        }
    }

    /**
     * Prepare public bookmarks for export
     */
    public function testFilterAndFormatPublic()
    {
        $links = NetscapeBookmarkUtils::filterAndFormat(
            self::$bookmarkService,
            self::$formatter,
            'public',
            false,
            ''
        );
        $this->assertEquals(self::$refDb->countPublicLinks(), sizeof($links));
        foreach ($links as $link) {
            $date = $link['created'];
            $this->assertEquals(
                $date->getTimestamp(),
                $link['timestamp']
            );
            $this->assertEquals(
                str_replace(' ', ',', $link['tags']),
                $link['taglist']
            );
        }
    }

    /**
     * Do not prepend notes with the Shaarli index's URL
     */
    public function testFilterAndFormatDoNotPrependNoteUrl()
    {
        $links = NetscapeBookmarkUtils::filterAndFormat(
            self::$bookmarkService,
            self::$formatter,
            'public',
            false,
            ''
        );
        $this->assertEquals(
            '?WDWyig',
            $links[2]['url']
        );
    }

    /**
     * Prepend notes with the Shaarli index's URL
     */
    public function testFilterAndFormatPrependNoteUrl()
    {
        $indexUrl = 'http://localhost:7469/shaarli/';
        $links = NetscapeBookmarkUtils::filterAndFormat(
            self::$bookmarkService,
            self::$formatter,
            'public',
            true,
            $indexUrl
        );
        $this->assertEquals(
            $indexUrl . '?WDWyig',
            $links[2]['url']
        );
    }
}
