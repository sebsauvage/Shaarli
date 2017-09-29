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

    /**
     * Test text2clickable without a redirector being set.
     */
    public function testText2clickableWithoutRedirector()
    {
        $text = 'stuff http://hello.there/is=someone#here otherstuff';
        $expectedText = 'stuff <a href="http://hello.there/is=someone#here">http://hello.there/is=someone#here</a> otherstuff';
        $processedText = text2clickable($text, '');
        $this->assertEquals($expectedText, $processedText);

        $text = 'stuff http://hello.there/is=someone#here(please) otherstuff';
        $expectedText = 'stuff <a href="http://hello.there/is=someone#here(please)">http://hello.there/is=someone#here(please)</a> otherstuff';
        $processedText = text2clickable($text, '');
        $this->assertEquals($expectedText, $processedText);

        $text = 'stuff http://hello.there/is=someone#here(please)&no otherstuff';
        $expectedText = 'stuff <a href="http://hello.there/is=someone#here(please)&no">http://hello.there/is=someone#here(please)&no</a> otherstuff';
        $processedText = text2clickable($text, '');
        $this->assertEquals($expectedText, $processedText);
    }

    /**
     * Test text2clickable a redirector set.
     */
    public function testText2clickableWithRedirector()
    {
        $text = 'stuff http://hello.there/is=someone#here otherstuff';
        $redirector = 'http://redirector.to';
        $expectedText = 'stuff <a href="'.
            $redirector .
            urlencode('http://hello.there/is=someone#here') .
            '">http://hello.there/is=someone#here</a> otherstuff';
        $processedText = text2clickable($text, $redirector);
        $this->assertEquals($expectedText, $processedText);
    }

    /**
     * Test testSpace2nbsp.
     */
    public function testSpace2nbsp()
    {
        $text = '  Are you   thrilled  by flags   ?'. PHP_EOL .' Really?';
        $expectedText = '&nbsp; Are you &nbsp; thrilled &nbsp;by flags &nbsp; ?'. PHP_EOL .'&nbsp;Really?';
        $processedText = space2nbsp($text);
        $this->assertEquals($expectedText, $processedText);
    }

    /**
     * Test hashtags auto-link.
     */
    public function testHashtagAutolink()
    {
        $index = 'http://domain.tld/';
        $rawDescription = '#hashtag\n
            # nothashtag\n
            test#nothashtag #hashtag \#nothashtag\n
            test #hashtag #hashtag test #hashtag.test\n
            #hashtag #hashtag-nothashtag #hashtag_hashtag\n
            What is #ашок anyway?\n
            カタカナ #カタカナ」カタカナ\n';
        $autolinkedDescription = hashtag_autolink($rawDescription, $index);

        $this->assertContains($this->getHashtagLink('hashtag', $index), $autolinkedDescription);
        $this->assertNotContains(' #hashtag', $autolinkedDescription);
        $this->assertNotContains('>#nothashtag', $autolinkedDescription);
        $this->assertContains($this->getHashtagLink('ашок', $index), $autolinkedDescription);
        $this->assertContains($this->getHashtagLink('カタカナ', $index), $autolinkedDescription);
        $this->assertContains($this->getHashtagLink('hashtag_hashtag', $index), $autolinkedDescription);
        $this->assertNotContains($this->getHashtagLink('hashtag-nothashtag', $index), $autolinkedDescription);
    }

    /**
     * Test hashtags auto-link without index URL.
     */
    public function testHashtagAutolinkNoIndex()
    {
        $rawDescription = 'blabla #hashtag x#nothashtag';
        $autolinkedDescription = hashtag_autolink($rawDescription);

        $this->assertContains($this->getHashtagLink('hashtag'), $autolinkedDescription);
        $this->assertNotContains(' #hashtag', $autolinkedDescription);
        $this->assertNotContains('>#nothashtag', $autolinkedDescription);
    }

    /**
     * Util function to build an hashtag link.
     *
     * @param string $hashtag Hashtag name.
     * @param string $index   Index URL.
     *
     * @return string HTML hashtag link.
     */
    private function getHashtagLink($hashtag, $index = '')
    {
        $hashtagLink = '<a href="'. $index .'?addtag=$1" title="Hashtag $1">#$1</a>';
        return str_replace('$1', $hashtag, $hashtagLink);
    }
}
