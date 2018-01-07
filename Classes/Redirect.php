<?php
/**
 * Classes/Redirect.php
 *
 * This class is used for header modification and page redirection.
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

use TempusProjectCore\Functions\Docroot as Docroot;

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
        if (Debug::status('redirect')) {
            Debug::warn('Redirect is Disabled in Debugging mode!');
            exit();
        }
        if (is_numeric($data)) {
            header('Location: ' . Docroot::getAddress() . 'Errors/' . $data);
        } else {
            if (!Check::path($data)) {
                Debug::info('Invalid Redirect path.');
            } else {
                header('Location: ' . Docroot::getAddress() . $data);
            }
        }
    }
    
    /**
     * Refreshes the current page.
     *
     * @return null
     */
    public static function reload()
    {
        if (Debug::status('redirect')) {
            Debug::warn('Redirect is Disabled in Debugging mode!');
            exit();
        }
        header("Refresh:0");
    }
}
