<?php
/**
 * Classes/Permission.php
 *
 * This class handles all the hard-coded Permissions.
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

class Permission
{
    private static $override = false;
    private static $permissions = false;

    private static function load()
    {
        self::$permissions = self::getPerms();
        return true;
    }

    public static function getPerms()
    {
        $docLocation = Routes::getLocation('appPermissions');
        if ($docLocation->error) {
            $docLocation = Routes::getLocation('appPermissionsDefault');
            if ($docLocation->error) {
                $docLocation = Routes::getLocation('permissionsDefault');
            }
        }
        return json_decode(file_get_contents($docLocation->fullPath), true);
    }

    public static function get($name)
    {
        if (self::$permissions === false) {
            self::load();
        }
        if (isset(self::$permissions[$name])) {
            return self::$permissions[$name];
        }
        Debug::warn("Config not found: $name");

        return;
    }

    public static function savePerms($default = false)
    {
        if (self::$permissions === false) {
            self::load();
        }
        if ($default) {
            if (file_put_contents(Routes::getLocation('appPermissionsDefault')->fullPath, json_encode(self::$permissions))) {
                return true;
            }
            return false;
        }
        if (file_put_contents(Routes::getLocation('appPermissions')->fullPath, json_encode(self::$permissions))) {
            return true;
        }
        return false;
    }

    public static function addPerm($name, $value)
    {
        if (self::$permissions === false) {
            self::load();
        }
        if (isset(self::$permissions[$name])) {
            Issue::error("Permission already exists: $name");
            return false;
        }
        self::$permissions[$name] = $value;
        return true;
    }
    public static function removePerm($name, $save = false)
    {
        if (self::$permissions === false) {
            self::load();
        }
        if (!isset(self::$permissions[$name])) {
            Debug::error("Permission does not exist: $name");
            return false;
        }
        unset(self::$permissions[$name]);
        if ($save === true) {
            self::savePerms(true);
        }
        return true;
    }
}
