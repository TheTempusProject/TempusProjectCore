<?php
/**
 * Classes/Redirect.php.
 *
 * This class is used for header modification and page redirection.
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

class Redirect
{
    /**
     * The main redirect function. This will automatically call the
     * error controller if the value passed to it is numerical. It will
     * automatically populate the url based on the config and add the 
     * $data string at the end
     * 
     * @param string|int $data - The desired redirect location (string for location and integer for error page).
     */
    public static function to($data)
    {
        if (!Debug::status()) {
            if (is_numeric($data)) {
                header('Location: ' . Config::get('main/base') . 'Errors/' . $data);
            } else {
                if (!Check::path($data)) {
                    Debug::info('Invalid Redirect path.');
                } else {
                    header('Location: ' . Config::get('main/base') . $data);
                }
            }
            exit();
        } else {
            Debug::warn('Redirect is Disabled in Debugging mode!');
        }
    }
}
