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

use Doctrine\DBAL\ParameterType;

class Authenticator
{

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
    private $cis;

    /**
     *  CONSTRUCTOR for the Authenticator class
     */
    public function __construct(EngCIS $cis, $username = NULL, $password = NULL)
    {
        $this->cis = $cis;

        $this->username = $username;
        $this->password = $password;
    }// /->Authenticator()

    /*
    ================================================================================
      PUBLIC
    ================================================================================
    */

    /**
     * Initialise the user details.
     *
     * @param $user
     *
     * @return bool
     */
    function initialise($user)
    {
        $this->user_type = NULL;
        $this->_authenticated = FALSE;
        $this->_disabled = TRUE;

        $is_admin = FALSE;
        $DAO = $this->get_DAO();
        $dbConn = $DAO->getConnection();

        if (!is_null($user)) {
            $is_admin = $user['admin'] == 1;
            $source_id = $user['source_id'];

            $this->module_id = $user['last_module_id'];

            if (!empty($this->module_id)) {
                if (!$is_admin) {
                    $userModuleQuery = "SELECT module_id, user_type FROM {APP__DB_TABLE_PREFIX}user_module WHERE module_id = ? AND user_id = ?";

                    $userModule = $dbConn->fetchAssociative($userModuleQuery, [$user['last_module_id'], $user['user_id']], [ParameterType::INTEGER, ParameterType::INTEGER]);

                    if (is_null($userModule)) {
                        $this->module_id = NULL;
                    }
                } else {
                    $adminModuleQuery = "SELECT source_id FROM {APP__DB_TABLE_PREFIX}module WHERE module_id = ?";

                    $adminModule = $dbConn->fetchAssociative($adminModuleQuery, [$user['last_module_id']], [ParameterType::INTEGER]);

                    if (!is_null($adminModule)) {
                        $source_id = $adminModule['source_id'];
                    }
                }
            }
            if (empty($this->module_id)) {
                if ($is_admin) {
                    $modules = $this->cis->get_user_modules(NULL, NULL, NULL, $source_id);
                } else {
                    $modules = $this->cis->get_user_modules($user['user_id']);
                }
                if (count($modules) > 0) {
                    $ids = array_keys($modules);
                    $this->module_id = $ids[0];
                }
            }

            if (!empty($this->module_id)) {
                $moduleQuery = "SELECT module_code FROM {APP__DB_TABLE_PREFIX}module WHERE module_id = ?";

                $moduleCode = $dbConn->fetchOne($moduleQuery, [$this->module_id], ParameterType::INTEGER);

                if (is_null($moduleCode)) {
                    $this->module_id = null;
                } else {
                    $this->module_code = $moduleCode;
                }
            }

            if (!is_null($this->module_id)) {
                $userTypeQuery = "SELECT user_type FROM {APP__DB_TABLE_PREFIX}user_module WHERE module_id = ? AND user_id = ?";

                $userType = $dbConn->fetchOne($userTypeQuery, [$this->module_id, $user['user_id']], [ParameterType::INTEGER, ParameterType::INTEGER]);

                // Update last login date
                $now = date(MYSQL_DATETIME_FORMAT, time());
                $sql_login_date = 'UPDATE ' . APP__DB_TABLE_PREFIX . "user SET date_last_login = '{$now}' WHERE user_id = '{$user['user_id']}'";
                $DAO->execute($sql_login_date);

                //with the database row data returned get all the information and add it to the class holders
                $this->user_id = $user['user_id'];
                $this->source_id = $source_id;
                if (!$is_admin) {
                    $this->user_type = $userType;
                } else {
                    $this->user_type = APP__USER_TYPE_ADMIN;
                }

                $this->_disabled = ($user['disabled'] == 1);
                $this->_authenticated = !$this->_disabled;

            }

        }

        return $this->_authenticated;

    }

    /*
    Is the user authenticated?
    */
    function is_authenticated()
    {
        return $this->_authenticated;
    }// /->is_authenticated()

    /*
    Is the user disabled?
    */
    function is_disabled()
    {
        return $this->_disabled;
    }// /->is_disabled()

    /*
    Is this user admin?
    */
    function is_admin()
    {
        return ($this->user_type == APP__USER_TYPE_ADMIN);
    }// /->is_admin()

    /*
    Is this user staff?
    */
    function is_staff()
    {
        return ($this->user_type == APP__USER_TYPE_TUTOR) || ($this->user_type == APP__USER_TYPE_ADMIN);
    }// /->is_staff()

    /*
    Is this user tutor?
    */
    function is_tutor()
    {
        return ($this->user_type == APP__USER_TYPE_TUTOR);
    }// /->is_staff()

    /*
    Is this user student?
    */
    function is_student()
    {
        return ($this->user_type == APP__USER_TYPE_STUDENT);
    }// /->is_student()

    /*
    Get the last authorisation error
    */
    function get_error()
    {
        return $this->_error;
    }// /->get_error()

    /*
    Get the DAO object
    */
    function get_DAO()
    {

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
