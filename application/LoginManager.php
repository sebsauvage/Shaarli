<?php
namespace Shaarli;

/**
 * User login management
 */
class LoginManager
{
    /** @var array A reference to the $_GLOBALS array */
    protected $globals = [];

    /** @var ConfigManager Configuration Manager instance **/
    protected $configManager = null;

    /** @var SessionManager Session Manager instance **/
    protected $sessionManager = null;

    /** @var string Path to the file containing IP bans */
    protected $banFile = '';

    /** @var bool Whether the user is logged in **/
    protected $isLoggedIn = false;

    /** @var bool Whether the Shaarli instance is open to public edition **/
    protected $openShaarli = false;

    /**
     * Constructor
     *
     * @param array          $globals        The $GLOBALS array (reference)
     * @param ConfigManager  $configManager  Configuration Manager instance
     * @param SessionManager $sessionManager SessionManager instance
     */
    public function __construct(& $globals, $configManager, $sessionManager)
    {
        $this->globals = &$globals;
        $this->configManager = $configManager;
        $this->sessionManager = $sessionManager;
        $this->banFile = $this->configManager->get('resource.ban_file', 'data/ipbans.php');
        $this->readBanFile();
        if ($this->configManager->get('security.open_shaarli')) {
            $this->openShaarli = true;
        }
    }

    /**
     * Check user session state and validity (expiration)
     *
     * @param array  $server  The $_SERVER array
     * @param array  $session The $_SESSION array (reference)
     * @param array  $cookie  The $_COOKIE array
     * @param string $webPath Path on the server in which the cookie will be available on
     * @param string $token   Session token
     *
     * @return bool true if the user session is valid, false otherwise
     */
    public function checkLoginState($server, & $session, $cookie, $webPath, $token)
    {
        if (! $this->configManager->exists('credentials.login')) {
            // Shaarli is not configured yet
            $this->isLoggedIn = false;
            return;
        }

        if (isset($cookie[SessionManager::$LOGGED_IN_COOKIE])
            && $cookie[SessionManager::$LOGGED_IN_COOKIE] === $token
        ) {
            $this->sessionManager->storeLoginInfo($server);
            $this->isLoggedIn = true;
        }

        // Logout when:
        // - the session does not exist on the server side
        // - the session has expired
        // - the client IP address has changed
        if (empty($session['uid'])
            || ($this->configManager->get('security.session_protection_disabled') === false
                && $session['ip'] != client_ip_id($server))
            || time() >= $session['expires_on']
        ) {
            $this->sessionManager->logout($webPath);
            $this->isLoggedIn = false;
            return;
        }

        // Extend session validity
        if (! empty($session['longlastingsession'])) {
            // "Stay signed in" is enabled
            $session['expires_on'] = time() + $session['longlastingsession'];
        } else {
            $session['expires_on'] = time() + SessionManager::$INACTIVITY_TIMEOUT;
        }
    }

    /**
     * Return whether the user is currently logged in
     *
     * @return true when the user is logged in, false otherwise
     */
    public function isLoggedIn()
    {
        if ($this->openShaarli) {
            return true;
        }
        return $this->isLoggedIn;
    }

    /**
     * Check user credentials are valid
     *
     * @param array  $server   The $_SERVER array
     * @param string $login    Username
     * @param string $password Password
     *
     * @return bool true if the provided credentials are valid, false otherwise
     */
    public function checkCredentials($server, $login, $password)
    {
        $hash = sha1($password . $login . $this->configManager->get('credentials.salt'));

        if ($login != $this->configManager->get('credentials.login')
            || $hash != $this->configManager->get('credentials.hash')
        ) {
            logm(
                $this->configManager->get('resource.log'),
                $server['REMOTE_ADDR'],
                'Login failed for user ' . $login
            );
            return false;
        }

        $this->sessionManager->storeLoginInfo($server);
        logm(
            $this->configManager->get('resource.log'),
            $server['REMOTE_ADDR'],
            'Login successful'
        );
        return true;
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
