<?php

namespace Shaarli\Plugin\DefaultColors;

use DateTime;
use PHPUnit\Framework\TestCase;
use Shaarli\Bookmark\LinkDB;
use Shaarli\Config\ConfigManager;
use Shaarli\Plugin\PluginManager;

require_once 'plugins/default_colors/default_colors.php';

/**
 * Class PluginDefaultColorsTest
 *
 * Test the DefaultColors plugin (allowing to override default template colors).
 */
class PluginDefaultColorsTest extends TestCase
{
    /**
     * Reset plugin path
     */
    public function setUp()
    {
        PluginManager::$PLUGINS_PATH = 'sandbox';
        mkdir(PluginManager::$PLUGINS_PATH . '/default_colors/');
        copy(
            'plugins/default_colors/default_colors.css.template',
            PluginManager::$PLUGINS_PATH . '/default_colors/default_colors.css.template'
        );
    }

    /**
     * Remove sandbox files and folder
     */
    public function tearDown()
    {
        if (file_exists('sandbox/default_colors/default_colors.css.template')) {
            unlink('sandbox/default_colors/default_colors.css.template');
        }

        if (file_exists('sandbox/default_colors/default_colors.css')) {
            unlink('sandbox/default_colors/default_colors.css');
        }

        if (is_dir('sandbox/default_colors')) {
            rmdir('sandbox/default_colors');
        }
    }

    /**
     * Test DefaultColors init without errors.
     */
    public function testDefaultColorsInitNoError()
    {
        $conf = new ConfigManager('');
        $conf->set('plugins.DEFAULT_COLORS_BACKGROUND', 'value');
        $errors = default_colors_init($conf);
        $this->assertEmpty($errors);
    }

    /**
     * Test DefaultColors init with errors.
     */
    public function testDefaultColorsInitError()
    {
        $conf = new ConfigManager('');
        $errors = default_colors_init($conf);
        $this->assertNotEmpty($errors);
    }

    /**
     * Test the save plugin parameters hook with all colors specified.
     */
    public function testSavePluginParametersAll()
    {
        $post = [
            'other1' => true,
            'DEFAULT_COLORS_MAIN' => 'blue',
            'DEFAULT_COLORS_BACKGROUND' => 'pink',
            'other2' => ['yep'],
            'DEFAULT_COLORS_DARK_MAIN' => 'green',
        ];

        hook_default_colors_save_plugin_parameters($post);
        $this->assertFileExists($file = 'sandbox/default_colors/default_colors.css');
        $content = file_get_contents($file);
        $expected = ':root {
  --main-color: blue;
  --background-color: pink;
  --dark-main-color: green;

}
';
        $this->assertEquals($expected, $content);
    }

    /**
     * Test the save plugin parameters hook with only one color specified.
     */
    public function testSavePluginParametersSingle()
    {
        $post = [
            'other1' => true,
            'DEFAULT_COLORS_BACKGROUND' => 'pink',
            'other2' => ['yep'],
            'DEFAULT_COLORS_DARK_MAIN' => '',
        ];

        hook_default_colors_save_plugin_parameters($post);
        $this->assertFileExists($file = 'sandbox/default_colors/default_colors.css');
        $content = file_get_contents($file);
        $expected = ':root {
  --background-color: pink;

}
';
        $this->assertEquals($expected, $content);
    }

    /**
     * Test the save plugin parameters hook with no color specified.
     */
    public function testSavePluginParametersNone()
    {
        hook_default_colors_save_plugin_parameters([]);
        $this->assertFileNotExists($file = 'sandbox/default_colors/default_colors.css');
    }

    /**
     * Make sure that the CSS is properly included by the include hook.
     */
    public function testIncludeWithFile()
    {
        $data = [
            'css_files' => ['file1'],
            'js_files' => ['file2'],
        ];
        touch($file = 'sandbox/default_colors/default_colors.css');
        $processedData = hook_default_colors_render_includes($data);

        $this->assertCount(2, $processedData['css_files']);
        $this->assertEquals($file, $processedData['css_files'][1]);
        $this->assertCount(1, $processedData['js_files']);
    }

    /**
     * Make sure that the CSS is not included by the include hook if the CSS file does not exist.
     */
    public function testIncludeWithoutFile()
    {
        $data = [
            'css_files' => ['file1'],
            'js_files' => ['file2'],
        ];
        $processedData = hook_default_colors_render_includes($data);

        $this->assertEquals($data, $processedData);
    }

    /**
     * Test helper function which generates CSS rules with valid input.
     */
    public function testFormatCssRuleValid()
    {
        $data = [
            'other1' => true,
            'DEFAULT_COLORS_BLIP_BLOP' => 'shinyColor',
            'other2' => ['yep'],
        ];
        $result = default_colors_format_css_rule($data, 'DEFAULT_COLORS_BLIP_BLOP');
        $this->assertEquals('  --blip-blop-color: shinyColor', $result);

        $data = ['unknown-parameter' => true];
        $result = default_colors_format_css_rule($data, 'unknown-parameter');
        $this->assertEquals('  --unknown-parameter-color: 1', $result);
    }

    /**
     * Test helper function which generates CSS rules with invalid input.
     */
    public function testFormatCssRuleInvalid()
    {
        $result = default_colors_format_css_rule([], 'DEFAULT_COLORS_BLIP_BLOP');
        $this->assertEmpty($result);

        $data = [
            'other1' => true,
            'DEFAULT_COLORS_BLIP_BLOP' => 'shinyColor',
            'other2' => ['yep'],
        ];
        $result = default_colors_format_css_rule($data, '');
        $this->assertEmpty($result);
    }
}
