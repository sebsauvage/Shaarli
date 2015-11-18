<?php

/**
 * Plugin readityourself
 */

// If we're talking about https://github.com/memiks/readityourself
// it seems kinda dead.
// Not tested.

// don't raise unnecessary warnings
if (is_file(PluginManager::$PLUGINS_PATH . '/readityourself/config.php')) {
    include PluginManager::$PLUGINS_PATH . '/readityourself/config.php';
}

if (empty($GLOBALS['plugins']['READITYOUSELF_URL'])) {
    $GLOBALS['plugin_errors'][] = 'Readityourself plugin error: '.
        'Please define "$GLOBALS[\'plugins\'][\'READITYOUSELF_URL\']" '.
        'in "plugins/readityourself/config.php" or in your Shaarli config.php file.';
}

/**
 * Add readityourself icon to link_plugin when rendering linklist.
 *
 * @param mixed $data - linklist data.
 *
 * @return mixed - linklist data with readityourself plugin.
 */
function hook_readityourself_render_linklist($data)
{
    if (!isset($GLOBALS['plugins']['READITYOUSELF_URL'])) {
        return $data;
    }

    $readityourself_html = file_get_contents(PluginManager::$PLUGINS_PATH . '/readityourself/readityourself.html');

    foreach ($data['links'] as &$value) {
        $readityourself = sprintf($readityourself_html, $GLOBALS['plugins']['READITYOUSELF_URL'], $value['url'], PluginManager::$PLUGINS_PATH);
        $value['link_plugin'][] = $readityourself;
    }

    return $data;
}
