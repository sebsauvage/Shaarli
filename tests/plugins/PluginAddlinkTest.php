<?php
namespace Shaarli\Plugin\Addlink;

use Shaarli\Plugin\PluginManager;
use Shaarli\Router;

require_once 'plugins/addlink_toolbar/addlink_toolbar.php';

/**
 * Unit test for the Addlink toolbar plugin
 */
class PluginAddlinkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Reset plugin path.
     */
    public function setUp()
    {
        PluginManager::$PLUGINS_PATH = 'plugins';
    }

    /**
     * Test render_header hook while logged in.
     */
    public function testAddlinkHeaderLoggedIn()
    {
        $str = 'stuff';
        $data = array($str => $str);
        $data['_PAGE_'] = Router::$PAGE_LINKLIST;
        $data['_LOGGEDIN_'] = true;

        $data = hook_addlink_toolbar_render_header($data);
        $this->assertEquals($str, $data[$str]);
        $this->assertEquals(1, count($data['fields_toolbar']));

        $data = array($str => $str);
        $data['_PAGE_'] = $str;
        $data['_LOGGEDIN_'] = true;
        $data = hook_addlink_toolbar_render_header($data);
        $this->assertEquals($str, $data[$str]);
        $this->assertArrayNotHasKey('fields_toolbar', $data);
    }

    /**
     * Test render_header hook while logged out.
     */
    public function testAddlinkHeaderLoggedOut()
    {
        $str = 'stuff';
        $data = array($str => $str);
        $data['_PAGE_'] = Router::$PAGE_LINKLIST;
        $data['_LOGGEDIN_'] = false;

        $data = hook_addlink_toolbar_render_header($data);
        $this->assertEquals($str, $data[$str]);
        $this->assertArrayNotHasKey('fields_toolbar', $data);
    }
}
