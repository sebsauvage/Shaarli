<?php

namespace Shaarli\Http;

require_once 'application/http/HttpUtils.php';

/**
 * Class IsHttpsTest
 *
 * Test class for is_https() function.
 */
class IsHttpsTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Test is_https with HTTPS values.
     */
    public function testIsHttpsTrue()
    {
        $this->assertTrue(is_https(['HTTPS' => true]));
        $this->assertTrue(is_https(['HTTPS' => '1']));
        $this->assertTrue(is_https(['HTTPS' => false, 'HTTP_X_FORWARDED_PORT' => 443]));
        $this->assertTrue(is_https(['HTTPS' => false, 'HTTP_X_FORWARDED_PORT' => '443']));
        $this->assertTrue(is_https(['HTTPS' => false, 'HTTP_X_FORWARDED_PORT' => '443,123,456,']));
    }

    /**
     * Test is_https with HTTP values.
     */
    public function testIsHttpsFalse()
    {
        $this->assertFalse(is_https([]));
        $this->assertFalse(is_https(['HTTPS' => false]));
        $this->assertFalse(is_https(['HTTPS' => '0']));
        $this->assertFalse(is_https(['HTTPS' => false, 'HTTP_X_FORWARDED_PORT' => 123]));
        $this->assertFalse(is_https(['HTTPS' => false, 'HTTP_X_FORWARDED_PORT' => '123']));
        $this->assertFalse(is_https(['HTTPS' => false, 'HTTP_X_FORWARDED_PORT' => ',123,456,']));
    }
}
