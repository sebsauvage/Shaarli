<?php

namespace Shaarli\Config;

/**
 * Class ConfigPhp (ConfigIO implementation)
 *
 * Handle Shaarli's legacy PHP configuration file.
 * Note: this is only designed to support the transition to JSON configuration.
 */
class ConfigPhp implements ConfigIO
{
    /**
     * @var array List of config key without group.
     */
    public static $ROOT_KEYS = [
        'login',
        'hash',
        'salt',
        'timezone',
        'title',
        'titleLink',
        'redirector',
        'disablesessionprotection',
        'privateLinkByDefault',
    ];

    /**
     * Map legacy config keys with the new ones.
     * If ConfigPhp is used, getting <newkey> will actually look for <legacykey>.
     * The updater will use this array to transform keys when switching to JSON.
     *
     * @var array current key => legacy key.
     */
    public static $LEGACY_KEYS_MAPPING = [
        'credentials.login' => 'login',
        'credentials.hash' => 'hash',
        'credentials.salt' => 'salt',
        'resource.data_dir' => 'config.DATADIR',
        'resource.config' => 'config.CONFIG_FILE',
        'resource.datastore' => 'config.DATASTORE',
        'resource.updates' => 'config.UPDATES_FILE',
        'resource.log' => 'config.LOG_FILE',
        'resource.update_check' => 'config.UPDATECHECK_FILENAME',
        'resource.raintpl_tpl' => 'config.RAINTPL_TPL',
        'resource.theme' => 'config.theme',
        'resource.raintpl_tmp' => 'config.RAINTPL_TMP',
        'resource.thumbnails_cache' => 'config.CACHEDIR',
        'resource.page_cache' => 'config.PAGECACHE',
        'resource.ban_file' => 'config.IPBANS_FILENAME',
        'security.session_protection_disabled' => 'disablesessionprotection',
        'security.ban_after' => 'config.BAN_AFTER',
        'security.ban_duration' => 'config.BAN_DURATION',
        'general.title' => 'title',
        'general.timezone' => 'timezone',
        'general.header_link' => 'titleLink',
        'updates.check_updates' => 'config.ENABLE_UPDATECHECK',
        'updates.check_updates_branch' => 'config.UPDATECHECK_BRANCH',
        'updates.check_updates_interval' => 'config.UPDATECHECK_INTERVAL',
        'privacy.default_private_links' => 'privateLinkByDefault',
        'feed.rss_permalinks' => 'config.ENABLE_RSS_PERMALINKS',
        'general.links_per_page' => 'config.LINKS_PER_PAGE',
        'thumbnail.enable_thumbnails' => 'config.ENABLE_THUMBNAILS',
        'thumbnail.enable_localcache' => 'config.ENABLE_LOCALCACHE',
        'general.enabled_plugins' => 'config.ENABLED_PLUGINS',
        'redirector.url' => 'redirector',
        'redirector.encode_url' => 'config.REDIRECTOR_URLENCODE',
        'feed.show_atom' => 'config.SHOW_ATOM',
        'privacy.hide_public_links' => 'config.HIDE_PUBLIC_LINKS',
        'privacy.hide_timestamps' => 'config.HIDE_TIMESTAMPS',
        'security.open_shaarli' => 'config.OPEN_SHAARLI',
    ];

    /**
     * @inheritdoc
     */
    public function read($filepath)
    {
        if (! file_exists($filepath) || ! is_readable($filepath)) {
            return [];
        }

        include $filepath;

        $out = [];
        foreach (self::$ROOT_KEYS as $key) {
            $out[$key] = isset($GLOBALS[$key]) ? $GLOBALS[$key] : '';
        }
        $out['config'] = isset($GLOBALS['config']) ? $GLOBALS['config'] : [];
        $out['plugins'] = isset($GLOBALS['plugins']) ? $GLOBALS['plugins'] : [];
        return $out;
    }

    /**
     * @inheritdoc
     */
    public function write($filepath, $conf)
    {
        $configStr = '<?php ' . PHP_EOL;
        foreach (self::$ROOT_KEYS as $key) {
            if (isset($conf[$key])) {
                $configStr .= '$GLOBALS[\'' . $key . '\'] = ' . var_export($conf[$key], true) . ';' . PHP_EOL;
            }
        }

        // Store all $conf['config']
        foreach ($conf['config'] as $key => $value) {
            $configStr .= '$GLOBALS[\'config\'][\''
                . $key
                . '\'] = '
                . var_export($conf['config'][$key], true) . ';'
                . PHP_EOL;
        }

        if (isset($conf['plugins'])) {
            foreach ($conf['plugins'] as $key => $value) {
                $configStr .= '$GLOBALS[\'plugins\'][\''
                    . $key
                    . '\'] = '
                    . var_export($conf['plugins'][$key], true) . ';'
                    . PHP_EOL;
            }
        }

        if (
            !file_put_contents($filepath, $configStr)
            || strcmp(file_get_contents($filepath), $configStr) != 0
        ) {
            throw new \Shaarli\Exceptions\IOException(
                $filepath,
                t('Shaarli could not create the config file. ' .
                  'Please make sure Shaarli has the right to write in the folder is it installed in.')
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getExtension()
    {
        return '.php';
    }
}
