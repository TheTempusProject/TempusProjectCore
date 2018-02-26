<?php
/**
 * Core/Controller.php
 *
 * The controller handles our main template and provides the
 * model and view functions which are the backbone of the tempus
 * project. Used to hold and keep track of many of the variables
 * that support the applications execution.
 *
 * @version 1.0
 *
 * @author  Joey Kimsey <JoeyKimsey@thetempusproject.com>
 *
 * @link    https://TheTempusProject.com/Core
 *
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */

namespace TempusProjectCore\Core;

use TempusProjectCore\Classes\CustomException;
use TempusProjectCore\Classes\Pagination;
use TempusProjectCore\Functions\Docroot;
use TempusProjectCore\Classes\Session;
use TempusProjectCore\Classes\Cookie;
use TempusProjectCore\Classes\Config;
use TempusProjectCore\Classes\Check;
use TempusProjectCore\Classes\Debug;
use TempusProjectCore\Classes\Token;
use TempusProjectCore\Classes\Input;
use TempusProjectCore\Classes\Email;
use TempusProjectCore\Classes\Issue;
use TempusProjectCore\Classes\Hash;
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
    protected static $activeGroup = null;
    protected static $activePrefs = null;
    protected static $isLoggedIn = false;
    protected static $activeUser = null;
    protected static $isMember = false;
    protected static $isAdmin = false;
    protected static $isMod = false;

    /////////////////
    // TO BE MOVED //
    /////////////////
    public static $bugreport;
    public static $subscribe;
    public static $feedback;
    public static $session;
    public static $message;
    public static $comment;
    public static $group;
    public static $user;
    public static $blog;
    public static $log;

    /**
     * This is the constructor, we use this to populate some of our system
     * variables needed for the application like; initiating the DB, loading
     * the Template class, and storing any Issues from previous sessions
     */
    public function __construct()
    {
        Debug::group("Controller Constructor", 1);
        Issue::checkSessions();
        Debug::warn('Requested URL: ' . Docroot::getUrl());
        self::$base = Docroot::getAddress();
        self::$location = Docroot::getFull();
        self::$cookiePrefix = Config::get('cookie/cookiePrefix');
        self::$sessionPrefix = Config::get('session/sessionPrefix');
        self::$db = DB::getInstance();
        self::$template = new Template();
        self::$activeGroup = json_decode(file_get_contents(Docroot::getLocation('permissionsDefault')->fullPath), true);
        self::$activePrefs = json_decode(file_get_contents(Docroot::getLocation('preferencesDefault')->fullPath));
        
        /**
         * @todo  - This needs to be moved to TheTempusProject but that
         * requires more rewriting than i wanted to do in this revision
         * soooooo..... till next time folks!
         */
        self::$blog         = $this->model('blog');
        self::$bugreport    = $this->model('bugreport');
        self::$comment      = $this->model('comment');
        self::$feedback     = $this->model('feedback');
        self::$group        = $this->model('group');
        self::$log          = $this->model('log');
        self::$message      = $this->model('message');
        self::$session      = $this->model('sessions');
        self::$subscribe    = $this->model('subscribe');
        self::$user         = $this->model('user');

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
        $docLocation = Docroot::getLocation('models', $modelName);
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
