<?php
/**
 * Unitary tests for get_url_scheme()
 */

namespace Shaarli\Http;

require_once 'application/http/UrlUtils.php';

class GetUrlSchemeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Get empty scheme string for empty UrlUtils
     */
    public function testGetUrlSchemeEmpty()
    {
        $this->assertEquals('', get_url_scheme(''));
    }

    /**
     * Get normal scheme of UrlUtils
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
