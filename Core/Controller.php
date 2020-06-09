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
 * @author  Joey Kimsey <JoeyKimsey@thetempusproject.com>
 * @link    https://TheTempusProject.com/Core
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */
namespace TempusProjectCore\Core;

use TempusProjectCore\Classes\Debug;
use TempusProjectCore\Classes\Issue;

class Controller extends TPCore
{
    /**
     * This is the constructor, we use this to populate some of our system
     * variables needed for the application like; initiating the DB, loading
     * the Template class, and storing any Issues from previous sessions
     */
    public function __construct()
    {
        Debug::log('Controller Constructing: ' . get_class($this));
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
}
