<?php

require_once 'application/LinkUtils.php';

/**
* Class LinkUtilsTest.
*/
class LinkUtilsTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test html_extract_title() when the title is found.
     */
    public function testHtmlExtractExistentTitle()
    {
        $title = 'Read me please.';
        $html = '<html><meta>stuff</meta><title>'. $title .'</title></html>';
        $this->assertEquals($title, html_extract_title($html));
        $html = '<html><title>'. $title .'</title>blabla<title>another</title></html>';
        $this->assertEquals($title, html_extract_title($html));
    }

    /**
     * Test html_extract_title() when the title is not found.
     */
    public function testHtmlExtractNonExistentTitle()
    {
        $html = '<html><meta>stuff</meta></html>';
        $this->assertFalse(html_extract_title($html));
    }

    /**
     * Test get_charset() with all priorities.
     */
    public function testGetCharset()
    {
        $headers = array('Content-Type' => 'text/html; charset=Headers');
        $html = '<html><meta>stuff</meta><meta charset="Html"/></html>';
        $default = 'default';
        $this->assertEquals('headers', get_charset($headers, $html, $default));
        $this->assertEquals('html', get_charset(array(), $html, $default));
        $this->assertEquals($default, get_charset(array(), '', $default));
        $this->assertEquals('utf-8', get_charset(array(), ''));
    }

    /**
     * Test headers_extract_charset() when the charset is found.
     */
    public function testHeadersExtractExistentCharset()
    {
        $charset = 'x-MacCroatian';
        $headers = array('Content-Type' => 'text/html; charset='. $charset);
        $this->assertEquals(strtolower($charset), headers_extract_charset($headers));
    }

    /**
     * Test headers_extract_charset() when the charset is not found.
     */
    public function testHeadersExtractNonExistentCharset()
    {
        $headers = array();
        $this->assertFalse(headers_extract_charset($headers));

        $headers = array('Content-Type' => 'text/html');
        $this->assertFalse(headers_extract_charset($headers));
    }

    /**
     * Test html_extract_charset() when the charset is found.
     */
    public function testHtmlExtractExistentCharset()
    {
        $charset = 'x-MacCroatian';
        $html = '<html><meta>stuff2</meta><meta charset="'. $charset .'"/></html>';
        $this->assertEquals(strtolower($charset), html_extract_charset($html));
    }

    /**
     * Test html_extract_charset() when the charset is not found.
     */
    public function testHtmlExtractNonExistentCharset()
    {
        $html = '<html><meta>stuff</meta></html>';
        $this->assertFalse(html_extract_charset($html));
        $html = '<html><meta>stuff</meta><meta charset=""/></html>';
        $this->assertFalse(html_extract_charset($html));
    }

    /**
     * Test count_private.
     */
    public function testCountPrivateLinks()
    {
        $refDB = new ReferenceLinkDB();
        $this->assertEquals($refDB->countPrivateLinks(), count_private($refDB->getLinks()));
    }
}
