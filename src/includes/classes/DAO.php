<?php
/**
 * Class : DAO
 *
 * This data access object is designed for MySQL only
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\includes\classes;

use Doctrine\DBAL\DriverManager;

class DAO
{
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
    private $_result = [];      // Query results, as array of row objects

    private $_output_type = 'ARRAY_A';  // 'ARRAY_A': Associative Array : $results[row]['field']
    // 'ARRAY_N': Numeric Array : $results[row][col]
    // 'ARRAY_B': Assoc + Numeric Array : use $results[row]['field'] or $results[row][col]

    private $_output_type_int = MYSQLI_ASSOC;  // MYSQLI_ASSOC, MYSQLI_BOTH, MYSQLI_NUM

    private $_insert_id = null;     // Last inserted id (on auto-increment columns)

    private $_num_cols = null;
    private $_num_rows = null;
    private $_num_affected = null;

    private $_last_error = null;

    /**
     * CONSTRUCTOR
     *
     * Set database connection strings
     *
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $database
     * @param boolean $persistent
     *
     * @return void
     */
    function __construct($host, $user, $password, $database, $persistent = false)
    {
        $connectionParams = [
            'dbname' => $database,
            'user' => $user,
            'password' => $password,
            'host' => $host,
            'driver' => 'mysqli'
        ];

        $this->_conn = DriverManager::getConnection($connectionParams);
    }

    /*
    * ================================================================================
    * Public Methods
    * ================================================================================
    */

    /**
     * Open database connection
     * @param boolean $new_link block reusing an existing database connection when the same server/username/password are used
     * @return boolean
     */
    function open($new_link = true)
    {
        if (is_null($this->_conn)) {
            if ($this->_persistent) {
                $func = 'mysqli_pconnect';
            } else {
                $func = 'mysqli_connect';
            }


            $this->_conn = $func($this->_host, $this->_user, $this->_password, $this->_database);

            if (!$this->_conn) {
                die ('Can\'t use database due to  : ' . mysqli_connect_errno() . ' - ' . mysqli_connect_error());

                return false;
            }

            return true;
        }

        return true;
    } // /->open()

    /**
     * Execute the SQL query, and ignore results (ie, not a SELECT)
     * Sets affected rows (ie could be DELETE/INSERT/REPLACE/UPDATE)
     * Sets inserted id if query is INSERT/REPLACE (checks first word of query)
     * @param string $sql
     * @return boolean
     */
    function execute($sql)
    {
        $this->open();
        $this->_last_sql = trim($sql);  // Save query

        $this->_result_set = @mysqli_query($this->_conn, $sql);

        if ($this->_result_set) {
            $this->_num_affected = mysqli_affected_rows($this->_conn);

            if (preg_match("/^\\s*(insert|replace) /i", $sql)) {
                $this->_insert_id = mysqli_insert_id($this->_conn);
            }

            if ($this->_num_affected) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Return last inserted id (for auto-increment columns)
     *
     * @return integer
     */
    function get_insert_id()
    {
        return $this->_insert_id;
    }

    public function getConnection()
    {
        return $this->_conn;
    }
}