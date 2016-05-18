<?php

/**
 * Interface ConfigIO
 *
 * This describes how Config types should store their configuration.
 */
interface ConfigIO
{
    /**
     * Read configuration.
     *
     * @param string $filepath Config file absolute path.
     *
     * @return array All configuration in an array.
     */
    function read($filepath);

    /**
     * Write configuration.
     *
     * @param string $filepath Config file absolute path.
     * @param array  $conf   All configuration in an array.
     */
    function write($filepath, $conf);

    /**
     * Get config file extension according to config type.
     *
     * @return string Config file extension.
     */
    function getExtension();
}
