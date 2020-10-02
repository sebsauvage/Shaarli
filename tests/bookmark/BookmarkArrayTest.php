<?php

namespace Shaarli\Bookmark;

use Shaarli\TestCase;

/**
 * Class BookmarkArrayTest
 */
class BookmarkArrayTest extends TestCase
{
    /**
     * Test the constructor and make sure that the instance is properly initialized
     */
    public function testArrayConstructorEmpty()
    {
        $array = new BookmarkArray();
        $this->assertTrue(is_iterable($array));
        $this->assertEmpty($array);
    }

    /**
     * Test adding entries to the array, specifying the key offset or not.
     */
    public function testArrayAccessAddEntries()
    {
        $array = new BookmarkArray();
        $bookmark = new Bookmark();
        $bookmark->setId(11)->validate();
        $array[] = $bookmark;
        $this->assertCount(1, $array);
        $this->assertTrue(isset($array[11]));
        $this->assertNull($array[0]);
        $this->assertEquals($bookmark, $array[11]);

        $bookmark = new Bookmark();
        $bookmark->setId(14)->validate();
        $array[14] = $bookmark;
        $this->assertCount(2, $array);
        $this->assertTrue(isset($array[14]));
        $this->assertNull($array[0]);
        $this->assertEquals($bookmark, $array[14]);
    }

    /**
     * Test adding a bad entry: wrong type
     */
    public function testArrayAccessAddBadEntryInstance()
    {
        $this->expectException(\Shaarli\Bookmark\Exception\InvalidBookmarkException::class);

        $array = new BookmarkArray();
        $array[] = 'nope';
    }

    /**
     * Test adding a bad entry: no id
     */
    public function testArrayAccessAddBadEntryNoId()
    {
        $this->expectException(\Shaarli\Bookmark\Exception\InvalidBookmarkException::class);

        $array = new BookmarkArray();
        $bookmark = new Bookmark();
        $array[] = $bookmark;
    }

    /**
     * Test adding a bad entry: no url
     */
    public function testArrayAccessAddBadEntryNoUrl()
    {
        $this->expectException(\Shaarli\Bookmark\Exception\InvalidBookmarkException::class);

        $array = new BookmarkArray();
        $bookmark = (new Bookmark())->setId(11);
        $array[] = $bookmark;
    }

    /**
     * Test adding a bad entry: invalid offset
     */
    public function testArrayAccessAddBadEntryOffset()
    {
        $this->expectException(\Shaarli\Bookmark\Exception\InvalidBookmarkException::class);

        $array = new BookmarkArray();
        $bookmark = (new Bookmark())->setId(11);
        $bookmark->validate();
        $array['nope'] = $bookmark;
    }

    /**
     * Test adding a bad entry: ID/offset not consistent
     */
    public function testArrayAccessAddBadEntryIdOffset()
    {
        $this->expectException(\Shaarli\Bookmark\Exception\InvalidBookmarkException::class);

        $array = new BookmarkArray();
        $bookmark = (new Bookmark())->setId(11);
        $bookmark->validate();
        $array[14] = $bookmark;
    }

    /**
     * Test update entries through array access.
     */
    public function testArrayAccessUpdateEntries()
    {
        $array = new BookmarkArray();
        $bookmark = new Bookmark();
        $bookmark->setId(11)->validate();
        $bookmark->setTitle('old');
        $array[] = $bookmark;
        $bookmark = new Bookmark();
        $bookmark->setId(11)->validate();
        $bookmark->setTitle('test');
        $array[] = $bookmark;
        $this->assertCount(1, $array);
        $this->assertEquals('test', $array[11]->getTitle());

        $bookmark = new Bookmark();
        $bookmark->setId(11)->validate();
        $bookmark->setTitle('test2');
        $array[11] = $bookmark;
        $this->assertCount(1, $array);
        $this->assertEquals('test2', $array[11]->getTitle());
    }

    /**
     * Test delete entries through array access.
     */
    public function testArrayAccessDeleteEntries()
    {
        $array = new BookmarkArray();
        $bookmark11 = new Bookmark();
        $bookmark11->setId(11)->validate();
        $array[] = $bookmark11;
        $bookmark14 = new Bookmark();
        $bookmark14->setId(14)->validate();
        $array[] = $bookmark14;
        $bookmark23 = new Bookmark();
        $bookmark23->setId(23)->validate();
        $array[] = $bookmark23;
        $bookmark0 = new Bookmark();
        $bookmark0->setId(0)->validate();
        $array[] = $bookmark0;
        $this->assertCount(4, $array);

        unset($array[14]);
        $this->assertCount(3, $array);
        $this->assertEquals($bookmark11, $array[11]);
        $this->assertEquals($bookmark23, $array[23]);
        $this->assertEquals($bookmark0, $array[0]);

        unset($array[23]);
        $this->assertCount(2, $array);
        $this->assertEquals($bookmark11, $array[11]);
        $this->assertEquals($bookmark0, $array[0]);

        unset($array[11]);
        $this->assertCount(1, $array);
        $this->assertEquals($bookmark0, $array[0]);

        unset($array[0]);
        $this->assertCount(0, $array);
    }

    /**
     * Test iterating through array access.
     */
    public function testArrayAccessIterate()
    {
        $array = new BookmarkArray();
        $bookmark11 = new Bookmark();
        $bookmark11->setId(11)->validate();
        $array[] = $bookmark11;
        $bookmark14 = new Bookmark();
        $bookmark14->setId(14)->validate();
        $array[] = $bookmark14;
        $bookmark23 = new Bookmark();
        $bookmark23->setId(23)->validate();
        $array[] = $bookmark23;
        $this->assertCount(3, $array);

        foreach ($array as $id => $bookmark) {
            $this->assertEquals(${'bookmark'. $id}, $bookmark);
        }
    }

    /**
     * Test reordering the array.
     */
    public function testReorder()
    {
        $refDB = new \ReferenceLinkDB();
        $refDB->write('sandbox/datastore.php');


        $bookmarks = $refDB->getLinks();
        $bookmarks->reorder('ASC');
        $this->assertInstanceOf(BookmarkArray::class, $bookmarks);

        $stickyIds = [11, 10];
        $standardIds = [42, 4, 9, 1, 0, 7, 6, 8, 41];
        $linkIds = array_merge($stickyIds, $standardIds);
        $cpt = 0;
        foreach ($bookmarks as $key => $value) {
            $this->assertEquals($linkIds[$cpt++], $key);
        }

        $bookmarks = $refDB->getLinks();
        $bookmarks->reorder('DESC');
        $this->assertInstanceOf(BookmarkArray::class, $bookmarks);

        $linkIds = array_merge(array_reverse($stickyIds), array_reverse($standardIds));
        $cpt = 0;
        foreach ($bookmarks as $key => $value) {
            $this->assertEquals($linkIds[$cpt++], $key);
        }
    }
}
