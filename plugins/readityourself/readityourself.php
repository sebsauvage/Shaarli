<?php

/**
 * Plugin readityourself
 */

// If we're talking about https://github.com/memiks/readityourself
// it seems kinda dead.
// Not tested.

$conf = ConfigManager::getInstance();
$riyUrl = $conf->get('plugins.READITYOUSELF_URL');
if (empty($riyUrl)) {
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
    $conf = ConfigManager::getInstance();
    $riyUrl = $conf->get('plugins.READITYOUSELF_URL');
    if (empty($riyUrl)) {
        return $data;
    }

    $readityourself_html = file_get_contents(PluginManager::$PLUGINS_PATH . '/readityourself/readityourself.html');

    foreach ($data['links'] as &$value) {
        $readityourself = sprintf($readityourself_html, $riyUrl, $value['url'], PluginManager::$PLUGINS_PATH);
        $value['link_plugin'][] = $readityourself;
    }

    return $data;
}
