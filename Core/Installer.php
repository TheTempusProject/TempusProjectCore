<?php
/**
 * Core/Installer.php
 *
 * This class is used for the installation, regulation, tracking, and updating of
 * the application. It handles installing the application, installing and updating
 * models as well as the database, and generating and checking the htaccess file.
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

use TempusProjectCore\Classes\Debug;
use TempusProjectCore\Classes\Config;
use TempusProjectCore\Classes\DB;
use TempusProjectCore\Classes\Session;
use TempusProjectCore\Classes\Cookie;
use TempusProjectCore\Classes\Code;
use TempusProjectCore\Classes\Redirect;
use TempusProjectCore\Classes\Check;
use TempusProjectCore\Classes\Input;
use TempusProjectCore\Classes\Email;
use TempusProjectCore\Classes\Pagination;
use TempusProjectCore\Classes\Hash;
use TempusProjectCore\Classes\Token;
use TempusProjectCore\Functions\Routes;
use TempusProjectCore\Classes\CustomException;

class Installer extends Controller
{
    private $override = false;
    private $status = null;
    private static $installJson = null;
    private static $errors = [];

    /**
     * The constructor
     */
    public function __construct()
    {
        Debug::log('Installer Initiated.');
        if (self::$installJson === null) {
            self::$installJson = $this->getJson();
        }
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

    public function getModelVersion($name, $folder = null)
    {
        $docroot = Routes::getLocation('models', $name, $folder);
        if ($docroot->error) {
            self::$errors = array_merge(self::$errors, ['errorInfo' => "$name was not installed: $docroot->errorString"]);
            return false;
        }
        require_once $docroot->fullPath;
        if (method_exists($docroot->className, 'modelVersion')) {
            $version = call_user_func_array([$docroot->className, 'modelVersion'], []);
        } else {
            $version = 'unknown';
        }
        return $version;
    }

    public function getModelList($folder = null)
    {
        $dir = Routes::getLocation('models', '', $folder)->folder;
        if (!file_exists($dir)) {
            self::$errors = array_merge(self::$errors, ['errorInfo' => "Models folder is missing: $dir"]);
            return [];
        }
        $files = scandir($dir);
        array_shift($files);
        array_shift($files);
        foreach ($files as $key => $value) {
            $modelList[] = str_replace('.php', '', $value);
        }
        return $modelList;
    }

    public function getModelVersionList($folder = null)
    {
        $modelsList = $this->getModelList($folder);
        foreach ($modelsList as $model) {
            $modelList[] = (object) [
                'name' => $model,
                'version' => $this->getModelVersion($model),
            ];
        }
        return $modelList;
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
    public function installModels($directory = null, $modelList = [], $flags = null)
    {
        self::$db = DB::getInstance('', '', '', '', true);
        $query = 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
                  SET time_zone = "+05:00"';
        self::$db->raw($query);
        if (empty($modelList)) {
            $list = $this->getModelList($directory);
            foreach ($list as $model) {
                $modelList[] = [$model => true];
            }
        }
        Debug::log('Installing selected models in folder: ' . $directory);
        foreach ($modelList as $key => $value) {
            if ($value === true) {
                if (!$this->installModel($key, $directory, $flags)) {
                    $fail = true;
                }
            }
        }

        if (!isset($fail)) {
            return true;
        }

        return false;
    }

    public function uninstallModel($name, $folder = null, $flags = null)
    {
        Debug::log('Uninstalling Model: ' . $name);
        $docroot = Routes::getLocation('models', $name, $folder);
        if ($docroot->error) {
            self::$errors = array_merge(self::$errors, ['errorInfo' => "$name was not installed: $docroot->errorString"]);
            return false;
        }
        $errors = null;
        require_once $docroot->fullPath;
        $node = $this->getNode($name);
        if ($node === false) {
            Debug::error('Cannot uninstall model that has not been installed.');
            return false;
        }
        if ($node['installStatus'] === 'not installed') {
            Debug::error('Cannot uninstall model that has not been installed.');
            return false;
        }
        if (!method_exists($docroot->className, 'uninstall')) {
            Debug::error('Model has no uninstall method.');
            return false;
        }
        if (!call_user_func_array([$docroot->className, 'uninstall'], [])) {
            $errors[] = ['errorInfo' => "$name failed to execute uninstall properly."];
        } else {
            $node['currentVersion'] = '';
            $node['installStatus'] = 'uninstalled';
            $node['lastUpdate'] = time();
        }
        $installTypes = ['installDB', 'installPermissions', 'installConfigs', 'installResources', 'installPreferences'];
        foreach ($installTypes as $type) {
            if ($node[$type] !== 'skipped') {
                $node[$type] = 'uninstalled';
            }
        }
        $this->setNode($name, $node, true);
        if ($errors !== null) {
            $errors[] = ['errorInfo' => "$name did not uninstall properly."];
            self::$errors = array_merge(self::$errors, $errors);
            return false;
        }
        self::$errors = array_merge(self::$errors, ['errorInfo' => "$name has been uninstalled."]);

        return true;
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
    public function installModel($name, $folder = null, $flags = null)
    {
        Debug::log('Installing Model: ' . $name);
        $errors = null;

        //require the files
        $docroot = Routes::getLocation('models', $name, $folder);
        if ($docroot->error) {
            Debug::error("$name was not installed: $docroot->errorString");
            return false;
        }
        require_once $docroot->fullPath;

        // Set the model info
        $node = $this->getNode($name);
        if ($node === false) {
            $modelInfo = [
                'name' => $name,
                'installDate' => time(),
                'lastUpdate' => time(),
                'installStatus' => 'not installed',
                'currentVersion' => $this->getModelVersion($name)
            ];
        } else {
            $modelInfo = $node;
        }

        // Check for installer flags, currently reequired for alll models.
        $installTypes = ['installDB', 'installPermissions', 'installConfigs', 'installResources', 'installPreferences'];
        if (method_exists($docroot->className, 'installFlags')) {
            $modelFlags = call_user_func_array([$docroot->className, 'installFlags'], []);
        } else {
            foreach ($installTypes as $type) {
                $modelFlags[$type] = false;
            }
        }

        // Determine the modules that can and should be installed.
        // This is the safeguard for when a model doesn't have an installer you are requiring
        foreach ($installTypes as $type) {
            if (!isset($flags[$type])) {
                $finalFlags[$type] = $modelFlags[$type];
                continue;
            }
            if ($flags[$type] == false) {
                $finalFlags[$type] = $flags[$type];
                continue;
            }
            if ($modelFlags[$type] == false) {
                Debug::warn("$type cannot be installed due to installFlags on the model.");
                $finalFlags[$type] = $modelFlags[$type];
                continue;
            }
            $finalFlags[$type] = $flags[$type];
            continue;
        }
        $flags = $finalFlags;

        if ($this->getModelVersion($name) === $modelInfo['currentVersion'] && $modelInfo['installStatus'] === 'installed') {
            self::$errors = array_merge(self::$errors, ['errorInfo' => "$name has already been successfully installed"]);
            return true;
        }
        foreach ($installTypes as $Type) {
            if ($flags[$Type] === false) {
                if (empty($modelInfo[$Type])) {
                    if ($modelFlags[$Type] === true) {
                        $modelInfo[$Type] = 'not installed';
                    } else {
                        $modelInfo[$Type] = 'skipped';
                    }
                }
                continue;
            }
            if (!empty($modelInfo[$Type]) && $modelInfo[$Type] == 'success') {
                Debug::warn("$Type has already been successfully installed");
                continue;
            }
            if (!method_exists($docroot->className, $Type)) {
                $errors[] = ['errorInfo' => "$name $Type method not found."];
                $modelInfo[$Type] = 'not found';
                continue;
            }
            if (!call_user_func_array([$docroot->className, $Type], [])) {
                $errors[] = ['errorInfo' => "$name failed to execute $Type properly."];
                $modelInfo[$Type] = 'error';
                continue;
            }
            $modelInfo[$Type] = 'success';
            continue;
        }
        $modelInfo['currentVersion'] = $this->getModelVersion($name);
        $this->setNode($name, $modelInfo, true);
        $this->updateInstallStatus($name);

        if ($errors !== null) {
            $errors[] = ['errorInfo' => "$name did not install properly."];
            self::$errors = array_merge(self::$errors, $errors);
            return false;
        }
        self::$errors = array_merge(self::$errors, ['errorInfo' => "$name has been installed."]);
        return true;
    }
    private function updateInstallStatus($name)
    {
        $node = $this->getNode($name);
        if ($node === false) {
            $modelInfo = [
                'name' => $name,
                'installDate' => time(),
                'lastUpdate' => time(),
                'installStatus' => 'not installed',
                'currentVersion' => $this->getModelVersion($name)
            ];
        } else {
            $modelInfo = $node;
        }

        $installTypes = ['installDB', 'installPermissions', 'installConfigs', 'installResources', 'installPreferences'];
        foreach ($installTypes as $type) {
            if (!in_array($modelInfo[$type], ['success', 'skipped'])) {
                $modelInfo['installStatus'] = 'partially installed';
                Debug::error($type);
                break;
            }
            $modelInfo['installStatus'] = 'installed';
        }
        $this->setNode($name, $modelInfo, true);
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
    protected function generateHtaccess($docroot = null, $rewrite = true)
    {
        if (empty($docroot)) {
            $docroot = Routes::getRoot();
        }
        $out = "";
        if ($rewrite === true) {
            $out .= "RewriteEngine On";
        }
        $out .= "
RewriteBase $docroot

# Tracking pixel
RewriteRule ^pixel/(.*)$ index.php?tracking=pixel&url=$1 [L,NC,QSA]

# Intercepts for images not found
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^images/(.*)$ index.php?error=image404&url=$1 [L,NC,QSA]

# Intercepts for uploads not found
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^uploads/(.*)$ index.php?error=upload404&url=$1 [L,NC,QSA]

# Intercepts other errors
RewriteRule ^errors/(.*)$ index.php?error=$1 [L,NC,QSA]

# Intercept all traffic not originating locally and not going to images or uploads
RewriteCond %{REMOTE_ADDR} !^127\.0\.0\.1
RewriteCond %{REMOTE_ADDR} !^\:\:1
RewriteCond %{REQUEST_URI} !^images/(.*)$ [NC]
RewriteCond %{REQUEST_URI} !^uploads/(.*)$ [NC]
RewriteRule ^(.+)$ index.php?url=$1 [QSA,L]

# Catchall for any non existent files or folders
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.+)$ index.php?url=$1 [QSA,L]";
        return $out;
    }
    
    protected function buildHtaccess()
    {
        $write = '';
        if (file_exists(Routes::getLocation('htaccess')->fullPath)) {
            $currentHtaccess = file_get_contents(Routes::getLocation('htaccess')->fullPath);
            if ($currentHtaccess !== $this->generateHtaccess()) {
                $findRewrite1 = "RewriteEngine On";
                $findRewrite2 = "\nRewriteBase " . Routes::getRoot() . "\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule ^(.+)$ index.php?url=$1 [QSA,L]";
                if (stripos($currentHtaccess, $findRewrite1) === false) {
                    $write .= $this->generateHtaccess();
                } elseif (stripos($currentHtaccess, $findRewrite2) === false) {
                    $write .= $this->generateHtaccess(null, false);
                }
            } else {
                $write = $currentHtaccess;
            }
        } else {
            $write = $this->generateHtaccess();
        }

        file_put_contents(Routes::getLocation('htaccess')->fullPath, $write);
        return true;
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
     */
    public function checkHtaccess($create = false)
    {
        if (file_exists(Routes::getLocation('htaccess')->fullPath)) {
            $htaccess = file_get_contents(Routes::getLocation('htaccess')->fullPath);
            if ($htaccess === $this->generateHtaccess()) {
                return true;
            }
            $check = 0;
            $findRewrite1 = "RewriteEngine On\n";
            $findRewrite2 = "RewriteBase " . Routes::getRoot() . "\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-l\nRewriteRule ^(.+)$ index.php?url=$1 [QSA,L]";
            if (stripos($htaccess, $findRewrite1)) {
                $check++;
            }
            if (stripos($htaccess, $findRewrite2)) {
                $check++;
            }
            if ($check === 2) {
                if ($create) {
                    $errors[] = ['errorInfo' => "Previous htaccess file did not need to be edited."];
                }
                return true;
            }
        }
        if (!$create) {
            return false;
        }
        return $this->buildHtaccess();
    }

    public function checkSession()
    {
        if (!isset(self::$installJson['installHash'])) {
            Debug::error("install hash not found on file.");

            return false;
        }
        if (!Session::exists('installHash') && !Cookie::exists('installHash')) {
            Debug::error("install hash not found in session or cookie.");

            return false;
        }
        if (Cookie::exists('installHash') && !Session::exists('installHash')) {
            if (Cookie::get('installHash') !== self::$installJson['installHash']) {
                Cookie::delete('installHash');
                return false;
            }
            Session::put('installHash', Cookie::get('installHash'));
        }
        if (Session::get('installHash') !== self::$installJson['installHash']) {
            Session::delete('installHash');
            return false;
        }
        return true;
    }

    public function nextStep($page, $redirect = false)
    {
        $newHash = Code::genInstall();
        $this->setNode('installHash', $newHash, true);
        $this->setNode('installStatus', $page, true);
        Session::put('installHash', $newHash);
        Cookie::put('installHash', $newHash);
        if ($redirect === true) {
            Redirect::reload();
        }
        return true;
    }

    public function getStatus()
    {
        if (isset(self::$installJson['installStatus'])) {
            return self::$installJson['installStatus'];
        }
        Debug::error("install status not found.");

        return false;
    }

    public function getComposerJson()
    {
        $docLocation = Routes::getLocation('composerJson');
        if ($docLocation->error) {
            Debug::error('No install json found.');
            return false;
        }
        return json_decode(file_get_contents($docLocation->fullPath), true);
    }

    public function getComposerLock()
    {
        $docLocation = Routes::getLocation('composerLock');
        if ($docLocation->error) {
            Debug::error('No install json found.');
            return false;
        }
        return json_decode(file_get_contents($docLocation->fullPath), true);
    }

    public function getJson()
    {
        $docLocation = Routes::getLocation('installer');
        if ($docLocation->error) {
            Debug::error('No install json found.');
            return false;
        }
        return json_decode(file_get_contents($docLocation->fullPath), true);
    }

    public function getNode($name)
    {
        if (isset(self::$installJson[$name])) {
            return self::$installJson[$name];
        }
        Debug::error("install node not found: $name");
        
        return false;
    }

    public function saveJson()
    {
        if (file_put_contents(Routes::getLocation('installer')->fullPath, json_encode(self::$installJson))) {
            return true;
        }
        return false;
    }

    public function setNode($name, $value, $save = false)
    {
        self::$installJson[$name] = $value;
        if ($save !== false) {
            return $this->saveJson();
        }
        return true;
    }
}
