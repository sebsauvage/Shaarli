<?php

namespace Shaarli\Render;

/**
 * Class ThemeUtils
 *
 * Utility functions related to theme management.
 *
 * @package Shaarli
 */
class ThemeUtils
{
    /**
     * Get a list of available themes.
     *
     * It will return the name of any directory present in the template folder.
     *
     * @param string $tplDir Templates main directory.
     *
     * @return array List of theme names.
     */
    public static function getThemes($tplDir)
    {
        $tplDir = rtrim($tplDir, '/');
        $allTheme = glob($tplDir . '/*', GLOB_ONLYDIR);
        $themes = [];
        foreach ($allTheme as $value) {
            $themes[] = str_replace($tplDir . '/', '', $value);
        }

        return $themes;
    }
}
