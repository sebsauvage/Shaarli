<?php

namespace Shaarli\Updater\Exception;

use Exception;

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
        if (!empty($message)) {
            $out .= $message . PHP_EOL;
        }

        if (!empty($this->method)) {
            $out .= t('An error occurred while running the update ') . $this->method . PHP_EOL;
        }

        if (!empty($this->previous)) {
            $out .= '  ' . $this->previous->getMessage();
        }

        return $out;
    }
}
