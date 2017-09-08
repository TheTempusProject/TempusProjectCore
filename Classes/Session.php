<?php
/**
 * Classes/Session.php.
 *
 * This class is used for the modification and management of the session data.
 *
 * @version 0.9
 *
 * @author  Joey Kimsey <joeyk4816@gmail.com>
 *
 * @link    https://github.com/JoeyK4816/tempus-project-core
 *
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 *
 * @todo  check all these inputs
 */

namespace TempusProjectCore\Classes;

class Session
{
    /**
     * Checks if a session exists with the name $data.
     *
     * @param string $data - The name of the session you are checking for.
     *
     * @return bool
     */
    public static function exists($data)
    {
        if (isset($_SESSION[$data])) {
            return true;
        }
        Debug::info("Session: not found: $data");

        return false;
    }

    /**
     * Get a session variable named $data.
     *
     * @param string $data - The name of the session variable you are trying to retrieve.
     *
     * @return string|bool - Returns the data from the session or false if nothing is found..
     */
    public static function get($data)
    {
        if (Self::exists($data)) {
            return $_SESSION[$data];
        }
        Debug::info("Session: not found: $data");

        return false;
    }

    /**
     * Creates a session.
     *
     * @param string $name - Session name.
     * @param string $data - Session data.
     *
     * @return function - Returns the session creation for $name and $data.
     */
    public static function put($name, $data)
    {
        Debug::log("Session: Created: $name");
        $_SESSION[$name] = $data;

        return true;
    }

    /**
     * Deletes a session.
     *
     * @param string $data name of the session to delete
     */
    public static function delete($data)
    {
        if (Self::exists($data)) {
            Debug::log("Deleting Session: $data");
            unset($_SESSION[$data]);

            return true;
        }
        Debug::info("Session::Delete: $data - not found.");

        return false;
    }

    /**
     * Intended as a self destruct session. if it doesn't exist,
     * it is created, if it does exist it is destroyed and returned
     * by the function.
     *
     * @param string $name   - Session name to be created or checked
     * @param string $string - The string to be used if session needs to be created. (optional)
     *
     * @return bool|string - Returns bool if creating, and a string if the check is successful.
     */
    public static function flash($name, $string = null)
    {
        if (!empty($string)) {
            Self::put($name, $string);

            return true;
        }
        if (Self::exists($name)) {
            $session = Self::get($name);
            Self::delete($name);
            Debug::log("Flash session found: $name");

            return $session;
        }

        return false;
    }
}
