<?php
/**
 * Unitary tests for get_url_scheme()
 */

require_once 'application/Url.php';

class GetUrlSchemeTest extends PHPUnit_Framework_TestCase
{
    /**
     * Get empty scheme string for empty Url
     */
    public function testGetUrlSchemeEmpty()
    {
        $this->assertEquals('', get_url_scheme(''));
    }

    /**
     * Get normal scheme of Url
     */
    public function testGetUrlScheme()
    {
        $this->assertEquals('http', get_url_scheme('http://domain.tld:3000'));
        $this->assertEquals('https', get_url_scheme('https://domain.tld:3000'));
        $this->assertEquals('http', get_url_scheme('domain.tld'));
        $this->assertEquals('ssh', get_url_scheme('ssh://domain.tld'));
        $this->assertEquals('ftp', get_url_scheme('ftp://domain.tld'));
        $this->assertEquals('git', get_url_scheme('git://domain.tld/push?pull=clone#checkout'));
    }
}
