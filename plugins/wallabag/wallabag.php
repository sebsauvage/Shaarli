<?php

/**
 * Plugin Wallabag.
 */

// don't raise unnecessary warnings
if (is_file(PluginManager::$PLUGINS_PATH . '/wallabag/config.php')) {
    include PluginManager::$PLUGINS_PATH . '/wallabag/config.php';
}

if (!isset($GLOBALS['plugins']['WALLABAG_URL'])) {
    $GLOBALS['plugins']['errors'][] = 'Wallabag plugin error: '.
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

    $wallabag_html = file_get_contents(PluginManager::$PLUGINS_PATH . '/wallabag/wallabag.html');

    foreach ($data['links'] as &$value) {
        $wallabag = sprintf($wallabag_html, $GLOBALS['plugins']['WALLABAG_URL'], $value['url'], PluginManager::$PLUGINS_PATH);
        $value['link_plugin'][] = $wallabag;
    }

    return $data;
}
