<?php

require_once 'application/Thumbnailer.php';
require_once 'application/config/ConfigManager.php';

/**
 * Class ThumbnailerTest
 *
 * We only make 1 thumb test because:
 *
 *   1. the thumbnailer library is itself tested
 *   2. we don't want to make too many external requests during the tests
 */
class ThumbnailerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test a thumbnail with a custom size.
     */
    public function testThumbnailValid()
    {
        $conf = new ConfigManager('tests/utils/config/configJson');
        $width = 200;
        $height = 200;
        $conf->set('thumbnails.width', $width);
        $conf->set('thumbnails.height', $height);

        $thumbnailer = new Thumbnailer($conf);
        $thumb = $thumbnailer->get('https://github.com/shaarli/Shaarli/');
        $this->assertNotFalse($thumb);
        $image = imagecreatefromstring(file_get_contents($thumb));
        $this->assertEquals($width, imagesx($image));
        $this->assertEquals($height, imagesy($image));
    }

    /**
     * Test a thumbnail that can't be retrieved.
     *
     * @expectedException WebThumbnailer\Exception\ThumbnailNotFoundException
     */
    public function testThumbnailNotValid()
    {
        $oldlog = ini_get('error_log');
        ini_set('error_log', '/dev/null');

        $thumbnailer = new Thumbnailer(new ConfigManager());
        $thumb = $thumbnailer->get('nope');
        $this->assertFalse($thumb);

        ini_set('error_log', $oldlog);
    }
}
