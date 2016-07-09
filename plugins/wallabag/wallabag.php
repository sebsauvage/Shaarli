<?php

/**
 * Plugin Wallabag.
 */

require_once 'WallabagInstance.php';

$wallabagUrl = $conf->get('plugins.WALLABAG_URL');
if (empty($wallabagUrl)) {
    $GLOBALS['plugin_errors'][] = 'Wallabag plugin error: '.
        'Please define the "WALLABAG_URL" setting in the plugin administration page.';
}

/**
 * Add wallabag icon to link_plugin when rendering linklist.
 *
 * @param mixed         $data Linklist data.
 * @param ConfigManager $conf Configuration Manager instance.
 *
 * @return mixed - linklist data with wallabag plugin.
 */
function hook_wallabag_render_linklist($data, $conf)
{
    $wallabagUrl = $conf->get('plugins.WALLABAG_URL');
    if (empty($wallabagUrl)) {
        return $data;
    }

    $version = $conf->get('plugins.WALLABAG_VERSION');
    $wallabagInstance = new WallabagInstance($wallabagUrl, $version);

    $wallabagHtml = file_get_contents(PluginManager::$PLUGINS_PATH . '/wallabag/wallabag.html');

    foreach ($data['links'] as &$value) {
        $wallabag = sprintf(
            $wallabagHtml,
            $wallabagInstance->getWallabagUrl(),
            urlencode($value['url']),
            PluginManager::$PLUGINS_PATH
        );
        $value['link_plugin'][] = $wallabag;
    }

    return $data;
}

