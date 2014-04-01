<?php
/**
 * 
 * Class : Authenticate
 * 
 * Authenticates the given username and password against the LDAP server
 * In the event of an authentication error, ->get_error() will return:
 * 'connfailed' : A connection to the authentication server could not be established
 * 	'invalid'   : The login details were invalid
 * 			
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.1
 * 
 */

require_once("./include/inc_global.php");
require_once(DOC__ROOT . '/include/inc_ldap_settings.php');
require_once(DOC__ROOT . '/library/classes/class_dao.php');


class Authenticator {
	// Public Vars
	public $username = '';
	public $password = null;

	public $fullname = '';
	public $email = '';
	public $staff_id = null;
	public $student_id = null;
	public $user_type = '';

	// Private Vars
	private $_authenticated = false;
	private $_outcome = '';
	
	private $_DAO = null;

	
	/**
	 * 	CONSTRUCTOR for the 
	 */
	function Authenticator($username = null, $password = null) {
		$this->username = $username;
		$this->password = md5($password);
		$this->_DAO =& new DAO( APP__DB_HOST, APP__DB_USERNAME, APP__DB_PASSWORD, APP__DB_DATABASE);
		$this->_DAO->set_debug(true);
	}// /->DBAuthenticator()


/*
================================================================================
	PUBLIC
================================================================================
*/


	/*
	Authenticate the user against the LDAP directory
	*/
	function authenticate() {
		$this->fullname = '';
		$this->email = '';
		$this->staff_id = null;
		$this->student_id = null;
		$this->user_id = null;
		$this->user_type = '';
	
		$this->_authenticated = false;
		$this->_error = null;

		$user_params = null;

		//match the username and password to the values in the database.
		
		$sql = 'SELECT * ' .
			  'FROM user ' .
			  'WHERE username = "' . mysql_escape_string(stripslashes($this->username)) . 
			  '" and password = "' . mysql_escape_string(stripslashes($this->password)) .
			  '";';
		
		
		$user_data = $this->_DAO->fetch_row($sql);
	
		
		//with the database row data returned get all the information and add it to the class holders
		$this->fullname = $user_data['forename']. " " . $user_data['lastname'];
		$this->email = $user_data['email'];
		$this->user_type = $user_data['user_type'];
		
		
		
		$this->_authenticated = true;
	
		
		return $this->_authenticated;
	}// /->authenticate()


	/*
	Is the user authenticated?
	*/
	function is_authenticated() {
		return ($this->_authenticated) ? $this->_authenticated : $this->authenticate();
	}// /->is_authenticated()


	/*
	Is this user staff?
	*/
	function is_staff() {
		return ($this->user_type == 'staff');
	}// /->is_staff()

	
	/*
	Get the last authorisation error
	*/
	function get_error() {
		return ($this->_error);
	}// /->get_error()
	
/*
================================================================================
	PRIVATE
================================================================================
*/

}// /class DBAuthenticator
?>