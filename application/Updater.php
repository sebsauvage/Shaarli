<?php

/**
 * Class Updater.
 * Used to update stuff when a new Shaarli's version is reached.
 * Update methods are ran only once, and the stored in a JSON file.
 */
class Updater
{
    /**
     * @var array Updates which are already done.
     */
    protected $doneUpdates;

    /**
     * @var array Shaarli's configuration array.
     */
    protected $config;

    /**
     * @var LinkDB instance.
     */
    protected $linkDB;

    /**
     * @var bool True if the user is logged in, false otherwise.
     */
    protected $isLoggedIn;

    /**
     * @var ReflectionMethod[] List of current class methods.
     */
    protected $methods;

    /**
     * Object constructor.
     *
     * @param array   $doneUpdates Updates which are already done.
     * @param array   $config      Shaarli's configuration array.
     * @param LinkDB  $linkDB      LinkDB instance.
     * @param boolean $isLoggedIn  True if the user is logged in.
     */
    public function __construct($doneUpdates, $config, $linkDB, $isLoggedIn)
    {
        $this->doneUpdates = $doneUpdates;
        $this->config = $config;
        $this->linkDB = $linkDB;
        $this->isLoggedIn = $isLoggedIn;

        // Retrieve all update methods.
        $class = new ReflectionClass($this);
        $this->methods = $class->getMethods();
    }

    /**
     * Run all new updates.
     * Update methods have to start with 'updateMethod' and return true (on success).
     *
     * @return array An array containing ran updates.
     *
     * @throws UpdaterException If something went wrong.
     */
    public function update()
    {
        $updatesRan = array();

        // If the user isn't logged in, exit without updating.
        if ($this->isLoggedIn !== true) {
            return $updatesRan;
        }

        if ($this->methods == null) {
            throw new UpdaterException('Couldn\'t retrieve Updater class methods.');
        }

        foreach ($this->methods as $method) {
            // Not an update method or already done, pass.
            if (! startsWith($method->getName(), 'updateMethod')
                || in_array($method->getName(), $this->doneUpdates)
            ) {
                continue;
            }

            try {
                $method->setAccessible(true);
                $res = $method->invoke($this);
                // Update method must return true to be considered processed.
                if ($res === true) {
                    $updatesRan[] = $method->getName();
                }
            } catch (Exception $e) {
                throw new UpdaterException($method, $e);
            }
        }

        $this->doneUpdates = array_merge($this->doneUpdates, $updatesRan);

        return $updatesRan;
    }

    /**
     * @return array Updates methods already processed.
     */
    public function getDoneUpdates()
    {
        return $this->doneUpdates;
    }

    /**
     * Move deprecated options.php to config.php.
     *
     * Milestone 0.9 (old versioning) - shaarli/Shaarli#41:
     *    options.php is not supported anymore.
     */
    public function updateMethodMergeDeprecatedConfigFile()
    {
        $config_file = $this->config['config']['CONFIG_FILE'];

        if (is_file($this->config['config']['DATADIR'].'/options.php')) {
            include $this->config['config']['DATADIR'].'/options.php';

            // Load GLOBALS into config
            foreach ($GLOBALS as $key => $value) {
                $this->config[$key] = $value;
            }
            $this->config['config']['CONFIG_FILE'] = $config_file;
            writeConfig($this->config, $this->isLoggedIn);

            unlink($this->config['config']['DATADIR'].'/options.php');
        }

        return true;
    }

    /**
     * Rename tags starting with a '-' to work with tag exclusion search.
     */
    public function updateMethodRenameDashTags()
    {
        $linklist = $this->linkDB->filterSearch();
        foreach ($linklist as $link) {
            $link['tags'] = preg_replace('/(^| )\-/', '$1', $link['tags']);
            $link['tags'] = implode(' ', array_unique(LinkFilter::tagsStrToArray($link['tags'], true)));
            $this->linkDB[$link['linkdate']] = $link;
        }
        $this->linkDB->savedb($this->config['config']['PAGECACHE']);
        return true;
    }
}

/**
 * Class UpdaterException.
 */
class UpdaterException extends Exception
{
    /**
     * @var string Method where the error occurred.
     */
    protected $method;

    /**
     * @var Exception The parent exception.
     */
    protected $previous;

    /**
     * Constructor.
     *
     * @param string         $message  Force the error message if set.
     * @param string         $method   Method where the error occurred.
     * @param Exception|bool $previous Parent exception.
     */
    public function __construct($message = '', $method = '', $previous = false)
    {
        $this->method = $method;
        $this->previous = $previous;
        $this->message = $this->buildMessage($message);
    }

    /**
     * Build the exception error message.
     *
     * @param string $message Optional given error message.
     *
     * @return string The built error message.
     */
    private function buildMessage($message)
    {
        $out = '';
        if (! empty($message)) {
            $out .= $message . PHP_EOL;
        }

        if (! empty($this->method)) {
            $out .= 'An error occurred while running the update '. $this->method . PHP_EOL;
        }

        if (! empty($this->previous)) {
            $out .= '  '. $this->previous->getMessage();
        }

        return $out;
    }
}


/**
 * Read the updates file, and return already done updates.
 *
 * @param string $updatesFilepath Updates file path.
 *
 * @return array Already done update methods.
 */
function read_updates_file($updatesFilepath)
{
    if (! empty($updatesFilepath) && is_file($updatesFilepath)) {
        $content = file_get_contents($updatesFilepath);
        if (! empty($content)) {
            return explode(';', $content);
        }
    }
    return array();
}

/**
 * Write updates file.
 *
 * @param string $updatesFilepath Updates file path.
 * @param array  $updates         Updates array to write.
 *
 * @throws Exception Couldn't write version number.
 */
function write_updates_file($updatesFilepath, $updates)
{
    if (empty($updatesFilepath)) {
        throw new Exception('Updates file path is not set, can\'t write updates.');
    }

    $res = file_put_contents($updatesFilepath, implode(';', $updates));
    if ($res === false) {
        throw new Exception('Unable to write updates in '. $updatesFilepath . '.');
    }
}
