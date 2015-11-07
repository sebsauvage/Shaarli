<?php

/**
 * PluginArchiveorgTest.php
 */

require_once 'plugins/archiveorg/archiveorg.php';

/**
 * Class PlugQrcodeTest
 * Unit test for the QR-Code plugin
 */
class PluginArchiveorgTest extends PHPUnit_Framework_TestCase
{
    /**
     * Reset plugin path
     */
    function setUp()
    {
        PluginManager::$PLUGINS_PATH = 'plugins';
    }

    /**
     * Test render_linklist hook.
     */
    function testArchiveorgLinklist()
    {
        $str = 'http://randomstr.com/test';
        $data = array(
            'title' => $str,
            'links' => array(
                array(
                    'url' => $str,
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
}
