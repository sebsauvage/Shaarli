<?php
namespace Shaarli;

use \PHPUnit\Framework\TestCase;

/**
 * Fake ConfigManager
 */
class FakeConfigManager
{
    public static function get($key)
    {
        return $key;
    }
}


/**
 * Test coverage for SessionManager
 */
class SessionManagerTest extends TestCase
{
    /**
     * Generate a session token
     */
    public function testGenerateToken()
    {
        $session = [];
        $conf = new FakeConfigManager();
        $sessionManager = new SessionManager($session, $conf);

        $token = $sessionManager->generateToken();

        $this->assertEquals(1, $session['tokens'][$token]);
        $this->assertEquals(40, strlen($token));
    }

    /**
     * Generate and check a session token
     */
    public function testGenerateAndCheckToken()
    {
        $session = [];
        $conf = new FakeConfigManager();
        $sessionManager = new SessionManager($session, $conf);

        $token = $sessionManager->generateToken();

        // ensure a token has been generated
        $this->assertEquals(1, $session['tokens'][$token]);
        $this->assertEquals(40, strlen($token));

        // check and destroy the token
        $this->assertTrue($sessionManager->checkToken($token));
        $this->assertFalse(isset($session['tokens'][$token]));

        // ensure the token has been destroyed
        $this->assertFalse($sessionManager->checkToken($token));
    }

    /**
     * Check an invalid session token
     */
    public function testCheckInvalidToken()
    {
        $session = [];
        $conf = new FakeConfigManager();
        $sessionManager = new SessionManager($session, $conf);

        $this->assertFalse($sessionManager->checkToken('4dccc3a45ad9d03e5542b90c37d8db6d10f2b38b'));
    }
}
