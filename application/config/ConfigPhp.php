<?php

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
    public static $ROOT_KEYS = array(
        'login',
        'hash',
        'salt',
        'timezone',
        'title',
        'titleLink',
        'redirector',
        'disablesessionprotection',
        'privateLinkByDefault',
    );

    /**
     * @inheritdoc
     */
    function read($filepath)
    {
        $filepath .= $this->getExtension();
        if (! file_exists($filepath) || ! is_readable($filepath)) {
            return array();
        }

        include $filepath;

        $out = array();
        foreach (self::$ROOT_KEYS as $key) {
            $out[$key] = $GLOBALS[$key];
        }
        $out['config'] = $GLOBALS['config'];
        $out['plugins'] = !empty($GLOBALS['plugins']) ? $GLOBALS['plugins'] : array();
        return $out;
    }

    /**
     * @inheritdoc
     */
    function write($filepath, $conf)
    {
        $filepath .= $this->getExtension();

        $configStr = '<?php '. PHP_EOL;
        foreach (self::$ROOT_KEYS as $key) {
            if (isset($conf[$key])) {
                $configStr .= '$GLOBALS[\'' . $key . '\'] = ' . var_export($conf[$key], true) . ';' . PHP_EOL;
            }
        }
        
        // Store all $conf['config']
        foreach ($conf['config'] as $key => $value) {
            $configStr .= '$GLOBALS[\'config\'][\''. $key .'\'] = '.var_export($conf['config'][$key], true).';'. PHP_EOL;
        }

        if (isset($conf['plugins'])) {
            foreach ($conf['plugins'] as $key => $value) {
                $configStr .= '$GLOBALS[\'plugins\'][\''. $key .'\'] = '.var_export($conf['plugins'][$key], true).';'. PHP_EOL;
            }
        }

        // FIXME!
        //$configStr .= 'date_default_timezone_set('.var_export($conf['timezone'], true).');'. PHP_EOL;

        if (!file_put_contents($filepath, $configStr)
            || strcmp(file_get_contents($filepath), $configStr) != 0
        ) {
            throw new IOException(
                $filepath,
                'Shaarli could not create the config file.
                Please make sure Shaarli has the right to write in the folder is it installed in.'
            );
        }
    }

    /**
     * @inheritdoc
     */
    function getExtension()
    {
        return '.php';
    }
}
