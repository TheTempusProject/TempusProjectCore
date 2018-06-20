<?php
/**
 * App.php
 *
 * This file parses any given url and separates it into controller,
 * method, and data. This allows the application to direct the user
 * to the desired location and provide the controller any additional
 * information it may require to run.
 *
 * @version 1.0
 *
 * @author  Joey Kimsey <JoeyKimsey@thetempusproject.com>
 *
 * @link    https://TheTempusProject.com/Core
 *
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */

namespace TempusProjectCore;

use TempusProjectCore\Classes\Debug as Debug;
use TempusProjectCore\Classes\Config as Config;
use TempusProjectCore\Functions\Routes as Routes;
use TempusProjectCore\Classes\Input as Input;

class App
{
    //Default Controller
    protected $controllerName = 'home';

    //Default Method
    protected $methodName = 'index';

    protected $path = null;
    protected $directed = false;
    protected $params = [];
    protected $url = null;

    /**
     * The constructor handles the entire process of parsing the url,
     * finding the controller/method, and calling the appropriate
     * class/function for the application.
     *
     * @param string $urlDirected     - A custom URL to be parsed to determine
     *                                controller/method. (GET) url is used by
     *                                default if none is provided
     */
    public function __construct($urlDirected = null)
    {
        Debug::group('TPC Application');
        Debug::log("Class Initiated: " . __CLASS__);

        // Set Default Controller Location
        $this->path = Routes::getLocation('controllers')->folder;

        // Set the application url to be used
        if (!empty($urlDirected)) {
            $this->directed = true;
            $this->url = Routes::parseUrl($urlDirected);
        } else {
            $this->url = Routes::parseUrl();
        }

        // Find the Controller
        $this->controllerName = $this->getController();
        define('CORE_CONTROLLER', $this->controllerName);
        $this->controllerNameFull = (string) APP_SPACE . '\\Controllers\\' . $this->controllerName;

        // Ensure the controller is required
        Debug::log("Requiring Controller: $this->controllerName");
        require $this->path . $this->controllerName . '.php';

        // Find the Method
        $this->methodName = $this->getMethod();
        define('CORE_METHOD', $this->methodName);

        /////////////////////////////////////////////////////////////////
        // Load the appropriate Controller and Method which initiates  //
        // the dynamic part of the application.                        //
        /////////////////////////////////////////////////////////////////
        $this->loadController();
        $this->loadMethod();
        Debug::gend();
    }

    /**
     * This is used to determine the method to be called in the controller class.
     *
     * NOTE: If $url is set, this function will automatically remove the first
     * segment of the array regardless of whether or not it found the specified
     * method.
     *
     * @return string   - The method name to be used by the application.
     */
    private function getMethod()
    {
        if (empty($this->url[0])) {
            Debug::info('No Method Specified');
            return $this->methodName;
        }
        if (method_exists($this->controllerNameFull, $this->url[0])) {
            Debug::log("Modifying the method from $this->methodName to " . $this->url[0]);
            $out = array_shift($this->url);
            return strtolower($out);
        }
        Debug::info('Method not found: ' . $this->url[0] . ', loading default.');
        array_shift($this->url);
        return $this->methodName;
    }

    /**
     * Using the $url array, this function will define the controller
     * name and path to be used. If the $urlDirected flag was used,
     * the first location checked is the indexPath. If this does
     * not exist, it will default back to the Controllers folder to search
     * for the specified controller.
     *
     * NOTE: If $url is set, this function will automatically remove the first
     * segment of the array regardless of whether or not it found the specified
     * controller.
     *
     * @return string   - The controller name to be used by the application.
     */
    private function getController()
    {
        if (empty($this->url[0])) {
            Debug::info('No Controller Specified.');
            return $this->controllerName;
        }
        if ($this->directed) {
            if (file_exists(Routes::getFull() . $this->url[0] . '.php')) {
                Debug::log("Modifying the controller from $this->controllerName to " . $this->url[0]);
                $out = array_shift($this->url);
                $this->path = Routes::getFull();
                return strtolower($out);
            } elseif (file_exists($this->path . $this->url[0] . '.php')) {
                Debug::log("Modifying the controller from $this->controllerName to " . $this->url[0]);
                $out = array_shift($this->url);
                return strtolower($out);
            }
        }
        if (!$this->directed && file_exists($this->path . $this->url[0] . '.php')) {
            Debug::log("Modifying the controller from $this->controllerName to " . $this->url[0]);
            $out = array_shift($this->url);
            return strtolower($out);
        }
        Debug::info('Could not locate specified controller: ' . $this->url[0]);
        array_shift($this->url);
        return $this->controllerName;
    }
    private function updateController()
    {
        Debug::log("Modifying the controller from $this->controllerName to " . $this->url[0]);
    }
    /**
     * This function Initiates the specified controller and
     * stores it as an object in controllerObject.
     */
    private function loadController()
    {
        Debug::group("Initiating controller: $this->controllerName", 1);
        $this->controllerObject = new $this->controllerNameFull;
        Debug::gend();
    }

    /**
     * This function calls the application method/function from the
     * controllerObject.
     */
    private function loadMethod()
    {
        $this->params = $this->url ? array_values($this->url) : [];
        Debug::group("Initiating method : $this->methodName", 1);
        call_user_func_array([$this->controllerObject, $this->methodName], $this->params);
        Debug::gend();
    }
}
