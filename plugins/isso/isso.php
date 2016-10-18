<?php

/**
 * Plugin Isso.
 */

/**
 * Display an error everywhere if the plugin is enabled without configuration.
 *
 * @param $data array         List of links
 * @param $conf ConfigManager instance
 *
 * @return mixed - linklist data with Isso plugin.
 */
function isso_init($conf)
{
    $issoUrl = $conf->get('plugins.ISSO_SERVER');
    if (empty($issoUrl)) {
        $error = 'Isso plugin error: '.
            'Please define the "ISSO_SERVER" setting in the plugin administration page.';
        return array($error);
    }
}

/**
 * Render linklist hook.
 * Will only display Isso comments on permalinks.
 *
 * @param $data array         List of links
 * @param $conf ConfigManager instance
 *
 * @return mixed - linklist data with Isso plugin.
 */
function hook_isso_render_linklist($data, $conf)
{
    $issoUrl = $conf->get('plugins.ISSO_SERVER');
    if (empty($issoUrl)) {
        return $data;
    }

    // Only display comments for permalinks.
    if (count($data['links']) == 1 && empty($data['search_tags']) && empty($data['search_term'])) {
        $link = reset($data['links']);
        $isso_html = file_get_contents(PluginManager::$PLUGINS_PATH . '/isso/isso.html');

        $isso = sprintf($isso_html, $issoUrl, $issoUrl, $link['linkdate'], $link['linkdate']);
        $data['plugin_end_zone'][] = $isso;

        // Hackish way to include this CSS file only when necessary.
        $data['plugins_includes']['css_files'][] = PluginManager::$PLUGINS_PATH . '/isso/isso.css';
    }

    return $data;
}
