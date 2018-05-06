<?php

/**
 * Fake ConfigManager
 */
class FakeConfigManager
{
    protected $values = [];

    /**
     * Initialize with test values
     *
     * @param array $values Initial values
     */
    public function __construct($values = [])
    {
        $this->values = $values;
    }

    /**
     * Set a given value
     *
     * @param string $key   Key of the value to set
     * @param mixed  $value Value to set
     */
    public function set($key, $value)
    {
        $this->values[$key] = $value;
    }

    /**
     * Get a given configuration value
     *
     * @param string $key Index of the value to retrieve
     *
     * @return mixed The value if set, else the name of the key
     */
    public function get($key)
    {
        if (isset($this->values[$key])) {
            return $this->values[$key];
        }
        return $key;
    }

    /**
     * Check if a setting exists
     *
     * @param string $setting Asked setting, keys separated with dots
     *
     * @return bool true if the setting exists, false otherwise
     */
    public function exists($setting)
    {
        return array_key_exists($setting, $this->values);
    }
}
