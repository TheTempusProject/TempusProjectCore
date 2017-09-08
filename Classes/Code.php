<?php
/**
 * Classes/Code.php.
 *
 * This class is used for manipulation of custom-codes used by the application.
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

class Code
{
    /**
     * Generates a new confirmation code.
     *
     * @return string
     */
    public static function new_confirmation()
    {
        $code = md5(uniqid());
        Debug::log("Code Generated: Confirmation: $code");
        return $code;
    }

    /**
     * Generates a new token code.
     *
     * @return string
     */
    public static function new_token()
    {
        $code = md5(uniqid());
        Debug::log("Code Generated: Token: $code");
        return $code;
    }
}
