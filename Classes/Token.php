<?php
/**
 * Classes/Token.php.
 *
 * This class handles our form tokens, a small addition to help prevent XSS attacks.
 *
 * @version 0.9
 *
 * @author  Joey  Kimsey <joeyk4816@gmail.com>
 *
 * @link    https://github.com/JoeyK4816/tempus-project-core
 *
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */

namespace TempusProjectCore\Classes;

class Token
{
    private static $_prev_token = null;
    private static $_token_name = null;

    /**
     * Creates a token and stores it as a session variable.
     *
     * @return string - Returns the string of the token generated.
     */
    public static function generate()
    {
        if (empty(Self::$_token_name)) {
            Self::$_token_name = Config::get('session/token_name');
        }
        if (empty(Self::$_prev_token)) {
            Self::$_prev_token = Session::get(Self::$_token_name);
            Debug::info('First token saved: '.Self::$_prev_token);
        }
        $token = Code::new_token();
        Debug::log("Token Created: $token");
        Session::put(Self::$_token_name, $token);

        return $token;
    }

    /**
     * Checks a form token against a session token to confirm no XSS has occurred.
     *
     * @param string $token - This should be a post variable from the hidden token field.
     *
     * @return bool - Returns a boolean and deletes the token if successful.
     */
    public static function check($token)
    {
        if (!empty(Self::$_prev_token)) {
            if ($token === Self::$_prev_token) {
                Debug::log('Token Check Passed');
                
                return true;
            }
        } elseif ($token === Session::get(Self::$_token_name)) {
            Debug::log('Token Check Passed');
            
            return true;
        }
        Debug::info('Token Check Failed');

        return false;
    }
}
