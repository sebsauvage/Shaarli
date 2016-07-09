<?php

/**
 * Plugin readityourself
 */

// If we're talking about https://github.com/memiks/readityourself
// it seems kinda dead.
// Not tested.

$riyUrl = $conf->get('plugins.READITYOUSELF_URL');
if (empty($riyUrl)) {
    $GLOBALS['plugin_errors'][] = 'Readityourself plugin error: '.
        'Please define the "READITYOUSELF_URL" setting in the plugin administration page.';
}

/**
 * Add readityourself icon to link_plugin when rendering linklist.
 *
 * @param mixed         $data Linklist data.
 * @param ConfigManager $conf Configuration Manager instance.
 *
 * @return mixed - linklist data with readityourself plugin.
 */
function hook_readityourself_render_linklist($data, $conf)
{
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
