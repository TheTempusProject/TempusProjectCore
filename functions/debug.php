<?php
/**
 * functions/debug.php
 *
 * The Debug class is responsible for providing a log of relevant debugging information.
 * It has functionality to generate a log file as it goes allowing you to print it at any
 * given point in the script. It also acts as a portal for writing to a console output
 * using FirePHP.
 *
 * @version 3.0
 * @author  Joey Kimsey <Joey@thetempusproject.com>
 * @link    https://TheTempusProject.com/Core
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */
namespace TempusProjectCore\Functions;

use TempusProjectCore\Functions\Routes;
use TempusProjectCore\Installer;
use TempusDebugger\TempusDebugger;
use TempusProjectCore\Template\Components;
use TempusProjectCore\Template\Views;

class Debug
{
    private static $group = 0;
    private static $tempusDebugger;
    private static $debugLog;

    public static function handle_shutdown(){
        if (!self::status('console')) {
            return;
        }
        echo '<div style="margin: 0 auto; padding-bottom: 25px; background: #eee; width: 1000px;">';
        echo '<h2 style="text-align: center; padding-top: 15px; padding-bottom: 15px;">Running the shutdown handler</h2>';
        Components::set('DEBUGGING_LOG', self::dump());
        $error = error_get_last();
        if (!empty($error)) {
            print "Looks like there was an error: <pre>" . print_r($error, true) .'</pre>';
            // log_error( $error["type"], $error["message"], $error["file"], $error["line"] );
        } else {
            echo '<p>Shutting down without error.</p>';
        }
        echo Views::standardView( 'debug' );
        echo '</div>';
    }

    /**
     * @param {object} [$exception]
     */
    public static function handle_exception($exception) {
        echo '<hr><h3>Exception Handler:</h3>';
        echo "<b>Class: </b><code>" . get_class($exception) .  '</code><br>';
        echo "<b>Message: </b><code>{$exception->getMessage()}" .  '</code><br>';
        echo "<b>File: </b><code>{$exception->getFile()}" . '</code><br>';
        echo "<b>Line: </b><code>{$exception->getLine()}" .  '</code><br>';
        echo "<b>Exception:</b><pre>" . print_r($exception, true) .  '</pre><br><hr>';
    }

    /**
     * @param {object} [$exception]
     */
    public static function handle_error($error_code, $error_description, $file = null, $error_line_number = null, $error_context = null) {
        $displayErrors = ini_get("display_errors");
        $displayErrors = strtolower($displayErrors);

        if ( 0 === $error_code ) {
            echo '<h1>fail</h1>';
            return;
        }
        if ( 0 === error_reporting() || $displayErrors === "on") {
            echo '<h1>fail</h1>';
            return false;
        }
        if (!(error_reporting() & $error_code)) {
            echo '<h1>fail</h1>';
            return false;
        }
        if ( isset( $GLOBALS['error_fatal'] ) ) {
            if ( $GLOBALS['error_fatal'] && $error_code ) {
                die('fatal');
            }
        }
        echo '<h3>Error Handler:</h3>';
        print "Running error handler..." . PHP_EOL;
        // $errstr may need to be escaped:
        $errstr = htmlspecialchars($error_description);
        switch($error_code){
            case E_ERROR:
                // throw new ErrorException($error_description, 0, $error_code, $file, $err_line);
                $error = 'Fatal Error';
                $log = LOG_ERR;
                echo "<b>My ERROR</b> [$error_code] $errstr<br />\n";
                echo "  Fatal error on line $error_line_number in file $file";
                echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
                echo "Aborting...<br />\n";
                print "Error";
                break;
            case E_WARNING:
                // throw new WarningException($error_description, 0, $error_code, $file, $err_line);
                $error = 'Warning';
                $log = LOG_WARNING;
                echo "<b>My WARNING</b> [$error_code] $errstr<br />\n";
                print "Warning";
                break;
            case E_PARSE:
                // throw new ParseException($error_description, 0, $error_code, $file, $err_line);
                $error = 'Fatal Error';
                $log = LOG_ERR;
                echo "<b>My ERROR</b> [$error_code] $errstr<br />\n";
                echo "  Fatal error on line $error_line_number in file $file";
                echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
                echo "Aborting...<br />\n";
                print "Parse Error";
                break;
            case E_NOTICE:
                // throw new NoticeException($error_description, 0, $error_code, $file, $err_line);
                $error = 'Notice';
                $log = LOG_NOTICE;
                echo "<b>My NOTICE</b> [$error_code] $errstr<br />\n";
                print "Notice";
                break;
            case E_CORE_ERROR:
                // throw new CoreErrorException($error_description, 0, $error_code, $file, $err_line);
                $error = 'Fatal Error';
                $log = LOG_ERR;
                echo "<b>My ERROR</b> [$error_code] $errstr<br />\n";
                echo "  Fatal error on line $error_line_number in file $file";
                echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
                echo "Aborting...<br />\n";
                print "Core Error";
                break;
            case E_CORE_WARNING:
                // throw new CoreWarningException($error_description, 0, $error_code, $file, $err_line);
                $error = 'Warning';
                $log = LOG_WARNING;
                echo "<b>My WARNING</b> [$error_code] $errstr<br />\n";
                print "Core Warning";
                break;
            case E_COMPILE_ERROR:
                // throw new CompileErrorException($error_description, 0, $error_code, $file, $err_line);
                $error = 'Fatal Error';
                $log = LOG_ERR;
                echo "<b>My ERROR</b> [$error_code] $errstr<br />\n";
                echo "  Fatal error on line $error_line_number in file $file";
                echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
                echo "Aborting...<br />\n";
                print "Compile Error";
                break;
            case E_COMPILE_WARNING:
                // throw new CoreWarningException($error_description, 0, $error_code, $file, $err_line);
                $error = 'Warning';
                $log = LOG_WARNING;
                echo "<b>My WARNING</b> [$error_code] $errstr<br />\n";
                print "Compile Warning";
                break;
            case E_USER_ERROR:
                // throw new UserErrorException($error_description, 0, $error_code, $file, $err_line);
                if ($error_description == "(SQL)"){
                    // handling an sql error
                    echo "<b>SQL Error</b> [$errno] " . SQLMESSAGE . "<br />\n";
                    echo "Query : " . SQLQUERY . "<br />\n";
                    echo "On line " . SQLERRORLINE . " in file " . SQLERRORFILE . " ";
                    echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
                    echo "Aborting...<br />\n";
                } else {
                    echo "<b>My ERROR</b> [$errno] $error_description<br />\n";
                    echo "  Fatal error on line $error_line_number in file $file";
                    echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
                    echo "Aborting...<br />\n";
                }
                $error = 'Fatal Error';
                $log = LOG_ERR;
                echo "<b>My ERROR</b> [$error_code] $errstr<br />\n";
                echo "  Fatal error on line $error_line_number in file $file";
                echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
                echo "Aborting...<br />\n";
                print "User Error";
                break;
            case E_USER_NOTICE:
                $error = 'Notice';
                $log = LOG_NOTICE;
                echo "<b>My NOTICE</b> [$error_code] $errstr<br />\n";
                print "User Notice";
                break;
            case E_STRICT:
                $error = 'Strict';
                $log = LOG_NOTICE;
                print "Strict Notice";
                break;
            case E_RECOVERABLE_ERROR:
                $error = 'Warning';
                $log = LOG_WARNING;
                echo "<b>My WARNING</b> [$error_code] $errstr<br />\n";
                print "Recoverable Error";
                break;
            case E_USER_WARNING:
                $error = 'Warning';
                $log = LOG_WARNING;
                echo "<b>My WARNING</b> [$error_code] $errstr<br />\n";
                print "User Warning";
                break;
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                print "Depricated Error";
                $error = 'Deprecated';
                $log = LOG_NOTICE;
                break;
            default:
                echo "Unknown error type: [$error_code] $errstr<br />\n";
                print "Unknown error ($error_code)";
                break;
        }
        echo "Wow my custom error handler got #[$error_code] occurred in [$file] at line [$error_line_number]: [$error_description]";
        // $data = array(
        //     // 'level' => $log,
        //     'code' => $error_line_number,
        //     // 'error' => $error,
        //     'description' => $error_description,
        //     'file' => $file,
        //     'line' => $error_line_number,
        //     'context' => $error_context,
        //     'path' => $file,
        //     'message' => $error . ' (' . $error_line_number . '): ' . $error_description . ' in [' . $file . ', line ' . $error_line_number . ']'
        // );
    }

    /**
     * Acts as a constructor.
     */
    private static function startDebug()
    {
        if (DEBUG_TO_CONSOLE) {
            ob_start();
            self::$tempusDebugger = TempusDebugger::getInstance(true);
            self::$tempusDebugger->setOption('includeLineNumbers', TEMPUS_DEBUGGER_SHOW_LINES);
            // $installer = new Installer;
            self::$tempusDebugger->setHash( TEMPUS_DEBUGGER_SECURE_HASH );
            // if ($installer->getNode('installHash') !== false) {
            //     self::$tempusDebugger->setHash($installer->getNode('installHash'));
            // }
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
            // enables tempus debugger integration
            case 'console':
                return DEBUG_TO_CONSOLE;
            case 'redirect':
                return REDIRECTS_ENABLED;
            case 'trace':
                return DEBUG_TRACE_ENABLED;
            case 'debug':
                return DEBUG_ENABLED;
            case 'render':
                return RENDERING_ENABLED;

            default:
                return DEBUG_ENABLED;
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
        if (!DEBUG_ENABLED) {
            return;
        }
        if (strlen(self::$debugLog) > 50000) {
            self::$tempusDebugger->log('Error log too large, possible loop.');
            return;
        }
        if (!is_object($data)) {
            self::$debugLog .= var_export($data, true) . "<br>";
        } else {
            self::$debugLog .= "cannot save objects<br>";
        }
        if (!DEBUG_TO_CONSOLE) {
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
        if (DEBUG_TRACE_ENABLED) {
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
