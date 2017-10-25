<?php
namespace Shaarli;

/**
 * Manages the server-side session
 */
class SessionManager
{
    protected $session = [];

    /**
     * Constructor
     *
     * @param array         $session The $_SESSION array (reference)
     * @param ConfigManager $conf    ConfigManager instance (reference)
     */
    public function __construct(& $session, & $conf)
    {
        $this->session = &$session;
        $this->conf = &$conf;
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
}
