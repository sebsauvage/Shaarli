<?php

/**
 * Plugin default_colors.
 *
 * Allow users to easily overrides colors of the default theme.
 */

use Shaarli\Config\ConfigManager;
use Shaarli\Plugin\PluginManager;

const DEFAULT_COLORS_PLACEHOLDERS = [
    'DEFAULT_COLORS_MAIN',
    'DEFAULT_COLORS_BACKGROUND',
    'DEFAULT_COLORS_DARK_MAIN',
];

const DEFAULT_COLORS_CSS_FILE = '/default_colors/default_colors.css';

/**
 * Display an error if the plugin is active a no color is configured.
 *
 * @param $conf ConfigManager instance
 *
 * @return array|null The errors array or null of there is none.
 */
function default_colors_init($conf)
{
    $params = [];
    foreach (DEFAULT_COLORS_PLACEHOLDERS as $placeholder) {
        $value = trim($conf->get('plugins.' . $placeholder, ''));
        if (strlen($value) > 0) {
            $params[$placeholder] = $value;
        }
    }

    if (empty($params)) {
        $error = t('Default colors plugin error: ' .
            'This plugin is active and no custom color is configured.');
        return [$error];
    }

    // Colors are defined but the custom CSS file does not exist -> generate it
    if (!file_exists(PluginManager::$PLUGINS_PATH . DEFAULT_COLORS_CSS_FILE)) {
        default_colors_generate_css_file($params);
    }
}

/**
 * When plugin parameters are saved, we regenerate the custom CSS file with provided settings.
 *
 * @param array         $data $_POST array
 *
 * @return array Updated $_POST array
 */
function hook_default_colors_save_plugin_parameters($data)
{
    default_colors_generate_css_file($data);

    return $data;
}

/**
 * When linklist is displayed, include default_colors CSS file.
 *
 * @param array $data - header data.
 *
 * @return mixed - header data with default_colors CSS file added.
 */
function hook_default_colors_render_includes($data)
{
    $file = PluginManager::$PLUGINS_PATH . '/default_colors/default_colors.css';
    if (file_exists($file)) {
        $data['css_files'][] = $file ;
    }

    return $data;
}

/**
 * Regenerate the custom CSS file with provided settings.
 *
 * @param array $params Plugin configuration (CSS rules)
 */
function default_colors_generate_css_file($params): void
{
    $file = PluginManager::$PLUGINS_PATH . '/default_colors/default_colors.css';
    $template = file_get_contents(PluginManager::$PLUGINS_PATH . '/default_colors/default_colors.css.template');
    $content = '';
    foreach (DEFAULT_COLORS_PLACEHOLDERS as $rule) {
        $content .= !empty($params[$rule])
            ? default_colors_format_css_rule($params, $rule) . ';' . PHP_EOL
            : '';
    }

    if (! empty($content)) {
        file_put_contents($file, sprintf($template, $content));
    }
}

/**
 * Create a valid CSS rule from parameters settings and plugin parameter.
 *
 * @param array  $data      $_POST array
 * @param string $parameter Plugin parameter name
 *
 * @return string CSS rules for the provided parameter and its matching value.
 */
function default_colors_format_css_rule($data, $parameter)
{
    if (empty($data[$parameter])) {
        return '';
    }

    $key = str_replace('DEFAULT_COLORS_', '', $parameter);
    $key = str_replace('_', '-', strtolower($key)) . '-color';
    return '  --' . $key . ': ' . $data[$parameter];
}


/**
 * This function is never called, but contains translation calls for GNU gettext extraction.
 */
function default_colors_translation()
{
    // meta
    t('Override default theme colors. Use any CSS valid color.');
    t('Main color (navbar green)');
    t('Background color (light grey)');
    t('Dark main color (e.g. visited links)');
}
