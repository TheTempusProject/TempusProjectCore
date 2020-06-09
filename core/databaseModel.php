<?php
/**
 * core/databaseModel.php
 *
 * The database model provides some very basic functionality for 
 * database calls that get used in a very similar way across the board.
 *
 * @version 2.1
 * @author  Joey Kimsey <JoeyKimsey@thetempusproject.com>
 * @link    https://TheTempusProject.com/Core
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */
namespace TempusProjectCore\Core;

class DatabaseModel extends Model
{
    /**
     * Returns an array of models required to run this model without error.
     *
     * @return array - An array of models
     */
    public static function requiredModels()
    {
        $required = [
            'log'
        ];
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
            'installDB' => true,
            'installPermissions' => false,
            'installConfigs' => false,
            'installResources' => false,
            'installPreferences' => false
        ];
        return $flags;
    }

    /**
     * Filters the given data. Usually used to all additional lookup or 
     * fields to the data before its passed back as output.
     *
     * @param  array|object $data - The input to be filtered.
     *
     * @return object - The filtered output.
     */
    public function filter($data)
    {
        return $data;
    }

    /**
     * Retrieves a comment by its ID and parses it.
     *
     * @param  integer $id - The ID of the comment you are
     *                       trying to retrieve.
     *
     * @return object - The parsed comment db entry.
     */
    public function findById($id)
    {
        if (!Check::id($id)) {
            Debug::info("tracking: illegal ID.");
            
            return false;
        }
        $trackingData = self::$db->get(self::$tableName, ['ID', '=', $id]);
        if (!$trackingData->count()) {
            Debug::info("No " . self::$tableName . " data found.");

            return false;
        }
        return $this->filter($trackingData->results());
    }

    /**
     * Function to delete the specified entry.
     *
     * @param  int|array $ID the log ID or array of ID's to be deleted
     *
     * @return bool
     */
    public function delete($data)
    {
        if (!isset(self::$log)) {
            self::$log = $this->model('log');
        }
        foreach ($data as $instance) {
            if (!is_array($data)) {
                $instance = $data;
                $end = true;
            }
            if (!Check::id($instance)) {
                $error = true;
            }
            self::$db->delete(self::$tableName, ['ID', '=', $instance]);
            self::$log->admin("Deleted " . self::$tableName . ": $instance");
            Debug::info(self::$tableName . " deleted: $instance");
            if (!empty($end)) {
                break;
            }
        }
        if (!empty($error)) {
            Debug::info('One or more invalid ID\'s.');
            return false;
        }
        return true;
    }
    
    /**
     * Function to clear entries of a defined type.
     *
     * @param  string $data - The log type to be cleared
     *
     * @return bool
     *
     * @todo  this is probably dumb
     */
    public function empty()
    {
        if (!isset(self::$log)) {
            self::$log = $this->model('log');
        }
        self::$db->delete(self::$tableName, ['ID', '>=', '0']);
        self::$log->admin("Cleared " . self::$tableName);
        Debug::info(self::$tableName . " Cleared");
        return true;
    }

    /**
     * retrieves a list of paginated (limited) results.
     *
     * @param  array $filter - A filter to be applied to the list.
     *
     * @return bool|object - Depending on success.
     */
    public function listPaginated($filter = null)
    {
        $data = self::$db->getPaginated(self::$tableName, "*");
        if (!$data->count()) {
            Debug::info(self::$tableName . ' - No entries found');
            return false;
        }
        return (object) $data->results();
    }
}
