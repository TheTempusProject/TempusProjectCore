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

use TempusProjectCore\Classes\Debug;
use TempusProjectCore\Classes\Config;
use TempusProjectCore\Functions\Routes;
use TempusProjectCore\Classes\Input;
use TempusProjectCore\Core\TPCore;

class App extends TPCore
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
        require Routes::getLocation('controllers', $this->controllerName)->fullPath;;

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
}
