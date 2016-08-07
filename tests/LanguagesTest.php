<?php

require_once 'application/Languages.php';

/**
 * Class LanguagesTest.
 */
class LanguagesTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test t() with a simple non identified value.
     */
    public function testTranslateSingleNotID()
    {
        $text = 'abcdé 564 fgK';
        $this->assertEquals($text, t($text));
    }

    /**
     * Test t() with a non identified plural form.
     */
    public function testTranslatePluralNotID()
    {
        $text = '%s sandwich';
        $nText = '%s sandwiches';
        $this->assertEquals('0 sandwich', t($text, $nText));
        $this->assertEquals('1 sandwich', t($text, $nText, 1));
        $this->assertEquals('2 sandwiches', t($text, $nText, 2));
    }

    /**
     * Test t() with a non identified invalid plural form.
     */
    public function testTranslatePluralNotIDInvalid()
    {
        $text = 'sandwich';
        $nText = 'sandwiches';
        $this->assertEquals('sandwich', t($text, $nText, 1));
        $this->assertEquals('sandwiches', t($text, $nText, 2));
    }
}
