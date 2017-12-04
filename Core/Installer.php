<?php
/**
 * Core/Installer.php
 *
 * This class is used for the installation, regulation, tracking, and updating of
 * the application. It handles installing the application, installing and updating
 * models as well as the database, and generating and checking the htaccess file.
 *
 * @todo  - Migrate a lot of the db functions into the DB class instead of here.
 *          Add the ability for the installer to pass the model install if the
 *          columns and stuff it is adding match what is in the database already,
 *          from there expand to update ability
 *          add some debugging
 *
 * @version 1.0
 *
 * @author  Joey Kimsey <JoeyKimsey@thetempusproject.com>
 *
 * @link    https://TheTempusProject.com/Core
 *
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */

namespace TempusProjectCore\Core;

use TempusProjectCore\Classes\Debug as Debug;
use TempusProjectCore\Classes\Config as Config;
use TempusProjectCore\Classes\DB as DB;
use TempusProjectCore\Classes\Session as Session;
use TempusProjectCore\Classes\Cookie as Cookie;
use TempusProjectCore\Classes\Redirect as Redirect;
use TempusProjectCore\Classes\Log as Log;
use TempusProjectCore\Classes\Check as Check;
use TempusProjectCore\Classes\Input as Input;
use TempusProjectCore\Classes\Email as Email;
use TempusProjectCore\Classes\Pagination as Pagination;
use TempusProjectCore\Classes\Issue as Issue;
use TempusProjectCore\Classes\Hash as Hash;
use TempusProjectCore\Classes\Token as Token;
use TempusProjectCore\Functions\Docroot as Docroot;
use TempusProjectCore\Classes\CustomException as CustomException;

class Installer extends Controller
{
    private $override = false;
    private $status = null;
    private static $errors = [];

    /**
     * The constructor...
     */
    public function __construct()
    {
        Debug::log('Installer Initiated.');
    }
    /**
     * This function automatically attempts to install all models in the
     * specified directory.
     *
     * NOTE: The 'Models/ folder is used by default.
     *
     * @param  string $directory - The directory you wish to install all
     *                             models from.
     *
     * @return boolean
     */
    public function getErrors()
    {
        return self::$errors;
    }
    /**
     * This function automatically attempts to install all models in the
     * specified directory.
     *
     * NOTE: The 'Models/ folder is used by default.
     *
     * @param  string $directory - The directory you wish to install all
     *                             models from.
     *
     * @return boolean
     */
    public function installFolder($directory = 'Models')
    {
        self::$db = DB::getInstance('', '', '', '', true);
        $query = 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
                  SET time_zone = "+05:00"';
        self::$db->raw($query);
        Debug::log('Installing all models in folder: ' . $directory);
        $dir = Docroot::getFull() . $directory . '/';
        $files = scandir($dir);
        array_shift($files);
        array_shift($files);
        foreach ($files as $key => $value) {
            if (!self::installModel($directory, str_replace('.php', '', $value))) {
                $fail = true;
            }
        }

        if (!isset($fail)) {
            return true;
        }

        return false;
    }

    /**
     * Requires the specified folder / model combination and calls
     * its install function.
     *
     * @param  string $folder - The folder containing the model.
     * @param  string $name   - The 'model.php' you are trying to install.
     *
     * @return boolean
     */
    public function installModel($folder, $name)
    {
        Debug::log('Installing Model: ' . $name);
        $docroot = Docroot::getLocation('models', $name, $folder);
        if ($docroot->error) {
            Issue::error("$name was not installed: $docroot->errorString");
            return false;
        }
        require_once $docroot->fullPath;
        if (!method_exists($docroot->className, 'install')) {
            self::$errors[] = ['errorInfo' => "$name Install method not found."];
            return false;
        }
        if (call_user_func_array([$docroot->className, 'install'], [])) {
            Issue::success("$name was successfully installed.");
            return true;
        }
        self::$errors[] = ['errorInfo' => "$name failed to install properly."];
        return false;
    }

    /**
     * Generates the default htaccess file for the application. This will funnel
     * all traffic that comes into the application directory to index.php where we
     * use that data to construct the desired page using the controller.
     *
     * @param  string $docroot - A custom document root to use instead of the default.
     *
     * @return string   - The generated contents of the htaccess file.
     */
    protected function generateHtaccess($docroot = null)
    {
        if (empty($docroot)) {
            $docroot = Docroot::getRoot();
        }
        $out = "RewriteEngine On
RewriteBase $docroot
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-l
RewriteRule ^(.+)$ index.php?url=$1 [QSA,L]";
        return $out;
    }

    /**
     * Checks the root directory for a .htaccess file and compares it with
     * the .htaccess file the application generates by default.
     *
     * NOTE: The $override flag will cause this function to automatically generate a
     * new htaccess file if the .htaccess found in the root directory does not match
     * the default generated version.
     *
     * @param  boolean $create - Optional flag to generate and save a new htaccess
     *                           if none is found.
     *
     * @return boolean - Returns true if the htaccess file was found or
     *                   created, false otherwise.
     *
     * @todo  - Core should not "Issue" anything, it should return it as an object
     */
    public function checkHtaccess($create = false)
    {
        if (file_exists(Docroot::getLocation('htaccess')->fullPath)) {
            if (file_get_contents(Docroot::getLocation('htaccess')->fullPath) === $this->generateHtaccess()) {
                if ($create) {
                    Issue::notice('Previous htaccess file did not need to be overridden.');
                }

                return true;
            } else {
                if ($this->$override) {
                    file_put_contents(Docroot::getLocation('htaccess')->fullPath, $this->generateHtaccess());
                    Issue::success('.htaccess file has been overridden successfully.');

                    return true;
                }
                Issue::error('The .htaccess file already exists in the root directory and appears to be modified from the default generated version. You will need to manually modify or remove it before continuing.');
                Issue::notice('The htaccess file could easily be created/used by other applications or even your hosting provider. Please double check before removing or modifying it.');

                return false;
            }
        } else {
            if ($create) {
                file_put_contents(Docroot::getLocation('htaccess')->fullPath, $this->generateHtaccess());
                Issue::success('.htaccess file generated successfully.');

                return true;
            }

            return false;
        }
    }
}
