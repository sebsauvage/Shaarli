<?php

namespace Shaarli\Render;

/**
 * Class ThemeUtilsTest
 *
 * @package Shaarli
 */
class ThemeUtilsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test getThemes() with existing theme directories.
     */
    public function testGetThemes()
    {
        $themes = ['theme1', 'default', 'Bl1p_- bL0p'];
        foreach ($themes as $theme) {
            mkdir('sandbox/tpl/'. $theme, 0755, true);
        }

        // include a file which should be ignored
        touch('sandbox/tpl/supertheme');

        $res = ThemeUtils::getThemes('sandbox/tpl/');
        foreach ($res as $theme) {
            $this->assertTrue(in_array($theme, $themes));
        }
        $this->assertFalse(in_array('supertheme', $res));

        foreach ($themes as $theme) {
            rmdir('sandbox/tpl/'. $theme);
        }
        unlink('sandbox/tpl/supertheme');
        rmdir('sandbox/tpl');
    }

    /**
     * Test getThemes() without any theme dir.
     */
    public function testGetThemesEmpty()
    {
        mkdir('sandbox/tpl/', 0755, true);
        $this->assertEquals([], ThemeUtils::getThemes('sandbox/tpl/'));
        rmdir('sandbox/tpl/');
    }

    /**
     * Test getThemes() with an invalid path.
     */
    public function testGetThemesInvalid()
    {
        $this->assertEquals([], ThemeUtils::getThemes('nope'));
    }
}
