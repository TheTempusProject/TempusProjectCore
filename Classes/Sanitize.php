<?php
/**
 * Classes/Sanitize.php
 *
 * This class is used to sanitize user input.
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

class Sanitize
{
    /**
     * This function strips all html tags except for p/a/br from the given string.
     * 
     * @param  String $data - The string to be parsed
     * 
     * @return string   - The sanitized string.
     */
    public static function content_short($data)
    {
        $out = strip_tags($data, '<p><a><br>');
        return $out;
    }

    /**
     * This function is solely to remove $'s from the rich HTML editor 
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
