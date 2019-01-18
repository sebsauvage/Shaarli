<?php

namespace Shaarli;

/**
 * Fake ApplicationUtils class to avoid HTTP requests
 */
class FakeApplicationUtils extends ApplicationUtils
{
    public static $VERSION_CODE = '';

    /**
     * Toggle HTTP requests, allow overriding the version code
     */
    public static function getVersion($url, $timeout = 0)
    {
        return self::$VERSION_CODE;
    }
}
