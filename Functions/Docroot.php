<?php
/**
 * Functions/Docroot.php
 *
 * This class is used to easily return file and document location information.
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

use TempusProjectCore\Classes\Input;
use TempusProjectCore\Classes\Debug;

class Docroot
{
    /**
     * Finds the correct directory for the specified type.
     *
     * @return string - The directory location.
     */
    public static function getLocation($type, $file = null, $folder = null)
    {
        $locationData = [];
        switch ($type) {
            case 'models':
                $locationData['root'] = self::getFull();
                if ($folder != null) {
                    $locationData['folder'] = $folder . '/';
                } else {
                    $locationData['folder'] = 'Models/';
                }
                $locationData['file'] = $file . '.php';
                $file = ucfirst($file);
                $locationData['className'] = APP_SPACE . '\Models\\' . $file;
                $locationData['fullPath'] = $locationData['root'] . $locationData['folder'] . $locationData['file'];
                $locationData['errorString'] = 'Model could not be found: ' . $locationData['fullPath'];
                break;

            case 'views':
                $viewName = strtolower(str_replace('.', '_', $file));
                $locationData['root'] = self::getFull();
                $locationData['folder'] = 'Views/';
                $locationData['file'] = 'view_' . $viewName . '.php';
                $locationData['fullPath'] = $locationData['root'] . $locationData['folder'] . $locationData['file'];
                $locationData['errorString'] = 'View could not be found: ' . $locationData['fullPath'];
                break;

            case 'controllers':
                $locationData['root'] = self::getFull();
                $locationData['folder'] = 'Controllers/';
                $locationData['file'] = $file . '.php';
                $locationData['className'] = APP_SPACE . '\Controllers\\' . $file;
                $locationData['fullPath'] = $locationData['root'] . $locationData['folder'] . $locationData['file'];
                $locationData['errorString'] = 'Controller could not be found: ' . $locationData['fullPath'];
                break;

            case 'template':
                $locationData['root'] = self::getFull();
                $locationData['folder'] = 'Templates/' . $file . '/';
                $locationData['file'] = $file . '.tpl';
                $locationData['fullPath'] = $locationData['root'] . $locationData['folder'] . $locationData['file'];
                $locationData['errorString'] = 'Template file could not be found: ' . $locationData['fullPath'];
                break;

            case 'templateLoader':
                $locationData['root'] = self::getFull();
                $locationData['folder'] = 'Templates/' . $file . '/';
                $locationData['file'] = $file . '.inc.php';
                $locationData['className'] = APP_SPACE . '\Templates\\' . ucfirst($file) . 'Loader';
                $locationData['fullPath'] = $locationData['root'] . $locationData['folder'] . $locationData['file'];
                $locationData['errorString'] = 'Template loader could not be found: ' . $locationData['fullPath'];
                break;

            case 'errors':
                $locationData['root'] = self::getFull();
                $locationData['folder'] = 'Errors/';
                $locationData['file'] = $file . '.php';
                $locationData['fullPath'] = $locationData['root'] . $locationData['folder'] . $locationData['file'];
                $locationData['errorString'] = 'Error could not be found: ' . $locationData['fullPath'];
                break;

            case 'appConfig':
                $locationData['root'] = self::getFull();
                $locationData['folder'] = 'App/';
                $locationData['file'] = 'config.json';
                $locationData['fullPath'] = $locationData['root'] . $locationData['folder'] . $locationData['file'];
                $locationData['errorString'] = 'Application config could not be found: ' . $locationData['fullPath'];
                break;

            case 'appConfigDefault':
                $locationData['root'] = self::getFull();
                $locationData['folder'] = 'App/';
                $locationData['file'] = 'config.default.json';
                $locationData['fullPath'] = $locationData['root'] . $locationData['folder'] . $locationData['file'];
                $locationData['errorString'] = 'Default application config could not be found: ' . $locationData['fullPath'];
                break;

            case 'configDefault':
                $locationData['root'] = self::getCorePath();
                $locationData['folder'] = 'Resources/';
                $locationData['file'] = 'config.json';
                $locationData['fullPath'] = $locationData['root'] . $locationData['folder'] . $locationData['file'];
                $locationData['errorString'] = 'Default config could not be found: ' . $locationData['fullPath'];
                break;

            case 'appPreferences':
                $locationData['root'] = self::getFull();
                $locationData['folder'] = 'App/';
                $locationData['file'] = 'preferences.json';
                $locationData['fullPath'] = $locationData['root'] . $locationData['folder'] . $locationData['file'];
                $locationData['errorString'] = 'Application preferences could not be found: ' . $locationData['fullPath'];
                break;

            case 'appPreferencesDefault':
                $locationData['root'] = self::getFull();
                $locationData['folder'] = 'App/';
                $locationData['file'] = 'preferences.default.json';
                $locationData['fullPath'] = $locationData['root'] . $locationData['folder'] . $locationData['file'];
                $locationData['errorString'] = 'Default application preferences could not be found: ' . $locationData['fullPath'];
                break;
                
            case 'preferencesDefault':
                $locationData['root'] = self::getCorePath();
                $locationData['folder'] = 'Resources/';
                $locationData['file'] = 'preferences.json';
                $locationData['fullPath'] = $locationData['root'] . $locationData['folder'] . $locationData['file'];
                $locationData['errorString'] = 'Default preferences could not be found: ' . $locationData['fullPath'];
                break;

            case 'appPermissions':
                $locationData['root'] = self::getFull();
                $locationData['folder'] = 'App/';
                $locationData['file'] = 'permissions.json';
                $locationData['fullPath'] = $locationData['root'] . $locationData['folder'] . $locationData['file'];
                $locationData['errorString'] = 'Application permissions could not be found: ' . $locationData['fullPath'];
                break;

            case 'appPermissionsDefault':
                $locationData['root'] = self::getFull();
                $locationData['folder'] = 'App/';
                $locationData['file'] = 'permissions.default.json';
                $locationData['fullPath'] = $locationData['root'] . $locationData['folder'] . $locationData['file'];
                $locationData['errorString'] = 'Default application permissions could not be found: ' . $locationData['fullPath'];
                break;

            case 'permissionsDefault':
                $locationData['root'] = self::getCorePath();
                $locationData['folder'] = 'Resources/';
                $locationData['file'] = 'permissions.json';
                $locationData['fullPath'] = $locationData['root'] . $locationData['folder'] . $locationData['file'];
                $locationData['errorString'] = 'Default permissions could not be found: ' . $locationData['fullPath'];
                break;

            case 'imageUploadFolder':
                $locationData['root'] = self::getFull();
                $locationData['folder'] = 'Uploads/Images/';
                $locationData['file'] = $file . '/';
                $locationData['fullPath'] = $locationData['root'] . $locationData['folder'] . $locationData['file'];
                $locationData['errorString'] = 'Image upload folder could not be found.';
                break;

            case 'imageUploadFile':
                $locationData['root'] = self::getFull();
                $locationData['folder'] = 'Uploads/Images/' . $folder . '/';
                $locationData['file'] = $file;
                $locationData['fullPath'] = $locationData['root'] . $locationData['folder'] . $locationData['file'];
                $locationData['errorString'] = 'Image could not be found.';
                break;

            case 'formChecks':
                $locationData['root'] = self::getFull();
                $locationData['folder'] = 'App/';
                $locationData['file'] = 'forms.php';
                $locationData['className'] = APP_SPACE . '\\Forms';
                $locationData['fullPath'] = $locationData['root'] . $locationData['folder'] . $locationData['file'];
                $locationData['errorString'] = 'Form validation class could not be found.';
                break;

            case 'filters':
                $locationData['root'] = self::getFull();
                $locationData['folder'] = 'App/';
                $locationData['file'] = 'filters.php';
                $locationData['className'] = APP_SPACE . '\\Filters';
                $locationData['fullPath'] = $locationData['root'] . $locationData['folder'] . $locationData['file'];
                $locationData['errorString'] = 'Custom filter class could not be found.';
                break;

            case 'installer':
                $locationData['root'] = self::getFull();
                $locationData['folder'] = 'App/';
                $locationData['file'] = 'install.json';
                $locationData['fullPath'] = $locationData['root'] . $locationData['folder'] . $locationData['file'];
                $locationData['errorString'] = 'Install config could not be found.';
                break;

            case 'htaccess':
                $locationData['root'] = self::getFull();
                $locationData['folder'] = '';
                $locationData['file'] = '.htaccess';
                $locationData['fullPath'] = $locationData['root'] . $locationData['folder'] . $locationData['file'];
                $locationData['errorString'] = '.htaccess could not be found.';
                break;

            default:
                $out = null;
                break;
        }
        if (isset($locationData['fullPath'])) {
            $locationData['error'] = !file_exists($locationData['fullPath']);
            return (object) $locationData;
        }
        return $out;
    }
    /**
     * This takes the provided url and returns it as a filtered array.
     *
     * @param string $url - A provided url to be parsed.
     *
     * @return array   - The filtered and exploded (GET) URL.
     */
    public static function parseUrl($url = null)
    {
        if (!empty($url)) {
            Debug::info('Using provided URL.');
            return explode('/', filter_var(rtrim($url, '/'), FILTER_SANITIZE_URL));
        }
        return explode('/', filter_var(rtrim(Input::get('url'), '/'), FILTER_SANITIZE_URL));
    }

    public static function getCorePath()
    {
        $unixFriendly = str_replace('\\', '/', __DIR__);
        $fullArray = explode('/', $unixFriendly);
        array_pop($fullArray);
        $docroot = implode('/', $fullArray) . '/';
        return $docroot;
    }
    /**
     * Finds the root directory of the application.
     *
     * @return string - The applications root directory.
     */
    public static function getRoot()
    {
        $fullArray = explode('/', $_SERVER['PHP_SELF']);
        array_pop($fullArray);
        $docroot = implode('/', $fullArray) . '/';
        return $docroot;
    }

    /**
     * Retrieve the (GET) url as it was passed from the .htaccess file.
     *
     * @return string - The filtered url.
     */
    public static function getUrl()
    {
        return filter_var(rtrim(Input::get('url'), '/'), FILTER_SANITIZE_URL);
    }

    /**
     * finds the physical location of the application
     *
     * @return string - The root file location for the application.
     */
    public static function getAddress()
    {
        return self::getProtocol() . "://" . $_SERVER['HTTP_HOST'] . self::getRoot();
    }

    /**
     * finds the physical location of the application
     *
     * @return string - The root file location for the application.
     */
    public static function getFull()
    {
        return $_SERVER['DOCUMENT_ROOT'] . self::getRoot();
    }

    /**
     * A function to display debug information about the document location.
     */
    public static function getDebug()
    {
        $fullArray = explode('/', $_SERVER['PHP_SELF']);
        $start = array_pop($fullArray);
        $docroot = implode('/', $fullArray) . '/';
        Debug::info('PHP_SELF: ' . $_SERVER['PHP_SELF']);
        Debug::info('Full array from PHP_SELF: ');
        Debug::v($fullArray);
        Debug::info('array pop: ' . $start);
        Debug::info('Docroot: ' . $docroot);
    }

    /**
     * Determines if the server is using a secure transfer protocol or not.
     *
     * @return string - The string representation of the server's transfer protocol
     */
    public static function getProtocol()
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return 'https';
        }
        if ($_SERVER['SERVER_PORT'] == 443) {
            return 'https';
        }
        return 'http';
    }
}
