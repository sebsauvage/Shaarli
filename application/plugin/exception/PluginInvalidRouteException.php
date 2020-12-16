<?php

declare(strict_types=1);

namespace Shaarli\Plugin\Exception;

use Exception;

/**
 * Class PluginFileNotFoundException
 *
 * Raise when plugin files can't be found.
 */
class PluginInvalidRouteException extends Exception
{
    /**
     * Construct exception with plugin name.
     * Generate message.
     *
     * @param string $pluginName name of the plugin not found
     */
    public function __construct()
    {
        $this->message = 'trying to register invalid route.';
    }
}
