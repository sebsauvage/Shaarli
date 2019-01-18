<?php
namespace Shaarli\Plugin\Pubsubhubbub;

use Shaarli\Config\ConfigManager;
use Shaarli\Plugin\PluginManager;
use Shaarli\Router;

require_once 'plugins/pubsubhubbub/pubsubhubbub.php';

/**
 * Class PluginPubsubhubbubTest
 * Unit test for the pubsubhubbub plugin
 */
class PluginPubsubhubbubTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string Config file path (without extension).
     */
    protected static $configFile = 'tests/utils/config/configJson';

    /**
     * Reset plugin path
     */
    public function setUp()
    {
        PluginManager::$PLUGINS_PATH = 'plugins';
    }

    /**
     * Test render_feed hook with an RSS feed.
     */
    public function testPubSubRssRenderFeed()
    {
        $hub = 'http://domain.hub';
        $conf = new ConfigManager(self::$configFile);
        $conf->set('plugins.PUBSUBHUB_URL', $hub);
        $data['_PAGE_'] = Router::$PAGE_FEED_RSS;

        $data = hook_pubsubhubbub_render_feed($data, $conf);
        $expected = '<atom:link rel="hub" href="'. $hub .'" />';
        $this->assertEquals($expected, $data['feed_plugins_header'][0]);
    }

    /**
     * Test render_feed hook with an ATOM feed.
     */
    public function testPubSubAtomRenderFeed()
    {
        $hub = 'http://domain.hub';
        $conf = new ConfigManager(self::$configFile);
        $conf->set('plugins.PUBSUBHUB_URL', $hub);
        $data['_PAGE_'] = Router::$PAGE_FEED_ATOM;

        $data = hook_pubsubhubbub_render_feed($data, $conf);
        $expected = '<link rel="hub" href="'. $hub .'" />';
        $this->assertEquals($expected, $data['feed_plugins_header'][0]);
    }
}
