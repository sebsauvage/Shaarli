<?php

/**
 * Piwik plugin.
 * Adds tracking code on each page.
 */

use Shaarli\Plugin\PluginManager;

/**
 * Initialization function.
 * It will be called when the plugin is loaded.
 * This function can be used to return a list of initialization errors.
 *
 * @param $conf ConfigManager instance.
 *
 * @return array List of errors (optional).
 */
function piwik_init($conf)
{
    $piwikUrl = $conf->get('plugins.PIWIK_URL');
    $piwikSiteid = $conf->get('plugins.PIWIK_SITEID');
    if (empty($piwikUrl) || empty($piwikSiteid)) {
        $error = t('Piwik plugin error: ' .
            'Please define PIWIK_URL and PIWIK_SITEID in the plugin administration page.');
        return [$error];
    }
}

/**
 * Hook render_footer.
 * Executed on every page redering.
 *
 * Template placeholders:
 *   - text
 *   - endofpage
 *   - js_files
 *
 * Data:
 *   - _PAGE_: current page
 *   - _LOGGEDIN_: true/false
 *
 * @param array $data data passed to plugin
 *
 * @return array altered $data.
 */
function hook_piwik_render_footer($data, $conf)
{
    $piwikUrl = $conf->get('plugins.PIWIK_URL');
    $piwikSiteid = $conf->get('plugins.PIWIK_SITEID');
    if (empty($piwikUrl) || empty($piwikSiteid)) {
        return $data;
    }

    // Free elements at the end of the page.
    $data['endofpage'][] = sprintf(
        file_get_contents(PluginManager::$PLUGINS_PATH . '/piwik/piwik.html'),
        $piwikUrl,
        $piwikSiteid,
        $piwikUrl,
        $piwikSiteid
    );

    return $data;
}

/**
 * This function is never called, but contains translation calls for GNU gettext extraction.
 */
function piwik_dummy_translation()
{
    // meta
    t('A plugin that adds Piwik tracking code to Shaarli pages.');
    t('Piwik URL');
    t('Piwik site ID');
}
