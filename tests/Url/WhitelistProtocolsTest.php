<?php

require_once 'application/Url.php';

use Shaarli\Config\ConfigManager;

/**
 * Class WhitelistProtocolsTest
 *
 * Test whitelist_protocols() function of Url.
 */
class WhitelistProtocolsTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test whitelist_protocols() on a note (relative URL).
     */
    public function testWhitelistProtocolsRelative()
    {
        $whitelist = ['ftp', 'magnet'];
        $url = '?12443564';
        $this->assertEquals($url, whitelist_protocols($url, $whitelist));
        $url = '/path.jpg';
        $this->assertEquals($url, whitelist_protocols($url, $whitelist));
    }

    /**
     * Test whitelist_protocols() on a note (relative URL).
     */
    public function testWhitelistProtocolMissing()
    {
        $whitelist = ['ftp', 'magnet'];
        $url = 'test.tld/path/?query=value#hash';
        $this->assertEquals('http://'. $url, whitelist_protocols($url, $whitelist));
    }

    /**
     * Test whitelist_protocols() with allowed protocols.
     */
    public function testWhitelistAllowedProtocol()
    {
        $whitelist = ['ftp', 'magnet'];
        $url = 'http://test.tld/path/?query=value#hash';
        $this->assertEquals($url, whitelist_protocols($url, $whitelist));
        $url = 'https://test.tld/path/?query=value#hash';
        $this->assertEquals($url, whitelist_protocols($url, $whitelist));
        $url = 'ftp://test.tld/path/?query=value#hash';
        $this->assertEquals($url, whitelist_protocols($url, $whitelist));
        $url = 'magnet:test.tld/path/?query=value#hash';
        $this->assertEquals($url, whitelist_protocols($url, $whitelist));
    }

    /**
     * Test whitelist_protocols() with allowed protocols.
     */
    public function testWhitelistDisallowedProtocol()
    {
        $whitelist = ['ftp', 'magnet'];
        $url = 'javascript:alert("xss");';
        $this->assertEquals('http://alert("xss");', whitelist_protocols($url, $whitelist));
        $url = 'other://test.tld/path/?query=value#hash';
        $this->assertEquals('http://test.tld/path/?query=value#hash', whitelist_protocols($url, $whitelist));
    }
}
