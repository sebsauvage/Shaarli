<?php


namespace Shaarli\Security;

use PHPUnit\Framework\TestCase;
use Shaarli\FileUtils;

/**
 * Test coverage for BanManager
 */
class BanManagerTest extends TestCase
{
    /** @var BanManager Ban Manager instance */
    protected $banManager;

    /** @var string Banned IP filename */
    protected $banFile = 'sandbox/ipbans.php';

    /** @var string Log filename */
    protected $logFile = 'sandbox/shaarli.log';

    /** @var string Local client IP address */
    protected $ipAddr = '127.0.0.1';

    /** @var string Trusted proxy IP address */
    protected $trustedProxy = '10.1.1.100';

    /** @var array Simulates the $_SERVER array */
    protected $server = [];

    /**
     * Prepare or reset test resources
     */
    public function setUp()
    {
        if (file_exists($this->banFile)) {
            unlink($this->banFile);
        }

        $this->banManager = $this->getNewBanManagerInstance();
        $this->server['REMOTE_ADDR'] = $this->ipAddr;
    }

    /**
     * Test constructor with initial file.
     */
    public function testInstantiateFromFile()
    {
        $time = time() + 10;
        FileUtils::writeFlatDB(
            $this->banFile,
            [
                'failures' => [
                    $this->ipAddr => 2,
                    $ip = '1.2.3.4' => 1,
                ],
                'bans' => [
                    $ip2 = '8.8.8.8' => $time,
                    $ip3 = '1.1.1.1' => $time + 1,
                ],
            ]
        );
        $this->banManager = $this->getNewBanManagerInstance();

        $this->assertCount(2, $this->banManager->getFailures());
        $this->assertEquals(2, $this->banManager->getFailures()[$this->ipAddr]);
        $this->assertEquals(1, $this->banManager->getFailures()[$ip]);
        $this->assertCount(2, $this->banManager->getBans());
        $this->assertEquals($time, $this->banManager->getBans()[$ip2]);
        $this->assertEquals($time + 1, $this->banManager->getBans()[$ip3]);
    }

    /**
     * Test constructor with initial file with invalid values
     */
    public function testInstantiateFromCrappyFile()
    {
        FileUtils::writeFlatDB($this->banFile, 'plop');
        $this->banManager = $this->getNewBanManagerInstance();

        $this->assertEquals([], $this->banManager->getFailures());
        $this->assertEquals([], $this->banManager->getBans());
    }

    /**
     * Test failed attempt with a direct IP.
     */
    public function testHandleFailedAttempt()
    {
        $this->assertCount(0, $this->banManager->getFailures());

        $this->banManager->handleFailedAttempt($this->server);
        $this->assertCount(1, $this->banManager->getFailures());
        $this->assertEquals(1, $this->banManager->getFailures()[$this->ipAddr]);

        $this->banManager->handleFailedAttempt($this->server);
        $this->assertCount(1, $this->banManager->getFailures());
        $this->assertEquals(2, $this->banManager->getFailures()[$this->ipAddr]);
    }

    /**
     * Test failed attempt behind a trusted proxy IP (with proper IP forwarding).
     */
    public function testHandleFailedAttemptBehingProxy()
    {
        $server = [
            'REMOTE_ADDR' => $this->trustedProxy,
            'HTTP_X_FORWARDED_FOR' => $this->ipAddr,
        ];
        $this->assertCount(0, $this->banManager->getFailures());

        $this->banManager->handleFailedAttempt($server);
        $this->assertCount(1, $this->banManager->getFailures());
        $this->assertEquals(1, $this->banManager->getFailures()[$this->ipAddr]);

        $this->banManager->handleFailedAttempt($server);
        $this->assertCount(1, $this->banManager->getFailures());
        $this->assertEquals(2, $this->banManager->getFailures()[$this->ipAddr]);
    }

    /**
     * Test failed attempt behind a trusted proxy IP but without IP forwarding.
     */
    public function testHandleFailedAttemptBehindNotConfiguredProxy()
    {
        $server = [
            'REMOTE_ADDR' => $this->trustedProxy,
        ];
        $this->assertCount(0, $this->banManager->getFailures());

        $this->banManager->handleFailedAttempt($server);
        $this->assertCount(0, $this->banManager->getFailures());

        $this->banManager->handleFailedAttempt($server);
        $this->assertCount(0, $this->banManager->getFailures());
    }

    /**
     * Test failed attempts with multiple direct IP.
     */
    public function testHandleFailedAttemptMultipleIp()
    {
        $this->assertCount(0, $this->banManager->getFailures());
        $this->banManager->handleFailedAttempt($this->server);
        $this->server['REMOTE_ADDR'] = '1.2.3.4';
        $this->banManager->handleFailedAttempt($this->server);
        $this->banManager->handleFailedAttempt($this->server);
        $this->assertCount(2, $this->banManager->getFailures());
        $this->assertEquals(1, $this->banManager->getFailures()[$this->ipAddr]);
        $this->assertEquals(2, $this->banManager->getFailures()[$this->server['REMOTE_ADDR']]);
    }

    /**
     * Test clear failure for provided IP without any additional data.
     */
    public function testClearFailuresEmpty()
    {
        $this->assertCount(0, $this->banManager->getFailures());
        $this->banManager->clearFailures($this->server);
        $this->assertCount(0, $this->banManager->getFailures());
    }

    /**
     * Test clear failure for provided IP with failed attempts.
     */
    public function testClearFailuresFromFile()
    {
        FileUtils::writeFlatDB(
            $this->banFile,
            [
                'failures' => [
                    $this->ipAddr => 2,
                    $ip = '1.2.3.4' => 1,
                ]
            ]
        );
        $this->banManager = $this->getNewBanManagerInstance();

        $this->assertCount(2, $this->banManager->getFailures());
        $this->banManager->clearFailures($this->server);
        $this->assertCount(1, $this->banManager->getFailures());
        $this->assertEquals(1, $this->banManager->getFailures()[$ip]);
    }

    /**
     * Test clear failure for provided IP with failed attempts, behind a reverse proxy.
     */
    public function testClearFailuresFromFileBehindProxy()
    {
        $server = [
            'REMOTE_ADDR' => $this->trustedProxy,
            'HTTP_X_FORWARDED_FOR' => $this->ipAddr,
        ];

        FileUtils::writeFlatDB(
            $this->banFile,
            [
                'failures' => [
                    $this->ipAddr => 2,
                    $ip = '1.2.3.4' => 1,
                ]
            ]
        );
        $this->banManager = $this->getNewBanManagerInstance();

        $this->assertCount(2, $this->banManager->getFailures());
        $this->banManager->clearFailures($server);
        $this->assertCount(1, $this->banManager->getFailures());
        $this->assertEquals(1, $this->banManager->getFailures()[$ip]);
    }

    /**
     * Test clear failure for provided IP with failed attempts,
     * behind a reverse proxy without forwarding.
     */
    public function testClearFailuresFromFileBehindNotConfiguredProxy()
    {
        $server = [
            'REMOTE_ADDR' => $this->trustedProxy,
        ];

        FileUtils::writeFlatDB(
            $this->banFile,
            [
                'failures' => [
                    $this->ipAddr => 2,
                    $ip = '1.2.3.4' => 1,
                ]
            ]
        );
        $this->banManager = $this->getNewBanManagerInstance();

        $this->assertCount(2, $this->banManager->getFailures());
        $this->banManager->clearFailures($server);
        $this->assertCount(2, $this->banManager->getFailures());
    }

    /**
     * Test isBanned without any data
     */
    public function testIsBannedEmpty()
    {
        $this->assertFalse($this->banManager->isBanned($this->server));
    }

    /**
     * Test isBanned with banned IP from file data
     */
    public function testBannedFromFile()
    {
        FileUtils::writeFlatDB(
            $this->banFile,
            [
                'bans' => [
                    $this->ipAddr => time() + 10,
                ]
            ]
        );
        $this->banManager = $this->getNewBanManagerInstance();

        $this->assertCount(1, $this->banManager->getBans());
        $this->assertTrue($this->banManager->isBanned($this->server));
    }

    /**
     * Test isBanned with banned IP from file data behind a reverse proxy
     */
    public function testBannedFromFileBehindProxy()
    {
        $server = [
            'REMOTE_ADDR' => $this->trustedProxy,
            'HTTP_X_FORWARDED_FOR' => $this->ipAddr,
        ];
        FileUtils::writeFlatDB(
            $this->banFile,
            [
                'bans' => [
                    $this->ipAddr => time() + 10,
                ]
            ]
        );
        $this->banManager = $this->getNewBanManagerInstance();

        $this->assertCount(1, $this->banManager->getBans());
        $this->assertTrue($this->banManager->isBanned($server));
    }

    /**
     * Test isBanned with banned IP from file data behind a reverse proxy,
     * without IP forwarding
     */
    public function testBannedFromFileBehindNotConfiguredProxy()
    {
        $server = [
            'REMOTE_ADDR' => $this->trustedProxy,
        ];
        FileUtils::writeFlatDB(
            $this->banFile,
            [
                'bans' => [
                    $this->ipAddr => time() + 10,
                ]
            ]
        );
        $this->banManager = $this->getNewBanManagerInstance();

        $this->assertCount(1, $this->banManager->getBans());
        $this->assertFalse($this->banManager->isBanned($server));
    }

    /**
     * Test isBanned with an expired ban
     */
    public function testLiftBan()
    {
        FileUtils::writeFlatDB(
            $this->banFile,
            [
                'bans' => [
                    $this->ipAddr => time() - 10,
                ]
            ]
        );
        $this->banManager = $this->getNewBanManagerInstance();

        $this->assertCount(1, $this->banManager->getBans());
        $this->assertFalse($this->banManager->isBanned($this->server));
    }

    /**
     * Test isBanned with an expired ban behind a reverse proxy
     */
    public function testLiftBanBehindProxy()
    {
        $server = [
            'REMOTE_ADDR' => $this->trustedProxy,
            'HTTP_X_FORWARDED_FOR' => $this->ipAddr,
        ];

        FileUtils::writeFlatDB(
            $this->banFile,
            [
                'bans' => [
                    $this->ipAddr => time() - 10,
                ]
            ]
        );
        $this->banManager = $this->getNewBanManagerInstance();

        $this->assertCount(1, $this->banManager->getBans());
        $this->assertFalse($this->banManager->isBanned($server));
    }

    /**
     * Test isBanned with an expired ban behind a reverse proxy
     */
    public function testLiftBanBehindNotConfiguredProxy()
    {
        $server = [
            'REMOTE_ADDR' => $this->trustedProxy,
        ];

        FileUtils::writeFlatDB(
            $this->banFile,
            [
                'bans' => [
                    $this->ipAddr => time() - 10,
                ]
            ]
        );
        $this->banManager = $this->getNewBanManagerInstance();

        $this->assertCount(1, $this->banManager->getBans());
        $this->assertFalse($this->banManager->isBanned($server));
    }

    /**
     * Build a new instance of BanManager, which will reread the ban file.
     *
     * @return BanManager instance
     */
    protected function getNewBanManagerInstance()
    {
        return new BanManager(
            [$this->trustedProxy],
            3,
            1800,
            $this->banFile,
            $this->logFile
        );
    }
}
