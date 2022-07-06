<?php
/**
 * classes/config.php
 *
 * This class handles all the hard-coded configurations.
 *
 * @version 3.0
 * @author  Joey Kimsey <Joey@thetempusproject.com>
 * @link    https://TheTempusProject.com/Core
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */
namespace TempusProjectCore\Classes;

use TempusProjectCore\Functions\{
    Debug, Check, Routes
};

class Config
{
    public static $config = false;
    private $location = false;
    private $initialized = false;

    /**
     * Default constructor which will attempt to load the config from the location specified.
     *
     * @param {string} [$location]
     * @return {null|object}
     */ 
    public function __construct($location) {
        $this->initialized = $this->load($location);
        if ($this->initialized !== false) {
            return $this;
        }
    }

    /**
     * Attempts to retrieve then set the configuration from a file.
     * @note This function will reset the config every time it is used.
     *
     * @param {string} $location
     * @return {bool}
     */
    public function load($location) {
        $contents = $this->getConfig($location);
        if ($contents === false) {
            return false;
        }
        self::$config = $contents;
        $this->location = $location;
        return true;
    }

    /**
     * Opens and decodes the config json from the location provided.
     *
     * @param {string} [$location]
     * @return {bool|array}
     */
    public function getConfig($location) {
        if (file_exists($location)) {
            return json_decode(file_get_contents($location), true);
        } else {
            Debug::warn("Config json not found: $location");
            return false;
        }
    }

    /**
     * Retrieves the config option for $name.
     *
     * @param {string} [$name] - Must be in <category>/<option> format.
     * @return {WILD}
     */
    public static function get($name) {
        $data = explode('/', $name);
        if (count($data) != 2) {
            Debug::warn("Config not properly formatted: $name");
            return;
        }
        if (self::$config === false) {
            Debug::warn("Config not loaded.");
            return;
        }
        $category = $data[0];
        $node = $data[1];
        if (!isset(self::$config[$category][$node])) {
            Debug::warn("Config not found: $name");
            return;
        }
        return self::$config[$category][$node];
    }

    /**
     * Retrieves the config option for $name and if the result is bool, converts it to a string.
     *
     * @param {string} [$name] - Must be in <category>/<option> format.
     * @return {WILD}
     */
    public static function getString($name) {
        $result = $this->get($name);
        if (is_bool($result)) {
            $result = ($result ? 'true' : 'false');
        }
        return $result;
    }

    /**
     * Saves the current config.
     *
     * @param {bool} [$default] - Whether or not to save a default copy.
     * @return {bool}
     */
    public function save($default = false) {
        if (self::$config === false) {
            Debug::warn("Config not loaded.");
            return false;
        }
        if ($this->location === false) {
            Debug::warn("Config location not set.");
            return false;
        }
        if ($default) {
            $locationArray = explode('.', $this->location);
            $last = array_pop($locationArray);
            $locationArray[] = 'default';
            $locationArray[] = $last;
            $defaultLocation = implode('.', $locationArray);
            if (!file_put_contents($defaultLocation, json_encode(self::$config))) {
                return false;
            }
        }
        if (file_put_contents($this->location, json_encode(self::$config))) {
            return true;
        }
        return false;
    }

    /**
     * Adds a new category to the $config array.
     *
     * @param {string} [$categoryName]
     * @return {bool}
     */
    public function addCategory($categoryName) {
        if (self::$config === false) {
            Debug::warn("Config not loaded.");
            return false;
        }
        if (!Check::simpleName($categoryName)) {
            Debug::warn("Category name invalid: $categoryName");
            return false;
        }
        if (isset(self::$config[$categoryName])) {
            Debug::warn("Category already exists: $categoryName");
            return false;
        }
        self::$config[$categoryName] = [];
        return true;
    }

    /**
     * Removes an existing category from the $config array.
     *
     * @param {string} [$categoryName]
     * @param {string} [$save]
     * @return {bool}
     */
    public function removeCategory($categoryName, $save = false, $saveDefault = true) {
        if (self::$config === false) {
            Debug::warn("Config not loaded.");
            return;
        }
        if (!isset(self::$config[$categoryName])) {
            Debug::warn("Config does not have ceategory: $categoryName");
            return false;
        }
        unset(self::$config[$categoryName]);
        if ($save) {
            $this->save($saveDefault);
        }
        return true;
    }

    /**
     * Add a new config option for the specified category.
     *
     * NOTE: Use a default option when using this function to
     * aid in failsafe execution.
     *
     * @param {string} [$category] - The primary category to add the option to.
     * @param {string} [$node] - The name of the new option.
     * @param {wild} [$value] - The desired value for the new option.
     * @param {bool} [$createMissing] - Whether or not to create missing options.
     * @param {bool} [$save] - Whether or not to save the config.
     * @param {bool} [$saveDefault] - Whether or not to save the default config.
     * @return {bool}
     */
    public function update($category, $node, $value, $createMissing = false, $save = false, $saveDefault = false) {
        if (self::$config === false) {
            Debug::warn("Config not loaded.");
            return false;
        }
        if (!Check::simpleName($category)) {
            Debug::warn("Category name invalid: $categoryName");
            return false;
        }
        if (!isset(self::$config[$category])) {
            if (!$createMissing) {
                Debug::warn("No such category: $category");
                return false;
            }
            $this->addCategory($category);
        }
        if (!Check::simpleName($node)) {
            Debug::warn("Node name invalid: $categoryName");
            return false;
        }
        if ($value === 'true') {
            $value = true;
        }
        if ($value === 'false') {
            $value = false;
        }
        if (!isset(self::$config[$category][$node])) {
            if (!$createMissing) {
                Debug::warn("Config not found.");
                return false;
            }
            $this->add($category, $node, $value);
        } else {
            self::$config[$category][$node] = $value;
        }
        if ($save) {
            $this->saveConfig($saveDefault);
        }
        return true;
    }

    /**
     * Add a new config node for the specified category.
     *
     * @param {string} [$category] - The primary category to add the option to.
     * @param {string} [$node] - The name of the new option.
     * @param {wild} [$value] - The desired value for the new option.
     * @return {bool}
     */
    public function add($category, $node, $value) {
        if (self::$config === false) {
            self::$config = array();
        }
        if (!Check::simpleName($category)) {
            Debug::warn("Category name invalid: $category");
            return false;
        }
        if (!isset(self::$config[$category])) {
            Debug::warn("No such category: $category");
            return false;
        }
        if (!Check::simpleName($node)) {
            Debug::warn("Category Node name invalid: $node");
            return false;
        }
        if (isset(self::$config[$category][$node])) {
            Debug::warn("Config already exists: $node");
            return false;
        }
        self::$config[$category][$node] = $value;
        return true;
    }

    /**
     * Generates and saves a new config.json and config.default.json
     * based on Input variables if no other config file exists.
     *
     * @return boolean
     */
    public function generate($location, $mods = []) {
        $this->location = $location;
        if (!empty($mods)) {
            foreach ($mods as $mod) {
                $this->update($mod['category'], $mod['name'], $mod['value'], true);
            }
        }
        if ($this->save(true)) {
            Debug::info('config file generated successfully.');
            return true;
        }
        return false;
    }
}
