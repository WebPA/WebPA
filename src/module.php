<?php
/**
 * Change module page
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once 'includes/inc_global.php';

use Doctrine\DBAL\ParameterType;
use WebPA\includes\functions\Common;

if (($_source_id != '') && !$_user->is_admin()) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

$module_id = Common::fetch_POST('module_id');

if ($module_id) {
    // Update last module
    $updateLastModuleQuery =
      'UPDATE ' . APP__DB_TABLE_PREFIX . 'user ' .
      'SET last_module_id = ? ' .
      'WHERE user_id = ?';

    $DB->getConnection()->executeQuery(
        $updateLastModuleQuery,
        [$module_id, $_user_id],
        [ParameterType::INTEGER, ParameterType::INTEGER]
    );

    // Update session
    $dbConn = $DB->getConnection();

    $query = 'SELECT module_code FROM ' . APP__DB_TABLE_PREFIX . 'module WHERE module_id = ?';

    $moduleCode = $dbConn->fetchOne($query, [$module_id], [ParameterType::INTEGER]);

    $_SESSION['_module_id'] = $module_id;
    $_SESSION['_user_context_id'] = $moduleCode;

    Common::logEvent($DB, 'Leave module', $_module_id);
    Common::logEvent($DB, 'Enter module', $module_id);

    header('Location: ' . APP__WWW . '/');

    exit;
}

//set the page information
$UI->page_title = 'Change Module';
$UI->menu_selected = 'change module';
$UI->breadcrumbs = ['home' => './', 'change source' => null];
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
      $modules = $CIS->get_user_modules(null, null, 'name');
  } else {
      $modules = $CIS->get_user_modules($_user->id, null, 'name');
  }

    echo '<table>';
    if (count($modules) > 0) {
        foreach ($modules as $id => $module) {
            $checked_str = (isset($_module_id) && ($id == $_module_id)) ? ' checked="checked"' : '' ;
            echo '<tr>';
            echo "  <td><input type=\"radio\" name=\"module_id\" id=\"module_{$id}\" value=\"{$id}\"{$checked_str} /></td>";
            echo "  <td><label style=\"font-weight: normal;\" for=\"module_{$id}\">{$module['module_title']} [{$module['module_code']}]</label></td>";
            echo '</tr>';
        }
    } else {
        echo '<tr>';
        echo '  <td colspan="2">No modules</td>';
        echo '</tr>';
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
