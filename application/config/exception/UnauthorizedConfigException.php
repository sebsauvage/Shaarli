<?php

namespace Shaarli\Config\Exception;

/**
 * Exception used if an unauthorized attempt to edit configuration has been made.
 */
class UnauthorizedConfigException extends \Exception
{
    /**
     * Construct exception.
     */
    public function __construct()
    {
        $this->message = t('You are not authorized to alter config.');
    }
}
