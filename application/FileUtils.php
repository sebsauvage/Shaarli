<?php
/**
 * Exception class thrown when a filesystem access failure happens
 */
class IOException extends Exception
{
    private $path;

    /**
     * Construct a new IOException
     *
     * @param string $path path to the ressource that cannot be accessed
     */
    public function __construct($path)
    {
        $this->path = $path;
        $this->message = 'Error accessing '.$this->path;
    }
}
