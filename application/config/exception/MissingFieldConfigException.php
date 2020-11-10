<?php

namespace Shaarli\Config\Exception;

/**
 * Exception used if a mandatory field is missing in given configuration.
 */
class MissingFieldConfigException extends \Exception
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
        $this->message = sprintf(t('Configuration value is required for %s'), $this->field);
    }
}
