<?php

/**
 * PluginWallabagTest.php.php
 */

require_once 'plugins/wallabag/wallabag.php';

/**
 * Class PluginWallabagTest
 * Unit test for the Wallabag plugin
 */
class PluginWallabagTest extends PHPUnit_Framework_TestCase
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
    function testWallabagLinklist()
    {
        $conf = ConfigManager::getInstance();
        $conf->set('plugins.WALLABAG_URL', 'value');
        $str = 'http://randomstr.com/test';
        $data = array(
            'title' => $str,
            'links' => array(
                array(
                    'url' => $str,
                )
            )
        );

        $data = hook_wallabag_render_linklist($data);
        $link = $data['links'][0];
        // data shouldn't be altered
        $this->assertEquals($str, $data['title']);
        $this->assertEquals($str, $link['url']);

        // plugin data
        $this->assertEquals(1, count($link['link_plugin']));
        $this->assertNotFalse(strpos($link['link_plugin'][0], urlencode($str)));
        $this->assertNotFalse(strpos($link['link_plugin'][0], $conf->get('plugins.WALLABAG_URL')));
    }
}

