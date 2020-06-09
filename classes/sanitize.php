<?php
/**
 * Classes/Sanitize.php
 *
 * This class is used to sanitize user input.
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

class Sanitize
{
    /**
     * This function strips all html tags except for p/a/br from the given string.
     *
     * @param  String $data - The string to be parsed
     *
     * @return string   - The sanitized string.
     */
    public static function contentShort($data)
    {
        return strip_tags($data, '<p><a><br>');
    }

    /**
     * This function is to remove $'s and brackets from the rich HTML editor
     * which are the only parts that cause parse issues
     *
     * @param  String $data - The string to be parsed
     *
     * @return string   - The sanitized string.
     */
    public static function rich($data)
    {
        $data = preg_replace('#\{#', '&#123;', $data);
        $data = preg_replace('#\}#', '&#125;', $data);
        $data = preg_replace('#\$#', '&#36;', $data);
        return $data;
    }
}
