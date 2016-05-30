<?php

// FIXME! Namespaces...
require_once 'ConfigIO.php';
require_once 'ConfigPhp.php';
require_once 'ConfigJson.php';

/**
 * Class ConfigManager
 *
 * Singleton, manages all Shaarli's settings.
 * See the documentation for more information on settings:
 *   - doc/Shaarli-configuration.html
 *   - https://github.com/shaarli/Shaarli/wiki/Shaarli-configuration
 */
class ConfigManager
{
    /**
     * @var ConfigManager instance.
     */
    protected static $instance = null;

    /**
     * @var string Config folder.
     */
    public static $CONFIG_FILE = 'data/config';

    /**
     * @var string Flag telling a setting is not found.
     */
    protected static $NOT_FOUND = 'NOT_FOUND';

    /**
     * @var array Loaded config array.
     */
    protected $loadedConfig;

    /**
     * @var ConfigIO implementation instance.
     */
    protected $configIO;

    /**
     * Private constructor: new instances not allowed.
     */
    private function __construct() {}

    /**
     * Cloning isn't allowed either.
     */
    private function __clone() {}

    /**
     * Return existing instance of PluginManager, or create it.
     *
     * @return ConfigManager instance.
     */
    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
            self::$instance->initialize();
        }

        return self::$instance;
    }

    /**
     * Reset the ConfigManager instance.
     */
    public static function reset()
    {
        self::$instance = null;
        return self::getInstance();
    }

    /**
     * Rebuild the loaded config array from config files.
     */
    public function reload()
    {
        $this->load();
    }

    /**
     * Initialize the ConfigIO and loaded the conf.
     */
    protected function initialize()
    {
        if (! file_exists(self::$CONFIG_FILE .'.php')) {
            $this->configIO = new ConfigJson();
        } else {
            $this->configIO = new ConfigPhp();
        }
        $this->load();
    }

    /**
     * Load configuration in the ConfigurationManager.
     */
    protected function load()
    {
        $this->loadedConfig = $this->configIO->read($this->getConfigFile());
        $this->setDefaultValues();
    }

    /**
     * Get a setting.
     *
     * Supports nested settings with dot separated keys.
     * Eg. 'config.stuff.option' will find $conf[config][stuff][option],
     * or in JSON:
     *   { "config": { "stuff": {"option": "mysetting" } } } }
     *
     * @param string $setting Asked setting, keys separated with dots.
     * @param string $default Default value if not found.
     *
     * @return mixed Found setting, or the default value.
     */
    public function get($setting, $default = '')
    {
        // During the ConfigIO transition, map legacy settings to the new ones.
        if ($this->configIO instanceof ConfigPhp && isset(ConfigPhp::$LEGACY_KEYS_MAPPING[$setting])) {
            $setting = ConfigPhp::$LEGACY_KEYS_MAPPING[$setting];
        }

        $settings = explode('.', $setting);
        $value = self::getConfig($settings, $this->loadedConfig);
        if ($value === self::$NOT_FOUND) {
            return $default;
        }
        return $value;
    }

    /**
     * Set a setting, and eventually write it.
     *
     * Supports nested settings with dot separated keys.
     *
     * @param string $setting    Asked setting, keys separated with dots.
     * @param string $value      Value to set.
     * @param bool   $write      Write the new setting in the config file, default false.
     * @param bool   $isLoggedIn User login state, default false.
     *
     * @throws Exception Invalid
     */
    public function set($setting, $value, $write = false, $isLoggedIn = false)
    {
        if (empty($setting) || ! is_string($setting)) {
            throw new Exception('Invalid setting key parameter. String expected, got: '. gettype($setting));
        }

        // During the ConfigIO transition, map legacy settings to the new ones.
        if ($this->configIO instanceof ConfigPhp && isset(ConfigPhp::$LEGACY_KEYS_MAPPING[$setting])) {
            $setting = ConfigPhp::$LEGACY_KEYS_MAPPING[$setting];
        }

        $settings = explode('.', $setting);
        self::setConfig($settings, $value, $this->loadedConfig);
        if ($write) {
            $this->write($isLoggedIn);
        }
    }

    /**
     * Check if a settings exists.
     *
     * Supports nested settings with dot separated keys.
     *
     * @param string $setting    Asked setting, keys separated with dots.
     *
     * @return bool true if the setting exists, false otherwise.
     */
    public function exists($setting)
    {
        // During the ConfigIO transition, map legacy settings to the new ones.
        if ($this->configIO instanceof ConfigPhp && isset(ConfigPhp::$LEGACY_KEYS_MAPPING[$setting])) {
            $setting = ConfigPhp::$LEGACY_KEYS_MAPPING[$setting];
        }

        $settings = explode('.', $setting);
        $value = self::getConfig($settings, $this->loadedConfig);
        if ($value === self::$NOT_FOUND) {
            return false;
        }
        return true;
    }

    /**
     * Call the config writer.
     *
     * @param bool $isLoggedIn User login state.
     *
     * @return bool True if the configuration has been successfully written, false otherwise.
     *
     * @throws MissingFieldConfigException: a mandatory field has not been provided in $conf.
     * @throws UnauthorizedConfigException: user is not authorize to change configuration.
     * @throws IOException: an error occurred while writing the new config file.
     */
    public function write($isLoggedIn)
    {
        // These fields are required in configuration.
        $mandatoryFields = array(
            'credentials.login',
            'credentials.hash',
            'credentials.salt',
            'security.session_protection_disabled',
            'general.timezone',
            'general.title',
            'general.header_link',
            'general.default_private_links',
            'extras.redirector',
        );

        // Only logged in user can alter config.
        if (is_file(self::$CONFIG_FILE) && !$isLoggedIn) {
            throw new UnauthorizedConfigException();
        }

        // Check that all mandatory fields are provided in $conf.
        foreach ($mandatoryFields as $field) {
            if (! $this->exists($field)) {
                throw new MissingFieldConfigException($field);
            }
        }

        return $this->configIO->write($this->getConfigFile(), $this->loadedConfig);
    }

    /**
     * Get the configuration file path.
     *
     * @return string Config file path.
     */
    public function getConfigFile()
    {
        return self::$CONFIG_FILE . $this->configIO->getExtension();
    }

    /**
     * Recursive function which find asked setting in the loaded config.
     *
     * @param array $settings Ordered array which contains keys to find.
     * @param array $conf   Loaded settings, then sub-array.
     *
     * @return mixed Found setting or NOT_FOUND flag.
     */
    protected static function getConfig($settings, $conf)
    {
        if (!is_array($settings) || count($settings) == 0) {
            return self::$NOT_FOUND;
        }

        $setting = array_shift($settings);
        if (!isset($conf[$setting])) {
            return self::$NOT_FOUND;
        }

        if (count($settings) > 0) {
            return self::getConfig($settings, $conf[$setting]);
        }
        return $conf[$setting];
    }

    /**
     * Recursive function which find asked setting in the loaded config.
     *
     * @param array $settings Ordered array which contains keys to find.
     * @param mixed $value
     * @param array $conf   Loaded settings, then sub-array.
     *
     * @return mixed Found setting or NOT_FOUND flag.
     */
    protected static function setConfig($settings, $value, &$conf)
    {
        if (!is_array($settings) || count($settings) == 0) {
            return self::$NOT_FOUND;
        }

        $setting = array_shift($settings);
        if (count($settings) > 0) {
            return self::setConfig($settings, $value, $conf[$setting]);
        }
        $conf[$setting] = $value;
    }

    /**
     * Set a bunch of default values allowing Shaarli to start without a config file.
     */
    protected function setDefaultValues()
    {
        $this->setEmpty('path.data_dir', 'data');
        $this->setEmpty('path.config', 'data/config.php');
        $this->setEmpty('path.datastore', 'data/datastore.php');
        $this->setEmpty('path.ban_file', 'data/ipbans.php');
        $this->setEmpty('path.updates', 'data/updates.txt');
        $this->setEmpty('path.log', 'data/log.txt');
        $this->setEmpty('path.update_check', 'data/lastupdatecheck.txt');
        $this->setEmpty('path.raintpl_tpl', 'tpl/');
        $this->setEmpty('path.raintpl_tmp', 'tmp/');
        $this->setEmpty('path.thumbnails_cache', 'cache');
        $this->setEmpty('path.page_cache', 'pagecache');

        $this->setEmpty('security.ban_after', 4);
        $this->setEmpty('security.ban_after', 1800);
        $this->setEmpty('security.session_protection_disabled', false);

        $this->setEmpty('general.check_updates', false);
        $this->setEmpty('general.rss_permalinks', true);
        $this->setEmpty('general.links_per_page', 20);
        $this->setEmpty('general.default_private_links', false);
        $this->setEmpty('general.enable_thumbnails', true);
        $this->setEmpty('general.enable_localcache', true);
        $this->setEmpty('general.check_updates_branch', 'stable');
        $this->setEmpty('general.check_updates_interval', 86400);
        $this->setEmpty('general.header_link', '?');
        $this->setEmpty('general.enabled_plugins', array('qrcode'));

        $this->setEmpty('extras.show_atom', false);
        $this->setEmpty('extras.hide_public_links', false);
        $this->setEmpty('extras.hide_timestamps', false);
        $this->setEmpty('extras.open_shaarli', false);
        $this->setEmpty('extras.redirector', '');
        $this->setEmpty('extras.redirector_encode_url', true);

        $this->setEmpty('plugins', array());
    }

    /**
     * Set only if the setting does not exists.
     *
     * @param string $key   Setting key.
     * @param mixed  $value Setting value.
     */
    public function setEmpty($key, $value)
    {
        if (! $this->exists($key)) {
            $this->set($key, $value);
        }
    }

    /**
     * @return ConfigIO
     */
    public function getConfigIO()
    {
        return $this->configIO;
    }

    /**
     * @param ConfigIO $configIO
     */
    public function setConfigIO($configIO)
    {
        $this->configIO = $configIO;
    }
}

/**
 * Exception used if a mandatory field is missing in given configuration.
 */
class MissingFieldConfigException extends Exception
{
    public $field;

    /**
     * Construct exception.
     *
     * @param string $field field name missing.
     */
    public function __construct($field)
    {
        $this->field = $field;
        $this->message = 'Configuration value is required for '. $this->field;
    }
}

/**
 * Exception used if an unauthorized attempt to edit configuration has been made.
 */
class UnauthorizedConfigException extends Exception
{
    /**
     * Construct exception.
     */
    public function __construct()
    {
        $this->message = 'You are not authorized to alter config.';
    }
}
