<?php
/**
 * HttpUtils' tests
 */

namespace Shaarli\Http;

require_once 'application/http/HttpUtils.php';

/**
 * Unitary tests for client_ip_id()
 */
class ClientIpIdTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Get a remote client ID based on its IP
     */
    public function testClientIpIdRemote()
    {
        $this->assertEquals(
            '10.1.167.42',
            client_ip_id(['REMOTE_ADDR' => '10.1.167.42'])
        );
    }

    /**
     * Get a remote client ID based on its IP and proxy information (1)
     */
    public function testClientIpIdRemoteForwarded()
    {
        $this->assertEquals(
            '10.1.167.42_127.0.1.47',
            client_ip_id([
                'REMOTE_ADDR' => '10.1.167.42',
                'HTTP_X_FORWARDED_FOR' => '127.0.1.47'
            ])
        );
    }

    /**
     * Get a remote client ID based on its IP and proxy information (2)
     */
    public function testClientIpIdRemoteForwardedClient()
    {
        $this->assertEquals(
            '10.1.167.42_10.1.167.56_127.0.1.47',
            client_ip_id([
                'REMOTE_ADDR' => '10.1.167.42',
                'HTTP_X_FORWARDED_FOR' => '10.1.167.56',
                'HTTP_CLIENT_IP' => '127.0.1.47'
            ])
        );
    }
}
