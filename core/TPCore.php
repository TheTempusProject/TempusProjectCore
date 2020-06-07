<?php
/**
 * core/TPCore.php
 *
 * The controller handles our main template and provides the
 * model and view functions which are the backbone of the tempus
 * project. Used to hold and keep track of many of the variables
 * that support the applications execution.
 *
 * @version 2.1
 * @author  Joey Kimsey <JoeyKimsey@thetempusproject.com>
 * @link    https://TheTempusProject.com/Core
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */
namespace TempusProjectCore\Core;

use TempusProjectCore\Classes\CustomException;
use TempusProjectCore\Functions\Routes;
use TempusProjectCore\Classes\Config;
use TempusProjectCore\Classes\Debug;
use TempusProjectCore\Classes\Issue;
use TempusProjectCore\Classes\Log;
use TempusProjectCore\Classes\DB;

class TPCore
{
    /**
     * This is used to determine the method to be called in the controller class.
     *
     * NOTE: If $url is set, this function will automatically remove the first
     * segment of the array regardless of whether or not it found the specified
     * method.
     *
     * @return string   - The method name to be used by the application.
     */
    protected function getMethod()
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

    protected function setController($name = null)
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
    protected function getController()
    {
        if (file_exists($this->path . ucfirst($this->url[0]))) {
            $this->path = $this->path . $this->url[0] . '/';
            array_shift($this->url);
            Debug::Info('Modifying controller location to: ' . $this->path);
        }
        if (empty($this->url[0])) {
            Debug::info('No Controller Specified.');
            return $this->controllerName;
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

    protected function updateController()
    {
        Debug::log("Modifying the controller from $this->controllerName to " . $this->url[0]);

    }

    /**
     * This function Initiates the specified controller and
     * stores it as an object in controllerObject.
     */
    protected function loadController()
    {
        Debug::group("Initiating controller: $this->controllerName", 1);
        $this->controllerObject = new $this->controllerNamespace;
        Debug::gend();
    }
    protected function getParams()
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
    protected function loadMethod()
    {
        $this->params = $this->getParams();
        Debug::group("Initiating method : $this->methodName", 1);
        call_user_func_array([$this->controllerObject, $this->methodName], $this->params);
        Debug::gend();
    }
        /**
     * Function for initiating a new model.
     *
     * @param  wild     $data  - Any data the model may need when instantiated.
     * @param  string   $modelName - The name of the model you are calling.
     *                           ('.', and '_' are valid delimiters and the 'model_'
     *                           in the file name is not required)
     *
     * @return object          - The model object.
     */
    protected function model($modelName)
    {
        Debug::group("Model: $modelName", 1);
        $docLocation = Routes::getLocation('models', $modelName);
        if ($docLocation->error) {
            new CustomException('model', $docLocation->errorString);
        } else {
            Debug::log("Requiring model");
            require_once $docLocation->fullPath;
            Debug::log("Instantiating model");
            $model = new $docLocation->className;
        }
        Debug::gend();
        if (isset($model)) {
            return $model;
        }
    }

    /**
     * This function adds a standard view to the main {CONTENT}
     * section of the page.
     *
     * @param  string   $viewName - The name of the view being called.
     * @param  wild     $data - Any data to be used with the view.
     *
     * @todo  add a check to this and an exception
     */
    protected function view($viewName, $data = null)
    {
        if (!empty($data)) {
            $out = self::$template->standardView($viewName, $data);
        } else {
            $out = self::$template->standardView($viewName);
        }
        if (!empty($out)) {
            self::$content .= $out;
        } else {
            new CustomException('view', $viewName);
        }
    }
}
