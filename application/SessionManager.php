<?php
namespace Shaarli;

/**
 * Manages the server-side session
 */
class SessionManager
{
    /** Session expiration timeout, in seconds */
    public static $INACTIVITY_TIMEOUT = 3600;

    /** Name of the cookie set after logging in **/
    public static $LOGGED_IN_COOKIE = 'shaarli_staySignedIn';

    /** Local reference to the global $_SESSION array */
    protected $session = [];

    /** ConfigManager instance **/
    protected $conf = null;

    /**
     * Constructor
     *
     * @param array         $session The $_SESSION array (reference)
     * @param ConfigManager $conf    ConfigManager instance
     */
    public function __construct(& $session, $conf)
    {
        $this->session = &$session;
        $this->conf = $conf;
    }

    /**
     * Generates a session token
     *
     * @return string token
     */
    public function generateToken()
    {
        $token = sha1(uniqid('', true) .'_'. mt_rand() . $this->conf->get('credentials.salt'));
        $this->session['tokens'][$token] = 1;
        return $token;
    }

    /**
     * Checks the validity of a session token, and destroys it afterwards
     *
     * @param string $token The token to check
     *
     * @return bool true if the token is valid, else false
     */
    public function checkToken($token)
    {
        if (! isset($this->session['tokens'][$token])) {
            // the token is wrong, or has already been used
            return false;
        }

        // destroy the token to prevent future use
        unset($this->session['tokens'][$token]);
        return true;
    }

    /**
     * Validate session ID to prevent Full Path Disclosure.
     *
     * See #298.
     * The session ID's format depends on the hash algorithm set in PHP settings
     *
     * @param string $sessionId Session ID
     *
     * @return true if valid, false otherwise.
     *
     * @see http://php.net/manual/en/function.hash-algos.php
     * @see http://php.net/manual/en/session.configuration.php
     */
    public static function checkId($sessionId)
    {
        if (empty($sessionId)) {
            return false;
        }

        if (!$sessionId) {
            return false;
        }

        if (!preg_match('/^[a-zA-Z0-9,-]{2,128}$/', $sessionId)) {
            return false;
        }

        return true;
    }

    /**
     * Store user login information after a successful login
     *
     * @param array $server The global $_SERVER array
     */
    public function storeLoginInfo($server)
    {
        // Generate unique random number (different than phpsessionid)
        $this->session['uid'] = sha1(uniqid('', true) . '_' . mt_rand());
        $this->session['ip'] = client_ip_id($server);
        $this->session['username'] = $this->conf->get('credentials.login');
        $this->session['expires_on'] = time() + self::$INACTIVITY_TIMEOUT;
    }

    /**
     * Logout a user by unsetting all login information
     *
     * See:
     * - https://secure.php.net/manual/en/function.setcookie.php
     *
     * @param string $webPath path on the server in which the cookie will be available on
     */
    public function logout($webPath)
    {
        if (isset($this->session)) {
            unset($this->session['uid']);
            unset($this->session['ip']);
            unset($this->session['username']);
            unset($this->session['visibility']);
            unset($this->session['untaggedonly']);
        }
        setcookie(self::$LOGGED_IN_COOKIE, 'false', 0, $webPath);
    }
}
