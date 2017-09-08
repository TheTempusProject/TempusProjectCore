<?php
/**
 * Classes/Cookie.php.
 *
 * This class is used for manipulation of cookies used by the application.
 *
 * @version 0.9
 *
 * @author  Joey Kimsey <joeyk4816@gmail.com>
 *
 * @link    https://github.com/JoeyK4816/tempus-project-core
 *
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */

namespace TempusProjectCore\Classes;

class Cookie
{
    /**
     * Checks whether $data is a valid saved cookie or not.
     *
     * @param string $name - Name of the cookie to check for.
     *
     * @return bool
     */
    public static function exists($data)
    {
        if (!Check::data_title($data)) {
            return false;
        }
        if (isset($_COOKIE[$data])) {
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
     *
     * @return bool|string - String from the requested cookie, or false if the cookie does not exist.
     */
    public static function get($data)
    {
        if (!Check::data_title($data)) {
            return false;
        }
        if (Self::exists($data)) {
            return $_COOKIE[$data];
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
     *
     * @return bool returns true or false based on completion
     */
    public static function put($name, $value, $expire = null)
    {
        if (!Check::data_title($name)) {
            return false;
        }
        if (!$expire) {
            $expire = time() + Config::get('remember/cookie_expiry');
        }
        if (!Check::ID($expire)) {
            return false;
        }
        setcookie($name, $value, $expire, '/');
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
        if (!Check::data_title($data)) {
            return false;
        }
        setcookie($data, '', (time() - 1), '/');
        Debug::log("Cookie deleted: $data");

        return true;
    }
}
