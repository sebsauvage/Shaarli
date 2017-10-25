<?php
// Initialize reference data _before_ PHPUnit starts a session
require_once 'tests/utils/ReferenceSessionIdHashes.php';
ReferenceSessionIdHashes::genAllHashes();

use \Shaarli\SessionManager;
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
    // Session ID hashes
    protected static $sidHashes = null;

    /**
     * Assign reference data
     */
    public static function setUpBeforeClass()
    {
        self::$sidHashes = ReferenceSessionIdHashes::getHashes();
    }

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
     * Check a session token
     */
    public function testCheckToken()
    {
        $token = '4dccc3a45ad9d03e5542b90c37d8db6d10f2b38b';
        $session = [
            'tokens' => [
                $token => 1,
            ],
        ];
        $conf = new FakeConfigManager();
        $sessionManager = new SessionManager($session, $conf);


        // check and destroy the token
        $this->assertTrue($sessionManager->checkToken($token));
        $this->assertFalse(isset($session['tokens'][$token]));

        // ensure the token has been destroyed
        $this->assertFalse($sessionManager->checkToken($token));
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

    /**
     * Test SessionManager::checkId with a valid ID - TEST ALL THE HASHES!
     *
     * This tests extensively covers all hash algorithms / bit representations
     */
    public function testIsAnyHashSessionIdValid()
    {
        foreach (self::$sidHashes as $algo => $bpcs) {
            foreach ($bpcs as $bpc => $hash) {
                $this->assertTrue(SessionManager::checkId($hash));
            }
        }
    }

    /**
     * Test checkId with a valid ID - SHA-1 hashes
     */
    public function testIsSha1SessionIdValid()
    {
        $this->assertTrue(SessionManager::checkId(sha1('shaarli')));
    }

    /**
     * Test checkId with a valid ID - SHA-256 hashes
     */
    public function testIsSha256SessionIdValid()
    {
        $this->assertTrue(SessionManager::checkId(hash('sha256', 'shaarli')));
    }

    /**
     * Test checkId with a valid ID - SHA-512 hashes
     */
    public function testIsSha512SessionIdValid()
    {
        $this->assertTrue(SessionManager::checkId(hash('sha512', 'shaarli')));
    }

    /**
     * Test checkId with invalid IDs.
     */
    public function testIsSessionIdInvalid()
    {
        $this->assertFalse(SessionManager::checkId(''));
        $this->assertFalse(SessionManager::checkId([]));
        $this->assertFalse(
            SessionManager::checkId('c0ZqcWF3VFE2NmJBdm1HMVQ0ZHJ3UmZPbTFsNGhkNHI=')
        );
    }
}
