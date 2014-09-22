<?php
/**
 *
 * Class : DAO
 *
 * This data access object is designed for MySQL only
 *
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.5.0.0
 *
 * Updates:
 * 18-07-2007   : improved ->_throw_error() code
 * 13-07-2005 : added ->build_set() method - returns a string containing an SQL set
 * 12-07-2005 : added ->get_num_cols() method - returns the number of column names
 * 12-07-2005 : added ->get_cols() method - returns an array of column names
 * 16-06-2005 : fixed a bug when using NULL values in do_insert/do_update
 * 14-06-2005 : added $new_link parameter to ->open() method
 * 13-06-2005 : added ->do_insert_multi() method for adding multiple rows in one statement
 * 09-06-2005 : fixed potential insert-id bug
 * 15-10-2004 : new ->fetch_assoc() method - gets the query as an associative array
 *          of the form:  array ( row1.field1 => row1.field2, ... )
 * 31-09-2004 : Fixed multi-database usage (stops connection clashing)
 *
 */

class DAO {
  // Public Vars

  // Private Vars
  private $_host = '';        // DB Connection info
  private $_user = '';
  private $_password = '';
  private $_database = '';
  private $_persistent = false;

  private $_conn = null;        // DB Connection object

  private $_result_set = null;    // Contains the result set (temporarily)
  private $_result_cols = null;   // Array of columns for the result set

  private $_last_sql = null;      // Last query run
  private $_result = null;      // Query results, as array of row objects

  private $_output_type = 'ARRAY_A';  // 'ARRAY_A': Associative Array : $results[row]['field']
                    // 'ARRAY_N': Numeric Array : $results[row][col]
                    // 'ARRAY_B': Assoc + Numeric Array : use $results[row]['field'] or $results[row][col]

  private $_output_type_int = MYSQL_ASSOC;  // MYSQL_ASSOC, MYSQL_BOTH, MYSQL_NUM

  private $_insert_id = null;     // Last inserted id (on auto-increment columns)

  private $_num_cols = null;
  private $_num_rows = null;
  private $_num_affected = null;

  private $_debug = false;      // debug mode (default: off)
  private $_last_error = null;

   /**
  * CONSTRUCTOR
    * Set database connection strings
    * @param string $host
    * @param string $user
    * @param string $password
    * @param string $database
    * @param boolean $persistent
  */
  function DAO($host, $user, $password, $database, $persistent = false) {
    $this->_host = $host;
    $this->_user = $user;
    $this->_password = $password;
    $this->_database = $database;
    $this->_persistent = $persistent;
  } // /DAO()

/*
* ================================================================================
* Public Methods
* ================================================================================
*/

  /**
  * Open database connection
  * @param boolean $new_link   block reusing an existing database connection when the same server/username/password are used
  * @return boolean
  */
  function open($new_link = true) {
    if (is_null($this->_conn)) {
      if ($this->_persistent) {
        $func = 'mysql_pconnect';
      } else {
        $func = 'mysql_connect';
      }

      if ($this->_debug) {
        $this->_conn = $func($this->_host, $this->_user, $this->_password, $new_link);
        $db_selected = mysql_select_db($this->_database, $this->_conn);
        //$db_selected = mysql_select_db($this->_database);
        if (!$db_selected) {
          die (gettext('Can\'t use database due to').'  : ' .mysql_errno(). " -  " . mysql_error());
          return false;
        }else{
          return true;
        }
      } else {
        $this->_conn = $func($this->_host, $this->_user, $this->_password, $new_link);

        $db_selected = mysql_select_db($this->_database, $this->_conn);

        if (!$db_selected) {
          die (gettext('Can\'t use database due to').'  : ' .mysql_errno(). " -  " . mysql_error());
          return false;
        }else{
          return true;
        }

      }

    }

    return true;
  } // /->open()

/**
 * Close database connection
 * @return object
 */
  function close() {
    $this->flush();
    return ( @mysql_close($this->_conn) );
  } // /->close()

  /**
   * Clear results and reset result vars
   */
  function flush() {
    $this->_result = null;
    $this->_num_rows = null;
    $this->_num_affected = null;
    $this->_insert_id = null;
  } // /->flush()

  /**
  * Execute the SQL query, and ignore results (ie, not a SELECT)
  * Sets affected rows (ie could be DELETE/INSERT/REPLACE/UPDATE)
  * Sets inserted id if query is INSERT/REPLACE (checks first word of query)
  * @param string $sql
  * @return boolean
  */
  function execute($sql) {

    $this->flush();
    $this->open();
    $this->_last_sql = trim( $sql );  // Save query

    if ($this->_debug) {
      $this->_result_set = mysql_query($sql, $this->_conn) or $this->_throw_error(gettext('Executing SQL'));
    } else {
      $this->_result_set = @mysql_query($sql, $this->_conn);
    }

    if ($this->_result_set) {
      $this->_num_affected = mysql_affected_rows($this->_conn);

      if ( preg_match("/^\\s*(insert|replace) /i", $sql) ) {
        $this->_insert_id = mysql_insert_id($this->_conn);
      }

      if ($this->_num_affected) {
        return true;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }// /->execute()

  /**
  * Get results of the given query
  * @param string $sql
  * @return object
  */
  function fetch($sql = null) {
    // If there is an SQL query, get its results instead..
    if ( $sql ) { $this->_process_query($sql); }

    return $this->_result;
  } // /->fetch()

  /**
  * Get a single row (of the given query or cache)
  * Row indexes are 0-based
  * @param string $sql
  * @param integer $y
  * @return object
  */
  function fetch_row($sql = null, $y = 0) {
    // If there is an SQL query, get its results instead..
    if ( $sql ) { $this->_process_query($sql);  }
    return (isset($this->_result)) ? $this->_result[$y] : null;
  } // /->fetch_row()

  /**
  * Get a single column as a numeric array
  * column indexes are 0-based
  * @param string $sql
  * @param integer $x
  * @return array
  */
  function fetch_col($sql = null, $x = 0) {
    // If there is an SQL query, get its results instead..
    if ( $sql ) { $this->_process_query($sql);  }

    $new_array = null;

    // Extract the column value
    if ($this->_num_rows>0) {
      for ( $i=0; $i<$this->_num_rows; $i++ ) {
        $new_array[$i] = $this->fetch_value(null,$x,$i);
      }
    }
    return $new_array;
  } // /->fetch_col()

  /**
  * Get a single value from the result set
    * column/row indexes are 0-based
  * An empty string ('') or no value, will return null
  * @param string $sql
  * @param integer $x
  * @param integer $y
  * @return integer
  */
  function fetch_value($sql = null, $x = 0, $y = 0) {
    // If there is an SQL query, get its results instead..
    if ( $sql ) { $this->_process_query($sql);  }

    // Extract value using x,y vals
    if ( $this->_result[$y] ) {
      $values = array_values($this->_result[$y]);
    }
    // If there is a value return it, else return null
    return ( isset($values[$x]) && $values[$x]!=='') ? $values[$x] : null;
  } // /->fetch_value()

  /**
  * Get an associative array ( field1-value => field2-value ) from the result set
  * If you retrieve more than 2 fields, you will get an associative array of row-arrays
  * e.g.   field1-value => array ( field2 => field2-value, field3 => field3-value, ... ), ...
  * @param string $sql
  * @return array
  */
  function fetch_assoc($sql = null) {
    // Need to be in Numeric+Assoc Array mode, so set it
    $original_output_mode = $this->get_output_mode();
    $this->set_output($output = 'ARRAY_B');

    // Fetch the new result set
    $this->_process_query($sql);

    // Reset mode to what it was before
    $this->set_output($original_output_mode);

    $new_array = null;

    if ($this->_num_rows>0) {
      if ($this->get_num_cols()==2) {
        // Convert rows to simple associative array
        for ( $i=0; $i<$this->_num_rows; $i++ ) {
          $new_array["{$this->_result[$i][0]}"] = $this->_result[$i][1];
        }
      } else {
        // Convert rows to associative array of arrays
        for ( $i=0; $i<$this->_num_rows; $i++ ) {
          $row_array = null;
          for ( $j=1; $j<$this->_num_cols; $j++ ) {
            $row_array[$this->_result_cols["$j"]] = $this->_result[$i][$j];
          }
          $new_array["{$this->_result[$i][0]}"] = $row_array;
        }
      }
    }
    return $new_array;
  }// /->fetch_assoc()

  /**
  * Execute the insert query in $sql, using the fields in $fields
  * auto-slashes the given fields
  * @param string $sql string of the form 'INSERT INTO tbl_name ({fields}) VALUES ({values}) '
  * @param array $fields   array ( fieldname1 => ???, fieldname2 => ???, ... )
  */
  function do_insert($sql, $fields) {
    $fields_str = implode(',', (array) array_keys($fields) );

    $values = array();
    foreach ($fields as $k => $v) {
      $values[] = $this->_prepare_field_value($v);
    }
    $values_str = implode(',',$values);

    $sql = str_replace('{fields}',$fields_str,$sql);
    $sql = str_replace('{values}',$values_str,$sql);

    return $this->execute($sql);
  } // /->do_insert()

  /**
  * Execute the insert query in $sql, using multiple VALUES statements as given in $fields
  * Auto-slashes the given fields
  *
  * NOTE  : Unlike ->do_insert, the $fields array is an array[0..n] of assoc-arrays
  * NOTE  : Only the last insert-id will be available from ->get_insert_id() following execution
  *
  * @param string $sql of the form, 'INSERT INTO tbl_name ({fields}) VALUES {values}
  * @param array $fields array[0..n] of array ( fieldname1 -> ???, fieldname2 => ???, ... )
  * @return object
  */
  function do_insert_multi($sql, $fields) {
    if (is_array($fields)) {
      $fields_str = implode(',', array_keys($fields[0]) );

      $value_row = null;

      foreach ($fields as $i => $row) {
        $value_row["$i"] = array ();
        foreach($row as $k => $v) {
          $value_row["$i"][] = $this->_prepare_field_value($v);
        }
        $value_row["$i"] = '('. implode(',',$value_row["$i"]) .')';
      }
      $values_str = implode(',',$value_row);

      $sql = str_replace('{fields}',$fields_str,$sql);
      $sql = str_replace('{values}',$values_str,$sql);

      return $this->execute($sql);
    } else {
      return null;
    }
  }// /->do_insert_multi()

  /**
  * Execute the update query in $sql, setting the fields in $fields
  * Auto-slashes the given fields
  * @param string $sql  sql query of the form  'UPDATE tbl_name SET {fields} WHERE xxx=yyy'
  * @param array $fields of fields and values to set - of the form  array ( fieldname1 => ???, fieldname2 => ???, ... )
  * @return mixed
  */
  function do_update($sql, $fields) {
    $set_str = '';
    $fields_count = count($fields);
    $i=1;

    foreach ($fields AS $k=>$v) {
      $set_str .= " $k=". $this->_prepare_field_value($v);
      if ($i<$fields_count) { $set_str .= ','; }
      ++$i;
    }

    $sql = str_replace('{fields}',$set_str,$sql);
    return $this->execute($sql);
  } // /->do_update()

  /**
  * Builds filter clause of the form  (aaa='bbb' OR aaa='ccc' OR aaa='ddd' ... )
  *
  * @param string $field_name the field name being checked (aaa in example above)
  * @param array/value $filter_values the values to compare against.
  * @param string $logical_operator the operator to use to concatenate the filters (AND, OR, XOR)
  * @return string
  */
  function build_filter($field_name, $filter_values, $logical_operator = 'OR') {
    $filter_values = (array) $filter_values;  // cast values to array (in case it was only passed one)

    $filter_clause = '(';
    $w_count = count($filter_values);
    $i = 1;
    foreach($filter_values as $k => $v) {
      if (!is_int($v)) {
        $v = $this->escape_str($v);
      }
      $filter_clause .= "$field_name = ". $this->_prepare_field_value($v);
      if ($i<$w_count) { $filter_clause .= " $logical_operator "; }
      $i++;
    }
    $filter_clause .= ')';

    return $filter_clause;
  }// ->build_filter()

  /**
  * Builds an SQL set of the form  ('aaa','bbb','ccc') for use with IN operators
  *
  * @param array/value $value_array array of values to include in the set
  * @return string
  */
  function build_set($value_array, $quoted = true) {
    $value_array = array_map('addslashes',(array) $value_array);
    if ($quoted) {
      return '(\''. implode('\',\'',$value_array) .'\')';
    } else {
      return '('. implode(',',$value_array) .')';
    }
  }// /->build_set()

/*
* --------------------------------------------------------------------------------
* Accessor Methods
* --------------------------------------------------------------------------------
*/

  // GET_xxx functions

  /**
   * Return an array of columns names from the last query
   * @return boolean
   */
  function get_cols() { return (is_array($this->_result_cols)) ? $this->_result_cols : null ; }

  /**
   * Return the number of columns from the last query
   * @return integer
   */
  function get_num_cols() { return $this->_num_cols; }

  /**
   * Return the number of rows from the last query
   * @return integer
   */
  function get_num_rows() { return $this->_num_rows; }

  /**
   * Return the number of affected rows from the last query (insert/replace/update)
   * @return integer
   */
  function get_num_affected() { return $this->_num_affected; }

 /**
  * Return last inserted id (for auto-increment columns)
  * @return integer
  */
  function get_insert_id() { return $this->_insert_id; }

 /**
  * Return last run query
  * @return string
  */
  function get_last_sql() { return $this->_last_sql; }

/**
 *  Get last mysql error
 */
  function get_last_error() { mysql_error($this->_conn); }

  /**
   * Get output mode
   * @return mixed
   */
  function get_output_mode() { return $this->_output_type; }

  function getConnection() {

    return $this->_conn;

  }

/*
* --------------------------------------------------------------------------------
* SET_xxx functions
* --------------------------------------------------------------------------------
*/

  /**
  * Set debug mode
  * When in debug mode, detailed error reports are echoed
  * @param boolean
  */
  function set_debug($on) {
    $this->_debug = $on;
  }

  /**
  * Set default output mode for results array
  * @param string $output
  * @return boolean
  */
  function set_output($output = 'ARRAY_A') {
    switch ($output) {
      case 'ARRAY_A'  :
            $this->_output_type_int = MYSQL_ASSOC;
            $this->_output_type = $output;
            return true;
            break;
      // ----------------------------------------
      case 'ARRAY_B'  :
            $this->_output_type_int = MYSQL_BOTH;
            $this->_output_type = $output;
            return true;
            break;
      // ----------------------------------------
      case 'ARRAY_N'  :
            $this->_output_type_int = MYSQL_NUM;
            $this->_output_type = $output;
            return true;
            break;
      // ----------------------------------------
      default :
        return false;
        break;
    }
  }// /->set_output()

  /**
   * Escape character string
   * @param string $str
   * @return string
   */
  function escape_str($str) { return mysql_real_escape_string(stripslashes($str)); }

/*
* ================================================================================
* Private Methods
* ================================================================================
*/

  /**
   * Execute the SQL and collect the result set
   * @param string $sql
   * @return boolean
   */
  function _process_query($sql) {
    $this->flush();
    $this->open();
    $this->_last_sql = trim( $sql );  // Save query

    if ($this->_debug) {
      $this->_result_set = mysql_query($sql, $this->_conn) or $this->_throw_error(gettext('Querying database'));
    } else {
      $this->_result_set = @mysql_query($sql, $this->_conn);
    }

    // If got a result set..
    if ($this->_result_set) {

      // number of columns returned
      $this->_num_cols = mysql_num_fields($this->_result_set);

      // Store column names as an array
      $i=0;
      $this->_result_cols = array();
      while ($i < $this->_num_cols) {
        $field = @mysql_fetch_field($this->_result_set,$i);
        $this->_result_cols[] = $field->name;
        $i++;
      }

      // Store the results as an array of row objects
      while ( $row = @mysql_fetch_array($this->_result_set,$this->_output_type_int) ) {
        $this->_result[] = $row;
      }

      // number of rows returned
      $this->_num_rows = count($this->_result);

      // Free the actual result set
      @mysql_free_result($this->_result_set);

      // If there were results.. return true
      return ($this->_num_rows>=1);
    } else {
      $this->_num_cols = 0;
      $this->_num_rows = 0;
      $this->_result_cols = null;
      return false;
    }
  } // /->process_query()

  /**
   * function to capture the thrown error and out put to the screen
   * @param string $err_msg
   * @return boolean
   */
  function _throw_error($err_msg) {
    if ($this->_conn) {
      die("<hr />".gettext('DATABASE ERROR')."<hr />$err_msg :: ". mysql_error($this->_conn) .'<hr />'. $this->get_last_sql().'<hr />');
    } else {
      die("<hr />".gettext('DATABASE ERROR')."<hr />$err_msg :: &lt;NO SERVER&gt;<hr />". $this->get_last_sql().'<hr />');
    }
    return false;
  }// /->_throw_error()

  /**
  * Prepare a value for putting into the database
  * Escapes special characters, checks for NULL, and puts in quotes as necessary
  *
  * @param integer $value value to prepare
  *
  * @return string   en-quoted value, ready for insertion into a database (of the form 'value' or NULL)
  */
  function _prepare_field_value($value) {
    // NULL values don't need quotes, so if it's null, just return a string containing NULL
    // Else, return an escaped string containing the value enclosed in quotes
    if (is_null($value)) {
      return 'NULL';
    } else if (is_int($value)) {
      return "$value";
    } else {
      return '\''. $this->escape_str($value) .'\'';
    }

  }// /->_prepare_field_value()

} // /class: DAO

?>
