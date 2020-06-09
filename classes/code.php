<?php
/**
 * Classes/Code.php
 *
 * This class is used for creation of custom codes used by the application.
 *
 * @todo    Better code generation.
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

class Code
{
    /**
     * Generates a new confirmation code.
     *
     * @return string
     */
    public static function genConfirmation()
    {
        $code = md5(uniqid());
        Debug::log("Code Generated: Confirmation: $code");
        return $code;
    }

    /**
     * Generates a new install hash.
     *
     * @return string
     */
    public static function genInstall()
    {
        $code = md5(uniqid());
        Debug::log("Code Generated: Token: $code");
        return $code;
    }

    /**
     * Generates a new token code.
     *
     * @return string
     */
    public static function genToken()
    {
        $code = md5(uniqid());
        Debug::log("Code Generated: Token: $code");
        return $code;
    }
}
