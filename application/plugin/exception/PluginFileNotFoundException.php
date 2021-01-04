<?php

namespace Shaarli\Plugin\Exception;

use Exception;

/**
 * Class PluginFileNotFoundException
 *
 * Raise when plugin files can't be found.
 */
class PluginFileNotFoundException extends Exception
{
    /**
     * Construct exception with plugin name.
     * Generate message.
     *
     * @param string $pluginName name of the plugin not found
     */
    public function __construct($pluginName)
    {
        $this->message = sprintf(t('Plugin "%s" files not found.'), $pluginName);
    }
}
