<?php
/**
 * 
 * Class :  Cookie
 * 	
 * Simple cookie class - just for storing basic vars
 *
 * Save values to the cookie by:  $Cookie->vars['key'] = value;
 * 			
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 */

class Cookie {
	// Public
	public $vars = null;

	public $created = null;
	public $last_access = null;
	
	// Private
	private $_name = 'AUTH_COOKIE';
	private $_expires = 3600;


	/**
	*	CONSTRUCTOR for the cookie class
	*/
	function Cookie() {
		$this->vars = array ();
		// If no existing cookie, create a new one
		if (!array_key_exists($this->_name,$_COOKIE)) {
			$this->created = time();
			$this->last_access = $this->created;
		} else {
			// Load existing data
			$cookie_data = explode('|', base64_decode($_COOKIE["{$this->_name}"]) );

			$this->created_on = (int) $cookie_data[0];
			$this->last_access = (int) $cookie_data[1];
			$this->vars = unserialize($cookie_data[2]);
		}
	}// /->Cookie()

	
	// --------------------------------------------------------------------------------
	// Public Methods
	
	/**
	* Delete the cookie
	*/
	function delete() {
		setcookie($this->_name, null, time() - 3600, '/');
	}
	
	
	/**
	* Save the cookie (by sending it to browser)
	*/
	function save() {
		$this->_last_access = time();
		$cookie_vars = serialize($this->vars);
		$cookie_data = base64_encode( "{$this->created}|{$this->last_access}|{$cookie_vars}" );
		setcookie($this->_name, $cookie_data, 0, '/');
	}// /->save()

	
	/** 
	* Check if the cookie was last accessed less than (expiry-time) ago
	* @return mixed
	*/
	function validate() {
		return ( ($this->last_access + $this->_expires) > time() );
	}
	
}// /class: Cookie
?>