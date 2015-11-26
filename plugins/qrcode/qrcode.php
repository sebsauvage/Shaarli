<?php
/**
 * Plugin qrcode
 * Add QRCode containing URL for each links.
 * Display a QRCode icon in link list.
 */

/**
 * Add qrcode icon to link_plugin when rendering linklist.
 *
 * @param array $data - linklist data.
 *
 * @return mixed - linklist data with qrcode plugin.
 */
function hook_qrcode_render_linklist($data)
{
    $qrcode_html = file_get_contents(PluginManager::$PLUGINS_PATH . '/qrcode/qrcode.html');

    foreach ($data['links'] as &$value) {
        $qrcode = sprintf($qrcode_html, $value['real_url'], $value['real_url'], PluginManager::$PLUGINS_PATH);
        $value['link_plugin'][] = $qrcode;
    }

    return $data;
}

/**
 * When linklist is displayed, include qrcode JS files.
 *
 * @param array $data - footer data.
 *
 * @return mixed - footer data with qrcode JS files added.
 */
function hook_qrcode_render_footer($data)
{
    if ($data['_PAGE_'] == Router::$PAGE_LINKLIST) {
        $data['js_files'][] = PluginManager::$PLUGINS_PATH . '/qrcode/shaarli-qrcode.js';
    }

    return $data;
}
