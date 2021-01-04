<?php

namespace Shaarli\Security;

use Psr\Log\LoggerInterface;
use Shaarli\Helper\FileUtils;

/**
 * Class BanManager
 *
 * Failed login attempts will store the associated IP address.
 * After N failed attempts, the IP will be prevented from log in for duration D.
 * Both N and D can be set in the configuration file.
 *
 * @package Shaarli\Security
 */
class BanManager
{
    /** @var array List of allowed proxies IP */
    protected $trustedProxies;

    /** @var int Number of allowed failed attempt before the ban */
    protected $nbAttempts;

    /** @var  int Ban duration in seconds */
    protected $banDuration;

    /** @var string Path to the file containing IP bans and failures */
    protected $banFile;

    /** @var LoggerInterface Path to the log file, used to log bans */
    protected $logger;

    /** @var array List of IP with their associated number of failed attempts */
    protected $failures = [];

    /** @var array List of banned IP with their associated unban timestamp */
    protected $bans = [];

    /**
     * BanManager constructor.
     *
     * @param array           $trustedProxies List of allowed proxies IP
     * @param int             $nbAttempts     Number of allowed failed attempt before the ban
     * @param int             $banDuration    Ban duration in seconds
     * @param string          $banFile        Path to the file containing IP bans and failures
     * @param LoggerInterface $logger         PSR-3 logger to save login attempts in log directory
     */
    public function __construct($trustedProxies, $nbAttempts, $banDuration, $banFile, LoggerInterface $logger)
    {
        $this->trustedProxies = $trustedProxies;
        $this->nbAttempts = $nbAttempts;
        $this->banDuration = $banDuration;
        $this->banFile = $banFile;
        $this->logger = $logger;

        $this->readBanFile();
    }

    /**
     * Handle a failed login and ban the IP after too many failed attempts
     *
     * @param array $server The $_SERVER array
     */
    public function handleFailedAttempt($server)
    {
        $ip = $this->getIp($server);
        // the IP is behind a trusted forward proxy, but is not forwarded
        // in the HTTP headers, so we do nothing
        if (empty($ip)) {
            return;
        }

        // increment the fail count for this IP
        if (isset($this->failures[$ip])) {
            $this->failures[$ip]++;
        } else {
            $this->failures[$ip] = 1;
        }

        if ($this->failures[$ip] >= $this->nbAttempts) {
            $this->bans[$ip] = time() + $this->banDuration;
            $this->logger->info(format_log('IP address banned from login: ' . $ip, $ip));
        }
        $this->writeBanFile();
    }

    /**
     * Remove failed attempts for the provided client.
     *
     * @param array $server $_SERVER
     */
    public function clearFailures($server)
    {
        $ip = $this->getIp($server);
        // the IP is behind a trusted forward proxy, but is not forwarded
        // in the HTTP headers, so we do nothing
        if (empty($ip)) {
            return;
        }

        if (isset($this->failures[$ip])) {
            unset($this->failures[$ip]);
        }
        $this->writeBanFile();
    }

    /**
     * Check whether the client IP is banned or not.
     *
     * @param array $server $_SERVER
     *
     * @return bool True if the IP is banned, false otherwise
     */
    public function isBanned($server)
    {
        $ip = $this->getIp($server);
        // the IP is behind a trusted forward proxy, but is not forwarded
        // in the HTTP headers, so we allow the authentication attempt.
        if (empty($ip)) {
            return false;
        }

        // the user is not banned
        if (! isset($this->bans[$ip])) {
            return false;
        }

        // the user is still banned
        if ($this->bans[$ip] > time()) {
            return true;
        }

        // the ban has expired, the user can attempt to log in again
        if (isset($this->failures[$ip])) {
            unset($this->failures[$ip]);
        }
        unset($this->bans[$ip]);
        $this->logger->info(format_log('Ban lifted for: ' . $ip, $ip));

        $this->writeBanFile();
        return false;
    }

    /**
     * Retrieve the IP from $_SERVER.
     * If the actual IP is behind an allowed reverse proxy,
     * we try to extract the forwarded IP from HTTP headers.
     *
     * @param array $server $_SERVER
     *
     * @return string|bool The IP or false if none could be extracted
     */
    protected function getIp($server)
    {
        $ip = $server['REMOTE_ADDR'];
        if (! in_array($ip, $this->trustedProxies)) {
            return $ip;
        }
        return getIpAddressFromProxy($server, $this->trustedProxies);
    }

    /**
     * Read a file containing banned IPs
     */
    protected function readBanFile()
    {
        $data = FileUtils::readFlatDB($this->banFile);
        if (isset($data['failures']) && is_array($data['failures'])) {
            $this->failures = $data['failures'];
        }

        if (isset($data['bans']) && is_array($data['bans'])) {
            $this->bans = $data['bans'];
        }
    }

    /**
     * Write the banned IPs to a file
     */
    protected function writeBanFile()
    {
        return FileUtils::writeFlatDB(
            $this->banFile,
            [
                'failures' => $this->failures,
                'bans' => $this->bans,
            ]
        );
    }

    /**
     * Get the Failures (for UT purpose).
     *
     * @return array
     */
    public function getFailures()
    {
        return $this->failures;
    }

    /**
     * Get the Bans (for UT purpose).
     *
     * @return array
     */
    public function getBans()
    {
        return $this->bans;
    }
}
