<?php
/**
 * core/controller.php
 *
 * The controller handles our main template and provides the
 * model and view functions which are the backbone of the tempus
 * project. Used to hold and keep track of many of the variables
 * that support the applications execution.
 *
 * @version 3.0
 * @author  Joey Kimsey <Joey@thetempusproject.com>
 * @link    https://TheTempusProject.com/Core
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */
namespace TempusProjectCore;

use TempusProjectCore\Functions\Debug;
use TempusProjectCore\Template\Issues;
use TempusProjectCore\Template\Components;
use TempusProjectCore\Template\Filters;

class Controller
{
    protected static $title = null;
    protected static $content = null;
    protected static $template = null;
    protected static $pageDescription = null;

    public function __construct()
    {
        Debug::log('Controller Constructing: ' . get_class($this));
        self::$template = new Template;
        Issues::checkSessions();
    }

    public function __destruct() {
        Debug::log('Controller Destructing: ' . get_class($this));
        Filters::add('issues', '#{ISSUES}(.*?){/ISSUES}#is', (Issues::hasIssues() ? '$1' : ''), true);
        $test = implode('<br>', Issues::getNoticeMessages());
        if (!empty($test)) {
            $test = '<div class="alert alert-warning" role="alert">' . $test . '</div>';
        }
        Components::set(
            'NOTICE',
            $test,
        );
        $test = implode('<br>', Issues::getSuccessMessages());
        if (!empty($test)) {
            $test = '<div class="alert alert-success" role="alert">' . $test . '</div>';
        }
        Components::set(
            'SUCCESS',
            $test,
        );
        $test = implode('<br>', Issues::getErrorMessages());
        if (!empty($test)) {
            $test = '<div class="alert alert-danger" role="alert">' . $test . '</div>';
        }
        Components::set(
            'ERROR',
            $test,
        );
        $test = implode('<br>', Issues::getInfoMessages());
        if (!empty($test)) {
            $test = '<div class="alert alert-info" role="alert">' . $test . '</div>';
        }
        Components::set(
            'INFO',
            $test,
        );
        Template::render();
    }
}
