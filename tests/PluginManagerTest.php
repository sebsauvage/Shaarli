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

    public function setUp(): void
    {
        $conf = new ConfigManager('');
        $this->pluginManager = new PluginManager($conf);
    }

    /**
     * Test plugin loading and hook execution.
     */
    public function testPlugin(): void
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
     * Test plugin loading and hook execution with an error: raise an incompatibility error.
     */
    public function testPluginWithPhpError(): void
    {
        PluginManager::$PLUGINS_PATH = self::$pluginPath;
        $this->pluginManager->load(array(self::$pluginName));

        $this->assertTrue(function_exists('hook_test_error'));

        $data = [];
        $this->pluginManager->executeHooks('error', $data);

        $this->assertSame(
            'test [plugin incompatibility]: Class \'Unknown\' not found',
            $this->pluginManager->getErrors()[0]
        );
    }

    /**
     * Test missing plugin loading.
     */
    public function testPluginNotFound(): void
    {
        $this->pluginManager->load(array());
        $this->pluginManager->load(array('nope', 'renope'));
        $this->addToAssertionCount(1);
    }

    /**
     * Test plugin metadata loading.
     */
    public function testGetPluginsMeta(): void
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
