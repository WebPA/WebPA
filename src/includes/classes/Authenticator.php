<?php
/**
 * Class : Authenticator
 *
 * Authenticates the given username and any password against the internal database
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\includes\classes;

class Authenticator {

// Public variables
  public $user_id = NULL;
  public $source_id = NULL;
  public $user_type = NULL;
  public $module_id = NULL;
  public $module_code = NULL;

// Private variables
  protected $username = NULL;
  protected $password = NULL;
  protected $_authenticated = FALSE;
  protected $_disabled = NULL;
  protected $_error = NULL;
  private $_DAO = NULL;

  /**
   *  CONSTRUCTOR for the Authenticator class
   */
  function __construct($username = NULL, $password = NULL) {
    $this->username = $username;
    $this->password = $password;
  }// /->Authenticator()

/*
================================================================================
  PUBLIC
================================================================================
*/

  /*
  Initialise the user details
  */
  function initialise($sql) {

    global $CIS;

    $this->user_type = NULL;
    $this->_authenticated = FALSE;
    $this->_disabled = TRUE;

    $is_admin = FALSE;
    $DAO = $this->get_DAO();
    $user_data = $DAO->fetch_row($sql);
    if (!is_null($user_data)) {
      $is_admin = $user_data['admin'] == 1;
      $source_id = $user_data['source_id'];

      $this->module_id = $user_data['last_module_id'];
      if (!empty($this->module_id)) {
        if (!$is_admin) {
          $sql_user_module = 'SELECT module_id, user_type FROM ' . APP__DB_TABLE_PREFIX . "user_module WHERE module_id = {$user_data['last_module_id']} AND user_id = {$user_data['user_id']}";
          $user_module = $DAO->fetch_row($sql_user_module);
          if (is_null($user_module)) {
            $this->module_id = NULL;
          }
        } else {
          $sql_admin_module = 'SELECT source_id FROM ' . APP__DB_TABLE_PREFIX . "module WHERE module_id = {$user_data['last_module_id']}";
          $admin_module = $DAO->fetch_row($sql_admin_module);
          if (!is_null($admin_module)) {
            $source_id = $admin_module['source_id'];
          }
        }
      }
      if (empty($this->module_id)) {
        if ($is_admin) {
          $modules = $CIS->get_user_modules(NULL, NULL, NULL, $source_id);
        } else {
          $modules = $CIS->get_user_modules($user_data['user_id']);
        }
        if (count($modules) > 0) {
          $ids = array_keys($modules);
          $this->module_id = $ids[0];
        }
      }

      if (!empty($this->module_id)) {

        $sql_module = 'SELECT module_code FROM ' . APP__DB_TABLE_PREFIX . "module WHERE module_id = {$this->module_id}"; // AND source_id = '{$source_id}'";
        $module = $DAO->fetch_row($sql_module);
        if (is_null($module)) {
          $this->module_id = NULL;
        } else {
          $this->module_code = $module['module_code'];
        }
      }

      if (!is_null($this->module_id)) {

        $sql_user_module = 'SELECT user_type FROM ' . APP__DB_TABLE_PREFIX . "user_module WHERE module_id = {$this->module_id} AND user_id = {$user_data['user_id']}";
        $user_module = $DAO->fetch_row($sql_user_module);

        // Update last login date
        $now = date(MYSQL_DATETIME_FORMAT,time());
        $sql_login_date = 'UPDATE ' . APP__DB_TABLE_PREFIX . "user SET date_last_login = '{$now}' WHERE user_id = '{$user_data['user_id']}'";
        $DAO->execute($sql_login_date);

        //with the database row data returned get all the information and add it to the class holders
        $this->user_id = $user_data['user_id'];
        $this->source_id = $source_id;
        if (!$is_admin) {
          $this->user_type = $user_module['user_type'];
        } else {
          $this->user_type = APP__USER_TYPE_ADMIN;
        }

        $this->_disabled = ($user_data['disabled'] == 1);
        $this->_authenticated = !$this->_disabled;

      }

    }

    return $this->_authenticated;

  }

  /*
  Is the user authenticated?
  */
  function is_authenticated() {
    return $this->_authenticated;
  }// /->is_authenticated()

  /*
  Is the user disabled?
  */
  function is_disabled() {
    return $this->_disabled;
  }// /->is_disabled()

  /*
  Is this user admin?
  */
  function is_admin() {
    return ($this->user_type == APP__USER_TYPE_ADMIN);
  }// /->is_admin()

  /*
  Is this user staff?
  */
  function is_staff() {
    return ($this->user_type == APP__USER_TYPE_TUTOR) || ($this->user_type == APP__USER_TYPE_ADMIN);
  }// /->is_staff()

  /*
  Is this user tutor?
  */
  function is_tutor() {
    return ($this->user_type == APP__USER_TYPE_TUTOR);
  }// /->is_staff()

  /*
  Is this user student?
  */
  function is_student() {
    return ($this->user_type == APP__USER_TYPE_STUDENT);
  }// /->is_student()

  /*
  Get the last authorisation error
  */
  function get_error() {
    return $this->_error;
  }// /->get_error()

  /*
  Get the DAO object
  */
  function get_DAO() {

    if (is_null($this->_DAO)) {
      $this->_DAO = new DAO(APP__DB_HOST, APP__DB_USERNAME, APP__DB_PASSWORD, APP__DB_DATABASE);
    }

    return $this->_DAO;

  }

/*
================================================================================
  PRIVATE
================================================================================
*/

}// /class Authenticator

?>
