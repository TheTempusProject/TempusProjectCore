<?php
/**
 * core/model.php
 *
 * The model provides some very basic functionality for models. These
 * functions exist to prevent errors with installing and uninstalling.
 *
 * @version 2.1
 * @author  Joey Kimsey <JoeyKimsey@thetempusproject.com>
 * @link    https://TheTempusProject.com/Core
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */
namespace TempusProjectCore\Core;

class Model extends TPCore
{
    public static $tableName = "xxxxxx";
    public static $configName = "xxxxxx";
    public static $permissions = "xxxxxx";
    public static $preferences = "xxxxxx";
    public static $enabled = false;
    
    /**
     * The model constructor.
     */
    public function __construct()
    {
        Debug::log('Model Constructed: '.get_class($this));
        $this->load();
    }

    /**
     * Returns the current model version.
     *
     * @return string - the correct model version
     */
    public static function modelVersion()
    {
        return '0.0.0';
    }
    
    /**
     * Returns an array of models required to run this model without error.
     *
     * @return array - An array of models
     */
    public static function requiredModels()
    {
        $required = [];
        return $required;
    }

    /**
     * Tells the installer which types of integrations your model needs to install.
     *
     * @return array - Install flags
     */
    public static function installFlags()
    {
        $flags = [
            'installDB' => false,
            'installPermissions' => false,
            'installConfigs' => false,
            'installResources' => false,
            'installPreferences' => false
        ];
        return $flags;
    }

    /**
     * Tells the installer which types of integrations your model needs to install.
     *
     * @return bool - if the model was loaded without error
     */
    public static function load()
    {
        return true;
    }

    /**
     * This function is used to install database structures needed for this model.
     *
     * @return boolean - The status of the completed install
     */
    public static function installDB()
    {
        return true;
    }

    /**
     * Install configuration options needed for the model.
     *
     * @return bool - If the configurations were added without error
     */
    public static function installConfigs()
    {
        return true;
    }

    /**
     * Installs any resources needed for the model. Resources are generally
     * database entires or other structure data needed for the mdoel.
     *
     * @return bool - The status of the completed install
     */
    public static function installResources()
    {
        return true;
    }

    /**
     * Install preferences needed for the model.
     *
     * @return bool - If the preferences were added without error
     */
    public static function installPreferences()
    {
        return true;
    }

    /**
     * Install permissions needed for the model.
     *
     * @return bool - If the permissions were added without error
     */
    public static function installPermissions()
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

    /**
     * This method will remove all the installed model components.
     *
     * @return bool - If the uninstall was completed without error
     */
    public static function uninstall()
    {
        Preference::removePrefs(self::$preferences, true);
        Permission::removePerms(self::$permissions, true);
        Config::removeConfigCategory(self::$configName);
        self::$db->removeTable(self::$tableName);
        return true;
    }
}
