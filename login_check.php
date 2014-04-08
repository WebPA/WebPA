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

require_once("./includes/inc_global.php");

// --------------------------------------------------------------------------------
// Process Get/Post

$username = (string) fetch_POST('username');
$password = (string) fetch_POST('password');

// Sanitize the username/password data
$username = substr($username,0,255);
$password = substr($password,0,255);

$msg ='';

// --------------------------------------------------------------------------------
// Attempt Login

$auth_success = false;

if ( ($username) && ($password) ) {

  $authenticated = FALSE;

  // Authenticate...
  require_once(DOC__ROOT . 'includes/classes/class_authenticator.php');
  for ($i = 0; $i < count($LOGIN_AUTHENTICATORS); $i++) {
    $classname = $LOGIN_AUTHENTICATORS[$i];
    require_once(DOC__ROOT . 'includes/classes/class_' . strtolower($classname) . '_authenticator.php');
    $classname .= "Authenticator";
    $_auth = new $classname($username, $password);
    if ($_auth->authenticate()) {
      $authenticated = TRUE;
      break;
    }
  }

  if (!$authenticated) {

    $msg = 'invalid';

    //but just to be sure check for an authorisation failed message
    $auth_failed = $_auth->get_error();

    if(strlen($auth_failed) > 0){
      $msg = $auth_failed;
    }

  } else if ($_auth->is_disabled()) {

    $msg = 'no access';

  } else {

    $_SESSION = array();
    session_destroy();
    session_name(SESSION_NAME);
    session_start();

    // Save session data
    $_SESSION['_user_id'] = $_auth->user_id;
    $_SESSION['_user_source_id'] = $_auth->source_id;
    $_SESSION['_user_type'] = $_auth->user_type;
    $_SESSION['_source_id'] = $_auth->source_id;
    $_SESSION['_module_id'] = $_auth->module_id;
    $_SESSION['_user_context_id'] = $_auth->module_code;

    logEvent('Login');
    logEvent('Enter module', $_auth->module_id);

    header('Location: ' . APP__WWW . "/index.php?id={$_user_id}"); // This doesn't log them in, the user_id just shows as a debug check
    exit;

  }

} else {

  $msg = '';

}

header('Location: ' . APP__WWW . "/login.php?msg={$msg}");
exit;

?>