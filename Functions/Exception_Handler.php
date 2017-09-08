<?php
/**
 * Functions/Exception_Handler.php.
 *
 * This function coordinates with uncaught exceptions to channel
 * them into the debug log for the application.
 *
 * @version 0.9
 *
 * @author  Joey Kimsey <joeyk4816@gmail.com>
 *
 * @link    https://github.com/JoeyK4816/tempus-project-core
 *
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */

namespace TempusProjectCore\Functions;

use TempusProjectCore\Classes\Debug as Debug;

class Handler
{
    /**
     * Our fall back exception handler.
     *
     * @param object $data - The uncaught exception object.
     */
    public static function Exception_Handler($data)
    {
        Debug::error("Caught Exception: $data");
    }
}
