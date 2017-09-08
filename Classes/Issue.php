<?php
/**
 * Classes/Issue.php.
 *
 * This class is used to parse, store, and return application feedback for the front end.
 *
 * @version 0.9
 *
 * @author  Joey  Kimsey <joeyk4816@gmail.com>
 *
 * @link    https://github.com/JoeyK4816/tempus-project-core
 *
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 *
 * @todo Check and filter all inputs.
 */

namespace TempusProjectCore\Classes;

use TempusProjectCore\Core\Template as Template;

class Issue
{
    private static $_success = null;
    private static $_error = null;
    private static $_notice = null;
    private static $_ui = false;

    /**
     * This is the function that allows us to issue an error message to 
     * be used by the template engine before final rendering.
     * 
     * NOTE: Errors may interfere with execution of the 
     * application but shall not be issued by core failures 
     * which shall be handled by the Custom_Exception class.
     * 
     * @param  string $data The message to be issued.
     * 
     * @return NULL
     */
    public static function error($data)
    {
        Self::$_ui = true;
        if (!isset(Self::$_error))
        {
            Self::$_error = '<div class="alert alert-danger" role="alert">';
        } else {
            Self::$_error = str_replace('</div>', '<br>', Self::$_error);
        }
        $output = Template::parse(Sanitize::rich($data));
        Self::$_error .= $output.'</div>';
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
        Self::$_ui = true;
        if (!isset(Self::$_notice))
        {
            Self::$_notice = '<div class="alert alert-warning" role="alert">';
        } else {
            Self::$_notice = str_replace("</div>", "<br>", Self::$_notice);
        }
        $output = Template::parse(Sanitize::rich($data));
        Self::$_notice .= $output . "</div>";
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
        Self::$_ui = true;
        if (!isset(Self::$_success))
        {
            Self::$_success = '<div class="alert alert-success" role="alert">';
        } else {
            Self::$_success = str_replace("</div>", "<br>", Self::$_success);
        }
        $output = Template::parse(Sanitize::rich($data));
        Self::$_success .= $output . "</div>";
    }

    /**
     * This is the function to return the prepared warning messages.
     * 
     * @return string
     */
    public static function GetNotice()
    {
        return Self::$_notice;
    }

    /**
     * This is the function that tells the application if we have
     * have any messages to display or not.
     * 
     * @return string
     */
    public static function GetUI()
    {
        return Self::$_ui;
    }

    /**
     * This is the function to return the prepared success messages.
     * 
     * @return string
     */
    public static function GetSuccess()
    {
        return Self::$_success;
    }

    /**
     * This is the function to return the prepared error messages.
     * 
     * @return string
     */
    public static function GetError()
    {
        return Self::$_error;
    }
}