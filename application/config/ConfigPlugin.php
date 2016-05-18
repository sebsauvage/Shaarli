<?php
/**
 * Functions related to configuration management.
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
    // Make sure there are no duplicates in orders.
    if (!validate_plugin_order($formData)) {
        throw new PluginConfigOrderException();
    }

    $plugins = array();
    $newEnabledPlugins = array();
    foreach ($formData as $key => $data) {
        if (startsWith($key, 'order')) {
            continue;
        }

        // If there is no order, it means a disabled plugin has been enabled.
        if (isset($formData['order_' . $key])) {
            $plugins[(int) $formData['order_' . $key]] = $key;
        }
        else {
            $newEnabledPlugins[] = $key;
        }
    }

    // New enabled plugins will be added at the end of order.
    $plugins = array_merge($plugins, $newEnabledPlugins);

    // Sort plugins by order.
    if (!ksort($plugins)) {
        throw new PluginConfigOrderException();
    }

    $finalPlugins = array();
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
    $orders = array();
    foreach ($formData as $key => $value) {
        // No duplicate order allowed.
        if (in_array($value, $orders)) {
            return false;
        }

        if (startsWith($key, 'order')) {
            $orders[] = $value;
        }
    }

    return true;
}

/**
 * Affect plugin parameters values into plugins array.
 *
 * @param mixed $plugins Plugins array ($plugins[<plugin_name>]['parameters']['param_name'] = <value>.
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
                $out[$name]['parameters'][$key] = $conf[$key];
            }
        }
    }

    return $out;
}

/**
 * Exception used if an error occur while saving plugin configuration.
 */
class PluginConfigOrderException extends Exception
{
    /**
     * Construct exception.
     */
    public function __construct()
    {
        $this->message = 'An error occurred while trying to save plugins loading order.';
    }
}
