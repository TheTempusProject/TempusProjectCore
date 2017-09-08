<?php
/**
 * Classes/Config.php.
 *
 * This class handles all config settings. It will automatically default if
 * settings.php is not found in the /App/ folder from the root directory.
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

class Config
{
    private static $_config = null;

    /**
     * Updates the Settings file.
     */
    public static function update()
    {
        $path = Self::get('main/location').'App/settings.php';
        if (!is_file($path)) {
            Debug::error('No settings file.');
            return;
        }
        $test = file_get_contents($path);
        if (Input::exists('name')) {
            if (Check::data_string(Input::post('name'))) {
                $find = '#"name" => "(.*?)",#is';
                $replace = '"name" => "'.Input::post('name').'",';
                $test = preg_replace($find, $replace, $test);
                Self::$_config['main']['name'] = Input::post('name');
            }
        }
        if (Input::exists('template')) {
            if (Check::nospace(Input::post('template'))) {
                $find = '#"template" => "(.*?)",#is';
                $replace = '"template" => "'.Input::post('template').'",';
                $test = preg_replace($find, $replace, $test);
                Self::$_config['main']['template'] = Input::post('template');
            }
        }
        if (Input::exists('login_limit')) {
            if (Check::ID(Input::post('login_limit'))) {
                $find = '#"loginLimit" => (.*?),#is';
                $replace = '"loginLimit" => '.Input::post('login_limit').',';
                $test = preg_replace($find, $replace, $test);
                Self::$_config['main']['loginLimit'] = Input::post('login_limit');
            }
        }
        if (Input::exists('log_F')) {
            if (Check::tf(Input::post('log_F'))) {
                $find = '#"feedback" => (.*?),#is';
                $replace = '"feedback" => '.Input::post('log_F').',';
                $test = preg_replace($find, $replace, $test);
                Self::$_config['logging']['feedback'] = Input::post('log_F');
            }
        }
        if (Input::exists('log_L')) {
            if (Check::tf(Input::post('log_L'))) {
                $find = '#"logins" => (.*?),#is';
                $replace = '"logins" => '.Input::post('log_L').',';
                $test = preg_replace($find, $replace, $test);
                Self::$_config['logging']['logins'] = Input::post('log_L');
            }
        }
        if (Input::exists('log_E')) {
            if (Check::tf(Input::post('log_E'))) {
                $find = '#"errors" => (.*?),#is';
                $replace = '"errors" => '.Input::post('log_E').',';
                $test = preg_replace($find, $replace, $test);
                Self::$_config['logging']['errors'] = Input::post('log_E');
            }
        }
        if (Input::exists('log_BR')) {
            if (Check::tf(Input::post('log_BR'))) {
                $find = '#"bug_reports" => (.*?),#is';
                $replace = '"bug_reports" => '.Input::post('log_BR').',';
                $test = preg_replace($find, $replace, $test);
                Self::$_config['logging']['bug_reports'] = Input::post('log_BR');
            }
        }
        file_put_contents($path, $test);
    }

    /**
     * Retrieves the global config for $input.
     *
     * @param string $data - Must be in array/element format!
     *
     * @return WILD|null - Depending on the requested array, various returns are possible, null if not found.
     *
     * @example Config::get('main/name') - Should return the name you have set in App/Core/Settings.php
     */
    public static function get($name)
    {
        if (Self::_start()) {
            $data = explode('/', $name);
            if (count($data) != 2) {
                Debug::warn("Config not properly formatted: $name");
                
                return;
            }
            if (isset(Self::$_config[$data[0]][$data[1]])) {
                return Self::$_config[$data[0]][$data[1]];
            }
        }
        Debug::warn("Config not found: $name");

        return;
    }

    /**
     * Retrieves the config if it hasn't already been set.
     *
     * @return bool
     */
    private static function _start()
    {
        if (isset(Self::$_config) && Self::$_config !== false) {
            //Debug::log('Using preset local config.');

            return true;
        }

        $fullArray = explode('/', $_SERVER['PHP_SELF']);
        array_pop($fullArray);
        $docroot = implode('/', $fullArray) . '/';
        $path = $_SERVER['DOCUMENT_ROOT'] . $docroot . 'App/settings.php';
        
        if (file_exists($path)) {
            Debug::log("Requiring Settings file");
        } else {
            Debug::warn('No Global config found!');
            Debug::info('Using Default Settings.');
            $path = $_SERVER['DOCUMENT_ROOT'] . $docroot . 'Resources/default_settings.php';
        }
        require_once $path;
        Self::$_config = $GLOBALS['config'];

        return true;
    }
}
