<?php

/**
 * Fake ConfigManager
 */
class FakeConfigManager
{
    public static function get($key)
    {
        return $key;
    }
}
