<?php

namespace Shaarli;

use DateTime;
use Shaarli\Bookmark\Bookmark;

class HistoryTest extends \Shaarli\TestCase
{
    /**
     * @var string History file path
     */
    protected static $historyFilePath = 'sandbox/history.php';

    /**
     * Delete history file.
     */
    protected function setUp(): void
    {
        if (file_exists(self::$historyFilePath)) {
            unlink(self::$historyFilePath);
        }
    }

    /**
     * Test that the history file is created if it doesn't exist.
     */
    public function testConstructLazyLoading()
    {
        new History(self::$historyFilePath);
        $this->assertFileNotExists(self::$historyFilePath);
    }

    /**
     * Test that the history file is created if it doesn't exist.
     */
    public function testAddEventCreateFile()
    {
        $history = new History(self::$historyFilePath);
        $history->updateSettings();
        $this->assertFileExists(self::$historyFilePath);
    }

    /**
     * Not writable history file: raise an exception.
     */
    public function testConstructNotWritable()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('History file isn\'t readable or writable');

        touch(self::$historyFilePath);
        chmod(self::$historyFilePath, 0440);
        $history = new History(self::$historyFilePath);
        $history->updateSettings();
    }

    /**
     * Not parsable history file: raise an exception.
     */
    public function testConstructNotParsable()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/Could not parse history file/');

        file_put_contents(self::$historyFilePath, 'not parsable');
        $history = new History(self::$historyFilePath);
        // gzinflate generates a warning
        @$history->updateSettings();
    }

    /**
     * Test add link event
     */
    public function testAddLink()
    {
        $history = new History(self::$historyFilePath);
        $bookmark = (new Bookmark())->setId(0);
        $history->addLink($bookmark);
        $actual = $history->getHistory()[0];
        $this->assertEquals(History::CREATED, $actual['event']);
        $this->assertTrue(new DateTime('-2 seconds') < $actual['datetime']);
        $this->assertEquals(0, $actual['id']);

        $history = new History(self::$historyFilePath);
        $bookmark = (new Bookmark())->setId(1);
        $history->addLink($bookmark);
        $actual = $history->getHistory()[0];
        $this->assertEquals(History::CREATED, $actual['event']);
        $this->assertTrue(new DateTime('-2 seconds') < $actual['datetime']);
        $this->assertEquals(1, $actual['id']);
    }

//    /**
//     * Test updated link event
//     */
//    public function testUpdateLink()
//    {
//        $history = new History(self::$historyFilePath);
//        $history->updateLink(['id' => 1]);
//        $actual = $history->getHistory()[0];
//        $this->assertEquals(History::UPDATED, $actual['event']);
//        $this->assertTrue(new DateTime('-2 seconds') < $actual['datetime']);
//        $this->assertEquals(1, $actual['id']);
//    }
//
//    /**
//     * Test delete link event
//     */
//    public function testDeleteLink()
//    {
//        $history = new History(self::$historyFilePath);
//        $history->deleteLink(['id' => 1]);
//        $actual = $history->getHistory()[0];
//        $this->assertEquals(History::DELETED, $actual['event']);
//        $this->assertTrue(new DateTime('-2 seconds') < $actual['datetime']);
//        $this->assertEquals(1, $actual['id']);
//    }
//
//    /**
//     * Test updated settings event
//     */
//    public function testUpdateSettings()
//    {
//        $history = new History(self::$historyFilePath);
//        $history->updateSettings();
//        $actual = $history->getHistory()[0];
//        $this->assertEquals(History::SETTINGS, $actual['event']);
//        $this->assertTrue(new DateTime('-2 seconds') < $actual['datetime']);
//        $this->assertEmpty($actual['id']);
//    }
//
//    /**
//     * Make sure that new items are stored at the beginning
//     */
//    public function testHistoryOrder()
//    {
//        $history = new History(self::$historyFilePath);
//        $history->updateLink(['id' => 1]);
//        $actual = $history->getHistory()[0];
//        $this->assertEquals(History::UPDATED, $actual['event']);
//        $this->assertTrue(new DateTime('-2 seconds') < $actual['datetime']);
//        $this->assertEquals(1, $actual['id']);
//
//        $history->addLink(['id' => 1]);
//        $actual = $history->getHistory()[0];
//        $this->assertEquals(History::CREATED, $actual['event']);
//        $this->assertTrue(new DateTime('-2 seconds') < $actual['datetime']);
//        $this->assertEquals(1, $actual['id']);
//    }
//
//    /**
//     * Re-read history from file after writing an event
//     */
//    public function testHistoryRead()
//    {
//        $history = new History(self::$historyFilePath);
//        $history->updateLink(['id' => 1]);
//        $history = new History(self::$historyFilePath);
//        $actual = $history->getHistory()[0];
//        $this->assertEquals(History::UPDATED, $actual['event']);
//        $this->assertTrue(new DateTime('-2 seconds') < $actual['datetime']);
//        $this->assertEquals(1, $actual['id']);
//    }
//
//    /**
//     * Re-read history from file after writing an event and make sure that the order is correct
//     */
//    public function testHistoryOrderRead()
//    {
//        $history = new History(self::$historyFilePath);
//        $history->updateLink(['id' => 1]);
//        $history->addLink(['id' => 1]);
//
//        $history = new History(self::$historyFilePath);
//        $actual = $history->getHistory()[0];
//        $this->assertEquals(History::CREATED, $actual['event']);
//        $this->assertTrue(new DateTime('-2 seconds') < $actual['datetime']);
//        $this->assertEquals(1, $actual['id']);
//
//        $actual = $history->getHistory()[1];
//        $this->assertEquals(History::UPDATED, $actual['event']);
//        $this->assertTrue(new DateTime('-2 seconds') < $actual['datetime']);
//        $this->assertEquals(1, $actual['id']);
//    }
//
//    /**
//     * Test retention time: delete old entries.
//     */
//    public function testHistoryRententionTime()
//    {
//        $history = new History(self::$historyFilePath, 5);
//        $history->updateLink(['id' => 1]);
//        $this->assertEquals(1, count($history->getHistory()));
//        $arr = $history->getHistory();
//        $arr[0]['datetime'] = new DateTime('-1 hour');
//        FileUtils::writeFlatDB(self::$historyFilePath, $arr);
//
//        $history = new History(self::$historyFilePath, 60);
//        $this->assertEquals(1, count($history->getHistory()));
//        $this->assertEquals(1, $history->getHistory()[0]['id']);
//        $history->updateLink(['id' => 2]);
//        $this->assertEquals(1, count($history->getHistory()));
//        $this->assertEquals(2, $history->getHistory()[0]['id']);
//    }
}
