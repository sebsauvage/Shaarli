<?php
/**
 * HttpUtils' tests
 */

require_once 'application/HttpUtils.php';

/**
 * Unitary tests for get_http_response()
 */
class GetHttpUrlTest extends PHPUnit_Framework_TestCase
{
    /**
     * Get an invalid local URL
     */
    public function testGetInvalidLocalUrl()
    {
        // Local
        list($headers, $content) = get_http_response('/non/existent', 1);
        $this->assertEquals('Invalid HTTP Url', $headers[0]);
        $this->assertFalse($content);

        // Non HTTP
        list($headers, $content) = get_http_response('ftp://save.tld/mysave', 1);
        $this->assertEquals('Invalid HTTP Url', $headers[0]);
        $this->assertFalse($content);
    }

    /**
     * Get an invalid remote URL
     */
    public function testGetInvalidRemoteUrl()
    {
        list($headers, $content) = @get_http_response('http://non.existent', 1);
        $this->assertFalse($headers);
        $this->assertFalse($content);
    }
}
