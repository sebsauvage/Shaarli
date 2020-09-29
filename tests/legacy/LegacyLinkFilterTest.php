<?php

namespace Shaarli\Bookmark;

use Exception;
use ReferenceLinkDB;
use Shaarli\Legacy\LegacyLinkDB;
use Shaarli\Legacy\LegacyLinkFilter;

/**
 * Class LegacyLinkFilterTest.
 */
class LegacyLinkFilterTest extends \Shaarli\TestCase
{
    /**
     * @var string Test datastore path.
     */
    protected static $testDatastore = 'sandbox/datastore.php';
    /**
     * @var BookmarkFilter instance.
     */
    protected static $linkFilter;

    /**
     * @var ReferenceLinkDB instance
     */
    protected static $refDB;

    /**
     * @var LegacyLinkDB instance
     */
    protected static $linkDB;

    /**
     * Instantiate linkFilter with ReferenceLinkDB data.
     */
    public static function setUpBeforeClass(): void
    {
        self::$refDB = new ReferenceLinkDB(true);
        self::$refDB->write(self::$testDatastore);
        self::$linkDB = new LegacyLinkDB(self::$testDatastore, true, false);
        self::$linkFilter = new LegacyLinkFilter(self::$linkDB);
    }

    /**
     * Blank filter.
     */
    public function testFilter()
    {
        $this->assertEquals(
            self::$refDB->countLinks(),
            count(self::$linkFilter->filter('', ''))
        );

        $this->assertEquals(
            self::$refDB->countLinks(),
            count(self::$linkFilter->filter('', '', 'all'))
        );

        $this->assertEquals(
            self::$refDB->countLinks(),
            count(self::$linkFilter->filter('', '', 'randomstr'))
        );

        // Private only.
        $this->assertEquals(
            self::$refDB->countPrivateLinks(),
            count(self::$linkFilter->filter('', '', false, 'private'))
        );

        // Public only.
        $this->assertEquals(
            self::$refDB->countPublicLinks(),
            count(self::$linkFilter->filter('', '', false, 'public'))
        );

        $this->assertEquals(
            ReferenceLinkDB::$NB_LINKS_TOTAL,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TAG, ''))
        );

        $this->assertEquals(
            self::$refDB->countUntaggedLinks(),
            count(
                self::$linkFilter->filter(
                    LegacyLinkFilter::$FILTER_TAG,
                    /*$request=*/
                    '',
                    /*$casesensitive=*/
                    false,
                    /*$visibility=*/
                    'all',
                    /*$untaggedonly=*/
                    true
                )
            )
        );

        $this->assertEquals(
            ReferenceLinkDB::$NB_LINKS_TOTAL,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TEXT, ''))
        );
    }

    /**
     * Filter bookmarks using a tag
     */
    public function testFilterOneTag()
    {
        $this->assertEquals(
            4,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TAG, 'web', false))
        );

        $this->assertEquals(
            4,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TAG, 'web', false, 'all'))
        );

        $this->assertEquals(
            4,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TAG, 'web', false, 'default-blabla'))
        );

        // Private only.
        $this->assertEquals(
            1,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TAG, 'web', false, 'private'))
        );

        // Public only.
        $this->assertEquals(
            3,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TAG, 'web', false, 'public'))
        );
    }

    /**
     * Filter bookmarks using a tag - case-sensitive
     */
    public function testFilterCaseSensitiveTag()
    {
        $this->assertEquals(
            0,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TAG, 'mercurial', true))
        );

        $this->assertEquals(
            1,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TAG, 'Mercurial', true))
        );
    }

    /**
     * Filter bookmarks using a tag combination
     */
    public function testFilterMultipleTags()
    {
        $this->assertEquals(
            2,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TAG, 'dev cartoon', false))
        );
    }

    /**
     * Filter bookmarks using a non-existent tag
     */
    public function testFilterUnknownTag()
    {
        $this->assertEquals(
            0,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TAG, 'null', false))
        );
    }

    /**
     * Return bookmarks for a given day
     */
    public function testFilterDay()
    {
        $this->assertEquals(
            4,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_DAY, '20121206'))
        );
    }

    /**
     * 404 - day not found
     */
    public function testFilterUnknownDay()
    {
        $this->assertEquals(
            0,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_DAY, '19700101'))
        );
    }

    /**
     * Use an invalid date format
     */
    public function testFilterInvalidDayWithChars()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/Invalid date format/');

        self::$linkFilter->filter(LegacyLinkFilter::$FILTER_DAY, 'Rainy day, dream away');
    }

    /**
     * Use an invalid date format
     */
    public function testFilterInvalidDayDigits()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/Invalid date format/');

        self::$linkFilter->filter(LegacyLinkFilter::$FILTER_DAY, '20');
    }

    /**
     * Retrieve a link entry with its hash
     */
    public function testFilterSmallHash()
    {
        $links = self::$linkFilter->filter(LegacyLinkFilter::$FILTER_HASH, 'IuWvgA');

        $this->assertEquals(
            1,
            count($links)
        );

        $this->assertEquals(
            'MediaGoblin',
            $links[7]['title']
        );
    }

    /**
     * No link for this hash
     */
    public function testFilterUnknownSmallHash()
    {
        $this->expectException(\Shaarli\Bookmark\Exception\BookmarkNotFoundException::class);

        self::$linkFilter->filter(LegacyLinkFilter::$FILTER_HASH, 'Iblaah');
    }

    /**
     * Full-text search - no result found.
     */
    public function testFilterFullTextNoResult()
    {
        $this->assertEquals(
            0,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TEXT, 'azertyuiop'))
        );
    }

    /**
     * Full-text search - result from a link's URL
     */
    public function testFilterFullTextURL()
    {
        $this->assertEquals(
            2,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TEXT, 'ars.userfriendly.org'))
        );

        $this->assertEquals(
            2,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TEXT, 'ars org'))
        );
    }

    /**
     * Full-text search - result from a link's title only
     */
    public function testFilterFullTextTitle()
    {
        // use miscellaneous cases
        $this->assertEquals(
            2,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TEXT, 'userfriendly -'))
        );
        $this->assertEquals(
            2,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TEXT, 'UserFriendly -'))
        );
        $this->assertEquals(
            2,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TEXT, 'uSeRFrIendlY -'))
        );

        // use miscellaneous case and offset
        $this->assertEquals(
            2,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TEXT, 'RFrIendL'))
        );
    }

    /**
     * Full-text search - result from the link's description only
     */
    public function testFilterFullTextDescription()
    {
        $this->assertEquals(
            1,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TEXT, 'publishing media'))
        );

        $this->assertEquals(
            1,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TEXT, 'mercurial w3c'))
        );

        $this->assertEquals(
            3,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TEXT, '"free software"'))
        );
    }

    /**
     * Full-text search - result from the link's tags only
     */
    public function testFilterFullTextTags()
    {
        $this->assertEquals(
            6,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TEXT, 'web'))
        );

        $this->assertEquals(
            6,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TEXT, 'web', 'all'))
        );

        $this->assertEquals(
            6,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TEXT, 'web', 'bla'))
        );

        // Private only.
        $this->assertEquals(
            1,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TEXT, 'web', false, 'private'))
        );

        // Public only.
        $this->assertEquals(
            5,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TEXT, 'web', false, 'public'))
        );
    }

    /**
     * Full-text search - result set from mixed sources
     */
    public function testFilterFullTextMixed()
    {
        $this->assertEquals(
            3,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TEXT, 'free software'))
        );
    }

    /**
     * Full-text search - test exclusion with '-'.
     */
    public function testExcludeSearch()
    {
        $this->assertEquals(
            1,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TEXT, 'free -gnu'))
        );

        $this->assertEquals(
            ReferenceLinkDB::$NB_LINKS_TOTAL - 1,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TEXT, '-revolution'))
        );
    }

    /**
     * Full-text search - test AND, exact terms and exclusion combined, across fields.
     */
    public function testMultiSearch()
    {
        $this->assertEquals(
            2,
            count(self::$linkFilter->filter(
                LegacyLinkFilter::$FILTER_TEXT,
                '"Free Software " stallman "read this" @website stuff'
            ))
        );

        $this->assertEquals(
            1,
            count(self::$linkFilter->filter(
                LegacyLinkFilter::$FILTER_TEXT,
                '"free software " stallman "read this" -beard @website stuff'
            ))
        );
    }

    /**
     * Full-text search - make sure that exact search won't work across fields.
     */
    public function testSearchExactTermMultiFieldsKo()
    {
        $this->assertEquals(
            0,
            count(self::$linkFilter->filter(
                LegacyLinkFilter::$FILTER_TEXT,
                '"designer naming"'
            ))
        );

        $this->assertEquals(
            0,
            count(self::$linkFilter->filter(
                LegacyLinkFilter::$FILTER_TEXT,
                '"designernaming"'
            ))
        );
    }

    /**
     * Tag search with exclusion.
     */
    public function testTagFilterWithExclusion()
    {
        $this->assertEquals(
            1,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TAG, 'gnu -free'))
        );

        $this->assertEquals(
            ReferenceLinkDB::$NB_LINKS_TOTAL - 1,
            count(self::$linkFilter->filter(LegacyLinkFilter::$FILTER_TAG, '-free'))
        );
    }

    /**
     * Test crossed search (terms + tags).
     */
    public function testFilterCrossedSearch()
    {
        $terms = '"Free Software " stallman "read this" @website stuff';
        $tags = 'free';
        $this->assertEquals(
            1,
            count(self::$linkFilter->filter(
                LegacyLinkFilter::$FILTER_TAG | LegacyLinkFilter::$FILTER_TEXT,
                array($tags, $terms)
            ))
        );
        $this->assertEquals(
            2,
            count(self::$linkFilter->filter(
                LegacyLinkFilter::$FILTER_TAG | LegacyLinkFilter::$FILTER_TEXT,
                array('', $terms)
            ))
        );
        $this->assertEquals(
            1,
            count(self::$linkFilter->filter(
                LegacyLinkFilter::$FILTER_TAG | LegacyLinkFilter::$FILTER_TEXT,
                array(false, 'PSR-2')
            ))
        );
        $this->assertEquals(
            1,
            count(self::$linkFilter->filter(
                LegacyLinkFilter::$FILTER_TAG | LegacyLinkFilter::$FILTER_TEXT,
                array($tags, '')
            ))
        );
        $this->assertEquals(
            ReferenceLinkDB::$NB_LINKS_TOTAL,
            count(self::$linkFilter->filter(
                LegacyLinkFilter::$FILTER_TAG | LegacyLinkFilter::$FILTER_TEXT,
                ''
            ))
        );
    }

    /**
     * Filter bookmarks by #hashtag.
     */
    public function testFilterByHashtag()
    {
        $hashtag = 'hashtag';
        $this->assertEquals(
            3,
            count(self::$linkFilter->filter(
                LegacyLinkFilter::$FILTER_TAG,
                $hashtag
            ))
        );

        $hashtag = 'private';
        $this->assertEquals(
            1,
            count(self::$linkFilter->filter(
                LegacyLinkFilter::$FILTER_TAG,
                $hashtag,
                false,
                'private'
            ))
        );
    }
}
