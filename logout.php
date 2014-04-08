<?php
/**
 *
 * Logout page
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 *
 */

require_once("includes/inc_global.php");

if (isset($_SESSION['_user_id'])) {
  logEvent('Logout');
}

$old_session = $_SESSION;
$_SESSION = array();
session_destroy();

if (isset($old_session['logout_url'])) {
  $url = $old_session['logout_url'];
  if ($_SERVER['QUERY_STRING']) {
    if (strpos($url, '?') === FALSE) {
      $url .= '?';
    } else {
      $url .= '&';
    }
    $url .= $_SERVER['QUERY_STRING'];
  }
  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
      $params["path"], $params["domain"],
      $params["secure"], $params["httponly"]);
  }
} else {
  $msg = (fetch_GET('msg',null)) ? fetch_GET('msg',null) : 'logout' ;
  $url = "login.php?msg=$msg";
  session_start();
  if (isset($old_session['branding_logo'])) {
    $_SESSION['branding_logo'] = $old_session['branding_logo'];
  }
  if (isset($old_session['branding_logo.width'])) {
    $_SESSION['branding_logo.width'] = $old_session['branding_logo.width'];
  }
  if (isset($old_session['branding_logo.height'])) {
    $_SESSION['branding_logo.height'] = $old_session['branding_logo.height'];
  }
  if (isset($old_session['branding_name'])) {
    $_SESSION['branding_name'] = $old_session['branding_name'];
  }
  if (isset($old_session['branding_css'])) {
    $_SESSION['branding_css'] = $old_session['branding_css'];
  }

}

header("Location: $url");

?>