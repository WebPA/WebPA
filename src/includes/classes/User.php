<?php
/**
 * User
 *
 * This is a lightweight user class and does not contain the database access stuff
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\includes\classes;

use Doctrine\DBAL\ParameterType;

class User
{
    // Public Vars
    public $username;

    public $source_id;

    public $password;

    public $id;

    public $admin;

    public $id_number;

    public $department_id;

    public $forename;

    public $lastname;

    public $email;

    public $type;

    public DAO $DAO;

    /**
    * CONSTRUCTOR for the class function
    * @param string $username
    * @param string $passsword
    */
    public function __construct($username = null, $password = null)
    {
        $this->username = $username;
        $this->source_id = '';
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        $this->id = null;
        $this->type = null;
        $this->id_number = null;
        $this->department_id = null;
        $this->forename = null;
        $this->lastname = null;
        $this->admin = 0;
    }

    // /->User()

    /*
    * ================================================================================
    * PUBLIC
    *================================================================================
    */

    /**
    * Load the object from the given data
    *
    * @param array $user_info  assoc-array of User info
    *
    * @return boolean did the load succeed
    */
    public function load_from_row($user_info)
    {
        if (is_array($user_info)) {
            $this->id = $user_info['user_id'];
            $this->admin = $user_info['admin'];
            $this->id_number = $user_info['id_number'];
            $this->department_id = $user_info['department_id'];
            $this->username = $user_info['username'];
            $this->source_id = $user_info['source_id'];
            $this->password = $user_info['password'];
            $this->forename = $user_info['forename'];
            $this->lastname = $user_info['lastname'];
            $this->email = $user_info['email'];
            if ($this->admin) {
                $this->type = APP__USER_TYPE_ADMIN;
            } else {
                $this->type = $user_info['user_type'];
            }
        }
        return true;
    }

    // /->load_from_row()

    /**
    * Is this user admin?
    *
    * @return boolean user is admin
    */
    public function is_admin()
    {
        return $this->admin == 1;
    }

    // /->is_admin()

    /**
    * Is this user staff?
    *
    * @return boolean user is staff
    */
    public function is_staff()
    {
        return ($this->type == APP__USER_TYPE_ADMIN) || ($this->type == APP__USER_TYPE_TUTOR);
    }

    // /->is_staff()

    // Is this user tutor?
    public function is_tutor()
    {
        return $this->type == APP__USER_TYPE_TUTOR;
    }

    // /->is_staff()

    // Is this user student?
    public function is_student()
    {
        return $this->type == APP__USER_TYPE_STUDENT;
    }

    // /->is_student()

    /**
     * Update password
     *
     * Updates the password used by the user
     *
     * @param string $password
     */
    public function update_password($password)
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Function to update the username
     * @param string $username
     */
    public function update_username($username)
    {
        $this->username = $username;
    }

    /**
     * Function to update the source_id
     * @param string $source_id
     */
    public function update_source_id($source_id)
    {
        $this->source_id = $source_id;
    }

    /**
     * Function to update the user details
     */
    public function save_user()
    {
        $this->DAO
        ->getConnection()
        ->createQueryBuilder()
        ->update(APP__DB_TABLE_PREFIX . 'user')
        ->set('forename', '?')
        ->set('lastname', '?')
        ->set('email', '?')
        ->set('username', '?')
        ->set('source_id', '?')
        ->set('password', '?')
        ->set('id_number', '?')
        ->set('department_id', '?')
        ->where('user_id = ?')
        ->setParameter(0, $this->forename)
        ->setParameter(1, $this->lastname)
        ->setParameter(2, $this->email)
        ->setParameter(3, $this->username)
        ->setParameter(4, $this->source_id)
        ->setParameter(5, $this->password)
        ->setParameter(6, $this->id_number)
        ->setParameter(7, $this->department_id)
        ->setParameter(8, $this->id, ParameterType::INTEGER)
        ->execute();

        return true;
    }

    /**
     * Function to set the database connection to be used
     * @param DAO connection $this->DAO
     */
    public function set_dao_object(DAO $DB)
    {
        $this->DAO = $DB;
    }

    /**
     * Function to add new user details
     */
    public function add_user()
    {
        $this->DAO
           ->getConnection()
           ->createQueryBuilder()
           ->insert(APP__DB_TABLE_PREFIX . 'user')
           ->values([
               'forename' => '?',
               'lastname' => '?',
               'email' => '?',
               'username' => '?',
               'source_id' => '?',
               'password' => '?',
               'id_number' => '?',
               'department_id' => '?',
               'admin' => '?',
           ])
           ->setParameter(0, $this->forename)
           ->setParameter(1, $this->lastname)
           ->setParameter(2, $this->email)
           ->setParameter(3, $this->username)
           ->setParameter(4, $this->source_id)
           ->setParameter(5, $this->password)
           ->setParameter(6, $this->id_number)
           ->setParameter(7, $this->department_id)
           ->setParameter(8, $this->admin, ParameterType::INTEGER)
           ->execute();

        return $this->DAO->getConnection()->lastInsertId('user_id');
    }

    /**
     * Function to delete a user
     */
    public function delete()
    {
        $dbConn = $this->DAO->getConnection();

        $dbConn->executeQuery(
            'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'user_reset_request WHERE user_id = ?',
            [$this->id],
            [ParameterType::INTEGER]
        );

        $dbConn->executeQuery(
            'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'form WHERE form_owner_id = ?',
            [$this->id],
            [ParameterType::INTEGER]
        );

        $dbConn->executeQuery(
            'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'user_justification WHERE marked_user_id = ? OR user_id = ?',
            [$this->id, $this->id],
            [ParameterType::INTEGER, ParameterType::INTEGER]
        );

        $dbConn->executeQuery(
            'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'user_mark WHERE marked_user_id = ? OR user_id = ?',
            [$this->id, $this->id],
            [ParameterType::INTEGER, ParameterType::INTEGER]
        );

        $dbConn->executeQuery(
            'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'user_group_member WHERE user_id = ?',
            [$this->id],
            [ParameterType::INTEGER]
        );

        $dbConn->executeQuery(
            'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'user_response WHERE user_id = ?',
            [$this->id],
            [ParameterType::INTEGER]
        );

        $dbConn->executeQuery(
            'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'user_module WHERE user_id = ?',
            [$this->id],
            [ParameterType::INTEGER]
        );

        $dbConn->executeQuery(
            'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'user_tracking WHERE user_id = ?',
            [$this->id],
            [ParameterType::INTEGER]
        );

        $dbConn->executeQuery(
            'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'user WHERE user_id = ?',
            [$this->id],
            [ParameterType::INTEGER]
        );

        $this->id = null;
    }

    /*
    * ================================================================================
    * PRIVATE
    * ================================================================================
    */
}// /class: User
