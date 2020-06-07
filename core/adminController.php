<?php
/**
 * core/adminController.php
 *
 * This is the main admin controller. Every other admin controller should 
 * ecxtend this controller.
 *
 * @version 2.1
 * @author  Joey Kimsey <JoeyKimsey@thetempusproject.com>
 * @link    https://TheTempusProject.com
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */
namespace TempusProjectCore\Core;

use TempusProjectCore\Classes\Debug;
use TempusProjectCore\Classes\Issue;

class AdminController extends Controller
{
    protected static $group;
    protected static $log;
    protected static $message;
    protected static $session;
    protected static $user;

    public function __construct()
    {
        Debug::log('Controller Constructing: ' . get_class($this));
        self::$template->noFollow();
        self::$template->noIndex();
        self::$session = $this->model('sessions');
        if (!self::$isLoggedIn) {
            Issue::notice('You must be logged in to view this page.');
            exit();
        }
        if (!self::$isAdmin) {
            Issue::error('You do not have permission to view this page.');
            exit();
        }
        self::$template->setTemplate('admin');
    }

    public function __destruct()
    {
        Debug::log('Controller Destructing: ' . get_class($this));
        self::$session->updatePage(self::$title);
        $this->build();
        Debug::closeAllGroups();
    }
}