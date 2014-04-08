<?php
/**
 *
 * Library of common functions
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 *
 */
define('MYSQL_DATETIME_FORMAT','Y-m-d H:i:s');    // MYSQL datetime format (for update/insert/etc)

/**
 * fetch var from a cookie (or return default if unset)
 *
 * @param string $key
 * @param mixed $default_value
 *
 * @return mixed
 */
function fetch_COOKIE($key, $default_value = '') {
  return (isset($_COOKIE[$key])) ? $_COOKIE[$key] : $default_value;
}

/**
 * fetch var from querystring (or return default if unset)
 *
 * @param string $key
 * @param mixed $default_value
 *
 * @return mixed
 */
function fetch_GET($key, $default_value = '') {
  return (isset($_GET[$key])) ? $_GET[$key] : $default_value;
}

/**
 * fetch var from a form-post (or return default if unset)
 *
 * @param string $key
 * @param mixed $default_value
 *
 * @return mixed
 */
function fetch_POST($key, $default_value = '') {
  return (isset($_POST[$key])) ? $_POST[$key] : $default_value;
}

/**
 * fetch var from the $_SERVER superglobal (or return default if unset)
 *
 * @param string $key
 * @param mixed $default_value
 *
 * @return mixed
 */
function fetch_SERVER($key, $default_value = '') {
  return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default_value;
}

/**
 * fetch var from a $_SESSION (or return default if unset)
 *
 * @param string $key
 * @param mixed $default_value
 *
 * @return mixed
 */
function fetch_SESSION($key, $default_value = '') {
  return (isset($_SESSION[$key])) ? $_SESSION[$key] : $default_value;
}

/**
 * Check if the given bits are set (any bits or all bits)
 *
 * @param int $bit
 * @param int $want_bits
 * @param bool $must_have_all
 *
 * @return bool
 */
function check_bits($bits = 0, $want_bits = 0, $must_have_all = false) {
    return ($must_have_all) ? (($bits & $want_bits) == $want_bits) : (($bits & $want_bits) > 0);
} // /check_bits()

/**
 * Wrapper for empty()
 * Enables use of empty() on methods and variable-functions
 *
 * @param var $var
 *
 * @return bool
 */
function is_empty(&$var) {
  return empty($var);
}// /is_empty()

/**
 * Generate a UUID formatted Unique Identifier (ABCDEFGH-IJKL-MNOP-QRST-UVWXYZ123456)
 * NOTE - This does not use the UUID algorithm
 *
 * @return UUID
 */
function uuid_create() {
  // Get random 32-char 'UUID'
  $uuid_32 = strtoupper( md5( uniqid( rand(), true) ) );

  // Convert to the correct 'dashed' format, and return the UUID
  return preg_replace('#([\dA-F]{8})([\dA-F]{4})([\dA-F]{4})([\dA-F]{4})([\dA-F]{12})#', "\\1-\\2-\\3-\\4-\\5", $uuid_32);
}

/**
 * Add an entry to the tracking table
 */
function logEvent($description, $module_id = NULL, $object_id = NULL) {

  global $DB;

  $now = date(MYSQL_DATETIME_FORMAT,mktime());
  if (!empty($module_id)) {
    $module_id = intval($module_id);
  }

  $fields = array ('user_id' => intval($_SESSION['_user_id']),
           'datetime'    => $now,
           'ip_address'  => $_SERVER['REMOTE_ADDR'],
           'description' => $description,
           'module_id'   => $module_id,
           'object_id'   => $object_id);
  return $DB->do_insert('INSERT INTO ' . APP__DB_TABLE_PREFIX . 'user_tracking ({fields}) VALUES ({values})', $fields);

}

/**
* Check if the user is logged in and is a user of the given type
* If not, it logs the user out
* @param string $_user
* @param string $user_type
*/
function check_user($_user, $user_type = NULL) {

  // Is the user valid?
  if ($_user) {

    // if we're not checking the user type, or we are checking and it matches, return OK
    if (!$user_type || $_user->is_admin()) {
      return true;
    } else {
      switch ($user_type) {
        case APP__USER_TYPE_ADMIN:
          if ($_user->is_admin()) {
            return true;
          }
          break;
        case APP__USER_TYPE_TUTOR:
          if ($_user->is_tutor()) {
            return true;
          }
          break;
        case APP__USER_TYPE_STUDENT:
          if ($_user->is_student()) {
            return true;
          }
          break;
      }
      return false;
    }
  } else {
    return false;
  }


  // If we didn't call 'return' then the user is denied access

  // If they tried to access the main index page, assume they haven't logged in and go to the login page directly
  if ($_SERVER['PHP_SELF']=='/index.php') {
    header('Location: '. APP__WWW .'/login.php');
  } else {  // log them out and give the DENIED message
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
  }
  exit;
}

?>
