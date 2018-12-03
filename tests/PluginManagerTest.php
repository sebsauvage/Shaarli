<?php
namespace Shaarli\Plugin;

use Shaarli\Config\ConfigManager;

/**
 * Unit tests for Plugins
 */
class PluginManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Path to tests plugin.
     * @var string $pluginPath
     */
    private static $pluginPath = 'tests/plugins';

    /**
     * Test plugin.
     * @var string $pluginName
     */
    private static $pluginName = 'test';

    /**
     * @var PluginManager $pluginManager Plugin Mananger instance.
     */
    protected $pluginManager;

    public function setUp()
    {
        $conf = new ConfigManager('');
        $this->pluginManager = new PluginManager($conf);
    }

    /**
     * Test plugin loading and hook execution.
     *
     * @return void
     */
    public function testPlugin()
    {
        PluginManager::$PLUGINS_PATH = self::$pluginPath;
        $this->pluginManager->load(array(self::$pluginName));

        $this->assertTrue(function_exists('hook_test_random'));

        $data = array(0 => 'woot');
        $this->pluginManager->executeHooks('random', $data);
        $this->assertEquals('woot', $data[1]);

        $data = array(0 => 'woot');
        $this->pluginManager->executeHooks('random', $data, array('target' => 'test'));
        $this->assertEquals('page test', $data[1]);

        $data = array(0 => 'woot');
        $this->pluginManager->executeHooks('random', $data, array('loggedin' => true));
        $this->assertEquals('loggedin', $data[1]);
    }

    /**
     * Test missing plugin loading.
     *
     * @return void
     */
    public function testPluginNotFound()
    {
        $this->pluginManager->load(array());
        $this->pluginManager->load(array('nope', 'renope'));
    }

    /**
     * Test plugin metadata loading.
     */
    public function testGetPluginsMeta()
    {
        PluginManager::$PLUGINS_PATH = self::$pluginPath;
        $this->pluginManager->load(array(self::$pluginName));

        $expectedParameters = array(
            'pop' => array(
                'value' => '',
                'desc'  => 'pop description',
            ),
            'hip' => array(
                'value' => '',
                'desc' => '',
            ),
        );
        $meta = $this->pluginManager->getPluginsMeta();
        $this->assertEquals('test plugin', $meta[self::$pluginName]['description']);
        $this->assertEquals($expectedParameters, $meta[self::$pluginName]['parameters']);
    }
}
