<?php
/**
 * core/database_model.php
 *
 * The class provides some basic functionality for models that interact
 * with the database.
 *
 * @version 3.0
 * @author  Joey Kimsey <Joey@thetempusproject.com>
 * @link    https://TheTempusProject.com/Core
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */
namespace TempusProjectCore;

use TempusProjectCore\Functions\Debug;
use TempusProjectCore\Classes\Database;
use TempusProjectCore\Functions\Check;

class DatabaseModel extends Model
{
    public static $tableName = '';
    public static $db = '';

    public function __construct() {
        self::$db = Database::getInstance();
    }

    /**
     * Retrieves a comment by its ID and parses it.
     *
     * @param  {int} [$id]
     * @return {object} - The parsed comment db entry.
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
     * @return bool
     */
    public function delete($data)
    {
        foreach ($data as $instance) {
            if (!is_array($data)) {
                $instance = $data;
                $end = true;
            }
            if (!Check::id($instance)) {
                $error = true;
            }
            self::$db->delete(self::$tableName, ['ID', '=', $instance]);
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
     * @todo  this is probably dumb
     * @param  string $data - The log type to be cleared
     * @return bool
     */
    public function empty()
    {
        self::$db->delete(self::$tableName, ['ID', '>=', '0']);
        Debug::info(self::$tableName . " Cleared");
        return true;
    }

    /**
     * retrieves a list of paginated (limited) results.
     *
     * @param  array $filter - A filter to be applied to the list.
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
