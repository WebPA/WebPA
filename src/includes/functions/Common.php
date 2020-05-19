<?php
/**
 * Library of common functions
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\includes\functions;

define('MYSQL_DATETIME_FORMAT','Y-m-d H:i:s');    // MYSQL datetime format (for update/insert/etc)

class Common
{
    /**
     * fetch var from querystring (or return default if unset)
     *
     * @param string $key
     * @param mixed $default_value
     *
     * @return mixed
     */
    public static function fetch_GET($key, $default_value = '') {
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
    public static function fetch_POST($key, $default_value = '') {
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
    public static function fetch_SERVER($key, $default_value = '') {
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
    public static function fetch_SESSION($key, $default_value = '') {
      return (isset($_SESSION[$key])) ? $_SESSION[$key] : $default_value;
    }

    /**
     * Generate a UUID formatted Unique Identifier (ABCDEFGH-IJKL-MNOP-QRST-UVWXYZ123456)
     * NOTE - This does not use the UUID algorithm
     *
     * @return UUID
     */
    public static function uuid_create() {
      // Get random 32-char 'UUID'
      $uuid_32 = strtoupper( md5( uniqid( rand(), true) ) );

      // Convert to the correct 'dashed' format, and return the UUID
      return preg_replace('#([\dA-F]{8})([\dA-F]{4})([\dA-F]{4})([\dA-F]{4})([\dA-F]{12})#', "\\1-\\2-\\3-\\4-\\5", $uuid_32);
    }

    /**
     * Add an entry to the tracking table
     */
    public static function logEvent($description, $module_id = NULL, $object_id = NULL) {

      global $DB;

      $now = date(MYSQL_DATETIME_FORMAT,time());
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
    * Check if the user is logged in and is a user of the given type. If not, it logs the user out.
    *
    * @param string $_user
    * @param string $user_type
    */
    public static function check_user($_user, $user_type = NULL) {

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

    /**
     * This function is a legacy function which appears to determine if a file is located in a particular directory.
     *
     */
    public static function & rel1($struc, &$file) {
      return file_exists( ( $file = ( dirname($struc).'/'.$file ) ) );
    }

    /**
     * This function is a legacy function.
     */
    public static function relativetome1($structure, $filetoget) {
      return self::rel($structure,$filetoget) ? require_once($filetoget) : null;
    }

    /**
     * This function is a legacy function.
     */
    public static function & rel4($struc, &$file) {
      return file_exists( ( $file = ( dirname($struc).'/'.$file ) ) );
    }

    /**
     * This function is a legacy function.
     */
    public static function relativetome4($structure, $filetoget) {
      return rel4($structure,$filetoget) ? require_once($filetoget) : null;
    }

    /**
     * This function is a legacy function. 
     */
    public static function rel($struc, &$file) {
      return file_exists( ( $file = ( dirname($struc).'/'.$file ) ) );
    }

    /**
     * This function is a legacy function.
     */
    public static function relativetome($structure, $filetoget){
      return rel($structure,$filetoget) ? require_once($filetoget) : null;
    }
}
