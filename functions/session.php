<?php
/**
 * functions/session.php
 *
 * This class is used for management of session data.
 *
 * @todo  check all the inputs
 *
 * @version 3.0
 * @author  Joey Kimsey <Joey@thetempusproject.com>
 * @link    https://TheTempusProject.com/Core
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */
namespace TempusProjectCore\Functions;

use TempusProjectCore\Classes\Config;

class Session
{
    /**
     * Checks if a session named '$name' exists.
     *
     * @param string $name - The name of the session being checked for.
     * @return boolean
     */
    public static function exists($name)
    {
        $sessionName = DEFAULT_SESSION_PREFIX . $name;
        if (isset($_SESSION[$sessionName])) {
            return true;
        }
        //Debug::error("Session::exists - Session not found: $sessionName");

        return false;
    }

    /**
     * Retrieves the value of a session named '$data' if it exists
     *
     * @param string $data - The name of the session variable you are trying to retrieve.
     * @return string|bool - Returns the data from the session or false if nothing is found..
     */
    public static function get($data)
    {
        if (self::exists($data)) {
            $sessionName = DEFAULT_SESSION_PREFIX . $data;
            //Debug::error("Session::get - $sessionName => " . $_SESSION[$sessionName]);
            return $_SESSION[$sessionName];
        }
        //Debug::error("Session::get - Session not found: $sessionName");
        return false;
    }

    /**
     * Creates a session.
     *
     * @param string $name - Session name.
     * @param string $data - Session data.
     * @return boolean     - Returns the session creation for $name and $data.
     */
    public static function put($name, $data)
    {
        $sessionName = DEFAULT_SESSION_PREFIX . $name;
        $_SESSION[$sessionName] = $data;
        //Debug::warn("Session: Created: $sessionName");
        //Debug::error($_SESSION[$sessionName]);

        return true;
    }

    /**
     * Deletes the specified session.
     *
     * @param string $data - The name of the session to be destroyed.
     * @return boolean
     */
    public static function delete($data)
    {
        if (self::exists($data)) {
            $sessionName = DEFAULT_SESSION_PREFIX . $data;
            unset($_SESSION[$sessionName]);
            //Debug::error("Session::delete: $sessionName");

            return true;
        }
        //Debug::error("Session::delete - Session not found.");

        return false;
    }

    /**
     * Intended as a self-destruct session. If the specified session does not
     * exist, it is created. If the specified session does exist, it will be
     * destroyed and returned.
     *
     * @param string $name   - Session name to be created or checked
     * @param string $string - The string to be used if session needs to be created. (optional)
     * @return bool|string   - Returns bool if creating, and a string if the check is successful.
     */
    public static function flash($name, $string = null)
    {
        if (!empty($string)) {
            self::put($name, $string);
            //Debug::error("Session::flash - Session created.");
            return true;
        }
        if (self::exists($name)) {
            //Debug::error("Session::flash - Exists");
            $session = self::get($name);
            self::delete($name);
            return $session;
        }
        //Debug::error("Session::flash - null return");
        return;
    }
}
