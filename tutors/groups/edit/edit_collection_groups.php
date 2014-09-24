<?php
/**
 *
 * Edit Groupset Groups
 *
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 *
 */

require_once("../../../includes/inc_global.php");
require_once(DOC__ROOT . 'includes/classes/class_group_handler.php');
require_once(DOC__ROOT . 'includes/functions/lib_form_functions.php');

if (!check_user($_user, APP__USER_TYPE_TUTOR)){
  header('Location:'. APP__WWW .'/logout.php?msg=denied');
  exit;
}

// --------------------------------------------------------------------------------
// Process GET/POST

$collection_id = fetch_GET('c');

$command = fetch_POST('command');

$collection_url = "edit_collection.php?c={$collection_id}";

// --------------------------------------------------------------------------------

global $group_handler;
$group_handler = new GroupHandler();
$collection =& $group_handler->get_collection($collection_id);

$allow_edit = false;

if ($collection) {
  // Check if the user can edit this group
  $allow_edit = !$collection->is_locked();
}

// --------------------------------------------------------------------------------
// Process Form

$errors = null;

if ($allow_edit) {
  switch ($command) {
    case 'add':
      $new_name = fetch_POST('new_group_name');
      if (empty($new_name)) { $errors[] = gettext('You must give your new group a name'); }

      if (!$errors) {
        $new_group = $collection->new_group($new_name);
        $new_group->save();
      }
      break;
    // --------------------
    case 'delete':
      $groups_to_delete = fetch_POST('group');
      if (is_array($groups_to_delete)) {
        foreach($groups_to_delete as $i => $group_id) {
          $group =& $collection->get_group_object($group_id);
          $group->delete();
          unset($group);
        }
        $collection->refresh_groups();
      }
      break;
  }// /switch
}

// --------------------------------------------------------------------------------
// Begin Page

$collection_name = ($collection) ? $collection->name : gettext('Unknown Collection');
$collection_title = gettext("Editing:")." $collection_name";
$page_title = ($collection) ? gettext("Groups:")." {$collection->name}" : gettext('Groups');

$UI->page_title = APP__NAME . ' ' . $page_title;
$UI->menu_selected = gettext('my groups');
$UI->help_link = '?q=node/253';
$UI->breadcrumbs = array  ( 'home' => '../../' ,
    gettext('my groups') => '../' ,
    gettext("Editing:")." $collection_name" => "../edit/edit_collection.php?c={$collection->id}" ,
                $page_title                 => null ,
);

$UI->set_page_bar_button(gettext('List Groups'), '../../../../images/buttons/button_group_list.gif', '../');
$UI->set_page_bar_button(gettext('Create Groups'), '../../../../images/buttons/button_group_create.gif', '../create/');
$UI->set_page_bar_button(gettext('Clone Groups'), '../../../../images/buttons/button_group_clone.gif', '../clone/');

$UI->head();
?>
<script language="JavaScript" type="text/javascript">
<!--

  function do_command(com) {
    if ( (com!='delete') || (confirm('<?php echo gettext('WARNING \r\nThe selected groups will be deleted, and all students returned to the unassigned list!');?>'))) {
      document.collection_groups_form.command.value = com;
      document.collection_groups_form.submit();
    }
  }// /do_command()

//-->
</script>
<?php
$UI->content_start();

$UI->draw_boxed_list($errors, 'error_box', gettext('The following errors were found:'), gettext('No changes have been saved. Please check the details in the form, and try again.'));
?>

<div class="content_box">

<div class="nav_button_bar">
  <a href="<?php echo($collection_url); ?>"><img src="../../../images/buttons/arrow_green_left.gif" alt="<?php echo gettext('back');?> -"> <?php echo gettext('back to');?> <?php echo($collection_name); ?></a>
</div>

<?php
if (!$collection) {
?>
  <p><?php echo gettext('The collection you selected could not be loaded for some reason - please go back and try again.');?></p>
<?php
} else {
  if ($collection->is_locked()) {
    echo("<p>".gettext('This collection has been locked and cannot be edited.')."</p>");
  } else {
    echo("<p>".gettext('On this page you can choose which modules to associate with this collection.')."</p>");
  }

  $collection_qs = "c={$collection->id}";
?>

  <form action="edit_collection_groups.php?<?php echo($collection_qs); ?>" method="post" name="collection_groups_form">
  <input type="hidden" name="command" value="add" />

  <h2><?php echo gettext('New Group');?></h2>
  <div class="form_section form_line">
    <p><?php echo gettext('To add a new group to this collection, enter its name in the box below and click <em>add</em>.');?></p>
    <table cellpadding="2" cellspacing="2">
    <tr>
      <th><label for="new_group_name"><?php echo gettext('New Group Name');?></label></th>
      <td><input type="text" name="new_group_name" id="new_group_name" size="20" maxlength="50" value="" /></td>
      <td><input type="button" name="addbutton" id="addbutton" value="<?php echo gettext('add');?>" onclick="do_command('add');" /></td>
    </tr>
    </table>
  </div>

  <h2><?php echo gettext('Available Groups');?></h2>
  <div class="form_section">
    <p><?php echo gettext('Below are all the groups contained in this collection.');?></p>
    <p><?php echo gettext('To edit an individual group, click on its name in the list below. To remove one or more groups, tick the appropriate boxes and then click <em>delete</em>.');?></p>

    <table class="grid" cellpadding="2" cellspacing="1">
    <tr>
      <th width="400"><?php echo gettext('Groups in this collection');?></th>
      <th>&nbsp;</th>
    </tr>
<?php
  // Show all the groups contained in this collection
  $groups = $collection->get_groups_array();

  if (is_array($groups)) {
    foreach($groups as $i => $group) {
      $group_qs = "{$collection_qs}&g={$group['group_id']}";
      echo('<tr>');
      echo("<td><a href=\"edit_group.php?$group_qs\">{$group['group_name']}</a></td>");
      echo("<td align=\"center\"><input type=\"checkbox\" name=\"group[]\" id=\"group_{$group['group_id']}\" value=\"{$group['group_id']}\" /></td>");
      echo('</tr>');
    }
    if ($allow_edit) {?>
      <tr>
        <th>&nbsp;</th>
        <th><input type="button" name="deletebutton" id="deletebutton" value="<?php echo gettext('delete');?>" onclick="do_command('delete');" /></th>
      </tr>
      <?php
    }
  } else {
    echo('<tr class="in_collection"><td colspan="3">'.gettext('This collection does not contain any groups').'</td></tr>');
  }
?>
    </table>
      <td valign="bottom">
      </td>
    </tr>
    </table>
  </div>

  </form>
<?php
}
?>
</div>

<?php

$UI->content_end();

?>
