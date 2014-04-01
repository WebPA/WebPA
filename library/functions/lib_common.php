<?php
/**
 * 
 * Library of common functions
 *
 * 			 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 * 
 */
define('MYSQL_DATETIME_FORMAT','Y-m-d H:i:s');		// MYSQL datetime format (for update/insert/etc)

/**
 * fetch var from a cookie (or return default if unset)
 * 
 * @param string $key
 * @param mixed $default_value 
 *  
 * @return mixed
 */
function fetch_COOKIE($key, $default_value = '') {
	return (isset($_COOKIE[$key])) ? $_COOKIE[$key] : $default_value;
}

/**
 * fetch var from querystring (or return default if unset)
 * 
 * @param string $key
 * @param mixed $default_value 
 *  
 * @return mixed
 */
function fetch_GET($key, $default_value = '') {
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
function fetch_POST($key, $default_value = '') {
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
function fetch_SERVER($key, $default_value = '') {
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
function fetch_SESSION($key, $default_value = '') {
	return (isset($_SESSION[$key])) ? $_SESSION[$key] : $default_value;
}

/**
 * Check if the given bits are set (any bits or all bits)
 * 
 * @param int $bit
 * @param int $want_bits
 * @param bool $must_have_all
 * 
 * @return bool 
 */
function check_bits($bits = 0, $want_bits = 0, $must_have_all = false) {
		return ($must_have_all) ? (($bits & $want_bits) == $want_bits) : (($bits & $want_bits) > 0);
} // /check_bits()

/**
 * Wrapper for empty()
 * Enables use of empty() on methods and variable-functions
 * 
 * @param var $var
 * 
 * @return bool 
 */
function is_empty(&$var) {
	return empty($var);
}// /is_empty()

/**
 * Generate a UUID formatted Unique Identifier (ABCDEFGH-IJKL-MNOP-QRST-UVWXYZ123456)
 * NOTE - This does not use the UUID algorithm
 * 
 * @return UUID
 */
function uuid_create() {
	// Get random 32-char 'UUID'
	$uuid_32 = strtoupper( md5( uniqid( rand(), true) ) );

	// Convert to the correct 'dashed' format, and return the UUID
	return preg_replace('#([\dA-F]{8})([\dA-F]{4})([\dA-F]{4})([\dA-F]{4})([\dA-F]{12})#', "\\1-\\2-\\3-\\4-\\5", $uuid_32);
}
?>
