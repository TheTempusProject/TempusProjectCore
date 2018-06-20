<?php
/**
 * Classes/Preference.php
 *
 * This class handles all the hard-coded Preferences.
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
use TempusProjectCore\Classes\Debug;

class Preference
{
    private static $override = false;
    private static $preferences = false;

    private static function load()
    {
        self::$preferences = self::getPrefs();
        return true;
    }

    public static function getPrefs()
    {
        $docLocation = Routes::getLocation('appPreferences');
        if ($docLocation->error) {
            $docLocation = Routes::getLocation('appPreferencesDefault');
            if ($docLocation->error) {
                $docLocation = Routes::getLocation('preferencesDefault');
            }
        }
        return json_decode(file_get_contents($docLocation->fullPath), true);
    }

    public static function get($name)
    {
        if (self::$preferences === false) {
            self::load();
        }
        if (isset(self::$preferences[$name])) {
            return self::$preferences[$name];
        }
        Debug::warn("Preference not found: $name");

        return;
    }

    public static function savePrefs($default = false)
    {
        if (self::$preferences === false) {
            self::load();
        }
        if ($default) {
            if (file_put_contents(Routes::getLocation('appPreferencesDefault')->fullPath, json_encode(self::$preferences))) {
                return true;
            }
            return false;
        }
        if (file_put_contents(Routes::getLocation('appPreferences')->fullPath, json_encode(self::$preferences))) {
            return true;
        }
        return false;
    }

    public static function addPref($name, $value)
    {
        if (self::$preferences === false) {
            self::load();
        }
        if (isset(self::$preferences[$name])) {
            Debug::error("Preference already exists: $name");
            return false;
        }
        self::$preferences[$name] = $value;
        return true;
    }

    public static function removePref($name, $save = false)
    {
        if (self::$preferences === false) {
            self::load();
        }
        if (!isset(self::$preferences[$name])) {
            Debug::error("Preference does not exist: $name");
            return false;
        }
        unset(self::$preferences[$name]);
        if ($save === true) {
            self::savePrefs(true);
        }
        return true;
    }
}
