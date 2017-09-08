<?php
/**
 * App.php
 *
 * This file parses any given url and separates it into controller, 
 * method, and data. This allows the application to direct the user 
 * to the desired location and provide the controller any additional 
 * information it may require.
 *
 * @version 0.9
 *
 * @author  Joey Kimsey <joeyk4816@gmail.com>
 *
 * @link    https://github.com/JoeyK4816/tempus-project-core
 *
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */

namespace TempusProjectCore;

use TempusProjectCore\Classes\Debug as Debug;
use TempusProjectCore\Classes\Config as Config;
use TempusProjectCore\Classes\CustomException as CustomException;
use TempusProjectCore\Classes\Input as Input;

class App
{
    //Default Controller
    protected $controller = 'home';
    //Default Method
    protected $method = 'index';
    protected $params = [];

    public function __construct()
    {
        Debug::group('Main Application');
        Debug::log("Class Initiated: " . __CLASS__);
        $path = Config::get('main/location');
        $url = $this->parseUrl();

        ////////////////
        // controller //
        ////////////////
        if (isset($url[0])) {
            if (file_exists($path.'Controllers/' . $url[0] . '.php')) {
                Debug::log("Modifying the controller from $this->controller to $url[0]");
                $this->controller = strtolower($url[0]);
                unset($url[0]);
            } else {
                 new CustomException('controller', $url[0]);
                 unset($url[0]);
            }
        }
        if (!is_file($path.'Controllers/'.$this->controller.'.php')) {
            new CustomException('default_controller');
        }
        define('CORE_CONTROLLER', $this->controller);
        Debug::log("Requiring Controller: $this->controller");
        require_once $path.'Controllers/'. $this->controller . '.php';
        $newController = APP_SPACE . "\\Controllers\\" . $this->controller;

        /////////////
        // Method: //
        /////////////
        if (isset($url[1])) {
            if (method_exists($newController, $url[1])) {
                Debug::log("Modifying the method from $this->method to $url[1]");
                $this->method = strtolower($url[1]);
                unset($url[1]);
            } else {
                new CustomException('method', $url[1]);
                unset($url[1]);
            }
        }

        if (!method_exists($newController, $this->method)) {
            new CustomException('default_method', $this->controller . "::" . $this->method);
        }
        define('CORE_METHOD', $this->method);
        $this->params = $url ? array_values($url) : [];
        Debug::log("Initiating controller: $this->controller");
        $this->controller = new $newController();
        Debug::log("Calling method : $this->method");
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    /**
     * This takes the url provided and returns it as an array.
     * 
     * @return  array   - The exploded $_GET URL.
     */
    public function parseUrl()
    {
        if (Input::get('url')) {
            return $url = explode('/', filter_var(rtrim(Input::get('url'), '/'), FILTER_SANITIZE_URL));
        }
    }
}
