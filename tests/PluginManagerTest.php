<?php

/**
 * Plugin Manager tests
 */

require_once 'application/PluginManager.php';

/**
 * Unit tests for Plugins
 */
class PluginManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Path to tests plugin.
     * @var string $_PLUGIN_PATH
     */
    private static $_PLUGIN_PATH = 'tests/plugins';

    /**
     * Test plugin.
     * @var string $_PLUGIN_NAME
     */
    private static $_PLUGIN_NAME = 'test';

    /**
     * Test plugin loading and hook execution.
     *
     * @return void
     */
    public function testPlugin()
    {
        $pluginManager = PluginManager::getInstance();

        PluginManager::$PLUGINS_PATH = self::$_PLUGIN_PATH;
        $pluginManager->load(array(self::$_PLUGIN_NAME));

        $this->assertTrue(function_exists('hook_test_random'));

        $data = array(0 => 'woot');
        $pluginManager->executeHooks('random', $data);
        $this->assertEquals('woot', $data[1]);

        $data = array(0 => 'woot');
        $pluginManager->executeHooks('random', $data, array('target' => 'test'));
        $this->assertEquals('page test', $data[1]);

        $data = array(0 => 'woot');
        $pluginManager->executeHooks('random', $data, array('loggedin' => true));
        $this->assertEquals('loggedin', $data[1]);
    }

    /**
     * Test missing plugin loading.
     *
     * @return void
     */
    public function testPluginNotFound()
    {
        $pluginManager = PluginManager::getInstance();

        $pluginManager->load(array());

        $pluginManager->load(array('nope', 'renope'));
    }
}