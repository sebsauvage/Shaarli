<?php
namespace Shaarli\Security;

use Shaarli\Config\ConfigManager;

/**
 * User login management
 */
class LoginManager
{
    /** @var string Name of the cookie set after logging in **/
    public static $STAY_SIGNED_IN_COOKIE = 'shaarli_staySignedIn';

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

    /** @var string User sign-in token depending on remote IP and credentials */
    protected $staySignedInToken = '';

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
        if ($this->configManager->get('security.open_shaarli') === true) {
            $this->openShaarli = true;
        }
    }

    /**
     * Generate a token depending on deployment salt, user password and client IP
     *
     * @param string $clientIpAddress The remote client IP address
     */
    public function generateStaySignedInToken($clientIpAddress)
    {
        $this->staySignedInToken = sha1(
            $this->configManager->get('credentials.hash')
            . $clientIpAddress
            . $this->configManager->get('credentials.salt')
        );
    }

    /**
     * Return the user's client stay-signed-in token
     *
     * @return string User's client stay-signed-in token
     */
    public function getStaySignedInToken()
    {
        return $this->staySignedInToken;
    }

    /**
     * Check user session state and validity (expiration)
     *
     * @param array  $cookie     The $_COOKIE array
     * @param string $clientIpId Client IP address identifier
     */
    public function checkLoginState($cookie, $clientIpId)
    {
        if (! $this->configManager->exists('credentials.login')) {
            // Shaarli is not configured yet
            $this->isLoggedIn = false;
            return;
        }

        if (isset($cookie[self::$STAY_SIGNED_IN_COOKIE])
            && $cookie[self::$STAY_SIGNED_IN_COOKIE] === $this->staySignedInToken
        ) {
            // The user client has a valid stay-signed-in cookie
            // Session information is updated with the current client information
            $this->sessionManager->storeLoginInfo($clientIpId);
        } elseif ($this->sessionManager->hasSessionExpired()
            || $this->sessionManager->hasClientIpChanged($clientIpId)
        ) {
            $this->sessionManager->logout();
            $this->isLoggedIn = false;
            return;
        }

        $this->isLoggedIn = true;
        $this->sessionManager->extendSession();
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
     * @param string $remoteIp   Remote client IP address
     * @param string $clientIpId Client IP address identifier
     * @param string $login      Username
     * @param string $password   Password
     *
     * @return bool true if the provided credentials are valid, false otherwise
     */
    public function checkCredentials($remoteIp, $clientIpId, $login, $password)
    {
        $hash = sha1($password . $login . $this->configManager->get('credentials.salt'));

        if ($login != $this->configManager->get('credentials.login')
            || $hash != $this->configManager->get('credentials.hash')
        ) {
            logm(
                $this->configManager->get('resource.log'),
                $remoteIp,
                'Login failed for user ' . $login
            );
            return false;
        }

        $this->sessionManager->storeLoginInfo($clientIpId);
        logm(
            $this->configManager->get('resource.log'),
            $remoteIp,
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
