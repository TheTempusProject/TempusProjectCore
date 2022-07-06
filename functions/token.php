<?php
/**
 * functions/token.php
 *
 * This class handles form tokens.
 * 
 * @version 3.0
 * @author  Joey Kimsey <Joey@thetempusproject.com>
 * @link    https://TheTempusProject.com/Core
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */
namespace TempusProjectCore\Functions;

use TempusProjectCore\Classes\Config;

class Token
{
    private static $tokenName;
    private static $tokenSaved;
    private static $tokenEnabled = 'not_set';

    public static function start()
    {
        if ( ! self::isTokenEnabled() ) {
            return false;
        }
        if (empty(self::$tokenName)) {
            self::setTokenName();
        }
        if (empty(self::$tokenSaved)) {
            self::$tokenSaved = Session::get(self::$tokenName);
            Debug::log('First token saved: ' . Session::get(self::$tokenName));
        } else {
            Debug::log('Original token was already saved');
        }
        return true;
    }

    /**
     * Determines, saves, then returns whether or not tokens are enabled.
     *
     * @todo test this, it could return true or false incorrectly depending on values
     * 
     * @return true
     */
    public static function setTokenName( $name = '' )
    {
        if (!empty($name)) {
            if (!Check::simpleName($name)) {
                Debug::warn("Token name invalid: $name");
                return false;
            }
            self::$tokenName = $name;
        }
        if (!empty(self::$tokenName)) {
            return true;
        }
        self::$tokenName = DEFAULT_TOKEN_NAME;
        return true;
    }

    /**
     * Determines, saves, then returns whether or not tokens are enabled.
     *
     * @return bool
     */
    public static function isTokenEnabled()
    {
        if (self::$tokenEnabled !== 'not_set') {
            return self::$tokenEnabled;
        }

        $sessionCheck = Check::sessions();
        if ( $sessionCheck === false ) {
            self::$tokenEnabled = false;
            return self::$tokenEnabled;
        }

        $tokenConfig = Config::get('main/tokenEnabled');
        if (!empty($tokenConfig)) {
            self::$tokenEnabled = $tokenConfig;
            return self::$tokenEnabled;
        }

        if (!empty(TOKEN_ENABLED)) {
            self::$tokenEnabled = TOKEN_ENABLED;
            return self::$tokenEnabled;
        }

        self::$tokenEnabled = false;
        return self::$tokenEnabled;
    }

    /**
     * Creates a token and stores it as a session variable.
     *
     * @return string - Returns the string of the token generated.
     */
    public static function generate()
    {
        if ( !self::start() ) {    
            Debug::log('Token disabled');
            return false;
        }
        $token = Code::genToken();
        Session::put(self::$tokenName, $token);
        Debug::log('New token generated');
        return $token;
    }

    /**
     * Checks a form token against a session token to confirm no XSS has occurred.
     *
     * @param string $token - This should be a post variable from the hidden token field.
     * @return bool
     */
    public static function check($token)
    {
        if ( !self::start() ) {
            Debug::log('Token disabled');
            return false;
        }
        if ($token === self::$tokenSaved) {
            Debug::log('Token check passed');
            return true;
        }
        Debug::info('Token check failed');
        Debug::info('token: ' . $token);
        Debug::info('tokenSaved: ' . self::$tokenSaved);
        return false;
    }
}
