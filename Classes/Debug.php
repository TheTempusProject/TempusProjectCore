<?php
/**
 * Classes/Debug.php
 *
 * The Debug class is responsible for providing a log of relevant debugging information.
 * It has functionality to generate a log file as it goes allowing you to print it at any
 * given point in the script. It also acts as a portal for writing to a console output
 * using FirePHP.
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

use TempusProjectCore\Functions\Routes;
use TempusProjectCore\Core\Installer;
use TempusDebugger\TempusDebugger;
class Debug
{
    /**
     * Toggle Debugging mode on or off.
     *
     * @var bool
     */
    private static $debugStatus = true;

    /**
     * Very Important, this will enable the TempusTools console output.
     * It only applies when debugging is enabled, or the config cannot
     * be found as a safety net.
     *
     * @var bool
     */
    private static $console = true;
    private static $showLines = false;
    private static $redirect = false;
    private static $errorTrace = false;
    private static $group = 0;
    private static $tempusDebugger;
    private static $debugLog;

    /**
     * Acts as a constructor.
     */
    private static function startDebug()
    {
        if (self::$console) {
            require_once Routes::getFull() . 'vendor/TheTempusProject/TempusDebugger/TempusDebugger.php';
            
            ob_start();
            self::$tempusDebugger = TempusDebugger::getInstance(true);
            self::$tempusDebugger->setOption('includeLineNumbers', self::$showLines);
            $installer = new Installer;
            if ($installer->getNode('installHash') !== false) {
                self::$tempusDebugger->setHash($installer->getNode('installHash'));
            }
        }
    }

    /**
     * Returns the current Debug Status;.
     *
     * @return bool
     */
    public static function status($flag = null)
    {
        switch ($flag) {
            case 'console':
                return self::$console;
                break;

            case 'redirect':
                return self::$redirect;
                break;
            
            case 'trace':
                return self::$errorTrace;
                break;

            default:
                return self::$debugStatus;
                break;
        }
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
        if (!self::$debugStatus) {
            return;
        }
        if (strlen(self::$debugLog) > 50000) {
            self::$tempusDebugger->log('Error log too large, possible loop.');
            self::$debugStatus = false;
            return;
        }
        if (!is_object($data)) {
            self::$debugLog .= var_export($data, true) . PHP_EOL;
        } else {
            self::$debugLog .= 'cannot save objects' . PHP_EOL;
        }
        if (!self::$console) {
            return;
        }
        if (!self::$tempusDebugger) {
            self::startDebug();
        }
        switch ($type) {
            case 'variable':
                self::$tempusDebugger->info($data, $params);
                break;

            case 'groupEnd':
                self::$tempusDebugger->groupEnd();
                break;

            case 'trace':
                self::$tempusDebugger->trace($data);
                break;

            case 'group':
                if ($params) {
                    self::$tempusDebugger->group($data, $params);
                } else {
                    self::$tempusDebugger->group($data);
                }
                break;

            case 'info':
                self::$tempusDebugger->$type('color: #1452ff', '%c' . $data);
                break;

            default:
                self::$tempusDebugger->$type($data);
                break;
        }
    }

    /**
     * Ends a group.
     */
    public static function gend()
    {
        if (self::$group > 0) {
            self::$group--;
            self::put('groupEnd');
        }
    }
    /**
     * Creates a group divider into the console output.
     *
     * @param string $data      name of the group
     * @param wild   $collapsed if anything is present the group will be collapsed by default
     */
    public static function closeAllGroups()
    {
        if (self::$group > 0) {
            while (self::$group > 0) {
                self::$group--;
                self::put('groupEnd');
            }
            // self::put('log', 'closed all groups.');
        }
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
            $params = ['Collapsed' => true];
            self::put('group', $data, $params);
        } else {
            self::put('group', $data);
        }
        self::$group++;
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
        self::put('variable', $var, $data);
    }

    /**
     * Socket function for a basic debugging log.
     *
     * @param string $data - The debug data.
     */
    public static function log($data, $params = null)
    {
        self::put('log', $data);
        if (!empty($params)) {
            self::gend();
        }
    }

    /**
     * Provides a stack trace from the current calling spot.
     *
     * @param string $data the name of the trace
     */
    public static function trace($data = 'Default Trace')
    {
        self::group("$data", 1);
        self::put('trace', $data);
        self::gend();
    }

    /**
     * Socket function for debugging info.
     *
     * @param string $data - The debug data.
     */
    public static function info($data, $params = null)
    {
        self::put('info', $data);
        if (!empty($params)) {
            self::gend();
        }
    }

    /**
     * Socket function for a debugging warning.
     *
     * @param string $data - The debug data.
     */
    public static function warn($data, $params = null)
    {
        self::put('warn', $data);
        if (!empty($params)) {
            self::gend();
        }
    }

    /**
     * Socket function for a debugging error.
     *
     * @param string $data - The debug data.
     */
    public static function error($data, $params = null)
    {
        self::put('error', $data);
        if (self::$errorTrace) {
            self::trace();
        }
        if (!empty($params)) {
            self::gend();
        }
    }

    /**
     * This var_dumps the contents of the debug log.
     */
    public static function dump()
    {
        return self::$debugLog;
    }
}
