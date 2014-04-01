<?php
/**
 * 
 * Check login credentials
 * 
 * The file will also check the type of login that is taking place.
 * 			
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.1
 * 
 * + Given that the DBAuthenticator never returns an error message, changed the returned 
 * message in case of incorrect username/password to 'invalid', as with an empty username 
 * or password.
 * made by Morgan Harris [morgan@snowproject.net] as of 15/10/09
 * 
 */
 
require_once("./include/inc_global.php");
//only load the authentication class we actually need
if (AUTH__CLASS == 'LDAPAuthenticator'){
	require_once(DOC__ROOT.'/library/classes/class_ldap_authenticator.php');
}elseif (AUTH__CLASS == 'DBAuthenticator'){
	require_once(DOC__ROOT.'/library/classes/class_db_authenticator.php');
}
require_once(DOC__ROOT.'/library/functions/lib_string_functions.php');


// --------------------------------------------------------------------------------
// Process Get/Post

$username = (string) fetch_POST('username', null);
$password = (string) fetch_POST('password', null);

// Sanitize the username/password data
$username = substr($username,0,32);
$password = substr($password,0,32);

$msg ="";


// --------------------------------------------------------------------------------
// Attempt Login

$auth_success = false;

if ( ($username) && ($password) ) {
    
	$authenticated = false;

	// Authenticate...
	$_auth = new Authenticator($username, $password);
	$authenticated = $_auth->authenticate();

	// if authentication successful..
	if ($authenticated) {

		// We need to get the user_id of the person authenticated

		$_user_id = null;
		
		// Use the email address to look them up...
		if ($_auth->email) {
			$user_info = $CIS->get_user_for_email($_auth->email);
			if ($user_info) {
				$_user_id = $user_info['user_id'];
				$_user_admin = $user_info['admin'];
			}
		}

		// Save session data
		$_SESSION['_user_id'] = $_user_id;
		$_SESSION['_admin'] = $_user_admin;

		// Save cookie data
		$_cookie->vars['user_id'] = $_user_id;
		$_cookie->vars['admin'] = $_user_admin;
		$_cookie->save();
		
		header('Location: '.APP__WWW.'/index.php?id='.$_user_id);	// This doesn't log them in, the user_id just shows as a debug check
		exit;
	}else{
		$msg = 'invalid';
		
		//but just to be sure check for an authorisation failed message
		$auth_failed = $_auth->get_error();
		
		if(strlen($auth_failed)>0){
			$msg = $auth_failed;
		}
		
	}

} else {
	$msg = 'invalid';
}

header('Location: '.APP__WWW."/login.php?msg=".$msg);
exit;
?>