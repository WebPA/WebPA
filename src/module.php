<?php
/**
 * Change module page
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

use WebPA\includes\Config;
use WebPA\includes\functions\Common;

if (($_source_id != '') && !$_user->is_admin()) {
  header('Location:'. Config::APP__WWW .'/logout.php?msg=denied');
  exit;
}

$module_id = Common::fetch_POST('module_id');

if ($module_id) {

  // Update last module
  $sql_last_module = 'UPDATE ' . APP__DB_TABLE_PREFIX . "user SET last_module_id = '{$module_id}' WHERE user_id = '{$_user_id}'";
  $DB->execute($sql_last_module);

  // Update session
  $sql_module = 'SELECT module_code FROM ' . APP__DB_TABLE_PREFIX . "module WHERE module_id = {$module_id}";
  $module = $DB->fetch_row($sql_module);
  $_SESSION['_module_id'] = $module_id;
  $_SESSION['_user_context_id'] = $module['module_code'];

  Common::logEvent('Leave module', $_module_id);
  Common::logEvent('Enter module', $module_id);

  header('Location: ' . Config::APP__WWW . "/");
  exit;

}

//set the page information
$UI->page_title = 'Change Module';
$UI->menu_selected = 'change module';
$UI->breadcrumbs = array ('home' => './', 'change source' => null);
$UI->help_link = '?q=node/237';
$UI->head();
$UI->body();
$UI->content_start();

//build the content to be written to the screen

$page_intro = 'Use this page to change the currently selected module.';

?>

<p><?php echo $page_intro; ?></p>

<form action="" method="post" name="select_module">
<div class="content_box">
<table class="option_list" style="width: 100%;">
<?php
  //get the modules associated with the user being edited
  if ($_user->is_admin()) {
    $modules = $CIS->get_user_modules(NULL, NULL, 'name');
  } else {
    $modules = $CIS->get_user_modules($_user->id, NULL, 'name');
  }

    echo "<table>";
    if (count($modules) > 0) {
      foreach ($modules as $id => $module) {
        $checked_str = (isset($_module_id) && ($id == $_module_id)) ? ' checked="checked"' : '' ;
        echo('<tr>');
        echo("  <td><input type=\"radio\" name=\"module_id\" id=\"module_{$id}\" value=\"{$id}\"{$checked_str} /></td>");
        echo("  <td><label style=\"font-weight: normal;\" for=\"module_{$id}\">{$module['module_title']} [{$module['module_code']}]</label></td>");
        echo('</tr>');
      }
    } else {
      echo('<tr>');
      echo('  <td colspan="2">No modules</td>');
      echo('</tr>');
    }
?>
</table>
</div>
<?php
  if (count($modules) > 0) {
?>
<p>
<input type="submit" value="Select module" />
</p>
<?php
  }
?>
</form>
<?php

$UI->content_end();

?>
