<?php
/**
 * Classes/Input.php
 *
 * This class manages and returns GET, FILE, and POST variables.
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

class Input
{
    /**
     * Checks to see if input exists in the order of POST, GET, FILE.
     * A default name value of "submit" is used if none is specified.
     *
     * @param string $data - Name of the desired input (default: 'submit')
     *
     * @return boolean
     */
    public static function exists($data = 'submit')
    {
        if (self::post($data)) {
            return true;
        } elseif (self::get($data)) {
            return true;
        } elseif (self::file($data)) {
            return true;
        } else {
            Debug::info('Input::exists: No input Found');

            return false;
        }
    }

    /**
     * Checks for a files existence and that it is not null
     * then returns its value or bool false if none is found
     *
     * @param string $data - Name of desired $_FILES value.
     *
     * @return boolean|string - Returns false if not found and a string if found.
     */
    public static function file($data)
    {
        if (!isset($_FILES[$data])) {
            Debug::info("Input - file : $data not found.");

            return false;
        }
        if ($_FILES[$data]['tmp_name'] == "") {
            Debug::info("Input - file : $data empty.");

            return false;
        }

        return $_FILES[$data];
    }

    /**
     * Checks for a post variable named $data and returns
     * its value if true or bool false if none is found.
     *
     * @param string $data - Name of desired $_POST value.
     *
     * @return boolean|string - Returns false if not found and a string if found.
     */
    public static function post($data)
    {
        if (!isset($_POST[$data])) {
            Debug::info("Input - post : $data not found.");

            return false;
        }
        if (empty($_POST[$data])) {
            Debug::info("Input - post : $data empty.");

            return false;
        }

        return $_POST[$data];
    }

    /**
     * Checks for a post variable named $data and returns
     * its value if found or null if not found.
     *
     * @param string $data - Name of desired $_POST value.
     *
     * @return string
     */
    public static function postNull($data)
    {
        if (!isset($_POST[$data])) {
            Debug::info("Input - post : $data not found.");

            return;
        }
        if (empty($_POST[$data])) {
            Debug::info("Input - post : $data empty.");

            return;
        }

        return $_POST[$data];
    }

    /**
     * Checks for a get variable named $data.
     *
     * @param string $data - Name of desired $_GET value.
     *
     * @return boolean|string - Returns false if not found and a string if found.
     */
    public static function get($data)
    {
        if (!isset($_GET[$data])) {
            Debug::info("Input - get : $data not found.");

            return false;
        }
        if (empty($_GET[$data])) {
            Debug::info("Input - get : $data empty.");

            return false;
        }

        return $_GET[$data];
    }
}
