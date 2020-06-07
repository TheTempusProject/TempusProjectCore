<?php
/**
 * Core/Controller.php
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

class Controller
{
    /////////////////////////
    // Meta-data Variables //
    /////////////////////////
    protected static $pageDescription = null;
    protected static $title = null;

    ///////////////////////////
    // Main Config Variables //
    ///////////////////////////
    protected static $sessionPrefix = null;
    protected static $cookiePrefix = null;
    public static $location = null;
    public static $base = null;

    ////////////////////////
    // Common Use Objects //
    ////////////////////////
    protected static $template = null;
    protected static $content = null;
    protected static $db = null;

    /////////////////////////
    // Main User Variables //
    /////////////////////////
    protected static $activePerms = null;
    protected static $activePrefs = null;

    /**
     * This is the constructor, we use this to populate some of our system
     * variables needed for the application like; initiating the DB, loading
     * the Template class, and storing any Issues from previous sessions
     */
    public function __construct()
    {
        Debug::group("Controller Constructor", 1);
        Issue::checkSessions();
        Debug::warn('Requested URL: ' . Routes::getUrl());
        self::$base = Routes::getAddress();
        self::$location = Routes::getFull();
        self::$cookiePrefix = Config::get('cookie/cookiePrefix');
        self::$sessionPrefix = Config::get('session/sessionPrefix');
        self::$db = DB::getInstance();
        self::$template = new Template();
        self::$activeGroup = json_decode(file_get_contents(Routes::getLocation('permissionsDefault')->fullPath), true);
        self::$activePrefs = json_decode(file_get_contents(Routes::getLocation('preferencesDefault')->fullPath));
        Debug::gend();
    }

    /**
     * This is the build function. Here we set the final template variables
     * before we render the entire page to the end user.
     */
    protected function build()
    {
        Debug::info("Controller: Build Call");
        self::$template->addFilter('ui', '#{UI}(.*?){/UI}#is', (Issue::getUI() ? '$1' : ''), true);
        self::$template->set('CONTENT', self::$content);
        self::$template->set('TITLE', self::$title);
        self::$template->set('PAGE_DESCRIPTION', self::$pageDescription);
        self::$template->set('NOTICE', Issue::getNotice());
        self::$template->set('SUCCESS', Issue::getSuccess());
        self::$template->set('ERROR', Issue::getError());
        self::$template->set('INFO', Issue::getInfo());
        self::$template->render();
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
