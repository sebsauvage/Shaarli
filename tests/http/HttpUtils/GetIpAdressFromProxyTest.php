<?php

namespace Shaarli\Http;

require_once 'application/http/HttpUtils.php';

/**
 * Unitary tests for getIpAddressFromProxy()
 */
class GetIpAdressFromProxyTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Test without proxy
     */
    public function testWithoutProxy()
    {
        $this->assertFalse(getIpAddressFromProxy(array(), array()));
    }

    /**
     * Test with a single IP in proxy header.
     */
    public function testWithOneForwardedIp()
    {
        $ip = '1.1.1.1';
        $server = array('HTTP_X_FORWARDED_FOR' => $ip);
        $this->assertEquals($ip, getIpAddressFromProxy($server, array()));
    }

    /**
     * Test with a multiple IPs in proxy header.
     */
    public function testWithMultipleForwardedIp()
    {
        $ip = '1.1.1.1';
        $ip2 = '2.2.2.2';

        $server = array('HTTP_X_FORWARDED_FOR' => $ip .','. $ip2);
        $this->assertEquals($ip2, getIpAddressFromProxy($server, array()));

        $server = array('HTTP_X_FORWARDED_FOR' => $ip .' ,   '. $ip2);
        $this->assertEquals($ip2, getIpAddressFromProxy($server, array()));
    }

    /**
     * Test with a trusted IP address.
     */
    public function testWithTrustedIp()
    {
        $ip = '1.1.1.1';
        $ip2 = '2.2.2.2';

        $server = array('HTTP_X_FORWARDED_FOR' => $ip);
        $this->assertFalse(getIpAddressFromProxy($server, array($ip)));

        $server = array('HTTP_X_FORWARDED_FOR' => $ip .','. $ip2);
        $this->assertEquals($ip2, getIpAddressFromProxy($server, array($ip)));
        $this->assertFalse(getIpAddressFromProxy($server, array($ip, $ip2)));
    }
}
