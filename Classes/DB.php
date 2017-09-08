<?php
/**
 * Classes/DB.php.
 *
 * The DB class defines all our interactions with the database. This particular 
 * db interface uses PDO so it can have a wide variety of flexibility, currently 
 * it is setup specifically with mysql.
 *
 * @version 0.9
 *
 * @author  Joey Kimsey <joeyk4816@gmail.com>
 *
 * @link    https://github.com/JoeyK4816/tempus-project-core
 *
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */

namespace TempusProjectCore\Classes;

class DB
{
    public static $instance = null;
    private static $_enabled = null;
    private $_pdo = null;
    private $_query = null;
    private $_error = false;
    private $_results = null;
    private $_count = 0;
    private $_max_query = 0;
    private $_total_results = 0;
    private $_pagination = null;

    /**
     * Automatically open the DB connection with settings from our global config.
     */
    private function __construct($host = null, $name = null, $user = null, $pass = null)
    {
        Debug::log('Class Initiated: '.get_class($this));
        if (isset($host) && isset($name) && isset($user) && isset($pass)) {
            try {
                Debug::log('Attempting to connect to DB with supplied credentials.');
                $this->_pdo = new \PDO('mysql:host='.$host.';dbname='.$name, $user, $pass);
            } catch (\PDOException $Exception) {
                Self::$_enabled = false;
                new CustomException('DB_connection', $Exception);
                return;
            }
            Self::$_enabled = true;
            Debug::log('DB connection successful');
            return;
        }
        if (!Self::enabled()) {
            Debug::error("DB disabled.");
            $this->_pdo = false;
            return;
        }
        try {
            Debug::log('Attempting to connect to DB with config credentials.');
            $this->_pdo = new \PDO('mysql:host='.Config::get('database/db_host').';dbname='.Config::get('database/db_name'), Config::get('database/db_username'), Config::get('database/db_password'));
        } catch (\PDOException $Exception) {
            Self::$_enabled = false;
            new CustomException('DB_connection', $Exception);
            return;
        }
        $this->_max_query = Config::get('database/db_max_query');
        Self::$_enabled = true;
        Debug::log('DB connection successful');
        return;
    }

    /**
     * Checks to see if there is already a DB instance open, and if not; create one.
     *
     * @return function - Returns the PDO DB connection.
     */
    public static function getInstance($host = null, $name = null, $user = null, $pass = null)
    {
        if (isset($host) && isset($name) && isset($user) && isset($pass)) {
            Debug::log('Creating new DB Connection.');
            self::$instance = new self($host, $name, $user, $pass);
        }
        if (!isset(self::$instance)) {
            Debug::log('Creating new DB Connection.');
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
        if (!Self::enabled()) {
            return false;
        }
        $this->_error = false;
        $sql = 'select version()';
        if ($this->_query = $this->_pdo->prepare($sql)) {
            try {
                $this->_query->execute();
            } catch (PDOException $Exception) {
                Debug::warn('DB Version Error');
                Debug::warn($this->_query->errorInfo());
                $this->_error = true;
                return false;
            }
            return $this->_query->fetchColumn();
        }

        return false;
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
        if (!Self::enabled()) {
            return false;
        }
        $this->_error = false;
        $this->_query = $this->_pdo->prepare($data);
        try {
            $this->_query->execute();
        } catch (PDOException $Exception) {
            Debug::warn('DB Raw Query Error');
            Debug::warn($this->_query->errorInfo());
            $this->_error = true;
            return false;
        }
        return true;
    }

    /**
     * Checks whether the DB is enabled via the config file.
     * 
     * @return bool - whether the db module is enabled or not.
     */
    public static function enabled()
    {
        if (Self::$_enabled === false)
        {
            return false;
        }
        if (Self::$_enabled === true)
        {
            return true;
        }
        if (Config::get('database/db_enabled') === false) 
        {
            Self::$_enabled = false;
            return false;
        }
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
    public function query($sql, $params = array())
    {
        $this->_error = false;
        if ($this->_pdo === false) {
            Debug::warn('DB Query Error');
            $this->_error = true;
            return $this;
        }
        if ($this->_query = $this->_pdo->prepare($sql)) {
            $x = 1;
            $y = 0;
            if (count($params)) {
                foreach ($params as $param) {
                    $this->_query->bindValue($x, $param);
                    ++$x;
                    ++$y;
                }
            }
            try {
                $this->_query->execute();
            } catch (PDOException $Exception) {
                Debug::warn('DB Query Error: ');
                Debug::warn($this->_query->errorInfo());
                $this->_error = true;
            }
            $this->_results = $this->_query->fetchAll(\PDO::FETCH_OBJ);
            $this->_count = $this->_query->rowCount();
        }

        return $this;
    }
    /**
     * The action function builds all of our SQL.
     *
     * @todo :  Clean this up.
     * 
     * @param string $action    - The type of action being carried out.
     * @param string $table     - The table being used.
     * @param array  $where     - The parameters for the action
     * @param string $by        - The key to sort by.
     * @param string $direction - The direction to sort the results.
     * @param array $limit      - The result limit of the query.
     * 
     * @return bool
     */
    public function action($action, $table, $where, $by = null, $direction = 'DESC', $req_limit = null)
    {
        if (!Self::enabled()) {
            return $this;
        }
        $this->_error = false;
        if (!empty($req_limit)) {
            $limit = " LIMIT {$req_limit[0]},{$req_limit[1]}";
        }
        $sql = "{$action} FROM {$table}";
        if (count($where) >= 3) {
            $operators = array('=', '>', '<', '>=', '<=', '!=', 'LIKE');
            $operator = $where[1];
            if (!in_array($operator, $operators)) {
                return false;
            }
            $field = $where[0];
            $value = array($where[2]);
            $sql .= " WHERE {$field} {$operator} ?";
            $extras = array('AND', 'OR');
            $extra = 3;
            $vCount = 0;
            if (isset($where[$extra])) {
                while ($extra < count($where)) {
                    if (in_array($where[$extra], $extras)) {
                        ++$extra;
                        $field = $where[$extra];
                        ++$extra;
                        $operator = $where[$extra];
                        ++$extra;
                        array_push($value, $where[$extra]);
                        ++$vCount;
                        if (in_array($operator, $operators)) {
                            $w = ($extra - 3);
                            $sql .= " {$where[$w]} {$field} {$operator} ?";
                        }
                        ++$extra;
                    }
                }
            }
        }
        if (isset($by)) {
            $sql .= " ORDER BY {$by} {$direction}";
        }
        $sql_pre_limit = $sql;
        if (!empty($limit)) {
            $sql .= $limit;
        }
        if (isset($value)) {
            $error = $this->query($sql, $value)->error();
        } else {
            $error = $this->query($sql)->error();
        }
        if (!$error) {
            $this->_total_results = $this->_count;
            if ($this->_count > $this->_max_query) {
                Debug::warn('Query exceeded maximum results. Maximum allowed is ' . $this->_max_query);
                if (!empty($limit)) {
                    $new_limit = ($req_limit[0] + Pagination::perPage());
                    $limit = " LIMIT {$req_limit[0]},{$new_limit}";
                } else {
                    $limit = " LIMIT 0," . Pagination::perPage();
                }
                $sql = $sql_pre_limit . $limit;
                if (isset($value)) {
                    $error = $this->query($sql, $value)->error();
                } else {
                    $error = $this->query($sql)->error();
                }
                if ($error) {
                    Debug::warn('DB Action Error: ');
                    Debug::warn($this->_query->errorInfo());
                    return $this;
                }
            }
            return $this;
        }
        Debug::warn('DB Action Error: ');
        //Debug::warn($this->_query->errorInfo());
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
    public function insert($table, $fields = array())
    {
        $keys = array_keys($fields);
        $values = null;
        $x = 1;
        foreach ($fields as $value) {
            $values .= '?';
            if ($x < count($fields)) {
                $values .= ', ';
            }
            ++$x;
        }
        $sql = "INSERT INTO {$table} (`".implode('`, `', $keys)."`) VALUES ({$values})";
        if (!$this->query($sql, $fields)->error()) {
            return true;
        }
        Debug::error('DB Insert error');
        //Debug::warn($this->_query->errorInfo());
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
    public function update($table, $id, $fields = array())
    {
        $set = null;
        $x = 1;
        foreach ($fields as $name => $value) {
            $set .= "{$name} = ?";
            if ($x < count($fields)) {
                $set .= ', ';
            }
            ++$x;
        }
        $sql = "UPDATE {$table} SET {$set} WHERE ID = {$id}";
        if (!$this->query($sql, $fields)->error()) {
            return true;
        }
        Debug::warn('DB Update error: ');
        //Debug::warn($this->_query->errorInfo());
        return false;
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
        return $this->action('SELECT *', $table, $where, $by, $direction, $limit);
    }
    public function search($table, $column, $param) 
    {
        return $this->action('SELECT *', $table, array($column, 'LIKE', '%' . $param . '%'));
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
    public function get_paginated($table, $where, $by = 'ID', $direction = 'DESC', $limit = null)
    {
        $this->action('SELECT *', $table, $where, $by, $direction);
        Pagination::update_results($this->_total_results);
        $limit = array(Pagination::getMin(),Pagination::getMax());
        return $this->action('SELECT *', $table, $where, $by, $direction, $limit);
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
     * Function for returning the entire $_results array.
     *
     * @return array - Returns the current query's results.
     */
    public function results()
    {
        return $this->_results;
    }

    /**
     * Function for returning the first result in the results array.
     *
     * @return array - Returns the current first member of the results array.
     */
    public function first()
    {
        return $this->_results[0];
    }

    /**
     * Function for returning current results' row count.
     *
     * @return int - Returns the current instance's SQL result count.
     */
    public function count()
    {
        return $this->_count;
    }

    /**
     * Returns if there are errors with the current query or not.
     *
     * @return bool
     */
    public function error()
    {
        return $this->_error;
    }
}
