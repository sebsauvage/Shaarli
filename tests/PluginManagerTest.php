<?php

namespace Shaarli\Plugin;

use Shaarli\Bookmark\Bookmark;
use Shaarli\Config\ConfigManager;

/**
 * Unit tests for Plugins
 */
class PluginManagerTest extends \Shaarli\TestCase
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
        $this->pluginManager->load([self::$pluginName]);

        $this->assertTrue(function_exists('hook_test_random'));

        $data = [0 => 'woot'];
        $this->pluginManager->executeHooks('random', $data);

        static::assertCount(2, $data);
        static::assertSame('woot', $data[1]);

        $data = [0 => 'woot'];
        $this->pluginManager->executeHooks('random', $data, ['target' => 'test']);

        static::assertCount(2, $data);
        static::assertSame('page test', $data[1]);

        $data = [0 => 'woot'];
        $this->pluginManager->executeHooks('random', $data, ['loggedin' => true]);

        static::assertCount(2, $data);
        static::assertEquals('loggedin', $data[1]);

        $data = [0 => 'woot'];
        $this->pluginManager->executeHooks('random', $data, ['loggedin' => null]);

        static::assertCount(3, $data);
        static::assertEquals('loggedin', $data[1]);
        static::assertArrayHasKey(2, $data);
        static::assertNull($data[2]);
    }

    /**
     * Test plugin loading and hook execution with an error: raise an incompatibility error.
     */
    public function testPluginWithPhpError(): void
    {
        PluginManager::$PLUGINS_PATH = self::$pluginPath;
        $this->pluginManager->load([self::$pluginName]);

        $this->assertTrue(function_exists('hook_test_error'));

        $data = [];
        $this->pluginManager->executeHooks('error', $data);

        $this->assertRegExp(
            '/test \[plugin incompatibility\]: Class [\'"]Unknown[\'"] not found/',
            $this->pluginManager->getErrors()[0]
        );
    }

    /**
     * Test missing plugin loading.
     */
    public function testPluginNotFound(): void
    {
        $this->pluginManager->load([]);
        $this->pluginManager->load(['nope', 'renope']);
        $this->addToAssertionCount(1);
    }

    /**
     * Test plugin metadata loading.
     */
    public function testGetPluginsMeta(): void
    {
        PluginManager::$PLUGINS_PATH = self::$pluginPath;
        $this->pluginManager->load([self::$pluginName]);

        $expectedParameters = [
            'pop' => [
                'value' => '',
                'desc'  => 'pop description',
            ],
            'hip' => [
                'value' => '',
                'desc' => '',
            ],
        ];
        $meta = $this->pluginManager->getPluginsMeta();
        $this->assertEquals('test plugin', $meta[self::$pluginName]['description']);
        $this->assertEquals($expectedParameters, $meta[self::$pluginName]['parameters']);
    }

    /**
     * Test plugin custom routes - note that there is no check on callable functions
     */
    public function testRegisteredRoutes(): void
    {
        PluginManager::$PLUGINS_PATH = self::$pluginPath;
        $this->pluginManager->load([self::$pluginName]);

        $expectedParameters = [
            [
                'method' => 'GET',
                'route' => '/test',
                'callable' => 'getFunction',
            ],
            [
                'method' => 'POST',
                'route' => '/custom',
                'callable' => 'postFunction',
            ],
        ];
        $meta = $this->pluginManager->getRegisteredRoutes();
        static::assertSame($expectedParameters, $meta[self::$pluginName]);
    }

    /**
     * Test plugin custom routes with invalid route
     */
    public function testRegisteredRoutesInvalid(): void
    {
        $plugin = 'test_route_invalid';
        $this->pluginManager->load([$plugin]);

        $meta = $this->pluginManager->getRegisteredRoutes();
        static::assertSame([], $meta);

        $errors = $this->pluginManager->getErrors();
        static::assertSame(['test_route_invalid [plugin incompatibility]: trying to register invalid route.'], $errors);
    }

    public function testSearchFilterPlugin(): void
    {
        PluginManager::$PLUGINS_PATH = self::$pluginPath;
        $this->pluginManager->load([self::$pluginName]);

        static::assertNull($this->pluginManager->getFilterSearchEntryHooks());

        static::assertTrue($this->pluginManager->filterSearchEntry(new Bookmark(), ['_result' => true]));

        static::assertCount(1, $this->pluginManager->getFilterSearchEntryHooks());
        static::assertSame('hook_test_filter_search_entry', $this->pluginManager->getFilterSearchEntryHooks()[0]);

        static::assertFalse($this->pluginManager->filterSearchEntry(new Bookmark(), ['_result' => false]));
    }
}
