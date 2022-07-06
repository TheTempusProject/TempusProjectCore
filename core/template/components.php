<?php
/**
 * core/template/components.php
 *
 * This class is for managing template components.
 *
 * @version 3.0
 * @author  Joey Kimsey <Joey@thetempusproject.com>
 * @link    https://TheTempusProject.com/Core
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */
namespace TempusProjectCore\Template;

use TempusProjectCore\Functions\Check;
use TempusProjectCore\Functions\Debug;

class Components
{
    public static $components = [];

    /**
     * Adds a $key->$value combination to the $components array.
     *
     * @param {string} [$key]
     * @param {wild} [$value]
     * @return {bool}
     */
    public static function set($key, $value)
    {
        if (!Check::simpleName($key)) {
            Debug::error("Component name invalid: $key");
            return false;
        }
        self::$components[$key] = $value;
        return true;
    }
}