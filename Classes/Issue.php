<?php
/**
 * Classes/Issue.php
 *
 * This class is used to parse, store, and return application feedback for the front end.
 *
 * @todo Check and filter all inputs.
 *
 * @version 1.0
 *
 * @author  Joey Kimsey <JoeyKimsey@thetempusproject.com>
 *
 * @link    https://TheTempusProject.com/Core
 *
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */

namespace TempusProjectCore\Classes;

use TempusProjectCore\Core\Template as Template;

class Issue
{
    private static $success = null;
    private static $error = null;
    private static $notice = null;
    private static $ui = false;
    private static $sessionCheck = false;
    
    /**
     * This is the function that allows us to issue an error message to
     * be used by the template engine before final rendering.
     *
     * NOTE: Errors may interfere with execution of the
     * application but shall not be issued by core failures
     * which shall be handled by the Custom_Exception class.
     *
     * @param  string $error - The message to be issued.
     *
     * @return NULL
     */
    public static function error($error, $list = null)
    {
        self::$ui = true;
        if (!isset(self::$error)) {
            self::$error = '<div class="alert alert-danger" role="alert">';
        } else {
            self::$error = str_replace('</div>', '<br>', self::$error);
        }
        $output = Template::parse(Sanitize::rich($error));
        self::$error .= $output;
        $out = null;
        if (is_array($list)) {
            $out .= "<ul>";
            foreach ($list as $key) {
                Debug::error($key);
                $out .= "<li>" . $key['errorInfo'] . "</li>";
            }
            $out .= "</ul>";
        }
        self::$error .= $out . '</div>';
    }

    /**
     * This is the function that allows us to issue a warning message to
     * be used by the template engine before final rendering.
     *
     * NOTE: Notices shall not interfere with execution of the
     * application.
     *
     * @param  string $data The message to be issued.
     *
     * @return NULL
     */
    public static function notice($data)
    {
        self::$ui = true;
        if (!isset(self::$notice)) {
            self::$notice = '<div class="alert alert-warning" role="alert">';
        } else {
            self::$notice = str_replace("</div>", "<br>", self::$notice);
        }
        $output = Template::parse(Sanitize::rich($data));
        self::$notice .= $output . "</div>";
    }

    /**
     * This is the function that allows us to issue a success message to
     * be used by the template engine before final rendering.
     *
     * @param  string $data The message to be issued.
     *
     * @return NULL
     */
    public static function success($data)
    {
        self::$ui = true;
        if (!isset(self::$success)) {
            self::$success = '<div class="alert alert-success" role="alert">';
        } else {
            self::$success = str_replace("</div>", "<br>", self::$success);
        }
        $output = Template::parse(Sanitize::rich($data));
        self::$success .= $output . "</div>";
    }

    public static function checkSessions()
    {
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
    }

    /**
     * This is the function that tells the application if we have
     * have any messages to display or not.
     *
     * @return string
     */
    public static function getUI()
    {
        return self::$ui;
    }

    /**
     * This is the function to return the prepared warning messages.
     *
     * @return string
     */
    public static function getNotice()
    {
        return self::$notice;
    }

    /**
     * This is the function to return the prepared success messages.
     *
     * @return string
     */
    public static function getSuccess()
    {
        return self::$success;
    }

    /**
     * This is the function to return the prepared error messages.
     *
     * @return string
     */
    public static function getError()
    {
        return self::$error;
    }
}
