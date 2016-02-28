<?php
/**
 * HttpUtils' tests
 */

require_once 'application/HttpUtils.php';

/**
 * Unitary tests for server_url()
 */
class ServerUrlTest extends PHPUnit_Framework_TestCase
{
    /**
     * Detect if the server uses SSL
     */
    public function testHttpsScheme()
    {
        $this->assertEquals(
            'https://host.tld',
            server_url(
                array(
                    'HTTPS' => 'ON',
                    'SERVER_NAME' => 'host.tld',
                    'SERVER_PORT' => '443'
                )
            )
        );

        $this->assertEquals(
            'https://host.tld:8080',
            server_url(
                array(
                    'HTTPS' => 'ON',
                    'SERVER_NAME' => 'host.tld',
                    'SERVER_PORT' => '8080'
                )
            )
        );
    }

    /**
     * Detect a Proxy with SSL enabled
     */
    public function testHttpsProxyForward()
    {
        $this->assertEquals(
            'https://host.tld:8080',
            server_url(
                array(
                    'HTTPS' => 'Off',
                    'SERVER_NAME' => 'host.tld',
                    'SERVER_PORT' => '80',
                    'HTTP_X_FORWARDED_PROTO' => 'https',
                    'HTTP_X_FORWARDED_PORT' => '8080'
                )
            )
        );

        $this->assertEquals(
            'https://host.tld',
            server_url(
                array(
                    'HTTPS' => 'Off',
                    'SERVER_NAME' => 'host.tld',
                    'SERVER_PORT' => '80',
                    'HTTP_X_FORWARDED_PROTO' => 'https'
                )
            )
        );

        $this->assertEquals(
            'https://host.tld:4974',
            server_url(
                array(
                    'HTTPS' => 'Off',
                    'SERVER_NAME' => 'host.tld',
                    'SERVER_PORT' => '80',
                    'HTTP_X_FORWARDED_PROTO' => 'https, https',
                    'HTTP_X_FORWARDED_PORT' => '4974, 80'
                )
            )
        );
    }

    /**
     * Detect if the server uses a specific port (!= 80)
     */
    public function testPort()
    {
        // HTTP
        $this->assertEquals(
            'http://host.tld:8080',
            server_url(
                array(
                    'HTTPS' => 'OFF',
                    'SERVER_NAME' => 'host.tld',
                    'SERVER_PORT' => '8080'
                )
            )
        );

        // HTTPS
        $this->assertEquals(
            'https://host.tld:8080',
            server_url(
                array(
                    'HTTPS' => 'ON',
                    'SERVER_NAME' => 'host.tld',
                    'SERVER_PORT' => '8080'
                )
            )
        );
    }

    /**
     * HTTP server on port 80
     */
    public function testStandardHttpPort()
    {
        $this->assertEquals(
            'http://host.tld',
            server_url(
                array(
                    'HTTPS' => 'OFF',
                    'SERVER_NAME' => 'host.tld',
                    'SERVER_PORT' => '80'
                )
            )
        );
    }

    /**
     * HTTPS server on port 443
     */
    public function testStandardHttpsPort()
    {
        $this->assertEquals(
            'https://host.tld',
            server_url(
                array(
                    'HTTPS' => 'ON',
                    'SERVER_NAME' => 'host.tld',
                    'SERVER_PORT' => '443'
                )
            )
        );
    }
}
