<?php

require_once 'application/NetscapeBookmarkUtils.php';

/**
 * Netscape bookmark import and export
 */
class NetscapeBookmarkUtilsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string datastore to test write operations
     */
    protected static $testDatastore = 'sandbox/datastore.php';

    /**
     * @var ReferenceLinkDB instance.
     */
    protected static $refDb = null;

    /**
     * @var LinkDB private LinkDB instance.
     */
    protected static $linkDb = null;

    /**
     * Instantiate reference data
     */
    public static function setUpBeforeClass()
    {
        self::$refDb = new ReferenceLinkDB();
        self::$refDb->write(self::$testDatastore);
        self::$linkDb = new LinkDB(self::$testDatastore, true, false);
    }

    /**
     * Attempt to export an invalid link selection
     * @expectedException              Exception
     * @expectedExceptionMessageRegExp /Invalid export selection/
     */
    public function testFilterAndFormatInvalid()
    {
        NetscapeBookmarkUtils::filterAndFormat(self::$linkDb, 'derp');
    }

    /**
     * Prepare all links for export
     */
    public function testFilterAndFormatAll()
    {
        $links = NetscapeBookmarkUtils::filterAndFormat(self::$linkDb, 'all');
        $this->assertEquals(self::$refDb->countLinks(), sizeof($links));
        foreach ($links as $link) {
            $date = DateTime::createFromFormat(LinkDB::LINK_DATE_FORMAT, $link['linkdate']);
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
     * Prepare private links for export
     */
    public function testFilterAndFormatPrivate()
    {
        $links = NetscapeBookmarkUtils::filterAndFormat(self::$linkDb, 'private');
        $this->assertEquals(self::$refDb->countPrivateLinks(), sizeof($links));
        foreach ($links as $link) {
            $date = DateTime::createFromFormat(LinkDB::LINK_DATE_FORMAT, $link['linkdate']);
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
     * Prepare public links for export
     */
    public function testFilterAndFormatPublic()
    {
        $links = NetscapeBookmarkUtils::filterAndFormat(self::$linkDb, 'public');
        $this->assertEquals(self::$refDb->countPublicLinks(), sizeof($links));
        foreach ($links as $link) {
            $date = DateTime::createFromFormat(LinkDB::LINK_DATE_FORMAT, $link['linkdate']);
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
}
