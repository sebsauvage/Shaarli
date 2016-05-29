<?php

/**
 * Class ConfigJson (ConfigIO implementation)
 *
 * Handle Shaarli's JSON configuration file.
 */
class ConfigJson implements ConfigIO
{
    /**
     * The JSON data is wrapped in a PHP file for security purpose.
     * This way, even if the file is accessible, credentials and configuration won't be exposed.
     *
     * @var string PHP start tag and comment tag.
     */
    public static $PHP_HEADER;

    public function __construct()
    {
        // The field can't be initialized directly with concatenation before PHP 5.6.
        self::$PHP_HEADER = '<?php /*'. PHP_EOL;
    }

    /**
     * @inheritdoc
     */
    function read($filepath)
    {
        if (! file_exists($filepath) || ! is_readable($filepath)) {
            return array();
        }
        $data = file_get_contents($filepath);
        $data = str_replace(self::$PHP_HEADER, '', $data);
        $data = json_decode($data, true);
        if ($data === null) {
            $error = json_last_error();
            throw new Exception('An error occured while parsing JSON file: error code #'. $error);
        }
        return $data;
    }

    /**
     * @inheritdoc
     */
    function write($filepath, $conf)
    {
        // JSON_PRETTY_PRINT is available from PHP 5.4.
        $print = defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0;
        $data = self::$PHP_HEADER . json_encode($conf, $print);
        if (!file_put_contents($filepath, $data)) {
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
        return '.json.php';
    }
}
