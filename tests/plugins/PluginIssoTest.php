<?php

require_once 'plugins/isso/isso.php';

/**
 * Class PluginIssoTest
 *
 * Test the Isso plugin (comment system).
 */
class PluginIssoTest extends PHPUnit_Framework_TestCase
{
    /**
     * Reset plugin path
     */
    function setUp()
    {
        PluginManager::$PLUGINS_PATH = 'plugins';
    }

    /**
     * Test Isso init without errors.
     */
    function testWallabagInitNoError()
    {
        $conf = new ConfigManager('');
        $conf->set('plugins.ISSO_SERVER', 'value');
        $errors = isso_init($conf);
        $this->assertEmpty($errors);
    }

    /**
     * Test Isso init with errors.
     */
    function testWallabagInitError()
    {
        $conf = new ConfigManager('');
        $errors = isso_init($conf);
        $this->assertNotEmpty($errors);
    }

    /**
     * Test render_linklist hook with valid settings to display the comment form.
     */
    function testIssoDisplayed()
    {
        $conf = new ConfigManager('');
        $conf->set('plugins.ISSO_SERVER', 'value');

        $str = 'http://randomstr.com/test';
        $date = '20161118_100001';
        $data = array(
            'title' => $str,
            'links' => array(
                array(
                    'url' => $str,
                    'created' => DateTime::createFromFormat('Ymd_His', $date),
                )
            )
        );

        $data = hook_isso_render_linklist($data, $conf);

        // data shouldn't be altered
        $this->assertEquals($str, $data['title']);
        $this->assertEquals($str, $data['links'][0]['url']);

        // plugin data
        $this->assertEquals(1, count($data['plugin_end_zone']));
        $this->assertNotFalse(strpos($data['plugin_end_zone'][0], $date));
        $this->assertNotFalse(strpos($data['plugin_end_zone'][0], 'embed.min.js'));
    }

    /**
     * Test isso plugin when multiple links are displayed (shouldn't be displayed).
     */
    function testIssoMultipleLinks()
    {
        $conf = new ConfigManager('');
        $conf->set('plugins.ISSO_SERVER', 'value');

        $str = 'http://randomstr.com/test';
        $date1 = '20161118_100001';
        $date2 = '20161118_100002';
        $data = array(
            'title' => $str,
            'links' => array(
                array(
                    'url' => $str,
                    'created' => DateTime::createFromFormat('Ymd_His', $date1),
                ),
                array(
                    'url' => $str . '2',
                    'created' => DateTime::createFromFormat('Ymd_His', $date2),
                ),
            )
        );

        $processed = hook_isso_render_linklist($data, $conf);
        // data shouldn't be altered
        $this->assertEquals($data, $processed);
    }

    /**
     * Test isso plugin when using search (shouldn't be displayed).
     */
    function testIssoNotDisplayedWhenSearch()
    {
        $conf = new ConfigManager('');
        $conf->set('plugins.ISSO_SERVER', 'value');

        $str = 'http://randomstr.com/test';
        $date = '20161118_100001';
        $data = array(
            'title' => $str,
            'links' => array(
                array(
                    'url' => $str,
                    'created' => DateTime::createFromFormat('Ymd_His', $date),
                )
            ),
            'search_term' => $str
        );

        $processed = hook_isso_render_linklist($data, $conf);

        // data shouldn't be altered
        $this->assertEquals($data, $processed);
    }

    /**
     * Test isso plugin without server configuration (shouldn't be displayed).
     */
    function testIssoWithoutConf()
    {
        $data = 'abc';
        $conf = new ConfigManager('');
        $processed = hook_isso_render_linklist($data, $conf);
        $this->assertEquals($data, $processed);
    }
}
