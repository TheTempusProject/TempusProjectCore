<?php
/**
 * Classes/Config.php
 *
 * This class handles all the hard-coded configuration.
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

use TempusProjectCore\Functions\Routes as Routes;

class Config
{
    private static $override = false;
    private static $config = false;
    private static $configNew = null;
    private static $configLocation = null;
    private static $configLocationDefault = null;

    /**
     * Loads default location and default values as well as setting
     * the config to be used by the application.
     *
     * NOTE: This function will reload the config array from the
     * config.json every time it is used.
     *
     * @return boolean - true
     */
    private static function load()
    {
        self::$config = self::getConfig();
        return true;
    }

    /**
     * Returns the config array as its currently saved.
     *
     * Order or retrieval:
     *  - App/config.json
     *  - App/config_default.json
     *  - Config::$configNew
     *
     * @return array - The config array.
     */
    public static function getConfig()
    {
        $docLocation = Routes::getLocation('appConfig');
        if ($docLocation->error) {
            $docLocation = Routes::getLocation('appConfigDefault');
            if ($docLocation->error) {
                $docLocation = Routes::getLocation('configDefault');
            }
        }
        return json_decode(file_get_contents($docLocation->fullPath), true);
    }

    /**
     * Retrieves the config option for $name.
     *
     * @param string $data - Must be in <category>/<option> format.
     *
     * @return WILD - Depending on the requested option, various returns
     *                are possible; returns null if the option is not found.
     *
     * @example Config::get('main/name') - Should return the name you have set in App/Core/config.php
     */
    public static function get($name)
    {
        $data = explode('/', $name);
        if (count($data) != 2) {
            Debug::warn("Config not properly formatted: $name");
            
            return;
        }
        if (self::$config === false) {
            self::load();
        }
        if (isset(self::$config[$data[0]][$data[1]])) {
            return self::$config[$data[0]][$data[1]];
        }
        Debug::warn("Config not found: $name");

        return;
    }
    /**
     * Retrieves the config option for $name.
     *
     * @param string $data - Must be in <category>/<option> format.
     *
     * @return WILD - Depending on the requested option, various returns
     *                are possible; returns null if the option is not found.
     *
     * @example Config::get('main/name') - Should return the name you have set in App/Core/config.php
     */
    public static function getString($name)
    {
        $data = explode('/', $name);
        if (count($data) != 2) {
            Debug::warn("Config not properly formatted: $name");
            
            return;
        }
        if (self::$config === false) {
            self::load();
        }
        if (isset(self::$config[$data[0]][$data[1]])) {
            if (is_bool(self::$config[$data[0]][$data[1]])) {
                return (self::$config[$data[0]][$data[1]] ? 'true' : 'false');
            } else {
                return self::$config[$data[0]][$data[1]];
            }
        }
        Debug::warn("Config not found: $name");

        return;
    }

    /**
     * Saves the current $config array.
     *
     * @param  boolean $default - Option flag to save default_config
     *                            as well.
     */
    public static function saveConfig($default = false)
    {
        if (self::$config === false) {
            self::load();
        }
        if ($default) {
            if (!file_put_contents(Routes::getLocation('appConfigDefault')->fullPath, json_encode(self::$config))) {
                return false;
            }
        }
        if (file_put_contents(Routes::getLocation('appConfig')->fullPath, json_encode(self::$config))) {
            return true;
        }
        return false;
    }

    /**
     * Adds a new category to the $config array.
     *
     * @param string $name - The name of the new category.
     *
     * @todo  - check name.
     */
    public static function addConfigCategory($name)
    {
        if (self::$config === false) {
            self::load();
        }
        if (isset(self::$config[$name])) {
            Issue::error("Category already exists: $name");
            return false;
        }
        self::$config[$name] = [];
        return true;
    }
    public static function removeConfigCategory($name, $save = false)
    {
        if (self::$config === false) {
            self::load();
        }
        if (!isset(self::$config[$name])) {
            Issue::error("Config does not have ceategory: $name");
            return false;
        }
        unset(self::$config[$name]);
        if ($save) {
            self::saveConfig(true);
        }
        return true;
    }

    /**
     * Add a new config option for the specified category.
     *
     * NOTE: Use a default option when using this function to
     * aid in failsafe execution.
     *
     * @param string $parent - The primary category to add the option to.
     * @param string $name   - The name of the new option.
     * @param wild   $value  - The desired value for the new option.
     *
     * @return boolean
     */
    public static function updateConfig($parent, $name, $value, $create = false, $save = false)
    {
        if (self::$config === false) {
            self::load();
        }
        if (!isset(self::$config[$parent])) {
            Debug::error("No such parent: $parent");
            return false;
        }
        if (isset(self::$config[$parent][$name])) {
            self::$config[$parent][$name] = $value;
        } else {
            if ($create) {
                self::addConfig($parent, $name, $value);
            } else {
                Debug::error("Config not found.");
                return false;
            }
        }
        if ($save) {
            self::saveConfig();
        }
        return true;
    }

    /**
     * Add a new config option for the specified category.
     *
     * NOTE: Use a default option when using this function to
     * aid in failsafe execution.
     *
     * @param string $parent - The primary category to add the option to.
     * @param string $name   - The name of the new option.
     * @param wild   $value  - The desired value for the new option.
     *
     * @return boolean
     */
    public static function addConfig($parent, $name, $value)
    {
        if (self::$config === false) {
            self::load();
        }
        if (!isset(self::$config[$parent])) {
            Issue::error("No such parent: $parent");
            return false;
        }
        if (isset(self::$config[$parent][$name])) {
            Issue::error("Category already exists: $name");
            return false;
        }
        self::$config[$parent][$name] = $value;
        return true;
    }

    /**
     * Generates and saves a new config.json and config.default.json
     * based on Input variables if no other config file exists.
     *
     * @return boolean
     */
    public static function generateConfig($mods = [])
    {
        $docLocation = Routes::getLocation('appConfig');
        if (!$docLocation->error) {
            if (!self::$override) {
                Debug::error('config file already exists');

                return false;
            }
        }

        $docLocation = Routes::getLocation('appConfigDefault');
        if ($docLocation->error) {
            $docLocation = Routes::getLocation('configDefault');
        }

        self::$config = json_decode(file_get_contents($docLocation->fullPath), true);
        if (!empty($mods)) {
            foreach ($mods as $mod) {
                self::updateConfig($mod['category'], $mod['name'], $mod['value'], true);
            }
        }
        if (self::saveConfig(true)) {
            Debug::info('config file generated successfully.');
            return true;
        }

        return false;
    }
}
