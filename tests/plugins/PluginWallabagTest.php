<?php
namespace Shaarli\Plugin\Wallabag;

use Shaarli\Config\ConfigManager;
use Shaarli\Plugin\PluginManager;

require_once 'plugins/wallabag/wallabag.php';

/**
 * Class PluginWallabagTest
 * Unit test for the Wallabag plugin
 */
class PluginWallabagTest extends \Shaarli\TestCase
{
    /**
     * Reset plugin path
     */
    protected function setUp(): void
    {
        PluginManager::$PLUGINS_PATH = 'plugins';
    }

    /**
     * Test wallabag init without errors.
     */
    public function testWallabagInitNoError()
    {
        $conf = new ConfigManager('');
        $conf->set('plugins.WALLABAG_URL', 'value');
        $errors = wallabag_init($conf);
        $this->assertEmpty($errors);
    }

    /**
     * Test wallabag init with errors.
     */
    public function testWallabagInitError()
    {
        $conf = new ConfigManager('');
        $errors = wallabag_init($conf);
        $this->assertNotEmpty($errors);
    }

    /**
     * Test render_linklist hook.
     */
    public function testWallabagLinklist()
    {
        $conf = new ConfigManager('');
        $conf->set('plugins.WALLABAG_URL', 'value');
        $str = 'http://randomstr.com/test';
        $data = [
            'title' => $str,
            'links' => [
                [
                    'url' => $str,
                ]
            ],
            '_LOGGEDIN_' => true,
        ];

        $data = hook_wallabag_render_linklist($data, $conf);
        $link = $data['links'][0];
        // data shouldn't be altered
        $this->assertEquals($str, $data['title']);
        $this->assertEquals($str, $link['url']);

        // plugin data
        $this->assertEquals(1, count($link['link_plugin']));
        $this->assertNotFalse(strpos($link['link_plugin'][0], urlencode($str)));
        $this->assertNotFalse(strpos($link['link_plugin'][0], $conf->get('plugins.WALLABAG_URL')));
    }

    /**
     * Test render_linklist hook while logged out: no change.
     */
    public function testWallabagLinklistLoggedOut(): void
    {
        $conf = new ConfigManager('');
        $str = 'http://randomstr.com/test';
        $data = [
            'title' => $str,
            'links' => [
                [
                    'url' => $str,
                ]
            ],
            '_LOGGEDIN_' => false,
        ];

        $result = hook_wallabag_render_linklist($data, $conf);

        static::assertSame($data, $result);
    }
}
