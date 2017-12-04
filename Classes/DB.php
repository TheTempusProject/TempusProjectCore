<?php
/**
 * Classes/DB.php
 *
 * The DB class defines all our interactions with the database. This particular
 * db interface uses PDO so it can have a wide variety of flexibility, currently
 * it is setup specifically with mysql.
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

class DB
{
    public static $instance = null;
    private $pdo = null;
    private $query = null;
    private $error = false;
    private $results = null;
    private $count = 0;
    private $maxQuery = 0;
    private $totalResults = 0;
    private $pagination = null;
    private $errorMessage = null;
    
    /**
     * Automatically open the DB connection with settings from our global config.
     */
    private function __construct($host = null, $name = null, $user = null, $pass = null)
    {
        Debug::log('Class Initiated: '.get_class($this));
        $this->error = false;
        if (isset($host) && isset($name) && isset($user) && isset($pass)) {
            try {
                Debug::log('Attempting to connect to DB with supplied credentials.');
                $this->pdo = new \PDO('mysql:host='.$host.';dbname='.$name, $user, $pass);
            } catch (\PDOException $Exception) {
                $this->error = true;
                $this->errorMessage = $Exception->getMessage();
            }
        }
        if (!self::enabled()) {
            $this->error = true;
            $this->errorMessage = 'Database disabled in config.';
        }
        if ($this->error === false) {
            try {
                Debug::log('Attempting to connect to DB with config credentials.');
                $this->pdo = new \PDO('mysql:host='.Config::get('database/dbHost').';dbname='.Config::get('database/dbName'), Config::get('database/dbUsername'), Config::get('database/dbPassword'));
            } catch (\PDOException $Exception) {
                $this->error = true;
                $this->errorMessage = $Exception->getMessage();
            }
        }
        if ($this->error !== false) {
            new CustomException('dbConnection', $this->errorMessage);
            return;
        }
        $this->maxQuery = Config::get('database/dbMaxQuery');
        // @TODO add a toggle for this
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        Debug::log('DB connection successful');
        return;
    }

    /**
     * Checks whether the DB is enabled via the config file.
     *
     * @return bool - whether the db module is enabled or not.
     */
    public static function enabled()
    {
        if (Config::get('database/dbEnabled') === true) {
            return true;
        }
        return false;
    }

    /**
     * Checks to see if there is already a DB instance open, and if not; create one.
     *
     * @return function - Returns the PDO DB connection.
     */
    public static function getInstance($host = null, $name = null, $user = null, $pass = null, $new = false)
    {
        // used to force a new connection
        if (!empty($host) && !empty($name) && !empty($user) && !empty($pass)) {
            self::$instance = new self($host, $name, $user, $pass);
        }
        if (empty(self::$instance) || $new) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Returns the DB version.
     *
     * @return bool|string
     */
    public function version()
    {
        $this->error = false;
        if (!self::enabled()) {
            $this->error = true;
            $this->errorMessage = 'Database disabled';
            return false;
        }
        $sql = 'select version()';
        if ($this->query = $this->pdo->prepare($sql)) {
            try {
                $this->query->execute();
            } catch (\PDOException $Exception) {
                $this->error = true;
                $this->errorMessage = $Exception->getMessage();
                Debug::error('DB Version Error');
                Debug::error($this->errorMessage);
                return false;
            }
            return $this->query->fetchColumn();
        }

        return false;
    }

    /**
     * This function resets the values used for creating or modifying tables.
     * Essentially a cleaner function.
     */
    public function newQuery()
    {
        $this->tableBuff = null;
        $this->queryBuff = null;
        $this->fieldBuff = [];
    }

    /**
     * Checks the database to see if the specified table exists.
     *
     * @param  string $name - The name of the table to check for.
     *
     * @return boolean
     */
    protected function tableExists($name)
    {
        $this->raw("SHOW TABLES LIKE '$name'");
        if (!$this->error && $this->count === 0) {
            return false;
        }

        return true;
    }

    /**
     * Checks first that the table exists, then checks if the specified
     * column exists in the table.
     *
     * @param  string $table  - The table to search.
     * @param  string $column - The column to look for.
     *
     * @return boolean
     *
     * @todo  - Is it necessary to check the current $fields list too?
     */
    protected function columnExists($table, $column)
    {
        if (!$this->tableExists($table)) {
            return false;
        }
        $this->raw("SHOW COLUMNS FROM `$table` LIKE '$column'");
        if (!$this->error && $this->count === 0) {
            return false;
        }

        return true;
    }
    /**
     * Execute a raw DB query.
     *
     * @param string $data the query to execute
     *
     * @return bool
     */
    public function raw($data)
    {
        $this->error = false;
        if (!self::enabled()) {
            $this->error = true;
            $this->errorMessage = 'Database disabled';
            return false;
        }
        $this->query = $this->pdo->prepare($data);
        try {
            $this->query->execute();
        } catch (\PDOException $Exception) {
            $this->error = true;
            $this->errorMessage = $Exception->getMessage();
            Debug::warn('DB Raw Query Error');
            Debug::warn($this->errorMessage);
            return false;
        }
        // @todo i think this will cause an error some circumstances
        $this->count = $this->query->rowCount();
        return true;
    }

    /**
     * The actual Query function. This function takes our setup queries
     * and send them to the database. it then properly sets our instance
     * variables with the proper info from the DB, as secondary constructor
     * for almost all objects in this class.
     *
     * @param string $sql    - The SQL to execute.
     * @param array  $params - Any bound parameters for the query.
     *
     * @return object
     */
    public function query($sql, $params = [], $noFetch = false)
    {
        $this->error = false;
        if ($this->pdo == false) {
            Debug::warn('DB::query - no database connection established');
            $this->error = true;
            $this->errorMessage = 'DB::query - no database connection established';
            return $this;
        }
        $this->query = $this->pdo->prepare($sql);
        if (!empty($params)) {
            $x = 0;
            foreach ($params as $param) {
                $x++;
                $this->query->bindValue($x, $param);
            }
        }
        try {
            $this->query->execute();
        } catch (\PDOException $Exception) {
            $this->error = true;
            $this->errorMessage = $Exception->getMessage();
            Debug::error('DB Query Error');
            Debug::error($this->errorMessage);
            return $this;
        }
        if ($noFetch === true) {
            $this->results = null;
            $this->count = 1;
        } else {
            $this->results = $this->query->fetchAll(\PDO::FETCH_OBJ);
            $this->count = $this->query->rowCount();
        }

        return $this;
    }

    /**
     * The action function builds all of our SQL.
     *
     * @todo :  Clean this up.
     *
     * @param string $action    - The type of action being carried out.
     * @param string $tableName - The table name being used.
     * @param array  $where     - The parameters for the action
     * @param string $by        - The key to sort by.
     * @param string $direction - The direction to sort the results.
     * @param array $limit      - The result limit of the query.
     *
     * @return bool
     */
    public function action($action, $tableName, $where, $by = null, $direction = 'DESC', $reqLimit = null)
    {
        $this->error = false;
        if (!self::enabled()) {
            $this->error = true;
            $this->errorMessage = 'Database disabled';
            return $this;
        }
        $whereCount = count($where);
        if ($whereCount < 3) {
            Debug::error('DB::action - Not enough arguments supplied for "where" clause');
            $this->error = true;
            $this->errorMessage = 'DB::action - Not enough arguments supplied for "where" clause';
            return $this;
        }
        if ($action == 'DELETE') {
            $noFetch = true;
        }
        $sql = "{$action} FROM {$tableName} WHERE ";
        $validOperators = ['=', '!=', '>', '<', '>=', '<=', 'LIKE'];
        $validDelimiters = ['AND', 'OR'];
        $values = [];
        while ($whereCount > 2) {
            $whereCount = $whereCount - 3;
            $field = array_shift($where);
            $operator = array_shift($where);
            array_push($values, array_shift($where));
            if (!in_array($operator, $validOperators)) {
                Debug::error('DB::action - Invalid operator.');
                $this->error = true;
                $this->errorMessage = 'DB::action - Invalid operator.';
                return $this;
            }
            $sql .= "{$field} {$operator} ?";
            if ($whereCount > 0) {
                $delimiter = array_shift($where);
                if (!in_array($delimiter, $validDelimiters)) {
                    Debug::error('DB::action - Invalid delimiter.');
                    $this->error = true;
                    $this->errorMessage = 'DB::action - Invalid delimiter.';
                    return $this;
                }
                $sql .= " {$delimiter} ";
                $whereCount--;
            }
        }
        if (isset($by)) {
            $sql .= " ORDER BY {$by} {$direction}";
        }
        $sqlPreLimit = $sql;
        if (!empty($reqLimit)) {
            $sql .= " LIMIT {$reqLimit[0]},{$reqLimit[1]}";
        }
        if (isset($values)) {
            if (!empty($noFetch)) {
                $error = $this->query($sql, $values, true)->error();
            } else {
                $error = $this->query($sql, $values)->error();
            }
        } else {
            $error = $this->query($sql)->error();
        }
        if ($error) {
            Debug::warn('DB Action Error: ');
            Debug::warn($this->errorMessage);
            return $this;
        }
        $this->totalResults = $this->count;
        if ($this->count <= $this->maxQuery) {
            return $this;
        }
        Debug::warn('Query exceeded maximum results. Maximum allowed is ' . $this->maxQuery);
        if (!empty($limit)) {
            $newLimit = ($reqLimit[0] + Pagination::perPage());
            $limit = " LIMIT {$reqLimit[0]},{$newLimit}";
        } else {
            $limit = " LIMIT 0," . Pagination::perPage();
        }
        $sql = $sqlPreLimit . $limit;
        if (isset($values)) {
            $error = $this->query($sql, $values)->error();
        } else {
            $error = $this->query($sql)->error();
        }
        if ($error) {
            Debug::warn('DB Action Error: ');
            Debug::warn($this->errorMessage);
        }
        return $this;
    }

    /**
     * Function to insert into the DB.
     *
     * @param string $table  - The table you wish to insert into.
     * @param array  $fields - The array of fields you wish to insert.
     *
     * @return bool
     */
    public function insert($table, $fields = [])
    {
        $keys = array_keys($fields);
        $valuesSQL = null;
        $x = 0;
        $keysSQL = implode('`, `', $keys);
        foreach ($fields as $value) {
            $x++;
            $valuesSQL .= '?';
            if ($x < count($fields)) {
                $valuesSQL .= ', ';
            }
        }
        $sql = "INSERT INTO {$table} (`".$keysSQL."`) VALUES ({$valuesSQL})";
        if (!$this->query($sql, $fields, true)->error()) {
            return true;
        }
        return false;
    }

    /**
     * Function to update the database.
     *
     * @param string $table  - The table you wish to update in.
     * @param int    $id     - The ID of the entry you wish to update.
     * @param array  $fields - the various fields you wish to update
     *
     * @return bool
     */
    public function update($table, $id, $fields = [])
    {
        $updateSQL = null;
        $x = 0;
        foreach ($fields as $name => $value) {
            $x++;
            $updateSQL .= "{$name} = ?";
            if ($x < count($fields)) {
                $updateSQL .= ', ';
            }
        }
        $sql = "UPDATE {$table} SET {$updateSQL} WHERE ID = {$id}";
        if (!$this->query($sql, $fields, true)->error()) {
            return true;
        }
        return false;
    }

    /**
     * Deletes a series of, or a single instance(s) in the database.
     *
     * @param string $table - The table you are deleting from.
     * @param string $where - The criteria for deletion.
     *
     * @return function
     */
    public function delete($table, $where)
    {
        return $this->action('DELETE', $table, $where);
    }

    /**
     * Starts the object to create a new table if none already exists.
     *
     * NOTE: All tables created with this function will automatically
     * have an 11 digit integer called ID added as a primary key.
     *
     * @param  string $name - The name of the table you wish to create.
     *
     * @return boolean
     *
     * @todo  - add a check for the name.
     */
    public function newTable($name, $addID = true)
    {
        if ($this->tableExists($name)) {
            $this->tableBuff = null;
            Debug::error("Table already exists: $name");

            return false;
        }
        $this->newQuery();
        $this->tableBuff = $name;
        if ($addID === true) {
            $this->addfield('ID', 'int', 11);
        }

        return true;
    }

    /**
     * This function allows you to add a new field to be
     * added to a previously specified table.
     *
     * @param  string  $name    - The name of the field to add
     * @param  string  $type    - The type of field.
     * @param  integer $length  - The maximum length value for the field.
     * @param  boolean $null    - Whether or not the field can be null
     * @param  string  $default - The default value to use for new entries if any.
     * @param  string  $comment - DB comment for this field.
     *
     * @return boolean
     *
     * @todo  - add more error reporting and checks
     *          use switch/cases?
     */
    public function addfield($name, $type, $length, $null = false, $default = null, $comment = '')
    {
        if (empty($this->tableBuff)) {
            Debug::info("No Table set.");
            
            return false;
        }
        if ($this->columnExists($this->tableBuff, $name)) {
            Debug::error("Column already exists: $this->tableBuff > $name");
            
            return false;
        }
        if ($null === true) {
            $sDefault = " DEFAULT NULL";
        } else {
            $sDefault = " NOT NULL";
            if (!empty($default)) {
                $sDefault .= " DEFAULT '$default'";
            }
        }
        if (!empty($length) && ctype_digit($length)) {
            $sType = $type . '(' . $length . ')';
        } else {
            $sType = $type;
        }
        if (!empty($comment)) {
            $sComment = " COMMENT '$comment'";
        } else {
            $sComment = '';
        }
        $this->fieldBuff[] = ' `' . $name . '` ' . $sType . $sDefault . $sComment;
        return true;
    }

    /**
     * Builds and executes a database query to to create a table
     * using the current object's table name and fields.
     *
     * NOTE: By default: All tables have an auto incrementing primary key named 'ID'.
     *
     * @todo  - Come back and add more versatility here.
     */
    public function createTable()
    {
        $this->queryStatus = false;
        if (empty($this->tableBuff)) {
            Debug::info("No Table set.");
            
            return false;
        }
        if ($this->tableExists($this->tableBuff)) {
            Debug::error("Table already exists: $this->tableBuff");

            return false;
        }
        if (!empty($this->tableBuff)) {
            $this->queryBuff .= "CREATE TABLE `$this->tableBuff` (";
            $x = 0;
            $y = count($this->fieldBuff);
            while ($x < $y) {
                $this->queryBuff .= $this->fieldBuff[$x];
                $x++;
                $this->queryBuff .= ($x < $y) ? ',' :  '';
            }
            $this->queryBuff .= ")  ENGINE=InnoDB DEFAULT CHARSET=latin1; ALTER TABLE `" . $this->tableBuff . "` ADD PRIMARY KEY (`ID`); ";
            $this->queryBuff .= "ALTER TABLE `" . $this->tableBuff . "` MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary index value';";
            $this->queryStatus = ($this->raw($this->queryBuff) ? true : false);
        }
    }

    public function search($table, $column, $param)
    {
        return $this->action('SELECT *', $table, [$column, 'LIKE', '%' . $param . '%']);
    }

    /**
     * Selects data from the database.
     *
     * @param string $table     - The table we wish to select from.
     * @param string $where     - The criteria we wish to select.
     * @param string $by        - The key we wish to order by.
     * @param string $direction - The direction we wish to order the results.
     *
     * @return function
     */
    public function get($table, $where, $by = 'ID', $direction = 'DESC', $limit = null)
    {
        if ($where === '*') {
            $where = ['ID', '>=', '0'];
        }
        return $this->action('SELECT *', $table, $where, $by, $direction, $limit);
    }

    /**
     * Selects data from the database and automatically builds the pagination filter for the results array.
     *
     * @param string $table     - The table we wish to select from.
     * @param string $where     - The criteria we wish to select.
     * @param string $by        - The key we wish to order by.
     * @param string $direction - The direction we wish to order the results.
     *
     * @return function
     */
    public function getPaginated($table, $where, $by = 'ID', $direction = 'DESC', $limit = null)
    {
        if ($where === '*') {
            $where = ['ID', '>=', '0'];
        }
        $this->action('SELECT *', $table, $where, $by, $direction);
        Pagination::updateResults($this->totalResults);
        if (!is_array($limit)) {
            $limit = [Pagination::getMin(), Pagination::getMax()];
        }
        return $this->action('SELECT *', $table, $where, $by, $direction, $limit);
    }

    /**
     * Function for returning the entire $results array.
     *
     * @return array - Returns the current query's results.
     */
    public function results()
    {
        return $this->results;
    }

    /**
     * Function for returning the first result in the results array.
     *
     * @return array - Returns the current first member of the results array.
     */
    public function first()
    {
        if (!empty($this->results[0])) {
            return $this->results[0];
        }
        return false;
    }

    /**
     * Function for returning current results' row count.
     *
     * @return int - Returns the current instance's SQL result count.
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * Returns if there are errors with the current query or not.
     *
     * @return bool
     */
    public function error()
    {
        return $this->error;
    }

    /**
     * Returns if there are errors with the current query or not.
     *
     * @return bool
     */
    public function errorMessage()
    {
        //$this->query->errorInfo();
        return $this->errorMessage;
    }

    /**
     * Returns the boolean status of the most recently executed query.
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->queryStatus;
    }
}
