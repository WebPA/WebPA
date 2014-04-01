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
	public $username = '';
	public $password = null;

	public $id = '';
	public $forename = '';
	public $surname = '';
	public $email = '';
	public $staff_id = null;
	public $student_id = null;
	public $type = '';
	public $institutional_ref = '';
	public $department_id = '';
	public $course_id = '';
	public $admin = '';
	
	public $DAO = null;


	/**
	* CONSTRUCTOR for the class function
	* @param string $username
	* @param string $passsword
	*/
	function User($username = null, $password = null) {
		$this->username = $username;
		$this->password = $password;
		$this->id = null;
		$this->staff_id = null;
		$this->student_id = null;
		$this->admin = null;
		$this->institutional_ref = null;
		$this->department_id = null;
		$this->course_id = null;
	}// /->User()


/*
* ================================================================================
* PUBLIC
*================================================================================
*/


	/**
	* Load the object from the given data
	*
	* @param array $user_info	 assoc-array of User info
	*
	* @return	boolean did the load succeed
	*/
	function load_from_row($user_info) {
		if (is_array($user_info)) {
			$this->id = $user_info['user_id'];
			$this->type = $user_info['user_type'];
		
			switch ($this->type) {
				case 'staff':
							$this->staff_id = $this->id;
							break;
				// --------------------
				case 'student':
							$this->student_id = $this->id;
							break;
				// --------------------
				default: break;
			}// /switch

			$this->username = $user_info['username'];
			$this->forename = $user_info['forename'];
			$this->surname = $user_info['lastname'];
			$this->email = $user_info['email'];
			$this->admin = $user_info['admin'];
			$this->institutional_ref = $user_info['institutional_reference'];
			$this->department_id = $user_info['department_id'];
			$this->course_id = $user_info['course_id'];
			$this->password = $user_info['password'];
		}
		return true;
	}// /->load_from_row()


	/**
	* Is this user staff?
	*
	* @return boolean user is staff
	*/
	function is_staff() {
		return ($this->type == 'staff');
	}// /->is_staff()

	
	/**
	* Returns the staff/student id of the user
	*
	* @return string user's actual id number (staff/student ID depending on user_type)
	*/
	function get_id_number() {
		return ($this->is_staff()) ? $this->staff_id : $this->student_id ;
	}// /->get_id_number()

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
	 * Function to update the user details
	 */
	 function save_user(){
	 	
	 	$_fields = array ('forename'				=> $this->forename ,
						  'lastname'				=> $this->surname ,
						  'email'					=> $this->email,
						  'institutional_reference' => $this->institutional_ref,
						  'department_id' 			=> $this->department_id,
						  'username' 				=> $this->username,
						  'password' 				=> $this->password,
						  'user_type' 				=> $this->type,
						  'course_id' 				=> $this->course_id,
						  'admin'					=> $this->admin,
						   );
						  

		
		//save the changes to the user
		$this->DAO->do_update("UPDATE user SET {fields} WHERE user_id ='{$this->id}'; ",$_fields);
	 	
	 	return true;	 	
	 }
	 
	 /**
	  * Function to set the database connection to be used
	  * @param database connection $DB
	  */
	  function set_dao_object($DB){
	  	$this->DAO = $DB;
	  }
	
/*
* ================================================================================
* PRIVATE
* ================================================================================
*/


}// /class: User

?>