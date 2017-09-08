<?php
/**
 * Core/Controller.php
 *
 * The controller handles our main template and provides the
 * model and view functions which are the backbone of the system.
 *
 * @version 0.9
 *
 * @author  Joey Kimsey <joeyk4816@gmail.com>
 *
 * @link    https://github.com/JoeyK4816/tempus-project-core
 *
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */

namespace TempusProjectCore\Core;

use TempusProjectCore\Classes\Debug as Debug;
use TempusProjectCore\Classes\Config as Config;
use TempusProjectCore\Classes\DB as DB;
use TempusProjectCore\Classes\Session as Session;
use TempusProjectCore\Classes\Cookie as Cookie;
use TempusProjectCore\Classes\Log as Log;
use TempusProjectCore\Classes\Check as Check;
use TempusProjectCore\Classes\Input as Input;
use TempusProjectCore\Classes\Email as Email;
use TempusProjectCore\Classes\Pagination as Pagination;
use TempusProjectCore\Classes\Issue as Issue;
use TempusProjectCore\Classes\Hash as Hash;
use TempusProjectCore\Classes\Token as Token;
use TempusProjectCore\Classes\CustomException as CustomException;

class Controller
{
    /////////////////////////////
    // Main Template Variables //
    /////////////////////////////
    public  static $_title = null;
    public  static $_page_description = null;

    ///////////////////////////
    // Main Config Variables //
    ///////////////////////////
    protected static $_cookie_name = null;
    protected static $_session_name = null;
    public static $_base = null;
    public static $_location = null;

    ////////////////////////
    // Common Use Objects //
    ////////////////////////
    protected static $_template = null;
    protected static $_user = null;
    protected static $_db = null;
    protected static $_session = null;
    protected static $_message = null;
    protected static $_subscribe = null;
    protected static $_group = null;
    protected static $_log = null;
    protected static $_blog = null;
    protected static $_comment = null;

    /////////////////////////
    // Main User Variables //
    /////////////////////////
    protected static $_active_user = null;
    protected static $_active_group = 0;
    protected static $_is_logged_in = false;
    protected static $_is_member = false;
    protected static $_is_admin = false;
    protected static $_is_mod = false;
    protected static $_active_prefs = null;
    protected static $_content = null;
    public    static $_unread = null;

    /**
     * This is the constructor, we use this to populate some of our system assets.
     */
    public function __construct()
    {
        Debug::group("Controller Constructor", 1);
        Self::$_base = Config::get('main/base');
        Self::$_location = Config::get('main/location');
        Self::$_cookie_name = Config::get('remember/cookie_name');
        Self::$_session_name = Config::get('session/session_name');
        Self::$_db = DB::getInstance();
        Self::$_template = new Template();
        $success = Session::flash('success');
        $notice = Session::flash('notice');
        $error = Session::flash('error');
        if (!empty($notice)) {
            Issue::notice($notice);
        }
        if (!empty($error)) {
            Issue::error($error);
        }
        if (!empty($success)) {
            Issue::success($success);
        }
        Debug::gend();
    }
    
    /**
     * This is the execution command. This function sets our template variables, 
     * updates our session, and renders the page with all the final content.
     */
    protected function build()
    {
        Self::$_template->add_filter('ui', '#{UI}(.*?){/UI}#is', Issue::GetUI());
        Self::$_template->set('CONTENT', Self::$_content);
        Self::$_template->set('TITLE', Self::$_title);
        Self::$_template->set('PAGE_DESCRIPTION', Self::$_page_description);
        Self::$_template->set('MBADGE', Self::$_unread);
        Self::$_template->set('NOTICE', Issue::GetNotice());
        Self::$_template->set('SUCCESS', Issue::GetSuccess());
        Self::$_template->set('ERROR', Issue::GetError());
        Self::$_template->render();
    }

    /**
     * Function for calling a new model.
     *     
     * @param  string $model - The name of the model you are calling.
     * @param  wild $data - Any data the model may need when instantiated.
     * 
     * @return object 
     */
    protected function model($model, $data = null)
    {
        $new_model = str_replace('.', '_', $model);
        $path = Self::$_location . 'Models/model_'.$new_model.'.php';
        if (is_file($path)) {
            Debug::group("Model: $new_model", 1);
            Debug::log("Requiring Model");
            require_once $path;
            $new_model_2 = APP_SPACE . "\\Models\\" . 'model_' . $new_model;
            Debug::log("Calling Model");
            return new $new_model_2($data);
        } else {
            new CustomException('model', $model);
        }
    }

    /**
     * This function adds a standard view to the main content 
     * section of the page.
     *
     * NOTE: Everything unrelated to business logic and core 
     * display should be passed through this method.
     * 
     * @param  String   $view - The name of the view being called.
     * @param  wild      $data - Any data to be used with the view.
     */
    protected function view($view, $data = null)
    {
        if (!empty($data)) {
            Self::$_content .= Self::$_template->standard_view($view, $data);
        } else {
            Self::$_content .= Self::$_template->standard_view($view);
        }
    }
}