<?php
/**
 * Functions/ExceptionHandler.php
 *
 * This function coordinates with uncaught exceptions to channel
 * them into the debug log for the application.
 *
 * @version 1.0
 *
 * @author  Joey Kimsey <JoeyKimsey@thetempusproject.com>
 *
 * @link    https://TheTempusProject.com/Core
 *
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */

namespace TempusProjectCore\Functions;

use TempusProjectCore\Classes\Debug as Debug;

class ExceptionHandler
{
    /**
     * The fall-back exception handler.
     *
     * @param object $data - The uncaught exception object.
     *
     * @todo  - Account for the possibility of strings/arrays/objects/etc
     *          and ensure you deal with each accordingly
     */
    public static function exceptionHandler($data)
    {
        Debug::error("Caught Exception: $data");
    }
}
