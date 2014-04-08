<?php
/**
 *
 * INDEX - Main page
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 *
 */

require_once('includes/inc_global.php');

$url = '';
if (isset($_GET['url'])) {
  $url = $_GET['url'];
}

if ($_user) {
  $id = '';
  if (isset($_GET['id'])) {
    $id = $_GET['id'];
  }
  header('Location: ' . APP__WWW . '/index.php?id=' . $id);
} else if ($url) {
  if (strpos($url, '?') === FALSE) {
    $url .= '?';
  } else {
    $url .= '&';
  }
  header('Location: ' . $url . 'lti_errormsg=' . urlencode('Unable to connect to ' . APP__NAME . '; please ensure that your browser is not blocking third-party cookies'));
} else {
  header('Location: ' . APP__WWW . '/login.php?msg=cookies');
}

exit;

?>