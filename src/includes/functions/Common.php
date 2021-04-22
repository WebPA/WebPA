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

use Doctrine\DBAL\ParameterType;
use WebPA\includes\classes\DAO;

define('MYSQL_DATETIME_FORMAT', 'Y-m-d H:i:s');    // MYSQL datetime format (for update/insert/etc)

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
    public static function fetch_GET($key, $default_value = '')
    {
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
    public static function fetch_POST($key, $default_value = '')
    {
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
    public static function fetch_SERVER($key, $default_value = '')
    {
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
    public static function fetch_SESSION($key, $default_value = '')
    {
        return (isset($_SESSION[$key])) ? $_SESSION[$key] : $default_value;
    }

    /**
     * Generate a UUID formatted Unique Identifier (ABCDEFGH-IJKL-MNOP-QRST-UVWXYZ123456)
     * NOTE - This does not use the UUID algorithm
     *
     * @return UUID
     */
    public static function uuid_create()
    {
        // Get random 32-char 'UUID'
        $uuid_32 = strtoupper(md5(uniqid(rand(), true)));

        // Convert to the correct 'dashed' format, and return the UUID
        return preg_replace('#([\dA-F]{8})([\dA-F]{4})([\dA-F]{4})([\dA-F]{4})([\dA-F]{12})#', '\\1-\\2-\\3-\\4-\\5', $uuid_32);
    }

    /**
     * Add an entry to the tracking table
     */
    public static function logEvent(DAO $db, $description, $module_id = null, $object_id = null)
    {
        $dbConn = $db->getConnection();

        $now = date(MYSQL_DATETIME_FORMAT);

        if (!empty($module_id)) {
            $module_id = (int) $module_id;
        }

        $query =
            'INSERT INTO ' . APP__DB_TABLE_PREFIX . 'user_tracking ' .
            '(user_id, datetime, ip_address, description, module_id, object_id) ' .
            'VALUES (?, ?, ?, ?, ?, ?) ' .
            'ON DUPLICATE KEY UPDATE user_id = ?';

        $stmt = $dbConn->prepare($query);

        $stmt->bindValue(1, (int) $_SESSION['_user_id'], ParameterType::INTEGER);
        $stmt->bindValue(2, $now);
        $stmt->bindValue(3, $_SERVER['REMOTE_ADDR']);
        $stmt->bindValue(4, $description);
        $stmt->bindValue(5, $module_id, ParameterType::INTEGER);
        $stmt->bindValue(6, $object_id);
        $stmt->bindValue(7, (int) $_SESSION['_user_id'], ParameterType::INTEGER);

        $stmt->execute();
    }

    /**
    * Check if the user is logged in and is a user of the given type. If not, it logs the user out.
    *
    * @param string $_user
    * @param string $user_type
    */
    public static function check_user($_user, $user_type = null)
    {

      // Is the user valid?
        if ($_user) {

        // if we're not checking the user type, or we are checking and it matches, return OK
            if (!$user_type || $_user->is_admin()) {
                return true;
            }
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
        return false;
        


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
    public static function & rel1($struc, &$file)
    {
        return file_exists(($file = (dirname($struc).'/'.$file)));
    }
}
