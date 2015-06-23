<?php
/**
 * Utilities' tests
 */

require_once 'application/Utils.php';

/**
 * Unitary tests for Shaarli utilities
 */
class UtilsTest extends PHPUnit_Framework_TestCase
{
    /**
     * Represent a link by its hash
     */
    public function testSmallHash()
    {
        $this->assertEquals('CyAAJw', smallHash('http://test.io'));
        $this->assertEquals(6, strlen(smallHash('https://github.com')));
    }

    /**
     * Look for a substring at the beginning of a string
     */
    public function testStartsWithCaseInsensitive()
    {
        $this->assertTrue(startsWith('Lorem ipsum', 'lorem', false));
        $this->assertTrue(startsWith('Lorem ipsum', 'LoReM i', false));
    }

    /**
     * Look for a substring at the beginning of a string (case-sensitive)
     */
    public function testStartsWithCaseSensitive()
    {
        $this->assertTrue(startsWith('Lorem ipsum', 'Lorem', true));
        $this->assertFalse(startsWith('Lorem ipsum', 'lorem', true));
        $this->assertFalse(startsWith('Lorem ipsum', 'LoReM i', true));
    }

    /**
     * Look for a substring at the beginning of a string (Unicode)
     */
    public function testStartsWithSpecialChars()
    {
        $this->assertTrue(startsWith('å!ùµ', 'å!', false));
        $this->assertTrue(startsWith('µ$åù', 'µ$', true));
    }

    /**
     * Look for a substring at the end of a string
     */
    public function testEndsWithCaseInsensitive()
    {
        $this->assertTrue(endsWith('Lorem ipsum', 'ipsum', false));
        $this->assertTrue(endsWith('Lorem ipsum', 'm IpsUM', false));
    }

    /**
     * Look for a substring at the end of a string (case-sensitive)
     */
    public function testEndsWithCaseSensitive()
    {
        $this->assertTrue(endsWith('lorem Ipsum', 'Ipsum', true));
        $this->assertFalse(endsWith('lorem Ipsum', 'ipsum', true));
        $this->assertFalse(endsWith('lorem Ipsum', 'M IPsuM', true));
    }

    /**
     * Look for a substring at the end of a string (Unicode)
     */
    public function testEndsWithSpecialChars()
    {
        $this->assertTrue(endsWith('å!ùµ', 'ùµ', false));
        $this->assertTrue(endsWith('µ$åù', 'åù', true));
    }
}
?>
