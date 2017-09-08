<?php
/**
 * Classes/Debug.php.
 *
 * The Debug class is responsible for providing a log of relevant debugging information.
 * It has functionality to generate a log file as it goes allowing you to print it at any
 * given point in the script. It also acts as a portal for writing to a console output
 * using FirePHP.
 *
 * @version 0.9
 *
 * @author  Joey Kimsey <joeyk4816@gmail.com>
 *
 * @link    https://github.com/JoeyK4816/tempus-project-core
 *
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */

namespace TempusProjectCore\Classes;

use \FirePHP as FirePHP;
class Debug
{
    /**
     * Toggle Debugging mode on or off.
     *
     * @var bool
     */
    private static $debug_status = false;

    /**
     * Very Important, this will enable the firebug console output.
     * It only applies when debugging is enabled, or the config cannot
     * be found as a safety net.
     * 
     * @var bool
     */
    private static $console = false;
    private static $error_trace = false;
    private static $group = false;
    private static $fire = null;
    private static $debug_log = null;

    /**
     * Acts as a constructor.
     */
    private static function _start()
    {
        if (Self::$console) {
            ob_start();
            Self::$fire = FirePHP::getInstance(true);;
        }
    }

    /**
     * Returns the current Debug Status;.
     *
     * @return bool
     */
    public static function status()
    {
        return Self::$debug_status;
    }


    /**
     * This is the interface that writes to our log file/console depending on input type.
     *
     * @param string $type - Debugging type.
     * @param string $data - Debugging data.
     *
     * @todo  make a case statement
     */
    private static function put($type, $data = null, $params = null)
    {
        if (!Self::$debug_status) {
            return;
        }
        if (strlen(Self::$debug_log) > 50000) {
            Self::$fire->log('Error log too large, possible loop.');
            Self::$debug_status = false;
            return;
        }
        if (!is_object($data)) {
            Self::$debug_log .= var_export($data, true);
            Self::$debug_log .= '\n';
        } else {
            Self::$debug_log .= 'cannot save objects';
            Self::$debug_log .= '\n';
        }
        if (!Self::$console) {
            return;
        }
        if (!Self::$fire) {
            Self::_start();
        }
        switch ($type) {
            case 'variable':
                Self::$fire->info($data, $params);
                break;

            case 'group_end':
                Self::$fire->groupEnd();
                break;

            case 'trace':
                Self::$fire->trace($data);
                break;

            case 'group':
                if ($params) {
                    Self::$fire->group($data, $params);
                } else {
                    Self::$fire->group($data);
                }
                break;

            case 'info':
                Self::$fire->$type('color: #1452ff', '%c' . $data);
                break;

            default:
                Self::$fire->$type($data);
                break;
        }
    }

    /**
     * Ends a group.
     */
    public static function gend()
    {
        Self::$group = false;
        Self::put('group_end');
    }

    /**
     * Creates a group divider into the console output.
     *
     * @param string $data      name of the group
     * @param wild   $collapsed if anything is present the group will be collapsed by default
     */
    public static function group($data, $collapsed = null)
    {
        if (!empty($collapsed)) {
            $params = array('Collapsed' => true);
            Self::put('group', $data, $params);
        } else {
            Self::put('group', $data);
        }
        Self::$group = true;
    }
    /**
     * Allows you to print the contents of any variable into the console.
     *
     * @param WILD   $var  - The variable you wish to read.
     * @param string $data - Optional name for the variable output.
     */
    public static function v($var, $data = null)
    {
        if (!isset($data)) {
            $data = 'Default Variable label';
        }
        Self::put('variable', $var, $data);
    }

    /**
     * Socket function for a basic debugging log.
     *
     * @param string $data - The debug data.
     */
    public static function log($data, $params = null)
    {
        Self::put('log', $data);
        if (!empty($params)) {
            Self::gend();
        }
    }

    /**
     * Provides a stack trace from the current calling spot.
     *
     * @param string $data the name of the trace
     */
    public static function trace($data = 'Default Trace')
    {
        Self::group("$data", 1);
        Self::put('trace', $data);
        Self::gend();
    }

    /**
     * Socket function for debugging info.
     *
     * @param string $data - The debug data.
     */
    public static function info($data, $params = null)
    {
        Self::put('info', $data);
        if (!empty($params)) {
            Self::gend();
        }
    }

    /**
     * Socket function for a debugging warning.
     *
     * @param string $data - The debug data.
     */
    public static function warn($data, $params = null)
    {
        Self::put('warn', $data);
        if (!empty($params)) {
            Self::gend();
        }
    }

    /**
     * Socket function for a debugging error.
     *
     * @param string $data - The debug data.
     */
    public static function error($data, $params = null)
    {
        Self::put('error', $data);
        if (Self::$error_trace) {
            Self::trace();
        }
        if (!empty($params)) {
            Self::gend();
        }
    }

    /**
     * This returns the contents of the debug log.
     *
     * @return string - Returns the entire debug log.
     */
    public static function dump()
    {
        return var_dump(Self::$debug_log);
    }
}
