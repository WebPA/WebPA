<?php

/**
 * Edit Groups : Edit Group Set, and list Groups
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../../includes/inc_global.php';

use WebPA\includes\classes\GroupHandler;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

// --------------------------------------------------------------------------------
// Process GET/POST

$collection_id = Common::fetch_GET('c');

$command = Common::fetch_POST('command', Common::fetch_GET('command'));

$list_url = '../../../tutors/groups/';

// --------------------------------------------------------------------------------

$group_handler = new GroupHandler();
$collection = $group_handler->get_collection($collection_id);

$allow_edit = false;

if ($collection) {
    // Check if the user can edit this group
    $allow_edit = true;
}

// --------------------------------------------------------------------------------
// Process Form

$errors = null;

$errors = null;

if (($command) && ($collection) && ($allow_edit)) {
    switch ($command) {
    case 'save':
      $collection->name = Common::fetch_POST('collection_name');
      if (empty($collection->name)) {
          $errors[] = 'You must give this collection of groups a name.';
      }
      if (!$errors) {
          $collection->save();
      }
      break;
    // --------------------
    case 'delete':
      $collection->delete();
      header("Location: $list_url");
      exit;
      break;
    // --------------------
  }// /switch
}

$collection_qs = "c={$collection->id}";

// --------------------------------------------------------------------------------
// Begin Page

$page_title = ($collection) ? "Editing: {$collection->name}" : 'Editing Unknown Collection';

$UI->page_title = APP__NAME . ' ' . $page_title;
$UI->menu_selected = 'my groups';
$UI->help_link = '?q=node/253';
$UI->breadcrumbs = [
  'home'        => '../../',
  'my groups'     => '../',
  $page_title     => null,
];

$UI->set_page_bar_button('List Groups', '../../../../images/buttons/button_group_list.gif', '../');
$UI->set_page_bar_button('Create Groups', '../../../../images/buttons/button_group_create.gif', '../create/');
$UI->set_page_bar_button('Clone Groups', '../../../../images/buttons/button_group_clone.gif', '../clone/');

$UI->head();
?>
<style type="text/css">
<!--

div.group_info { font-size: 80%; }

-->
</style>
<script language="JavaScript" type="text/javascript">
<!--

  function do_command(com) {
    switch (com) {
      case 'delete' :
            if (confirm('This collection will be deleted.\n\nClick OK to confirm.')) {
              document.collection_form.command.value = 'delete';
              document.collection_form.submit();
            }
            break;
      default :
            document.collection_form.command.value = com;
            document.collection_form.submit();
    }
  }// /do_command()

//-->
</script>
<?php
$UI->content_start();

$UI->draw_boxed_list($errors, 'error_box', 'The following errors were found:', 'No changes have been saved. Please check the details in the form, and try again.');
?>

<p>On this page you can edit the details for this collection of groups, or examine the individual groups themselves.</p>

<div class="content_box">

<form action="edit_collection.php?<?php echo $collection_qs; ?>" method="post" name="collection_form">
<input type="hidden" name="command" value="none">

<div class="nav_button_bar">
  <table cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><a href="<?php echo $list_url; ?>"><img src="../../../images/buttons/arrow_green_left.gif" alt="back -"> back to my groups</a></td>
<?php
if ($collection) {
    ?>
    <td align="right"><input class="danger_button" type="button" name="" value="delete collection" onclick="do_command('delete');" /></td>
<?php
}
?>
  </tr>
  </table>
</div>

<?php
if (!$collection) {
    ?>
  <p>The collection you selected could not be loaded for some reason - please go back and try again.</p>
<?php
} else {
        $collection_qs = "c={$collection->id}"; ?>
  <h2>Collection</h2>
  <div class="form_section form_line">
    <p>You can change the name of this collection of groups using the box below.</p>
    <table class="form" cellpadding="2" cellspacing="2">
    <tr>
      <th><label for="collection_name">Name</label></th>
      <td><input type="text" name="collection_name" id="collection_name" maxlength="50" size="40" value="<?php echo $collection->name; ?>" /></td>
      <td>
        <input type="button" name="savebutton" value="update name" onclick="do_command('save');" />
      </td>
    </tr>
    </table>
  </div>

  <h2>Groups</h2>
  <div class="form_section">
<?php
  $num_module_students = $CIS->get_module_students_count($_module_id);
        // Group Information
        $groups_iterator =& $collection->get_groups_iterator();
        $num_groups = $groups_iterator->size();
        $num_groups_units = ($num_groups==1) ? 'group' : 'groups';

        $collection_total_members = 0;
        if ($num_groups==0) {
            echo "<p>You do not have any groups in this collection. To use this collection you need to <a href=\"edit_collection_groups.php?$collection_qs\">add groups</a>.</p>";
        } else {
            ?>
    <p>These are the groups contained in this collection.</p>
    <p>To edit an individual group and its members click on <img src="../../../images/buttons/edit.gif" width="16" height="16" alt="edit group" title="edit" /> in the list below.</p>
    <div style="text-align: right;"><a class="button" href="edit_collection_members.php?<?php echo $collection_qs; ?>">assign all students to groups</a> &nbsp; &nbsp; <a class="button" href="edit_collection_groups.php?<?php echo $collection_qs; ?>">add/remove groups</a></div>

    <div class="obj_list">
<?php
    for ($groups_iterator->reset(); $groups_iterator->is_valid(); $groups_iterator->next()) {
        $group = $groups_iterator->current();
        $num_members = count($group->get_members());
        $num_members_units = ($num_members==1) ? 'member' : 'members';
        $collection_total_members += $num_members;

        $edit_url = "edit_group.php?c={$collection->id}&g={$group->id}"; ?>
      <div class="obj">
        <table class="obj" cellpadding="2" cellspacing="2">
        <tr>
          <td width="24"><a class="text" href="<?php echo $edit_url; ?>"><img src="../../../images/icons/groups.gif" alt="Groups" height="24" width="24" /></a></td>
          <td class="obj_info">
            <div class="obj_name"><a class="text" href="<?php echo $edit_url; ?>"><?php echo $group->name; ?></a></div>
            <div class="obj_info_text">Contains <?php echo "$num_members $num_members_units"; ?></div>
          </td>
          <td class="buttons">
            <a href="<?php echo $edit_url; ?>"><img src="../../../images/buttons/edit.gif" width="16" height="16" alt="edit group" title="edit" /></a>
          </td>
        </tr>
        </table>
      </div>
<?php
    } ?>
    </div>
<?php
    $num_module_students_units = ($num_module_students==1) ? 'student' : 'students';
            $num_members_units = ($collection_total_members==1) ? 'member' : 'members';

            echo "<p>There are $num_groups $num_groups_units, containing $collection_total_members $num_members_units (out of a possible $num_module_students $num_module_students_units).</p>";
        } ?>
  </div>

<?php
    }
?>
  </form>
</div>

<?php

$UI->content_end();

?>
