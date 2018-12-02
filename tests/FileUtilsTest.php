<?php

namespace Shaarli;

use Exception;

/**
 * Class FileUtilsTest
 *
 * Test file utility class.
 */
class FileUtilsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string Test file path.
     */
    protected static $file = 'sandbox/flat.db';

    /**
     * Delete test file after every test.
     */
    public function tearDown()
    {
        @unlink(self::$file);
    }

    /**
     * Test writeDB, then readDB with different data.
     */
    public function testSimpleWriteRead()
    {
        $data = ['blue', 'red'];
        $this->assertTrue(FileUtils::writeFlatDB(self::$file, $data) > 0);
        $this->assertTrue(startsWith(file_get_contents(self::$file), '<?php /*'));
        $this->assertEquals($data, FileUtils::readFlatDB(self::$file));

        $data = 0;
        $this->assertTrue(FileUtils::writeFlatDB(self::$file, $data) > 0);
        $this->assertEquals($data, FileUtils::readFlatDB(self::$file));

        $data = null;
        $this->assertTrue(FileUtils::writeFlatDB(self::$file, $data) > 0);
        $this->assertEquals($data, FileUtils::readFlatDB(self::$file));

        $data = false;
        $this->assertTrue(FileUtils::writeFlatDB(self::$file, $data) > 0);
        $this->assertEquals($data, FileUtils::readFlatDB(self::$file));
    }

    /**
     * File not writable: raise an exception.
     *
     * @expectedException Shaarli\Exceptions\IOException
     * @expectedExceptionMessage Error accessing "sandbox/flat.db"
     */
    public function testWriteWithoutPermission()
    {
        touch(self::$file);
        chmod(self::$file, 0440);
        FileUtils::writeFlatDB(self::$file, null);
    }

    /**
     * Folder non existent: raise an exception.
     *
     * @expectedException Shaarli\Exceptions\IOException
     * @expectedExceptionMessage Error accessing "nopefolder"
     */
    public function testWriteFolderDoesNotExist()
    {
        FileUtils::writeFlatDB('nopefolder/file', null);
    }

    /**
     * Folder non writable: raise an exception.
     *
     * @expectedException Shaarli\Exceptions\IOException
     * @expectedExceptionMessage Error accessing "sandbox"
     */
    public function testWriteFolderPermission()
    {
        chmod(dirname(self::$file), 0555);
        try {
            FileUtils::writeFlatDB(self::$file, null);
        } catch (Exception $e) {
            chmod(dirname(self::$file), 0755);
            throw $e;
        }
    }

    /**
     * Read non existent file, use default parameter.
     */
    public function testReadNotExistentFile()
    {
        $this->assertEquals(null, FileUtils::readFlatDB(self::$file));
        $this->assertEquals(['test'], FileUtils::readFlatDB(self::$file, ['test']));
    }

    /**
     * Read non readable file, use default parameter.
     */
    public function testReadNotReadable()
    {
        touch(self::$file);
        chmod(self::$file, 0220);
        $this->assertEquals(null, FileUtils::readFlatDB(self::$file));
        $this->assertEquals(['test'], FileUtils::readFlatDB(self::$file, ['test']));
    }
}
