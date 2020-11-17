<?php

/**
 * Plugin Archive.org.
 *
 * Add an icon in the link list for archive.org.
 */

use Shaarli\Plugin\PluginManager;

/**
 * Add archive.org icon to link_plugin when rendering linklist.
 *
 * @param mixed $data - linklist data.
 *
 * @return mixed - linklist data with archive.org plugin.
 */
function hook_archiveorg_render_linklist($data)
{
    $archive_html = file_get_contents(PluginManager::$PLUGINS_PATH . '/archiveorg/archiveorg.html');
    $path = ($data['_ROOT_PATH_'] ?? '') . '/' . PluginManager::$PLUGINS_PATH;

    foreach ($data['links'] as &$value) {
        $isNote = startsWith($value['real_url'], '/shaare/');
        if ($value['private'] && $isNote) {
            continue;
        }
        $url = $isNote ? rtrim(index_url($_SERVER), '/') . $value['real_url'] : $value['real_url'];
        $archive = sprintf($archive_html, $url, $path, t('View on archive.org'));
        $value['link_plugin'][] = $archive;
    }

    return $data;
}

/**
 * This function is never called, but contains translation calls for GNU gettext extraction.
 */
function archiveorg_dummy_translation()
{
    // meta
    t('For each link, add an Archive.org icon.');
}
