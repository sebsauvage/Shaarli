<?php
namespace Shaarli;

/**
 * User login management
 */
class LoginManager
{
    protected $globals = [];
    protected $configManager = null;
    protected $banFile = '';

    /**
     * Constructor
     *
     * @param array         $globals       The $GLOBALS array (reference)
     * @param ConfigManager $configManager Configuration Manager instance.
     */
    public function __construct(& $globals, $configManager)
    {
        $this->globals = &$globals;
        $this->configManager = $configManager;
        $this->banFile = $this->configManager->get('resource.ban_file', 'data/ipbans.php');
        $this->readBanFile();
    }

    /**
     * Read a file containing banned IPs
     */
    protected function readBanFile()
    {
        if (! file_exists($this->banFile)) {
            return;
        }
        include $this->banFile;
    }

    /**
     * Write the banned IPs to a file
     */
    protected function writeBanFile()
    {
        if (! array_key_exists('IPBANS', $this->globals)) {
            return;
        }
        file_put_contents(
            $this->banFile,
            "<?php\n\$GLOBALS['IPBANS']=" . var_export($this->globals['IPBANS'], true) . ";\n?>"
        );
    }

    /**
     * Handle a failed login and ban the IP after too many failed attempts
     *
     * @param array $server The $_SERVER array
     */
    public function handleFailedLogin($server)
    {
        $ip = $server['REMOTE_ADDR'];
        $trusted = $this->configManager->get('security.trusted_proxies', []);

        if (in_array($ip, $trusted)) {
            $ip = getIpAddressFromProxy($server, $trusted);
            if (! $ip) {
                // the IP is behind a trusted forward proxy, but is not forwarded
                // in the HTTP headers, so we do nothing
                return;
            }
        }

        // increment the fail count for this IP
        if (isset($this->globals['IPBANS']['FAILURES'][$ip])) {
            $this->globals['IPBANS']['FAILURES'][$ip]++;
        } else {
            $this->globals['IPBANS']['FAILURES'][$ip] = 1;
        }

        if ($this->globals['IPBANS']['FAILURES'][$ip] >= $this->configManager->get('security.ban_after')) {
            $this->globals['IPBANS']['BANS'][$ip] = time() + $this->configManager->get('security.ban_duration', 1800);
            logm(
                $this->configManager->get('resource.log'),
                $server['REMOTE_ADDR'],
                'IP address banned from login'
            );
        }
        $this->writeBanFile();
    }

    /**
     * Handle a successful login
     *
     * @param array $server The $_SERVER array
     */
    public function handleSuccessfulLogin($server)
    {
        $ip = $server['REMOTE_ADDR'];
        // FIXME unban when behind a trusted proxy?

        unset($this->globals['IPBANS']['FAILURES'][$ip]);
        unset($this->globals['IPBANS']['BANS'][$ip]);

        $this->writeBanFile();
    }

    /**
     * Check if the user can login from this IP
     *
     * @param array $server The $_SERVER array
     *
     * @return bool true if the user is allowed to login
     */
    public function canLogin($server)
    {
        $ip = $server['REMOTE_ADDR'];

        if (! isset($this->globals['IPBANS']['BANS'][$ip])) {
            // the user is not banned
            return true;
        }

        if ($this->globals['IPBANS']['BANS'][$ip] > time()) {
            // the user is still banned
            return false;
        }

        // the ban has expired, the user can attempt to log in again
        logm($this->configManager->get('resource.log'), $server['REMOTE_ADDR'], 'Ban lifted.');
        unset($this->globals['IPBANS']['FAILURES'][$ip]);
        unset($this->globals['IPBANS']['BANS'][$ip]);

        $this->writeBanFile();
        return true;
    }
}
