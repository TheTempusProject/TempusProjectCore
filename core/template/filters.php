<?php
/**
 * core/template/filters.php
 *
 * This class is for managing template filters.
 *
 * @version 3.0
 * @author  Joey Kimsey <Joey@thetempusproject.com>
 * @link    https://TheTempusProject.com/Core
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */
namespace TempusProjectCore\Template;

use TempusProjectCore\Functions\Check;
use TempusProjectCore\Functions\Debug;

class Filters
{
    public static $filters = [];

    /**
     * Iterates through the filters list on $data. Leaving only the internal
     * contents of enabled filters and removing all traces of disabled filters.
     *
     * @param {string} [$data]
     * @return {string}
     */
    public static function apply($data)
    {
        if (empty(self::$filters)) {
            return $data;
        }
        foreach (self::$filters as $pattern) {
            if ($pattern['enabled']) {
                $data = trim(preg_replace($pattern['match'], $pattern['replace'], $data));
            }
        }
        return $data;
    }

    /**
     * Adds a filter.
     *
     * @param {string} [$filterName]
     * @param {string} [$match]
     * @param {string} [$replace]
     * @param {bool} [$enabled] - Whether the filter should be enabled or disabled.
     */
    public static function add($filterName, $match, $replace, $enabled = false)
    {
        if (!Check::simpleName($filterName)) {
            Debug::error("Filter name invalid: $filterName");
            return;
        }
        if (isset(self::$filters[$filterName])) {
            Debug::error("Filter already exists: $filterName");
            return;
        }
        self::$filters[$filterName] = [
            'name'    => $filterName,
            'match'   => $match,
            'replace' => $replace,
            'enabled' => $enabled,
        ];
        return;
    }

    /**
     * Removes a filter.
     *
     * @param {string} [$filterName]
     * @return {bool}
     */
    public function remove($filterName)
    {
        if (!Check::simpleName($filterName)) {
            Debug::error("Filter name invalid: $filterName");
            return false;
        }
        if (!isset(self::$filters[$filterName])) {
            Debug::error("Filter does not exist: $filterName");
            return false;
        }
        unset(self::$filters[$filterName]);
        return true;
    }

    /**
     * Enable a filter.
     *
     * @param {string} [$filterName]
     * @return {bool}
     */
    public function enable($filterName)
    {
        if (!Check::simpleName($filterName)) {
            Debug::error("Filter name invalid: $filterName");
            return false;
        }
        if (!isset(self::$filters[$filterName])) {
            Debug::error("Filter does not exist: $filterName");
            return false;
        }
        self::$filters[$filterName]['enabled'] = true;
        return true;
    }

    /**
     * Disables a filter.
     *
     * @param {string} [$filterName]
     * @return {bool}
     */
    public function disable($filterName)
    {
        if (!Check::simpleName($filterName)) {
            Debug::error("Filter name invalid: $filterName");
            return false;
        }
        if (!isset(self::$filters[$filterName])) {
            Debug::error("Filter does not exist: $filterName");
            return false;
        }
        self::$filters[$filterName]['enabled'] = false;
        return true;
    }
}