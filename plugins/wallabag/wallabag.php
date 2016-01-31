<?php

/**
 * Plugin Wallabag.
 */

require_once 'WallabagInstance.php';

// don't raise unnecessary warnings
if (is_file(PluginManager::$PLUGINS_PATH . '/wallabag/config.php')) {
    include PluginManager::$PLUGINS_PATH . '/wallabag/config.php';
}

if (empty($GLOBALS['plugins']['WALLABAG_URL'])) {
    $GLOBALS['plugin_errors'][] = 'Wallabag plugin error: '.
        'Please define "$GLOBALS[\'plugins\'][\'WALLABAG_URL\']" '.
        'in "plugins/wallabag/config.php" or in your Shaarli config.php file.';
}

/**
 * Add wallabag icon to link_plugin when rendering linklist.
 *
 * @param mixed $data - linklist data.
 *
 * @return mixed - linklist data with wallabag plugin.
 */
function hook_wallabag_render_linklist($data)
{
    if (!isset($GLOBALS['plugins']['WALLABAG_URL'])) {
        return $data;
    }

    $version = isset($GLOBALS['plugins']['WALLABAG_VERSION'])
        ? $GLOBALS['plugins']['WALLABAG_VERSION']
        : '';
    $wallabagInstance = new WallabagInstance($GLOBALS['plugins']['WALLABAG_URL'], $version);

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

