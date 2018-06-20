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
    protected $controllerNamespace = null;

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
     * @param string $url     - A custom URL to be parsed to determine
     *                                controller/method. (GET) url is used by
     *                                default if none is provided
     */
    public function __construct($url = false)
    {
        Debug::group('TPC Application');
        Debug::log("Class Initiated: " . __CLASS__);

        // Set Default Controller Location
        $this->path = Routes::getLocation('controllers')->folder;

        // Set the application url to be used
        if ($url !== false) {
            $this->directed = true;
        }
        $this->url = Routes::parseUrl($url);

        // Set the controller default
        $this->getController();
        $this->setController();

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
        if (empty($this->url[1])) {
            Debug::info('No Method Specified');
            return $this->methodName;
        }
        if (method_exists($this->controllerNamespace, $this->url[1])) {
            Debug::log("Modifying the method from $this->methodName to " . $this->url[1]);
            $this->methodName = strtolower($this->url[1]);
            return $this->methodName;
        }
        Debug::info('Method not found: ' . $this->url[1] . ', loading default.');
        return $this->methodName;
    }

    private function setController($name = null)
    {
        if (empty($name)) {
            define('CORE_CONTROLLER', $this->controllerName);
            $this->controllerNamespace = (string) APP_SPACE . '\\Controllers\\' . $this->controllerName;
            return true;
        }
        define('CORE_CONTROLLER', $name);
        $this->controllerNamespace = (string) APP_SPACE . '\\Controllers\\' . $name;
        return true;
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
        if (file_exists(Routes::getFull() . $this->url[0])) {
            $this->path = $this->path . $this->url[0] . '/';
            array_shift($this->url);
            Debug::Info('Modifying controller location to: ' . $this->path);
        }
        if ($this->directed) {
            if (file_exists(Routes::getFull() . $this->url[0] . '.php')) {
                Debug::log("Modifying the controller from $this->controllerName to " . $this->url[0]);
                $this->path = Routes::getFull();
                $this->controllerName = strtolower($this->url[0]);
                return $this->controllerName;
            }
        }
        if (file_exists($this->path . $this->url[0] . '.php')) {
            Debug::log("Modifying the controller from $this->controllerName to " . $this->url[0]);
            $this->controllerName = strtolower($this->url[0]);
            return $this->controllerName;
        }
        Debug::info('Could not locate specified controller: ' . $this->url[0]);
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
        $this->controllerObject = new $this->controllerNamespace;
        Debug::gend();
    }
    private function getParams()
    {
        $url = $this->url;
        if (!empty($url[0])) {
            // remove the controller
            array_shift($url);
        }
        if (!empty($url[0])) {
            // remove the method
            array_shift($url);
        }
        $out = !empty($url[0]) ? array_values($url) : [];
        return $out;
    }
    /**
     * This function calls the application method/function from the
     * controllerObject.
     */
    private function loadMethod()
    {
        $this->params = $this->getParams();
        Debug::group("Initiating method : $this->methodName", 1);
        call_user_func_array([$this->controllerObject, $this->methodName], $this->params);
        Debug::gend();
    }
}
