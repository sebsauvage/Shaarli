<?php
namespace Shaarli\Config;

use Shaarli\Config\Exception\PluginConfigOrderException;

require_once 'application/config/ConfigPlugin.php';

/**
 * Unitary tests for Shaarli config related functions
 */
class ConfigPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test save_plugin_config with valid data.
     *
     * @throws PluginConfigOrderException
     */
    public function testSavePluginConfigValid()
    {
        $data = array(
            'order_plugin1' => 2,   // no plugin related
            'plugin2' => 0,         // new - at the end
            'plugin3' => 0,         // 2nd
            'order_plugin3' => 8,
            'plugin4' => 0,         // 1st
            'order_plugin4' => 5,
        );

        $expected = array(
            'plugin3',
            'plugin4',
            'plugin2',
        );

        $out = save_plugin_config($data);
        $this->assertEquals($expected, $out);
    }

    /**
     * Test save_plugin_config with invalid data.
     *
     * @expectedException Shaarli\Config\Exception\PluginConfigOrderException
     */
    public function testSavePluginConfigInvalid()
    {
        $data = array(
            'plugin2' => 0,
            'plugin3' => 0,
            'order_plugin3' => 0,
            'plugin4' => 0,
            'order_plugin4' => 0,
        );

        save_plugin_config($data);
    }

    /**
     * Test save_plugin_config without data.
     */
    public function testSavePluginConfigEmpty()
    {
        $this->assertEquals(array(), save_plugin_config(array()));
    }

    /**
     * Test validate_plugin_order with valid data.
     */
    public function testValidatePluginOrderValid()
    {
        $data = array(
            'order_plugin1' => 2,
            'plugin2' => 0,
            'plugin3' => 0,
            'order_plugin3' => 1,
            'plugin4' => 0,
            'order_plugin4' => 5,
        );

        $this->assertTrue(validate_plugin_order($data));
    }

    /**
     * Test validate_plugin_order with invalid data.
     */
    public function testValidatePluginOrderInvalid()
    {
        $data = array(
            'order_plugin1' => 2,
            'order_plugin3' => 1,
            'order_plugin4' => 1,
        );

        $this->assertFalse(validate_plugin_order($data));
    }

    /**
     * Test load_plugin_parameter_values.
     */
    public function testLoadPluginParameterValues()
    {
        $plugins = array(
            'plugin_name' => array(
                'parameters' => array(
                    'param1' => array('value' => true),
                    'param2' => array('value' => false),
                    'param3' => array('value' => ''),
                )
            )
        );

        $parameters = array(
            'param1' => 'value1',
            'param2' => 'value2',
        );

        $result = load_plugin_parameter_values($plugins, $parameters);
        $this->assertEquals('value1', $result['plugin_name']['parameters']['param1']['value']);
        $this->assertEquals('value2', $result['plugin_name']['parameters']['param2']['value']);
        $this->assertEquals('', $result['plugin_name']['parameters']['param3']['value']);
    }
}
