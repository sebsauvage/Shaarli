<?php

/**
 * Wallabag plugin
 */

use Shaarli\Config\ConfigManager;
use Shaarli\Plugin\PluginManager;
use Shaarli\Plugin\Wallabag\WallabagInstance;

/**
 * Init function, return an error if the server is not set.
 *
 * @param $conf ConfigManager instance.
 *
 * @return array Eventual error.
 */
function wallabag_init($conf)
{
    $wallabagUrl = $conf->get('plugins.WALLABAG_URL');
    if (empty($wallabagUrl)) {
        $error = t('Wallabag plugin error: ' .
            'Please define the "WALLABAG_URL" setting in the plugin administration page.');
        return [$error];
    }
    $conf->setEmpty('plugins.WALLABAG_URL', '2');
}

/**
 * Add wallabag icon to link_plugin when rendering linklist.
 *
 * @param mixed         $data Linklist data.
 * @param ConfigManager $conf Configuration Manager instance.
 *
 * @return mixed - linklist data with wallabag plugin.
 */
function hook_wallabag_render_linklist($data, $conf)
{
    $wallabagUrl = $conf->get('plugins.WALLABAG_URL');
    if (empty($wallabagUrl) || !$data['_LOGGEDIN_']) {
        return $data;
    }

    $version = $conf->get('plugins.WALLABAG_VERSION');
    $wallabagInstance = new WallabagInstance($wallabagUrl, $version);

    $wallabagHtml = file_get_contents(PluginManager::$PLUGINS_PATH . '/wallabag/wallabag.html');

    $linkTitle = t('Save to wallabag');
    $path = ($data['_ROOT_PATH_'] ?? '') . '/' . PluginManager::$PLUGINS_PATH;

    foreach ($data['links'] as &$value) {
        $wallabag = sprintf(
            $wallabagHtml,
            $wallabagInstance->getWallabagUrl(),
            urlencode(unescape($value['url'])),
            $path,
            $linkTitle
        );
        $value['link_plugin'][] = $wallabag;
    }

    return $data;
}

/**
 * This function is never called, but contains translation calls for GNU gettext extraction.
 */
function wallabag_dummy_translation()
{
    // meta
    t("For each link, add a Wallabag icon to save it in your instance.");
    t('Wallabag API URL');
    t('Wallabag API version (1 or 2)');
}
