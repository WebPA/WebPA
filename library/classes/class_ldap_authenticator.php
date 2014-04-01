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
 * @version 1.0.0.0
 * 
 */

require_once("./include/inc_global.php");
require_once(DOC__ROOT . '/include/inc_ldap_settings.php');


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

	
	/**
	 * 	CONSTRUCTOR for the 
	 */
	function Authenticator($username = null, $password = null) {
		$this->username = $username;
		$this->password = $password;
	}// /->LDAPAuthenticator()


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

		//set the debug level 
		ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);			
	
		//using the ldap function connect with the specified server
		$ldapconn = ldap_connect(LDAP__HOST,LDAP__PORT);	
	
		//check the connection
		if(!$ldapconn) {
			return false; 		
			exit;
		}
	
		//Set this option to cope with Windows Server 2003 Active directories
		ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);	
		
		//Set the version of LDAP that we will be using
		ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
		
		//construct login name
		$user = $this->username . LDAP__USERNAME_EXT;
		
		//bind with the username and password
		$bind = ldap_bind($ldapconn, $user, $this->password);	
		
		//check the bind has worked
		if(!$bind) {
			// drop the ldap connection
			ldap_close($ldapconn);
			return false;
			exit;
		}
		
		$temp = LDAP__USERNAME_EXT;
		$filter = "name={$this->username}*";
		
		$base = LDAP__BASE_DN;

		$result = ldap_search($ldapconn, $base, $filter);
		
		//check the bind has worked
		if(!$result) {
			//drop the ldap connection
			ldap_close($ldapconn);
			return false;
			exit;
		}
		
		$info = ldap_get_entries($ldapconn,$result);
		
		// debug : var_dump($info);
		
		//with the results add this to the cls info.
		$this->fullname = $info[0]["displayname"][0];
		$this->email = $info[0]["mail"][0];
		$description_str = $info[0]["description"][0];
		
		//check in the string for staff
		if(strripos ($description_str, 'staff')){
			$this->user_type = 'staff';
		}else{
			$this->user_type = 'student';
		}	
		
		$this->_authenticated = true;
	
		ldap_close($ldapconn);
		
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

}// /class LDAPAuthenticator
?>