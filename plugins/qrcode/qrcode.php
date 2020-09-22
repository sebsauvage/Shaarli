<?php

/**
 * Plugin qrcode
 * Add QRCode containing URL for each links.
 * Display a QRCode icon in link list.
 */

use Shaarli\Plugin\PluginManager;
use Shaarli\Render\TemplatePage;

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

    $path = ($data['_ROOT_PATH_'] ?? '') . '/' . PluginManager::$PLUGINS_PATH;
    foreach ($data['links'] as &$value) {
        $qrcode = sprintf(
            $qrcode_html,
            $value['url'],
            $path
        );
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
    if ($data['_PAGE_'] == TemplatePage::LINKLIST) {
        $data['js_files'][] = PluginManager::$PLUGINS_PATH . '/qrcode/shaarli-qrcode.js';
    }

    return $data;
}

/**
 * When linklist is displayed, include qrcode CSS file.
 *
 * @param array $data - header data.
 *
 * @return mixed - header data with qrcode CSS file added.
 */
function hook_qrcode_render_includes($data)
{
    if ($data['_PAGE_'] == TemplatePage::LINKLIST) {
        $data['css_files'][] = PluginManager::$PLUGINS_PATH . '/qrcode/qrcode.css';
    }

    return $data;
}

/**
 * This function is never called, but contains translation calls for GNU gettext extraction.
 */
function qrcode_dummy_translation()
{
    // meta
    t('For each link, add a QRCode icon.');
}
