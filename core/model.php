<?php
/**
 * core/model.php
 *
 * The class provides some basic functionality for models.
 *
 * @version 3.0
 * @author  Joey Kimsey <Joey@thetempusproject.com>
 * @link    https://TheTempusProject.com/Core
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */
namespace TempusProjectCore;

use TempusProjectCore\Functions\Debug;
use TempusProjectCore\Functions\Routes;
use TempusProjectCore\Functions\Check;

class Model
{
    public static $enabled = false;
    const MODEL_VERSION = '0.0.0';
    public $requiredModels = [];
    public $configName = '';
    public $installFlags = [
        'installDB' => false,
        'installPermissions' => false,
        'installConfigs' => false,
        'installResources' => false,
        'installPreferences' => false
    ];
    public $permissions = '';
    public $preferences = '';

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

    /**
     * Installs any resources needed for the model. Resources are generally
     * database entires or other structure data needed for the mdoel.
     *
     * @return bool - The status of the completed install
     */
    public function installResources()
    {
        return true;
    }
    /**
     * The model constructor.
     */
    public function __construct()
    {
        Debug::log('Model Constructed: '.get_class($this));
        $this->load();
    }

    /**
     * Tells the installer which types of integrations your model needs to install.
     *
     * @return bool - if the model was loaded without error
     */
    public function load()
    {
        return true;
    }
    
    /**
     * Checks if the model and database are both enabled.
     *
     * @return bool - if the model is enabled or not
     */
    private static function enabled()
    {
        return self::$enabled;
    }
}
