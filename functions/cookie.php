<?php
/**
 * functions/cookie.php
 *
 * This class is used for manipulation of cookies.
 *
 * @version 3.0
 * @author  Joey Kimsey <Joey@thetempusproject.com>
 * @link    https://TheTempusProject.com/Core
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */
namespace TempusProjectCore\Functions;

class Cookie
{
    /**
     * Checks whether $data is a valid saved cookie or not.
     *
     * @param string $data - Name of the cookie to check for.
     * @return boolean
     */
    public static function exists($data)
    {
        if (!Check::dataTitle($data)) {
            return false;
        }
        $cookieName = DEFAULT_COOKIE_PREFIX . $data;
        if (isset($_COOKIE[$cookieName])) {
            Debug::log("Cookie found: $data");

            return true;
        }
        Debug::info("Cookie not found: $data");

        return false;
    }

    /**
     * Returns a specific cookie if it exists.
     *
     * @param string $data - Cookie to retrieve data from.
     * @return bool|string - String from the requested cookie, or false if the cookie does not exist.
     */
    public static function get($data)
    {
        if (!Check::dataTitle($data)) {
            return false;
        }
        if (self::exists($data)) {
            $cookieName = DEFAULT_COOKIE_PREFIX . $data;
            return $_COOKIE[$cookieName];
        }

        return false;
    }

    /**
     * Create cookie function.
     *
     * @param string $name   - Cookie name.
     * @param string $value  - Cookie value.
     * @param int    $expiry - How long (in seconds) until the cookie
     *                       should expire. Config default used if
     *                       none specified.
     * @return bool returns true or false based on completion
     */
    public static function put($name, $value, $expire = null)
    {
        if (!Check::dataTitle($name)) {
            return false;
        }
        if (!$expire) {
            $expire = time() + DEFAULT_COOKIE_EXPIRATION;
        }
        if (!Check::ID($expire)) {
            return false;
        }
        $cookieName = DEFAULT_COOKIE_PREFIX . $name;
        setcookie($cookieName, $value, $expire, '/');
        Debug::log("Cookie Created: $name till $expire");

        return true;
    }

    /**
     * Delete cookie function.
     *
     * @param string $data - Name of cookie to be deleted.
     */
    public static function delete($data)
    {
        if (!Check::dataTitle($data)) {
            return false;
        }
        $cookieName = DEFAULT_COOKIE_PREFIX . $data;
        setcookie($cookieName, '', (time() - 1), '/');
        Debug::log("Cookie deleted: $data");

        return true;
    }
}
