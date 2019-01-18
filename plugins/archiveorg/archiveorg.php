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

    foreach ($data['links'] as &$value) {
        if ($value['private'] && preg_match('/^\?[a-zA-Z0-9-_@]{6}($|&|#)/', $value['real_url'])) {
            continue;
        }
        $archive = sprintf($archive_html, $value['url'], t('View on archive.org'));
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
