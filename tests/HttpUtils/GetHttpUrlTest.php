<?php
/**
 * HttpUtils' tests
 */

require_once 'application/HttpUtils.php';

/**
 * Unitary tests for get_http_url()
 */
class GetHttpUrlTest extends PHPUnit_Framework_TestCase
{
    /**
     * Get an invalid local URL
     */
    public function testGetInvalidLocalUrl()
    {
        list($headers, $content) = get_http_url('/non/existent', 1);
        $this->assertEquals('HTTP Error', $headers[0]);
        $this->assertRegexp(
            '/failed to open stream: No such file or directory/',
            $content
        );
    }

    /**
     * Get an invalid remote URL
     */
    public function testGetInvalidRemoteUrl()
    {
        list($headers, $content) = get_http_url('http://non.existent', 1);
        $this->assertEquals('HTTP Error', $headers[0]);
        $this->assertRegexp(
            '/Name or service not known/',
            $content
        );
    }
}
