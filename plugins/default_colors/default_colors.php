<?php

/**
 * Plugin default_colors.
 *
 * Allow users to easily overrides colors of the default theme.
 */

use Shaarli\Plugin\PluginManager;

const DEFAULT_COLORS_PLACEHOLDERS = [
    'DEFAULT_COLORS_MAIN',
    'DEFAULT_COLORS_BACKGROUND',
    'DEFAULT_COLORS_DARK_MAIN',
];

/**
 * When plugin parameters are saved
 */
function hook_default_colors_save_plugin_parameters($data)
{
    $file = PluginManager::$PLUGINS_PATH . '/default_colors/default_colors.css';
    $template = file_get_contents(PluginManager::$PLUGINS_PATH . '/default_colors/default_colors.css.template');
    $content = '';
    foreach (DEFAULT_COLORS_PLACEHOLDERS as $rule) {
        $content .= ! empty($data[$rule])
            ? default_colors_format_css_rule($data, $rule) .';'. PHP_EOL
            : '';
    }
    file_put_contents($file, sprintf($template, $content));
    return $data;
}

/**
 * When linklist is displayed, include isso CSS file.
 *
 * @param array $data - header data.
 *
 * @return mixed - header data with isso CSS file added.
 */
function hook_default_colors_render_includes($data)
{
    $file = PluginManager::$PLUGINS_PATH . '/default_colors/default_colors.css';
    if (file_exists($file )) {
        $data['css_files'][] = $file ;
    }

    return $data;
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

function default_colors_format_css_rule($data, $parameter)
{
    $key = str_replace('DEFAULT_COLORS_', '', $parameter);
    $key = str_replace('_', '-', strtolower($key)) .'-color';
    return '  --'. $key .': '. $data[$parameter];
}
