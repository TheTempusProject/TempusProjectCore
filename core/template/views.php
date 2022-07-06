<?php
/**
 * core/template/views.php
 *
 * This class is for managing template views.
 *
 * @version 3.0
 * @author  Joey Kimsey <Joey@thetempusproject.com>
 * @link    https://TheTempusProject.com/Core
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */
namespace TempusProjectCore\Template;

use TempusProjectCore\Template;
use TempusProjectCore\Classes\Routes;
use TempusProjectCore\Functions\Debug;
use TempusProjectCore\Classes\CustomException;

class Views extends Template
{
    public static $additionalLocations = array();

	/**
     * This function adds a standard view to the main {CONTENT}
     * section of the page.
     *
     * @param  string   $viewName - The name of the view being called.
     * @param  wild     $data - Any data to be used with the view.
     *
     * @todo  add a check to viewName
     */
    public static function view($viewName, $data = null)
    {
        if (!empty($data)) {
            $out = self::standardView($viewName, $data);
        } else {
            $out = self::standardView($viewName);
        }
        if (!empty($out)) {
            self::$content .= $out;
        } else {
            new CustomException('view', $viewName);
        }
    }

    /**
     * Returns a completely parsed view.
     *
     * NOTE: Results will contain raw HTML.
     *
     * @param {string} [$view] - The name of the view you wish to call.
     * @param {var} [$data] - Any data to be used by the view.
     * @return {string} HTML view.
     */
    public static function standardView($view, $data = null)
    {
        Debug::log("Calling Standard: $view");
        // all views start with lowercase letters
        $lowerCase = lcfirst( $view );
        // convert ., \, and /, to DIRECTORY_SEPARATOR
        $normalized = str_replace( '.', DIRECTORY_SEPARATOR, $lowerCase );
        $normalized = str_replace( '\\', DIRECTORY_SEPARATOR, $normalized );
        $normalized = str_replace( '/', DIRECTORY_SEPARATOR, $normalized );
        // trim any hanging DIRECTORY_SEPARATOR (shouldn't be necessary)
        $trimmed = rtrim( $normalized, DIRECTORY_SEPARATOR );
        // add the html extension
        $viewName = $trimmed . '.html';

        // check the main views directory
        $path = VIEW_DIRECTORY . $viewName;
        Debug::log("Trying location: $path");
        if (is_file($path)) {
            if (!empty($data)) {
                return self::parse(file_get_contents($path), $data);
            } else {
                return self::parse(file_get_contents($path));
            }
        }

        // if the first part of the view name matches the name of an additionalLocation Index, we check there too
        $exploded = explode(DIRECTORY_SEPARATOR, $viewName);
        $potentialKey = array_shift($exploded);
        $imploded = implode(DIRECTORY_SEPARATOR,$exploded);
        Debug::log("Trying potentialKey: $potentialKey");
        Debug::log("Trying imploded: $imploded");
        Debug::log("additionalLocations: " . var_export(self::$additionalLocations,true));
        if (!empty(self::$additionalLocations[$potentialKey])) {
            $path = self::$additionalLocations[$potentialKey] . $imploded;
            Debug::log("Trying path: $path");
        }
        if (is_file($path)) {
            Debug::log("WINNER path: $path");
            if (!empty($data)) {
                return self::parse(file_get_contents($path), $data);
            } else {
                return self::parse(file_get_contents($path));
            }
        }
        throw new CustomException('standardView', $path);
        return false;
    }

    public static function addViewLocation($name, $location) {
        self::$additionalLocations[$name] = $location;
        return;
    }

    public static function removeViewLocation() {
        return;
    }
}