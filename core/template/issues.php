<?php
/**
 * core/template/issues.php
 *
 * This class is for managing template issues.
 *
 * @version 3.0
 * @author  Joey Kimsey <Joey@thetempusproject.com>
 * @link    https://TheTempusProject.com/Core
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */
namespace TempusProjectCore\Template;

use TempusProjectCore\Functions\Session;
use TempusProjectCore\Functions\Sanitize;
use TempusProjectCore\Template;

class Issues
{
    private static $hasIssues = false;
    private static $success = array();
    private static $notice = array();
    private static $error = array();
    private static $info = array();

    /**
     * Check for issues stored in sessions and add them to current issues.
     */
    public static function checkSessions()
    {
        $success = Session::flash('success');
        $notice = Session::flash('notice');
        $error = Session::flash('error');
        $info = Session::flash('info');
        if (!empty($success)) {
            self::add('success', $success);
        }
        if (!empty($notice)) {
            self::add('notice', $notice);
        }
        if (!empty($error)) {
            self::add('error', $error);
        }
        if (!empty($info)) {
            self::add('info', $info);
        }
    }

    /**
     * Returns the prepared success message html.
     *
     * @return {string}
     */
    public static function add($type, $messages, $parse = true)
    {
        if (!is_array($messages)) {
            $messages = array($messages);
        }
        foreach ($messages as $key => $message) {
            $message = Sanitize::rich($message);
            if ($parse) {
                $message = Template::parse($message);
            }
            self::$type($message);
        }
    }

    /**
     * Adds a success message to the issues list.
     *
     * @param  {string} [$message]
     */
    private static function success($message)
    {
        self::$hasIssues = true;
        self::$success[] = $message;
    }

    /**
     * Adds a warning message to the issues list.
     *
     * @param  {string} [$message]
     */
    private static function notice($message)
    {
        self::$hasIssues = true;
        self::$notice[] = $message;
    }

    /**
     * Adds an info message to the issues list.
     *
     * @param  {string} [$message]
     */
    private static function info($message)
    {
        self::$hasIssues = true;
        self::$info[] = $message;
    }

    /**
     * Adds an error message to the issues list.
     *
     * @param  {string} [$message]
     */
    private static function error($message)
    {
        self::$hasIssues = true;
        self::$error[] = $message;
    }

    /**
     * This is the function that tells the application if we have
     * have any messages to display or not.
     *
     * @return {bool}
     */
    public static function hasIssues()
    {
        return self::$hasIssues;
    }

    /**
     * Returns the success message array.
     *
     * @return {string}
     */
    public static function getSuccessMessages()
    {
        return self::$success;
    }

    /**
     * Returns the warning message array.
     *
     * @return {string}
     */
    public static function getNoticeMessages()
    {
        return self::$notice;
    }

    /**
     * Returns the error message array.
     *
     * @return {string}
     */
    public static function getErrorMessages()
    {
        return self::$error;
    }

    /**
     * Returns the info message array.
     *
     * @return {string}
     */
    public static function getInfoMessages()
    {
        return self::$info;
    }
}
