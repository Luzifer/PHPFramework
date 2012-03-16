<?php

/**
 * MySQL database wrapper class
 */
class MySQL {

  public  $trace = array();
  private $connection = null;
  private $database = false;
  private $charset = false;

  private $config = array();

  /**
   * @param IConfigReader $config Config object containing the database config
   * @param string $connection_target Name of the database connection to read the settings from
   */
  public function __construct($config, $connection_target = 'default') {
    $this->config = array(
        'host' => $config->get('db.mysql.' . $connection_target . '.host')
      , 'user' => $config->get('db.mysql.' . $connection_target . '.user')
      , 'pass' => $config->get('db.mysql.' . $connection_target . '.password')
      , 'name' => $config->get('db.mysql.' . $connection_target . '.database')
      , 'char' => $config->get('db.mysql.' . $connection_target . '.charset', 'utf8')
      , 'prefix' => $config->get('db.mysql.' . $connection_target . '.tableprefix', '')
    );

    if($this->config['name'] === null) {
      throw new DBConfigException('Please define the connection parameters for "' . $connection_target . '"');
    }
  }

  public function __destruct() {
    try {
      $this->disconnect();
    } catch(DBConnectionException $ex) {}
  }

  /**
   * Connects to the MySQL server, sets the charset for the connection and
   * selects the database
   *
   * @throws DBConnectionException when connection to database failed
   * @return resource Database connection handle
   */
  public function connect() {

    $connection = $this->connection();
    if($connection !== null) {
      return $connection;
    }

    // don't connect again if it's already done
    $connection = mysql_connect($this->config['host'], $this->config['user'], $this->config['pass']);

    // react on connection failures
    if(!$connection) {
      throw new DBConnectionException('Database connection failed');
    }

    $this->connection = $connection;
    
    $this->set_charset($this->config['char']);
    $this->select_database($this->config['name']);

    return $connection;
  }

  /**
   * Checks whether the connection to the database is established
   *
   * @return bool
   */
  public function is_connected() {
    return $this->connection() !== null;
  }
  
  private function connection() {
    return (is_resource($this->connection)) ? $this->connection : null;
  }
  
  /**
   * Disconnects previously opened database connection
   * 
   * @throws DBConnectionException when connection was not opened
   */
  public function disconnect() {
    $connection = $this->connection();
    if($connection === null) {
      throw new DBConnectionException('Tried to disconnect not opened connection.');
    }

    $disconnect = mysql_close($connection);
    $this->connection = null;

    if(!$disconnect) {
      throw new DBConnectionException('Disconnecting database failed');
    }
  }

  /**
   * @param string $table_prefix Prefix to use for all future statements
   */
  public function set_global_table_prefix($table_prefix) {
    $this->config['prefix'] = $table_prefix;
  }

  /**
   * @return string
   */
  public function get_global_table_prefix() {
    return $this->config['prefix'];
  }

  private function prefix_table($table) {
    return $this->config['prefix'] . $table;
  }
  
  /**
   * Selects the database on the server
   * 
   * @param string $database Database Name
   * @throws DBDatabaseException when database was not selectable
   */
  public function select_database($database) {
    if($this->database == $database) {
      return;
    }

    $select = mysql_select_db($database, $this->connection());

    if($select === false) {
      throw new DBDatabaseException('Selecting database "' . $database . '" failed');
    }

    $this->database = $database;
  }

  /**
   * Sets the charset for transfer encoding
   * 
   * @param string $charset Connection transfer charset
   */
  private function set_charset($charset = 'utf8') {

    // check if there is a assigned charset and compare it
    if($this->charset == $charset) {
      return;
    }

    // set the new charset
    $sql = 'SET NAMES ' . $charset;
    $this->execute($sql, false);

    // save the new charset to the globals
    $this->charset = $charset;
  }
  
  /**
   * Executes the passed SQL statement
   * 
   * @param string $sql Finally escaped SQL statement
   * @return array Result data of the query
   */
  public function query($sql) {
    $result = $this->execute($sql);

    $retval = array();
    while($row = $this->fetch($result)) {
      $retval[] = $row;
    }
    
    return $retval;
  }

  /**
   * Executes the passed SQL statement
   * 
   * @param string $sql Finally escaped SQL statement
   * @return resource Result data of the query
   */
  public function execute($sql) {
    if($this->connection() === null) {
      throw new DBConnectionException('Database has to be connected before executing query.');
    }

    // execute the query
    $result = mysql_query($sql, $this->connection());

    if($result === false) {
      throw new DBQueryException('Database query failed. Query was: "' . $sql . '"');
    }
    
    return $result;
  }
  
  /**
   * Returns number of affected rows of the last query
   * 
   * @return int
   */
  public function affected() {
      return mysql_affected_rows($this->connection());
  }
  
  /**
   * Returns the auto increment ID of the last query
   * 
   * @return int
   */
  public function last_id() {
    return mysql_insert_id($this->connection());
  }
  
  /**
   * Returns a row from the result set
   * 
   * @param resource $result Resultset from query-function
   * @param int $type Type of the result (One of MYSQL_ASSOC, MYSQL_NUM and MYSQL_BOTH)
   * @return array
   */
  public function fetch($result, $type = MYSQL_ASSOC) {
    return mysql_fetch_array($result, $type);
  }
  
  /**
   * Lists the fields of a table
   *
   * @param string $table Name of the table
   * @return array with field names
   */
  public function fields($table) {
    $sql = 'SHOW COLUMNS FROM ' . $this->prefix_table($table);
    $result = $this->query($sql);

    $output = array();
    foreach($result as $row) {
      $output[] = $row['Field'];
    }
    
    return $output;
  }
  
  /**
   * Inserts dataset into the table and returns the auto increment key for it
   * 
   * @param string $table Name of the table
   * @param string|array $input Dataset to insert into the table
   * @param boolean $ignore Use "INSERT IGNORE" for the query
   * @return int
   */
  public function insert($table, $input, $ignore = false) {
    $ignore = ($ignore) ? ' IGNORE' : '';
    $this->execute('INSERT' . ($ignore) . ' INTO ' . $this->prefix_table($table) . ' SET ' . $this->values($input));
    return $this->last_id();
  }
  
  /**
   * Inserts a bunch of rows into the table
   *
   * @param string $table Name of the table
   * @param array $fields Array of field names
   * @param array $values Array of array of values sorted like the fields array
   */
  public function insert_all($table, $fields, $values) {
    $sql = 'INSERT INTO ' . $this->prefix_table($table) . ' (`' . implode('`, `', $fields) . '`) VALUES (';
    
    $rows = array();
    foreach($values as $row) {
      $fields = array();
      foreach($row as $field) {
        $fields[] = $this->escape($field);
      }
      $rows[] = implode('\', \'', $fields);
    }
    $sql .= implode('), (', $rows)
         . ')';
    
    $this->execute($sql);
  }
  
  /**
   * Replaces dataset in the table
   * 
   * @param string $table Name of the table
   * @param string|array $input Dataset to replace in the table
   * @return resource
   */
  public function replace($table, $input) {
    return $this->execute('REPLACE INTO ' . $this->prefix_table($table) . ' SET ' . $this->values($input));
  }
  
  /**
   * Updates datasets in the table
   * 
   * @param string $table Name of the table
   * @param string|array $input Dataset to write over the old one into the table
   * @param string|array $where Selector for the datasets to overwrite
   * @return resource
   */
  public function update($table, $input, $where) {
    return $this->execute('UPDATE ' . $this->prefix_table($table) . ' SET ' . $this->values($input) . ' WHERE ' . $this->where($where));
  }
  
  /**
   * Deletes datasets from table
   * 
   * @param string $table Name of the table
   * @param string|array $where Selector for the datasets to delete
   */
  public function delete($table, $where = null) {
    $sql = 'DELETE FROM ' . $this->prefix_table($table);
    if($where !== null) {
      $sql .= ' WHERE ' . $this->where($where);
    }
    $this->execute($sql);
  }
  
  /**
   * Selects datasets from table
   *
   * @param string $table Name of the table
   * @param string $select Fields to retrieve from table
   * @param string|array $where Selector for the datasets to select
   * @param string $order Already escaped content of order clause
   * @param int $start First index of dataset to retrieve
   * @param int $limit Number of entries to retrieve
   * @param boolean $fetch Return an pre-processed array of entries or the raw resource
   * @return array|resource
   */
  public function select($table, $select = '*', $where = null, $order = null, $start = null, $limit = null, $fetch = true) {
    $sql = 'SELECT ' . $select . ' FROM ' . $this->prefix_table($table);

    if($where !== null) $sql .= ' WHERE ' . $this->where($where);
    if($order !== null) $sql .= ' ORDER BY ' . $order;
    if($start !== null && $limit !== null) $sql .= ' LIMIT ' . $start . ',' . $limit;
    
    if($fetch) {
      return $this->query($sql);
    }
    
    return $this->execute($sql);
  }
  
  /**
   * Select one row from table or false if there is no row
   *
   * @param string $table Name of the table
   * @param string $select Fields to retrieve from table
   * @param string|array $where Selector for the datasets to select
   * @param string $order Already escaped content of order clause
   * @return array|boolean
   */
  public function row($table, $select = '*', $where = null, $order = null) {
    $result = $this->select($table, $select, $where, $order, 0,1, true);
    return (count($result) > 0) ? $result[0] : false;
  }
  
  /**
   * Select contents of one column from table
   *
   * @param string $table Name of the table
   * @param string $column Name of column to retrieve
   * @param string|array $where Selector for the datasets to select
   * @param string $order Already escaped content of order clause
   * @param int $start First index of dataset to retrieve
   * @param int $limit Number of entries to retrieve
   * @return array
   */
  public function column($table, $column, $where = null, $order = null, $start = null, $limit = null) {
    $result = $this->select($table, $column, $where, $order, $start, $limit, true);

    $retval = array();
    foreach($result as $row) {
      $retval[] = $row[$column];
    }
    return $retval;
  }

  /**
   * Select one field from table
   *
   * @param string $table Name of the table
   * @param string $field Name of the field to return
   * @param string|array $where Selector for the datasets to select
   * @param string $order Already escaped content of order clause
   * @internal param string $column Name of column to retrieve
   * @return mixed
   */
  public function field($table, $field, $where = null, $order = null) {
    $result = $this->row($table, $field, $where, $order);
    return $result[$field];
  }
  
  /**
   * Counts the rows matching the where clause in table
   *
   * @param string $table Name of the table
   * @param string|array $where Selector for the datasets to select
   * @return int
   */
  public function count($table, $where = null) {
    $result = $this->row($table, 'count(1)', $where);
    return ($result) ? $result['count(1)'] : 0;
  }
  
  /**
   * Selects the minmum of a column or false if there is no data
   *
   * @param string $table Name of the table
   * @param string $column Name of column to retrieve
   * @param string|array $where Selector for the datasets to select
   * @return int|boolean
   */
  public function min($table, $column, $where = null) {
    $result = $this->row($table, 'MIN(`' . $column . '`) as min', $where);
    return ($result) ? $result['min'] : false;
  }
  
  /**
   * Selects the maximum of a column or false if there is no data
   *
   * @param string $table Name of the table
   * @param string $column Name of column to retrieve
   * @param string|array $where Selector for the datasets to select
   * @return int|boolean
   */
  public function max($table, $column, $where = null) {
    $result = $this->row($table, 'MAX(`' . $column . '`) as max', $where);
    return ($result) ? $result['max'] : false;
  }
  
  /**
   * Selects the sum of a column
   *
   * @param string $table Name of the table
   * @param string $column Name of column to retrieve
   * @param string|array $where Selector for the datasets to select
   * @return int
   */
  public function sum($table, $column, $where = null) {
    $result = $this->row($table, 'SUM(`' . $column . '`) as sum', $where);
    return ($result) ? $result['sum'] : 0;
  }

  private function values($input) {
    if(!is_array($input)) {
      return $input;
    }

    $retval = array();
    foreach($input as $key => $value) {
      if($value === 'NOW()') {
        $retval[] = '`' . $key . '`' . ' = NOW()';
      } elseif($value === null) {
        $retval[] = '`' . $key . '`' . ' = NULL';
      } else {
        $retval[] = '`' . $key . '`' . ' = \'' . $this->escape($value) . '\'';
      }
    }
    return implode(', ', $retval);

  }

  private function escape($value) {
    $value = stripslashes($value);
    if($this->connection() !== null) {
      return mysql_real_escape_string((string)$value, $this->connection());
    } else {
      return mysql_escape_string((string)$value);
    }
  }
  
  /**
   * Assembles a LIKE search for the WHERE clause
   *
   * @param string $search The string to search for (Will be pre- and appended with '%')
   * @param array $fields The fields to search in
   * @param string $mode One of 'OR' / 'AND'
   * @return string
   */
  public function search_clause($search, $fields, $mode = 'OR') {
    if(empty($search)) {
      throw new InputException('Empty search value not allowed. Use select instead.');
    }

    $arr = array();
    foreach($fields as $f) {
      $arr[] = '`' . $f . '` LIKE \'%' . $search . '%\'';
    }
    return '(' . implode(' ' . trim($mode) . ' ', $arr) . ')';
  }
  
  private function where($array, $method = 'AND') {
    if(!is_array($array)) {
      return $array;
    }

    $output = array();
    foreach($array AS $field => $value) {
      $operand = '=';
      $operand2 = 'IN';
      if (substr($field, -1) == '!') {
        $operand = '!=';
        $operand2 = 'NOT IN';
        $field = substr($field, 0, -1);
      } else if (substr($field, -1) == '?') {
        $operand = 'LIKE';
        $field = substr($field, 0, -1);
      }
      
      if(is_array($value)) {
        $arr = array();
        foreach($value as $v) {
          $arr[] = $this->escape($v);
        }
        $output[] = '`' . $field . '`' . ' ' . $operand2 . ' (\'' . implode('\', \'', $arr) . '\')';
      } else {
        $output[] = '`' . $field . '`' . ' ' . $operand . ' \'' . $this->escape($value) . '\'';
      }
    }
    return implode(' ' . $method . ' ', $output);
  }

}

class DBConnectionException extends Exception {}
class DBQueryException extends Exception {}
class DBDatabaseException extends Exception {}
class InputException extends Exception {}
class DBConfigException extends Exception {}
