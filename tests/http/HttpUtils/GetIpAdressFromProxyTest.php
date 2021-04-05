<?php

namespace Shaarli\Http;

/**
 * Unitary tests for getIpAddressFromProxy()
 */
class GetIpAdressFromProxyTest extends \Shaarli\TestCase
{

    /**
     * Test without proxy
     */
    public function testWithoutProxy()
    {
        $this->assertFalse(getIpAddressFromProxy([], []));
    }

    /**
     * Test with a single IP in proxy header.
     */
    public function testWithOneForwardedIp()
    {
        $ip = '1.1.1.1';
        $server = ['HTTP_X_FORWARDED_FOR' => $ip];
        $this->assertEquals($ip, getIpAddressFromProxy($server, []));
    }

    /**
     * Test with a multiple IPs in proxy header.
     */
    public function testWithMultipleForwardedIp()
    {
        $ip = '1.1.1.1';
        $ip2 = '2.2.2.2';

        $server = ['HTTP_X_FORWARDED_FOR' => $ip . ',' . $ip2];
        $this->assertEquals($ip2, getIpAddressFromProxy($server, []));

        $server = ['HTTP_X_FORWARDED_FOR' => $ip . ' ,   ' . $ip2];
        $this->assertEquals($ip2, getIpAddressFromProxy($server, []));
    }

    /**
     * Test with a trusted IP address.
     */
    public function testWithTrustedIp()
    {
        $ip = '1.1.1.1';
        $ip2 = '2.2.2.2';

        $server = ['HTTP_X_FORWARDED_FOR' => $ip];
        $this->assertFalse(getIpAddressFromProxy($server, [$ip]));

        $server = ['HTTP_X_FORWARDED_FOR' => $ip . ',' . $ip2];
        $this->assertEquals($ip2, getIpAddressFromProxy($server, [$ip]));
        $this->assertFalse(getIpAddressFromProxy($server, [$ip, $ip2]));
    }
}
