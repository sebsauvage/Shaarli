<?php

namespace Shaarli\Formatter;

use Shaarli\Config\ConfigManager;

/**
 * Class FormatterFactory
 *
 * Helper class used to instantiate the proper BookmarkFormatter.
 *
 * @package Shaarli\Formatter
 */
class FormatterFactory
{
    /** @var ConfigManager instance */
    protected $conf;

    /**
     * FormatterFactory constructor.
     *
     * @param ConfigManager $conf
     */
    public function __construct(ConfigManager $conf)
    {
        $this->conf = $conf;
    }

    /**
     * Instanciate a BookmarkFormatter depending on the configuration or provided formatter type.
     *
     * @param string|null $type force a specific type regardless of the configuration
     *
     * @return BookmarkFormatter instance.
     */
    public function getFormatter($type = null)
    {
        $type = $type ? $type : $this->conf->get('formatter', 'default');
        $className = '\\Shaarli\\Formatter\\Bookmark'. ucfirst($type) .'Formatter';
        if (!class_exists($className)) {
            $className = '\\Shaarli\\Formatter\\BookmarkDefaultFormatter';
        }

        return new $className($this->conf);
    }
}
