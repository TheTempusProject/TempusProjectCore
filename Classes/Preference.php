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

use TempusProjectCore\Functions\Docroot as Docroot;

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
        $docLocation = Docroot::getLocation('appPreferences');
        if ($docLocation->error) {
            $docLocation = Docroot::getLocation('appPreferencesDefault');
            if ($docLocation->error) {
                $docLocation = Docroot::getLocation('preferencesDefault');
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
        Debug::warn("Config not found: $name");

        return;
    }

    public static function savePrefs($default = false)
    {
        if (self::$preferences === false) {
            self::load();
        }
        if ($default) {
            if (file_put_contents(Docroot::getLocation('appPreferencesDefault')->fullPath, json_encode(self::$preferences))) {
                return true;
            }
            return false;
        }
        if (file_put_contents(Docroot::getLocation('appPreferences')->fullPath, json_encode(self::$preferences))) {
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
            Issue::error("Preference already exists: $name");
            return false;
        }
        self::$preferences[$name] = $value;
        return true;
    }
}
