<?php
/**
 * functions/routes.php
 *
 * This class is used to return file and directory locations.
 *
 * @version 3.0
 * @author  Joey Kimsey <Joey@thetempusproject.com>
 * @link    https://TheTempusProject.com/Core
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */
namespace TempusProjectCore\Functions;

class Routes
{
    /**
     * Finds the root directory of the application.
     *
     * @return string - The applications root directory.
     */
    public static function getRoot()
    {
        $fullArray = explode('/', $_SERVER['PHP_SELF']);
        array_pop($fullArray);
        $route = implode('/', $fullArray) . '/';
        return $route;
    }

    /**
     * finds the physical location of the application
     *
     * @return string - The root file location for the application.
     */
    public static function getAddress()
    {
        return self::getProtocol() . "://" . $_SERVER['HTTP_HOST'] . self::getRoot();
    }

    /**
     * Determines if the server is using a secure transfer protocol or not.
     *
     * @return string - The string representation of the server's transfer protocol
     */
    public static function getProtocol()
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return 'https';
        }
        if ($_SERVER['SERVER_PORT'] == 443) {
            return 'https';
        }
        return 'http';
    }
}
