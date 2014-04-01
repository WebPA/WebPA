<?php
/**
 * 
 * class ComplexUser
 * 	
 * This is a more complex version of a user object, and allows roles/permissions
 * 	
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 * 
 */
class ComplexUser {
	public $DAO = null;

	public $username = '';
	public $password = null;
	public $use_local_login = 0;

	public $_authenticated = false;
	public $_roles = null;
	public $_permissions = null;
	

	/**
	* CONSTRUCTOR for the class user
	* @param object $DAO
	* @param string $username
	* @param string $password
	*/
	function User(&$DAO = null, $username = null, $password = null) {
		$this->_initialise();
		$this->DAO =& $DAO;
		$this->username = $username;
		$this->password = $password;
	}// /->User()


	/*
	========================================
	PUBLIC
	========================================
	*/
/**
 * Function to load the user information
 * @param integer $user_id
 */
	function load($user_id) {
		$user_row = $this->DAO->fetch_row("SELECT * FROM user WHERE (user_id='$user_id') ");
		if ($user_row) { $this->load_from_row($user_row); }
	}// /->load()

/**
 * Function to load data using the username
 * @param string $username
 */
	function load_using_username($username) {
		$user_row = $this->DAO->fetch_row("SELECT * FROM user WHERE (username='$username') ");
		if ($user_row) { $this->load_from_row($user_row); }
	}// /->load_using_username()

/**
 * Load the information from the row
 * @param object $row
 */
	function load_from_row(&$row) {
		$this->username = $row['username'];
		$this->password = $row['password'];
		$this->use_local_login = $row['use_local_login'];
	}// /->load_from_row()

/**
 * Function to authenticate the use
 * @return boolean is authenticated or not
 */
	function authenticate() {
		$this->_authenticated = false;

		$user_row = $this->DAO->fetch_row("SELECT * FROM user WHERE (username='{$this->username}') AND (password='{$this->password}') AND (use_local_login=1) ");
		if ($user_row) {
			$this->load_from_row($user_row);
			$this->_permissions = $this->_fetch_permissions();
			$this->_roles = $this->_fetch_roles();
			$this->_authenticated = true;
		}
		return $this->_authenticated;
	}// /->authenticate()

/**
 * Function to check authentication
 * @return boolean is autheticated
 */
	function is_authenticated() {
		return ($this->_authenticated) ? $this->_authenticated : $this->authenticate();
	}// /->is_authenticated()

/**
 * Function to check permissions
 * @param string $object_name
 * @param string $permission)name
 * @return boolean has permission
 */
	function has_permission($object_name, $permission_name) {
		return (is_array($this->_permissions)) ? get_bit($this->permissions[$object_name],constant($permission_name)) : false;
	}// /->has_permission()

/**
 * Check the role
 * @param string $name
 * @return boolean has the role
 */
	function has_role($name) {
		return (is_array($this->_roles)) ? in_array($name,$this->_roles) : false;
	}// /->has_role()

/**
 * Function to save changes to information
 */
	function save() {
		$fields = array ('user_id'			=> $this->user_id ,
						 'username'			=> $this->username ,
						 'password'			=> $this->password ,
						 'use_local_login'	=> $this->use_local_login);

		// Save user record
		$this->DAO->do_insert('REPLACE INTO user ({fields}) VALUES ({values})',$fields);
	}// /->save()


	/*
	========================================
	PRIVATE
	========================================
	*/
/**
 * Initialisation function
 */
	function _initialise() {
		$this->DAO = null;
		$this->username = '';
		$this->password = '';
		$this->use_local_login = 0;

		$this->_authenticated = false;
	}// /->_initialise()

/**
 * fetch permissions
 * @return array 
 */
	function _fetch_permissions() {
		return array();
	}// /->_fetch_permissions()

/**
 * fetch the roles
 * @return array
 */
	function _fetch_roles() {
		return array();
	}// /->_fetch_roles()

}
?>