<?php

namespace Shaarli\Config\Exception;

/**
 * Exception used if an error occur while saving plugin configuration.
 */
class PluginConfigOrderException extends \Exception
{
    /**
     * Construct exception.
     */
    public function __construct()
    {
        $this->message = t('An error occurred while trying to save plugins loading order.');
    }
}
