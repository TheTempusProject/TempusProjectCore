<?php
/**
 * app.php
 *
 * This file parses any given url and separates it into controller,
 * method, and data. This allows the application to direct the user
 * to the desired location and provide the controller any additional
 * information it may require to run.
 *
 * @version 3.0
 * @author  Joey Kimsey <Joey@thetempusproject.com>
 * @link    https://TheTempusProject.com/Core
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */
namespace TempusProjectCore;

use TempusProjectCore\Functions\{
    Debug, Routes, Input, Session, Check, Token, Sanitize
};
use TempusProjectCore\Classes\{
    Config, CustomException, Autoloader
};
use TempusProjectCore\Template\ {
    Issues, Components
};

class App
{
    public static $controllerName = '';
    public static $methodName = '';
    public static $activeConfig = array();
    protected $controllerObject = null;
    protected $controllerClass = '';
    protected $directed = false;
    protected $params = [];

    /**
     * The constructor handles the entire process of parsing the url,
     * finding the controller/method, and calling the appropriate
     * class/function for the application.
     *
     * @param string $url     - A custom URL to be parsed to determine
     *                                controller/method. (GET) url is used by
     *                                default if none is provided
     */
    public function __construct($url = '') {
        Debug::group('TPC Application');
        self::$activeConfig = new Config( TPC_CONFIG_DIRECTORY . 'config.json' );
        set_error_handler(array('TempusProjectCore\\Functions\\Debug', 'handle_error'), E_ALL);
        set_exception_handler(array('TempusProjectCore\\Functions\\Debug', 'handle_exception'));
        register_shutdown_function(array('TempusProjectCore\\Functions\\Debug', 'handle_shutdown'));
        self::$controllerName = DEFAULT_CONTROLER_CLASS;
        self::$methodName = DEFAULT_CONTROLER_METHOD;
        $this->setUrl($url);
    }
    public function load() {
        $this->loadController();
        $this->loadPage();
    }
    protected function loadPage() {
        if (!method_exists($this->controllerClass, self::$methodName)) {
            // Debug::error('');
            return false;
        }
        call_user_func_array([$this->controllerObject, self::$methodName], $this->params);
    }
    protected function loadController() {
        // is_subclass_of($this->controllerClass, 'Controller');
        if ( empty( $this->controllerClass )) {
            $this->controllerClass = (string) APP_SPACE . '\\Controllers\\' . self::$controllerName;
        }
        $this->controllerObject = new $this->controllerClass;
    }
    protected function setController($name) {
        $controllerClass = (string) APP_SPACE . '\\Controllers\\' . $name;
        if ( Autoloader::testLoad( $controllerClass ) ) {
            $this->controllerClass = $controllerClass;
            self::$controllerName = $name;
        }
    }
    protected function setPage($name) {
        $name = strtolower($name);
        if (!method_exists($this->controllerClass, $name)) {
            Debug::info('setPage - Method not found: ' . $name);
            return false;
        }
        self::$methodName = $name;
    }
    protected function setVarsFromUrlArray($urlArray) {
        if (!empty($urlArray[0])) {
            $urlPart = array_shift($urlArray);
            $this->setController($urlPart);
        }
        if (!empty($urlArray[0])) {
            $urlPart = array_shift($urlArray);
            $this->setPage($urlPart);
        }
        if (!empty($urlArray)) {
            $this->params = array_values($urlArray);
        }
    }
    public function setUrl($url = '') {
        if (empty($url)) {
            Debug::info('Using GET url.');
            $url = Input::get('url');
        } else {
            $this->directed = true;
        }
        $urlArray = explode('/', Sanitize::url($url));
        $this->setVarsFromUrlArray($urlArray);
    }
    public function getCurrentUrl() {
        return Sanitize::url( Input::get('url') );
    }
    protected function printDebug() {
        echo '<div style="margin: 0 auto; padding-bottom: 25px; background: #eee; width: 1000px;">';
        echo '<h1 style="text-align: center;">PHP Info</h1>';
        echo '<table style="margin: 0 auto; padding-bottom: 25px; background: #eee; width: 950px;">';
        echo '<tr><td>PHP version: </td><td><code>'.phpversion().'</code><br></td></tr>';
        echo '<tr><td>PDO Loaded version: </td><td><code>'.extension_loaded('pdo').'</code><br></td></tr>';
        echo '<tr><td>PHP extensions: </td><td><pre>';
        foreach (get_loaded_extensions() as $i => $ext)
        {
           echo $ext .' => '. phpversion($ext). '<br/>';
        }
        echo '</pre><br></td></tr>';
        echo '</table>';
        echo '<h1 style="text-align: center;">Tempus Core Info</h1>';
        echo '<table style="margin: 0 auto; padding-bottom: 25px; background: #eee; width: 950px;">';
        // Just in case
        echo '<tr><td>_SERVER: </td><td><pre>';
        echo var_export($_SERVER,true);
        echo '</pre><br></td></tr>';
        // Checks
            echo '<tr><td style="text-align: center; padding-top: 25px; padding-bottom: 10px;" colspan="2"><h2>Checks</h2></td></tr>';
            echo '<tr><td>Uploads work?: </td><td><code>'.var_export(Check::uploads(),true).'</code><br></td></tr>';
            echo '<tr><td>PHP: </td><td><code>'.var_export(Check::php(),true).'</code><br></td></tr>';
            echo '<tr><td>Mail works?: </td><td><code>'.var_export(Check::mail(),true).'</code><br></td></tr>';
            echo '<tr><td>Is safe mode?: </td><td><code>'.var_export(Check::safe(),true).'</code><br></td></tr>';
            echo '<tr><td>Sessions work?: </td><td><code>'.var_export(Check::sessions(),true).'</code><br></td></tr>';
            echo '<tr><td>Cookies work?: </td><td><code>'.var_export(Check::cookies(),true).'</code><br></td></tr>';
            echo '<tr><td>is Apache?: </td><td><code>'.var_export(Check::isApache(),true).'</code><br></td></tr>';
            echo '<tr><td>is nginx?: </td><td><code>'.var_export(Check::isNginx(),true).'</code><br></td></tr>';
            echo '<tr><td>Is token enabled?: </td><td><code>'.var_export(Token::isTokenEnabled(),true).'</code><br></td></tr>';
        // Routes
            echo '<tr><td style="text-align: center; padding-top: 25px; padding-bottom: 10px;" colspan="2"><h2>Routes</h2></td></tr>';
            echo '<tr><td>Root: </td><td><code>'.var_export(Routes::getRoot(),true).'</code><br></td></tr>';
            echo '<tr><td>Address: </td><td><code>'.var_export(Routes::getAddress(),true).'</code><br></td></tr>';
            echo '<tr><td>Protocol: </td><td><code>'.var_export(Routes::getProtocol(),true).'</code><br></td></tr>';
        // Debugging
            echo '<tr><td style="text-align: center; padding-top: 25px; padding-bottom: 10px;" colspan="2"><h2>Debugging</h2></td></tr>';
            echo '<tr><td>Console Debugging Enabled: </td><td><code>'.var_export(Debug::status('console'),true).'</code><br></td></tr>';
            echo '<tr><td>Redirects Enabled: </td><td><code>'.var_export(Debug::status('redirect'),true).'</code><br></td></tr>';
            echo '<tr><td>Debug Trace Enabled: </td><td><code>'.var_export(Debug::status('trace'),true).'</code><br></td></tr>';
            echo '<tr><td>Debugging Enabled: </td><td><code>'.var_export(Debug::status('debug'),true).'</code><br></td></tr>';
            echo '<tr><td>Rendering Enabled: </td><td><code>'.var_export(Debug::status('render'),true).'</code><br></td></tr>';
        // Main
            echo '<tr><td style="text-align: center; padding-top: 25px; padding-bottom: 10px;" colspan="2"><h2>Main App Variables</h2></td></tr>';
            echo '<tr><td>Template Location: </td><td><code>'.var_export(Template::getLocation(),true).'</code><br></td></tr>';
            echo '<tr><td>Configuration: </td><td><pre>'.var_export(Config::$config,true).'</pre></td></tr>';
            echo '<tr><td>Check Errors: </td><td><pre>'.var_export(Check::systemErrors(),true).'</pre></td></tr>';
            echo '<tr><td>GET: </td><td><pre>'.var_export($_GET,true).'</pre></td></tr>';
        // Constants
            echo '<tr><td style="text-align: center; padding-top: 25px; padding-bottom: 10px;" colspan="2"><h2>Constants</h2></td></tr>';
            // Debugging
                echo '<tr><td style="text-align: center;"><b>Debugging:</b></td><td></td></tr>';
                echo '<tr><td>REDIRECTS_ENABLED: </td><td><code>'.var_export(REDIRECTS_ENABLED,true).'</code><br></td></tr>';
                echo '<tr><td>DEBUG_TRACE_ENABLED: </td><td><code>'.var_export(DEBUG_TRACE_ENABLED,true).'</code><br></td></tr>';
                echo '<tr><td>DEBUG_ENABLED: </td><td><code>'.var_export(DEBUG_ENABLED,true).'</code><br></td></tr>';
                echo '<tr><td>DEBUG_TO_CONSOLE: </td><td><code>'.var_export(DEBUG_TO_CONSOLE,true).'</code><br></td></tr>';
            // Tempus Debugger
                echo '<tr><td style="text-align: center;"><b>Tempus Debugger:</b></td><td></td></tr>';
                echo '<tr><td>TEMPUS_DEBUGGER_SECURE_HASH: </td><td><code>'.var_export(TEMPUS_DEBUGGER_SECURE_HASH,true).'</code><br></td></tr>';
                echo '<tr><td>TEMPUS_DEBUGGER_SHOW_LINES: </td><td><code>'.var_export(TEMPUS_DEBUGGER_SHOW_LINES,true).'</code><br></td></tr>';
            // Tokens
                echo '<tr><td style="text-align: center;"><b>Tokens:</b></td><td></td></tr>';
                echo '<tr><td>DEFAULT_TOKEN_NAME: </td><td><code>'.var_export(DEFAULT_TOKEN_NAME,true).'</code><br></td></tr>';
                echo '<tr><td>TOKEN_ENABLED: </td><td><code>'.var_export(TOKEN_ENABLED,true).'</code><br></td></tr>';
            // Cookies
                echo '<tr><td style="text-align: center;"><b>Cookies:</b></td><td></td></tr>';
                echo '<tr><td>DEFAULT_COOKIE_EXPIRATION: </td><td><code>'.var_export(DEFAULT_COOKIE_EXPIRATION,true).'</code><br></td></tr>';
                echo '<tr><td>DEFAULT_COOKIE_PREFIX: </td><td><code>'.var_export(DEFAULT_COOKIE_PREFIX,true).'</code><br></td></tr>';
            // Directories
                echo '<tr><td style="text-align: center;"><b>Directories:</b></td><td></td></tr>';
                echo '<tr><td>APP_ROOT_DIRECTORY: </td><td><code>'.var_export(APP_ROOT_DIRECTORY,true).'</code><br></td></tr>';
                echo '<tr><td>BIN_DIRECTORY: </td><td><code>'.var_export(BIN_DIRECTORY,true).'</code><br></td></tr>';
                echo '<tr><td>UPLOAD_DIRECTORY: </td><td><code>'.var_export(UPLOAD_DIRECTORY,true).'</code><br></td></tr>';
                echo '<tr><td>IMAGE_UPLOAD_DIRECTORY: </td><td><code>'.var_export(IMAGE_UPLOAD_DIRECTORY,true).'</code><br></td></tr>';
                echo '<tr><td>CLASSES_DIRECTORY: </td><td><code>'.var_export(CLASSES_DIRECTORY,true).'</code><br></td></tr>';
                echo '<tr><td>CONFIG_DIRECTORY: </td><td><code>'.var_export(CONFIG_DIRECTORY,true).'</code><br></td></tr>';
                echo '<tr><td>FUNCTIONS_DIRECTORY: </td><td><code>'.var_export(FUNCTIONS_DIRECTORY,true).'</code><br></td></tr>';
                echo '<tr><td>TEMPLATE_DIRECTORY: </td><td><code>'.var_export(TEMPLATE_DIRECTORY,true).'</code><br></td></tr>';
                echo '<tr><td>VIEW_DIRECTORY: </td><td><code>'.var_export(VIEW_DIRECTORY,true).'</code><br></td></tr>';
                echo '<tr><td>ERRORS_DIRECTORY: </td><td><code>'.var_export(ERRORS_DIRECTORY,true).'</code><br></td></tr>';
                echo '<tr><td>RESOURCES_DIRECTORY: </td><td><code>'.var_export(RESOURCES_DIRECTORY,true).'</code><br></td></tr>';
                echo '<tr><td>TPC_ROOT_DIRECTORY: </td><td><code>'.var_export(TPC_ROOT_DIRECTORY,true).'</code><br></td></tr>';
                echo '<tr><td>TPC_BIN_DIRECTORY: </td><td><code>'.var_export(TPC_BIN_DIRECTORY,true).'</code><br></td></tr>';
                echo '<tr><td>TPC_CLASSES_DIRECTORY: </td><td><code>'.var_export(TPC_CLASSES_DIRECTORY,true).'</code><br></td></tr>';
                echo '<tr><td>TPC_CONFIG_DIRECTORY: </td><td><code>'.var_export(TPC_CONFIG_DIRECTORY,true).'</code><br></td></tr>';
                echo '<tr><td>TPC_FUNCTIONS_DIRECTORY: </td><td><code>'.var_export(TPC_FUNCTIONS_DIRECTORY,true).'</code><br></td></tr>';
                echo '<tr><td>TPC_RESOURCES_DIRECTORY: </td><td><code>'.var_export(TPC_RESOURCES_DIRECTORY,true).'</code><br></td></tr>';
                echo '<tr><td>TPC_VIEW_DIRECTORY: </td><td><code>'.var_export(TPC_VIEW_DIRECTORY,true).'</code><br></td></tr>';
                echo '<tr><td>TPC_ERRORS_DIRECTORY: </td><td><code>'.var_export(TPC_ERRORS_DIRECTORY,true).'</code><br></td></tr>';
            // other
                echo '<tr><td style="text-align: center;"><b>Other:</b></td><td></td></tr>';
                echo '<tr><td>MINIMUM_PHP_VERSION: </td><td><code>'.var_export(MINIMUM_PHP_VERSION,true).'</code><br></td></tr>';
                echo '<tr><td>DATA_TITLE_PREG: </td><td><code>'.var_export(DATA_TITLE_PREG,true).'</code><br></td></tr>';
                echo '<tr><td>PATH_PREG_REQS: </td><td><code>'.var_export(PATH_PREG_REQS,true).'</code><br></td></tr>';
                echo '<tr><td>SIMPLE_NAME_PREG: </td><td><code>'.var_export(SIMPLE_NAME_PREG,true).'</code><br></td></tr>';
                echo '<tr><td>ALLOWED_IMAGE_UPLOAD_EXTENTIONS: </td><td><code>'.var_export(ALLOWED_IMAGE_UPLOAD_EXTENTIONS,true).'</code><br></td></tr>';
                echo '<tr><td>MAX_RESULTS_PER_PAGE: </td><td><code>'.var_export(MAX_RESULTS_PER_PAGE,true).'</code><br></td></tr>';
                echo '<tr><td>DEFAULT_RESULTS_PER_PAGE: </td><td><code>'.var_export(DEFAULT_RESULTS_PER_PAGE,true).'</code><br></td></tr>';
                echo '<tr><td>DEFAULT_SESSION_PREFIX: </td><td><code>'.var_export(DEFAULT_SESSION_PREFIX,true).'</code><br></td></tr>';
                echo '<tr><td>DEFAULT_CONTROLER_CLASS: </td><td><code>'.var_export(DEFAULT_CONTROLER_CLASS,true).'</code><br></td></tr>';
        echo '</table></div>';
    }
}
