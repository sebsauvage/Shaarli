<?php

// FIXME! Namespaces...
require_once 'ConfigIO.php';
require_once 'ConfigPhp.php';
require_once 'ConfigJson.php';

/**
 * Class ConfigManager
 *
 * Singleton, manages all Shaarli's settings.
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
            'login', 'hash', 'salt', 'timezone', 'title', 'titleLink',
            'redirector', 'disablesessionprotection', 'privateLinkByDefault'
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
        // Data subdirectory
        $this->setEmpty('config.DATADIR', 'data');

        // Main configuration file
        $this->setEmpty('config.CONFIG_FILE', 'data/config.php');

        // Link datastore
        $this->setEmpty('config.DATASTORE', 'data/datastore.php');

        // Banned IPs
        $this->setEmpty('config.IPBANS_FILENAME', 'data/ipbans.php');

        // Processed updates file.
        $this->setEmpty('config.UPDATES_FILE', 'data/updates.txt');

        // Access log
        $this->setEmpty('config.LOG_FILE', 'data/log.txt');

        // For updates check of Shaarli
        $this->setEmpty('config.UPDATECHECK_FILENAME', 'data/lastupdatecheck.txt');

        // Set ENABLE_UPDATECHECK to disabled by default.
        $this->setEmpty('config.ENABLE_UPDATECHECK', false);

        // RainTPL cache directory (keep the trailing slash!)
        $this->setEmpty('config.RAINTPL_TMP', 'tmp/');
        // Raintpl template directory (keep the trailing slash!)
        $this->setEmpty('config.RAINTPL_TPL', 'tpl/');

        // Thumbnail cache directory
        $this->setEmpty('config.CACHEDIR', 'cache');

        // Atom & RSS feed cache directory
        $this->setEmpty('config.PAGECACHE', 'pagecache');

        // Ban IP after this many failures
        $this->setEmpty('config.BAN_AFTER', 4);
        // Ban duration for IP address after login failures (in seconds)
        $this->setEmpty('config.BAN_DURATION', 1800);

        // Feed options
        // Enable RSS permalinks by default.
        // This corresponds to the default behavior of shaarli before this was added as an option.
        $this->setEmpty('config.ENABLE_RSS_PERMALINKS', true);
        // If true, an extra "ATOM feed" button will be displayed in the toolbar
        $this->setEmpty('config.SHOW_ATOM', false);

        // Link display options
        $this->setEmpty('config.HIDE_PUBLIC_LINKS', false);
        $this->setEmpty('config.HIDE_TIMESTAMPS', false);
        $this->setEmpty('config.LINKS_PER_PAGE', 20);

        // Open Shaarli (true): anyone can add/edit/delete links without having to login
        $this->setEmpty('config.OPEN_SHAARLI', false);

        // Thumbnails
        // Display thumbnails in links
        $this->setEmpty('config.ENABLE_THUMBNAILS', true);
        // Store thumbnails in a local cache
        $this->setEmpty('config.ENABLE_LOCALCACHE', true);

        // Update check frequency for Shaarli. 86400 seconds=24 hours
        $this->setEmpty('config.UPDATECHECK_BRANCH', 'stable');
        $this->setEmpty('config.UPDATECHECK_INTERVAL', 86400);

        $this->setEmpty('redirector', '');
        $this->setEmpty('config.REDIRECTOR_URLENCODE', true);

        // Enabled plugins.
        $this->setEmpty('config.ENABLED_PLUGINS', array('qrcode'));

        // Initialize plugin parameters array.
        $this->setEmpty('plugins', array());
    }

    /**
     * Set only if the setting does not exists.
     *
     * @param string $key   Setting key.
     * @param mixed  $value Setting value.
     */
    protected function setEmpty($key, $value)
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
