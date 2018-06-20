<?php
/**
 *
 * Class : User
 *
 * This is a lightweight user class and does not contain the database access stuff
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.1.0
 *
 */

class User {
  // Public Vars
  public $username = null;
  public $source_id = null;
  public $password = null;
  public $id = null;
  public $admin = null;
  public $id_number = null;
  public $department_id = null;
  public $forename = null;
  public $lastname = null;
  public $email = null;
  public $type = null;

  public $DAO = null;

  /**
  * CONSTRUCTOR for the class function
  * @param string $username
  * @param string $passsword
  */
  function __construct($username = null, $password = null) {
    $this->username = $username;
    $this->source_id = '';
    $this->password = $password;
    $this->id = null;
    $this->type = null;
    $this->id_number = null;
    $this->department_id = null;
    $this->forename = null;
    $this->lastname = null;
    $this->admin = 0;
  }// /->User()

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
  function load_from_row($user_info) {
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
  }// /->load_from_row()

  /**
  * Is this user admin?
  *
  * @return boolean user is admin
  */
  function is_admin() {
    return ($this->admin == 1);
  }// /->is_admin()

  /**
  * Is this user staff?
  *
  * @return boolean user is staff
  */
  function is_staff() {
    return ($this->type == APP__USER_TYPE_ADMIN) || ($this->type == APP__USER_TYPE_TUTOR);
  }// /->is_staff()

  /*
  Is this user tutor?
  */
  function is_tutor() {
    return ($this->type == APP__USER_TYPE_TUTOR);
  }// /->is_staff()

  /*
  Is this user student?
  */
  function is_student() {
    return ($this->type == APP__USER_TYPE_STUDENT);
  }// /->is_student()

  /**
   * Update password
   *
   * Updates the password used by the user
   *
   * @param string $password
   */
  function update_password($password){
    $this->password = $password;
  }

  /**
   * Function to update the username
   * @param string $username
   */
   function update_username($username){
    $this->username = $username;
   }

  /**
   * Function to update the source_id
   * @param string $source_id
   */
   function update_source_id($source_id){
    $this->source_id = $source_id;
   }

  /**
   * Function to update the user details
   */
   function save_user(){

    $_fields = array ('forename'        => $this->forename ,
              'lastname'        => $this->lastname ,
              'email'         => $this->email,
              'username'        => $this->username,
              'source_id'      => $this->source_id,
              'password'        => $this->password,
              'id_number' => $this->id_number,
              'department_id' => $this->department_id
              );

    //save the changes to the user
    $this->DAO->do_update("UPDATE " . APP__DB_TABLE_PREFIX . "user SET {fields} WHERE user_id = {$this->id}; ",$_fields);

    return true;
   }

   /**
    * Function to set the database connection to be used
    * @param database connection $this->DAO
    */
    function set_dao_object($DB){
      $this->DAO = $DB;
    }

  /**
   * Function to add new user details
   */
   function add_user(){

    $_fields = array ('forename'        => $this->forename ,
              'lastname'        => $this->lastname ,
              'email'         => $this->email,
              'username'        => $this->username,
              'source_id'      => $this->source_id,
              'password'        => $this->password,
              'id_number' => $this->id_number,
              'department_id' => $this->department_id,
              'admin' => $this->admin
              );

    //save the changes to the user
    $this->DAO->do_update("INSERT INTO " . APP__DB_TABLE_PREFIX . "user SET {fields}", $_fields);

    return $this->DAO->get_insert_id() ;
   }

  /**
   * Function to delete a user
   */
   function delete(){

     $this->DAO->execute("DELETE FROM " . APP__DB_TABLE_PREFIX . "user_reset_request WHERE user_id = {$this->id}");
     $this->DAO->execute("DELETE FROM " . APP__DB_TABLE_PREFIX . "form WHERE form_owner_id = {$this->id}");
     $this->DAO->execute("DELETE FROM " . APP__DB_TABLE_PREFIX . "user_justification WHERE (marked_user_id = {$this->id}) OR (user_id = {$this->id})");
     $this->DAO->execute("DELETE FROM " . APP__DB_TABLE_PREFIX . "user_mark WHERE (marked_user_id = {$this->id}) OR (user_id = {$this->id})");
     $this->DAO->execute("DELETE FROM " . APP__DB_TABLE_PREFIX . "user_group_member WHERE user_id = {$this->id}");
     $this->DAO->execute("DELETE FROM " . APP__DB_TABLE_PREFIX . "user_response WHERE user_id = {$this->id}");
     $this->DAO->execute("DELETE FROM " . APP__DB_TABLE_PREFIX . "user_module WHERE user_id = {$this->id}");
     $this->DAO->execute("DELETE FROM " . APP__DB_TABLE_PREFIX . "user_tracking WHERE user_id = {$this->id}");
     $this->DAO->execute("DELETE FROM " . APP__DB_TABLE_PREFIX . "user WHERE user_id = {$this->id}");

     $this->id = null;

   }

/*
* ================================================================================
* PRIVATE
* ================================================================================
*/

}// /class: User

?>
