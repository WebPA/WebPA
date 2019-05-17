<?php
/**
 * INDEX - Main page
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

use WebPA\includes\Config;

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
    header('Location: ' . Config::APP__WWW . "/mod/{$mod}/");
  }
} else if ($_user) {
  if ($_user->is_admin()) {
    header('Location: ' . Config::APP__WWW . '/admin/');
  } else if ($_user->is_tutor()) {
    header('Location: ' . Config::APP__WWW . '/tutors/');
  } else {
    header('Location: ' . Config::APP__WWW . '/students/');
  }
} else {
  header('Location: ' . Config::APP__WWW . '/login.php');
}

exit;

?>
