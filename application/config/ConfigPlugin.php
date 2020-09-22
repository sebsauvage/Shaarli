<?php

use Shaarli\Config\Exception\PluginConfigOrderException;
use Shaarli\Plugin\PluginManager;

/**
 * Plugin configuration helper functions.
 *
 * Note: no access to configuration files here.
 */

/**
 * Process plugin administration form data and save it in an array.
 *
 * @param array $formData Data sent by the plugin admin form.
 *
 * @return array New list of enabled plugin, ordered.
 *
 * @throws PluginConfigOrderException Plugins can't be sorted because their order is invalid.
 */
function save_plugin_config($formData)
{
    // We can only save existing plugins
    $directories = str_replace(
        PluginManager::$PLUGINS_PATH . '/',
        '',
        glob(PluginManager::$PLUGINS_PATH . '/*')
    );
    $formData = array_filter(
        $formData,
        function ($value, string $key) use ($directories) {
            return startsWith($key, 'order') || in_array($key, $directories);
        },
        ARRAY_FILTER_USE_BOTH
    );

    // Make sure there are no duplicates in orders.
    if (!validate_plugin_order($formData)) {
        throw new PluginConfigOrderException();
    }

    $plugins = [];
    $newEnabledPlugins = [];
    foreach ($formData as $key => $data) {
        if (startsWith($key, 'order')) {
            continue;
        }

        // If there is no order, it means a disabled plugin has been enabled.
        if (isset($formData['order_' . $key])) {
            $plugins[(int) $formData['order_' . $key]] = $key;
        } else {
            $newEnabledPlugins[] = $key;
        }
    }

    // New enabled plugins will be added at the end of order.
    $plugins = array_merge($plugins, $newEnabledPlugins);

    // Sort plugins by order.
    if (!ksort($plugins)) {
        throw new PluginConfigOrderException();
    }

    $finalPlugins = [];
    // Make plugins order continuous.
    foreach ($plugins as $plugin) {
        $finalPlugins[] = $plugin;
    }

    return $finalPlugins;
}

/**
 * Validate plugin array submitted.
 * Will fail if there is duplicate orders value.
 *
 * @param array $formData Data from submitted form.
 *
 * @return bool true if ok, false otherwise.
 */
function validate_plugin_order($formData)
{
    $orders = [];
    foreach ($formData as $key => $value) {
        // No duplicate order allowed.
        if (in_array($value, $orders, true)) {
            return false;
        }

        if (startsWith($key, 'order')) {
            $orders[] = $value;
        }
    }

    return true;
}

/**
 * Affect plugin parameters values from the ConfigManager into plugins array.
 *
 * @param mixed $plugins Plugins array:
 *                         $plugins[<plugin_name>]['parameters'][<param_name>] = [
 *                                                                                 'value' => <value>,
 *                                                                                 'desc' => <description>
 *                                                                               ]
 * @param mixed $conf  Plugins configuration.
 *
 * @return mixed Updated $plugins array.
 */
function load_plugin_parameter_values($plugins, $conf)
{
    $out = $plugins;
    foreach ($plugins as $name => $plugin) {
        if (empty($plugin['parameters'])) {
            continue;
        }

        foreach ($plugin['parameters'] as $key => $param) {
            if (!empty($conf[$key])) {
                $out[$name]['parameters'][$key]['value'] = $conf[$key];
            }
        }
    }

    return $out;
}
