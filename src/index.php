<?php
/**
 * INDEX - Main page
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('includes/inc_global.php');

$mod = '';
if (isset($_SERVER['PATH_INFO']) && (strlen($_SERVER['PATH_INFO']) > 0)) {
  $mod = substr($_SERVER['PATH_INFO'], 1);
} else if (isset($_SERVER['QUERY_STRING'])) {
  $mod = $_SERVER['QUERY_STRING'];
}

if ($mod && in_array($mod, $INSTALLED_MODS)) {
  if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
    include_once("mod/{$mod}/index.php");
  } else {
    header('Location: ' . APP__WWW . "/mod/{$mod}/");
  }
} else if ($_user) {
  if ($_user->is_admin()) {
    header('Location: ' . APP__WWW . '/admin/');
  } else if ($_user->is_tutor()) {
    header('Location: ' . APP__WWW . '/tutors/');
  } else {
    header('Location: ' . APP__WWW . '/students/');
  }
} else {
  header('Location: ' . APP__WWW . '/login.php');
}

exit;

?>
