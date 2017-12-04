<?php
/**
 * Classes/Token.php
 *
 * This class handles our form tokens, a small addition to help prevent XSS attacks.
 *
 * @version 1.0
 *
 * @author  Joey Kimsey <JoeyKimsey@thetempusproject.com>
 *
 * @link    https://TheTempusProject.com/Core
 *
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */

namespace TempusProjectCore\Classes;

class Token
{
    private static $savedToken = null;
    private static $tokenName = null;
    public static function start()
    {
        if (empty(self::$tokenName) && !empty(Config::get('main/tokenName'))) {
            self::$tokenName = Config::get('main/tokenName');
        }
        if (empty(self::$savedToken)) {
            self::$savedToken = Session::get(self::$tokenName);
            Debug::log('First token saved');
        }
    }

    /**
     * Creates a token and stores it as a session variable.
     *
     * @return string - Returns the string of the token generated.
     */
    public static function generate()
    {
        self::start();
        $token = Code::genToken();
        Session::put(self::$tokenName, $token);
        Debug::log('New token generated');
        return $token;
    }

    /**
     * Checks a form token against a session token to confirm no XSS has occurred.
     *
     * @param string $token - This should be a post variable from the hidden token field.
     *
     * @return boolean - Returns a boolean and deletes the token on success.
     */
    public static function check($token)
    {
        self::start();
        if ($token === self::$savedToken) {
            Debug::log('Token check passed');

            return true;
        }
        Debug::info('Token check failed');

        return false;
    }
}
