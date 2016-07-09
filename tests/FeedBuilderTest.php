<?php

require_once 'application/FeedBuilder.php';
require_once 'application/LinkDB.php';

/**
 * FeedBuilderTest class.
 *
 * Unit tests for FeedBuilder.
 */
class FeedBuilderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string locale Basque (Spain).
     */
    public static $LOCALE = 'eu_ES';

    /**
     * @var string language in RSS format.
     */
    public static $RSS_LANGUAGE = 'eu-es';

    /**
     * @var string language in ATOM format.
     */
    public static $ATOM_LANGUAGUE = 'eu';

    protected static $testDatastore = 'sandbox/datastore.php';

    public static $linkDB;

    public static $serverInfo;

    /**
     * Called before every test method.
     */
    public static function setUpBeforeClass()
    {
        $refLinkDB = new ReferenceLinkDB();
        $refLinkDB->write(self::$testDatastore);
        self::$linkDB = new LinkDB(self::$testDatastore, true, false);
        self::$serverInfo = array(
            'HTTPS' => 'Off',
            'SERVER_NAME' => 'host.tld',
            'SERVER_PORT' => '80',
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_URI' => '/index.php?do=feed',
        );
    }

    /**
     * Test GetTypeLanguage().
     */
    public function testGetTypeLanguage()
    {
        $feedBuilder = new FeedBuilder(null, FeedBuilder::$FEED_ATOM, null, null, false);
        $feedBuilder->setLocale(self::$LOCALE);
        $this->assertEquals(self::$ATOM_LANGUAGUE, $feedBuilder->getTypeLanguage());
        $feedBuilder = new FeedBuilder(null, FeedBuilder::$FEED_RSS, null, null, false);
        $feedBuilder->setLocale(self::$LOCALE);
        $this->assertEquals(self::$RSS_LANGUAGE, $feedBuilder->getTypeLanguage());
        $feedBuilder = new FeedBuilder(null, FeedBuilder::$FEED_ATOM, null, null, false);
        $this->assertEquals('en', $feedBuilder->getTypeLanguage());
        $feedBuilder = new FeedBuilder(null, FeedBuilder::$FEED_RSS, null, null, false);
        $this->assertEquals('en-en', $feedBuilder->getTypeLanguage());
    }

    /**
     * Test buildData with RSS feed.
     */
    public function testRSSBuildData()
    {
        $feedBuilder = new FeedBuilder(self::$linkDB, FeedBuilder::$FEED_RSS, self::$serverInfo, null, false);
        $feedBuilder->setLocale(self::$LOCALE);
        $data = $feedBuilder->buildData();
        // Test headers (RSS)
        $this->assertEquals(self::$RSS_LANGUAGE, $data['language']);
        $this->assertEmpty($data['pubsubhub_url']);
        $this->assertRegExp('/Tue, 10 Mar 2015 11:46:51 \+\d{4}/', $data['last_update']);
        $this->assertEquals(true, $data['show_dates']);
        $this->assertEquals('http://host.tld/index.php?do=feed', $data['self_link']);
        $this->assertEquals('http://host.tld/', $data['index_url']);
        $this->assertFalse($data['usepermalinks']);
        $this->assertEquals(ReferenceLinkDB::$NB_LINKS_TOTAL, count($data['links']));

        // Test first link (note link)
        $link = array_shift($data['links']);
        $this->assertEquals('20150310_114651', $link['linkdate']);
        $this->assertEquals('http://host.tld/?WDWyig', $link['guid']);
        $this->assertEquals('http://host.tld/?WDWyig', $link['url']);
        $this->assertRegExp('/Tue, 10 Mar 2015 11:46:51 \+\d{4}/', $link['iso_date']);
        $this->assertContains('Stallman has a beard', $link['description']);
        $this->assertContains('Permalink', $link['description']);
        $this->assertContains('http://host.tld/?WDWyig', $link['description']);
        $this->assertEquals(1, count($link['taglist']));
        $this->assertEquals('sTuff', $link['taglist'][0]);

        // Test URL with external link.
        $this->assertEquals('https://static.fsf.org/nosvn/faif-2.0.pdf', $data['links']['20150310_114633']['url']);

        // Test multitags.
        $this->assertEquals(5, count($data['links']['20141125_084734']['taglist']));
        $this->assertEquals('css', $data['links']['20141125_084734']['taglist'][0]);
    }

    /**
     * Test buildData with ATOM feed (test only specific to ATOM).
     */
    public function testAtomBuildData()
    {
        $feedBuilder = new FeedBuilder(self::$linkDB, FeedBuilder::$FEED_ATOM, self::$serverInfo, null, false);
        $feedBuilder->setLocale(self::$LOCALE);
        $data = $feedBuilder->buildData();
        $this->assertEquals(ReferenceLinkDB::$NB_LINKS_TOTAL, count($data['links']));
        $link = array_shift($data['links']);
        $this->assertRegExp('/2015-03-10T11:46:51\+\d{2}:+\d{2}/', $link['iso_date']);
    }

    /**
     * Test buildData with search criteria.
     */
    public function testBuildDataFiltered()
    {
        $criteria = array(
            'searchtags' => 'stuff',
            'searchterm' => 'beard',
        );
        $feedBuilder = new FeedBuilder(self::$linkDB, FeedBuilder::$FEED_ATOM, self::$serverInfo, $criteria, false);
        $feedBuilder->setLocale(self::$LOCALE);
        $data = $feedBuilder->buildData();
        $this->assertEquals(1, count($data['links']));
        $link = array_shift($data['links']);
        $this->assertEquals('20150310_114651', $link['linkdate']);
    }

    /**
     * Test buildData with nb limit.
     */
    public function testBuildDataCount()
    {
        $criteria = array(
            'nb' => '1',
        );
        $feedBuilder = new FeedBuilder(self::$linkDB, FeedBuilder::$FEED_ATOM, self::$serverInfo, $criteria, false);
        $feedBuilder->setLocale(self::$LOCALE);
        $data = $feedBuilder->buildData();
        $this->assertEquals(1, count($data['links']));
        $link = array_shift($data['links']);
        $this->assertEquals('20150310_114651', $link['linkdate']);
    }

    /**
     * Test buildData with permalinks on.
     */
    public function testBuildDataPermalinks()
    {
        $feedBuilder = new FeedBuilder(self::$linkDB, FeedBuilder::$FEED_ATOM, self::$serverInfo, null, false);
        $feedBuilder->setLocale(self::$LOCALE);
        $feedBuilder->setUsePermalinks(true);
        $data = $feedBuilder->buildData();
        $this->assertEquals(ReferenceLinkDB::$NB_LINKS_TOTAL, count($data['links']));
        $this->assertTrue($data['usepermalinks']);
        // First link is a permalink
        $link = array_shift($data['links']);
        $this->assertEquals('20150310_114651', $link['linkdate']);
        $this->assertEquals('http://host.tld/?WDWyig', $link['guid']);
        $this->assertEquals('http://host.tld/?WDWyig', $link['url']);
        $this->assertContains('Direct link', $link['description']);
        $this->assertContains('http://host.tld/?WDWyig', $link['description']);
        // Second link is a direct link
        $link = array_shift($data['links']);
        $this->assertEquals('20150310_114633', $link['linkdate']);
        $this->assertEquals('http://host.tld/?kLHmZg', $link['guid']);
        $this->assertEquals('https://static.fsf.org/nosvn/faif-2.0.pdf', $link['url']);
        $this->assertContains('Direct link', $link['description']);
        $this->assertContains('https://static.fsf.org/nosvn/faif-2.0.pdf', $link['description']);
    }

    /**
     * Test buildData with hide dates settings.
     */
    public function testBuildDataHideDates()
    {
        $feedBuilder = new FeedBuilder(self::$linkDB, FeedBuilder::$FEED_ATOM, self::$serverInfo, null, false);
        $feedBuilder->setLocale(self::$LOCALE);
        $feedBuilder->setHideDates(true);
        $data = $feedBuilder->buildData();
        $this->assertEquals(ReferenceLinkDB::$NB_LINKS_TOTAL, count($data['links']));
        $this->assertFalse($data['show_dates']);

        // Show dates while logged in
        $feedBuilder = new FeedBuilder(self::$linkDB, FeedBuilder::$FEED_ATOM, self::$serverInfo, null, true);
        $feedBuilder->setLocale(self::$LOCALE);
        $feedBuilder->setHideDates(true);
        $data = $feedBuilder->buildData();
        $this->assertEquals(ReferenceLinkDB::$NB_LINKS_TOTAL, count($data['links']));
        $this->assertTrue($data['show_dates']);
    }

    /**
     * Test buildData with hide dates settings.
     */
    public function testBuildDataPubsubhub()
    {
        $feedBuilder = new FeedBuilder(self::$linkDB, FeedBuilder::$FEED_ATOM, self::$serverInfo, null, false);
        $feedBuilder->setLocale(self::$LOCALE);
        $feedBuilder->setPubsubhubUrl('http://pubsubhub.io');
        $data = $feedBuilder->buildData();
        $this->assertEquals(ReferenceLinkDB::$NB_LINKS_TOTAL, count($data['links']));
        $this->assertEquals('http://pubsubhub.io', $data['pubsubhub_url']);
    }
}
