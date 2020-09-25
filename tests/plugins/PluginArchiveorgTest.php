<?php

namespace Shaarli\Plugin\Archiveorg;

/**
 * PluginArchiveorgTest.php
 */

use PHPUnit\Framework\TestCase;
use Shaarli\Plugin\PluginManager;

require_once 'plugins/archiveorg/archiveorg.php';

/**
 * Class PluginArchiveorgTest
 * Unit test for the archiveorg plugin
 */
class PluginArchiveorgTest extends TestCase
{
    protected $savedScriptName;

    /**
     * Reset plugin path
     */
    public function setUp(): void
    {
        PluginManager::$PLUGINS_PATH = 'plugins';

        // plugins manipulate global vars
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SERVER_NAME'] = 'shaarli.shaarli';
        $this->savedScriptName = $_SERVER['SCRIPT_NAME'] ?? null;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
    }

    public function tearDown(): void
    {
        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
        $_SERVER['SCRIPT_NAME'] = $this->savedScriptName;
    }

    /**
     * Test render_linklist hook on external bookmarks.
     */
    public function testArchiveorgLinklistOnExternalLinks(): void
    {
        $str = 'http://randomstr.com/test';

        $data = array(
            'title' => $str,
            'links' => array(
                array(
                    'url' => $str,
                    'private' => 0,
                    'real_url' => $str
                )
            )
        );

        $data = hook_archiveorg_render_linklist($data);

        $link = $data['links'][0];
        // data shouldn't be altered
        $this->assertEquals($str, $data['title']);
        $this->assertEquals($str, $link['url']);

        // plugin data
        $this->assertEquals(1, count($link['link_plugin']));
        $this->assertNotFalse(strpos($link['link_plugin'][0], $str));
    }

    /**
     * Test render_linklist hook on internal bookmarks.
     */
    public function testArchiveorgLinklistOnInternalLinks(): void
    {
        $internalLink1 = 'http://shaarli.shaarli/shaare/qvMAqg';
        $internalLinkRealURL1 = '/shaare/qvMAqg';

        $internalLink2 = 'http://shaarli.shaarli/shaare/2_7zww';
        $internalLinkRealURL2 = '/shaare/2_7zww';

        $internalLink3 = 'http://shaarli.shaarli/shaare/z7u-_Q';
        $internalLinkRealURL3 = '/shaare/z7u-_Q';

        $data = array(
            'title' => $internalLink1,
            'links' => array(
                array(
                    'url' => $internalLink1,
                    'private' => 0,
                    'real_url' => $internalLinkRealURL1
                ),
                array(
                    'url' => $internalLink1,
                    'private' => 1,
                    'real_url' => $internalLinkRealURL1
                ),
                array(
                    'url' => $internalLink2,
                    'private' => 0,
                    'real_url' => $internalLinkRealURL2
                ),
                array(
                    'url' => $internalLink2,
                    'private' => 1,
                    'real_url' => $internalLinkRealURL2
                ),
                array(
                    'url' => $internalLink3,
                    'private' => 0,
                    'real_url' => $internalLinkRealURL3
                ),
                array(
                    'url' => $internalLink3,
                    'private' => 1,
                    'real_url' => $internalLinkRealURL3
                )
            )
        );

        $data = hook_archiveorg_render_linklist($data);

        // Case n°1: first link type, public
        $link = $data['links'][0];

        $this->assertEquals(1, count($link['link_plugin']));
        $this->assertNotFalse(strpos($link['link_plugin'][0], $internalLink1));

        // Case n°2: first link type, private
        $link = $data['links'][1];

        $this->assertArrayNotHasKey('link_plugin', $link);

        // Case n°3: second link type, public
        $link = $data['links'][2];

        $this->assertEquals(1, count($link['link_plugin']));
        $this->assertNotFalse(strpos($link['link_plugin'][0], $internalLink2));

        // Case n°4: second link type, private
        $link = $data['links'][3];

        $this->assertArrayNotHasKey('link_plugin', $link);

        // Case n°5: third link type, public
        $link = $data['links'][4];

        $this->assertEquals(1, count($link['link_plugin']));
        $this->assertNotFalse(strpos($link['link_plugin'][0], $internalLink3));

        // Case n°6: third link type, private
        $link = $data['links'][5];

        $this->assertArrayNotHasKey('link_plugin', $link);
    }
}
