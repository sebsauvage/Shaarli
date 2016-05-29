<?php

/**
 * PluginReadityourselfTest.php.php
 */

require_once 'plugins/readityourself/readityourself.php';

/**
 * Class PluginWallabagTest
 * Unit test for the Wallabag plugin
 */
class PluginReadityourselfTest extends PHPUnit_Framework_TestCase
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
    function testReadityourselfLinklist()
    {
        $conf = ConfigManager::getInstance();
        $conf->set('plugins.READITYOUSELF_URL', 'value');
        $str = 'http://randomstr.com/test';
        $data = array(
            'title' => $str,
            'links' => array(
                array(
                    'url' => $str,
                )
            )
        );

        $data = hook_readityourself_render_linklist($data);
        $link = $data['links'][0];
        // data shouldn't be altered
        $this->assertEquals($str, $data['title']);
        $this->assertEquals($str, $link['url']);

        // plugin data
        $this->assertEquals(1, count($link['link_plugin']));
        $this->assertNotFalse(strpos($link['link_plugin'][0], $str));
    }

    /**
     * Test without config: nothing should happened.
     */
    function testReadityourselfLinklistWithoutConfig()
    {
        $conf = ConfigManager::getInstance();
        $conf->set('plugins.READITYOUSELF_URL', null);
        $str = 'http://randomstr.com/test';
        $data = array(
            'title' => $str,
            'links' => array(
                array(
                    'url' => $str,
                )
            )
        );

        $data = hook_readityourself_render_linklist($data);
        $link = $data['links'][0];
        // data shouldn't be altered
        $this->assertEquals($str, $data['title']);
        $this->assertEquals($str, $link['url']);

        // plugin data
        $this->assertArrayNotHasKey('link_plugin', $link);
    }
}
