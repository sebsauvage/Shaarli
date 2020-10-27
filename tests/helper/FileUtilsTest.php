<?php

namespace Shaarli\Helper;

use Exception;
use Shaarli\Exceptions\IOException;
use Shaarli\TestCase;

/**
 * Class FileUtilsTest
 *
 * Test file utility class.
 */
class FileUtilsTest extends TestCase
{
    /**
     * @var string Test file path.
     */
    protected static $file = 'sandbox/flat.db';

    protected function setUp(): void
    {
        @mkdir('sandbox');
        mkdir('sandbox/folder2');
        touch('sandbox/file1');
        touch('sandbox/file2');
        mkdir('sandbox/folder1');
        touch('sandbox/folder1/file1');
        touch('sandbox/folder1/file2');
        mkdir('sandbox/folder3');
        mkdir('/tmp/shaarli-to-delete');
    }

    /**
     * Delete test file after every test.
     */
    protected function tearDown(): void
    {
        @unlink(self::$file);

        @unlink('sandbox/folder1/file1');
        @unlink('sandbox/folder1/file2');
        @rmdir('sandbox/folder1');
        @unlink('sandbox/file1');
        @unlink('sandbox/file2');
        @rmdir('sandbox/folder2');
        @rmdir('sandbox/folder3');
        @rmdir('/tmp/shaarli-to-delete');
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
     */
    public function testWriteWithoutPermission()
    {
        $this->expectException(\Shaarli\Exceptions\IOException::class);
        $this->expectExceptionMessage('Error accessing "sandbox/flat.db"');

        touch(self::$file);
        chmod(self::$file, 0440);
        FileUtils::writeFlatDB(self::$file, null);
    }

    /**
     * Folder non existent: raise an exception.
     */
    public function testWriteFolderDoesNotExist()
    {
        $this->expectException(\Shaarli\Exceptions\IOException::class);
        $this->expectExceptionMessage('Error accessing "nopefolder"');

        FileUtils::writeFlatDB('nopefolder/file', null);
    }

    /**
     * Folder non writable: raise an exception.
     */
    public function testWriteFolderPermission()
    {
        $this->expectException(\Shaarli\Exceptions\IOException::class);
        $this->expectExceptionMessage('Error accessing "sandbox"');

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

    /**
     * Test clearFolder with self delete and excluded files
     */
    public function testClearFolderSelfDeleteWithExclusion(): void
    {
        FileUtils::clearFolder('sandbox', true, ['file2']);

        static::assertFileExists('sandbox/folder1/file2');
        static::assertFileExists('sandbox/folder1');
        static::assertFileExists('sandbox/file2');
        static::assertFileExists('sandbox');

        static::assertFileNotExists('sandbox/folder1/file1');
        static::assertFileNotExists('sandbox/file1');
        static::assertFileNotExists('sandbox/folder3');
    }

    /**
     * Test clearFolder with self delete and excluded files
     */
    public function testClearFolderSelfDeleteWithoutExclusion(): void
    {
        FileUtils::clearFolder('sandbox', true);

        static::assertFileNotExists('sandbox');
    }

    /**
     * Test clearFolder with self delete and excluded files
     */
    public function testClearFolderNoSelfDeleteWithoutExclusion(): void
    {
        FileUtils::clearFolder('sandbox', false);

        static::assertFileExists('sandbox');

        // 2 because '.' and '..'
        static::assertCount(2, new \DirectoryIterator('sandbox'));
    }

    /**
     * Test clearFolder on a file instead of a folder
     */
    public function testClearFolderOnANonDirectory(): void
    {
        $this->expectException(IOException::class);
        $this->expectExceptionMessage('Provided path is not a directory.');

        FileUtils::clearFolder('sandbox/file1', false);
    }

    /**
     * Test clearFolder on a file instead of a folder
     */
    public function testClearFolderOutsideOfShaarliDirectory(): void
    {
        $this->expectException(IOException::class);
        $this->expectExceptionMessage('Trying to delete a folder outside of Shaarli path.');


        FileUtils::clearFolder('/tmp/shaarli-to-delete', true);
    }
}
