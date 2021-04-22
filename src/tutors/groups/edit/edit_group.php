<?php

/**
 * Edit Groups : Edit Group and members
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../../includes/inc_global.php';

use WebPA\includes\classes\GroupHandler;
use WebPA\includes\functions\Common;
use WebPA\lang\en\Generic;
use WebPA\lang\en\tutors\Tutors;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

// --------------------------------------------------------------------------------
// Process GET/POST

$collection_id = Common::fetch_GET('c');
$group_id = Common::fetch_GET('g');

$command = Common::fetch_POST('command');

$collection_url = "edit_collection.php?c={$collection_id}";

// --------------------------------------------------------------------------------

$group_handler = new GroupHandler();
$collection = $group_handler->get_collection($collection_id);

$allow_edit = false;

if ($collection) {
    $group =& $collection->get_group_object($group_id);

    // Check if the user can edit this group
    $allow_edit = !$collection->is_locked();
    $collection_qs = "c={$collection->id}";
} else {
    $group = null;
    $collection_qs = '';
}

// --------------------------------------------------------------------------------
// Process Form

$errors = null;

if ($allow_edit) {
    switch ($command) {
    case 'save':
      // Change of name
      $group->name = Common::fetch_POST('group_name');
      if (empty($group->name)) {
          $errors[] = Tutors::GROUPS__EDIT_SAVE_ERR;
      }

      // Delete all the members with the 'member' role
      $group->purge_members('member');

      // Add the members who should be IN the group
      foreach ($_POST as $k => $v) {
          $s = strpos($k, 'student_');
          if (($s !== false) && ((int) $v == 1)) {
              $s_id = str_replace('student_', '', $k);
              $group->add_member($s_id, 'member');
          }
      }

      // If there were no errors, save the changes
      if (!$errors) {
          $group->save();
      }

      break;
    // --------------------
    case 'purge':
      $group->purge_members('member');
      $group->save();
      break;
    // --------------------
  }// /switch
}

// --------------------------------------------------------------------------------
// Begin Page

$collection_name = ($collection) ? $collection->name : Generic::UNKNOWN__COLLECTION;
$collection_title = ($collection) ? "Editing: {$collection->name}" : Generic::EDDITING__UNKNOWN_GROUP;
$page_title = ($group) ? "Editing: {$group->name}" : Generic::EDDITING__GROUP;

$UI->page_title = APP__NAME . ' ' . $page_title;
$UI->menu_selected = 'my groups';
$UI->help_link = '?q=node/253';
$UI->breadcrumbs = [
  'home'            => '../../',
  'my groups'         => '../',
  "Editing: $collection_name" => "edit_collection.php?c={$collection->id}",
  $page_title         => null,
];

$UI->set_page_bar_button(Generic::BTN__LIST_GROUPS, '../../../../images/buttons/button_group_list.gif', '../');
$UI->set_page_bar_button(Generic::BTN__CREATE_GROUPS, '../../../../images/buttons/button_group_create.gif', '../create/');
$UI->set_page_bar_button(Generic::BTN__CLONE_GROUPS, '../../../../images/buttons/button_group_clone.gif', '../clone/');

$UI->head();
?>
<style type="text/css">
<!--

div.group_info { font-size: 80%; }

td.radio { text-align: center; }

tr.in_group td { background-color: #beb; }
tr.in_group th { background-color: #9c9; font-weight: bold; }

tr.other_group td { background-color: #fcc; }
tr.other_group th { background-color: #daa; font-weight: bold; }

tr.no_group td {  }
tr.no_group th { font-weight: bold; }

-->
</style>
<script>
<!--

  function do_command(com) {
    document.group_form.command.value = com;
    document.group_form.submit();
  }// /do_command()

//-->
</script>
<?php
$UI->content_start();

$UI->draw_boxed_list($errors, 'error_box', Generic::FOLLOWING__FOUND, Generic::NO_CHANGES);

if ($collection->is_locked()) {
    echo Tutors::COLLECTION__LOCKED;
} else {
    echo Tutors::GROUPS__EDIT_INST;
}
?>

<div class="content_box">

<div class="nav_button_bar">
  <a href="<?php echo $collection_url; ?>"><img src="../../../images/buttons/arrow_green_left.gif" alt="back -"> back to <?php echo $collection_name; ?></a>
</div>

<?php
if (!$group) {
    echo Tutors::GROUP__SELECTED;
} else {
    $group_qs = "{$collection_qs}&g={$group->id}"; ?>

  <form action="edit_group.php?<?php echo $group_qs; ?>" method="post" name="group_form">
  <input type="hidden" name="command" value="none" />

  <h2>Group Name</h2>
  <div class="form_section form_line">
    <p>You can change this group's name using the box below. When you've made your changes, click a <em>save changes</em> button.</p>
    <table class="form" cellpadding="2" cellspacing="2">
    <tr>
      <th><label for="group_name">Name</label></th>
      <td>
<?php
  if ($collection->is_locked()) {
      echo $group->name;
  } else {
      echo "<input type=\"text\" name=\"group_name\" id=\"group_name\" maxlength=\"50\" size=\"40\" value=\"{$group->name}\" />";
  } ?>
      </td>
    </tr>
    </table>
  </div>

  <h2>Group Members</h2>
  <div class="form_section">
    <p>Below are all the students from the modules associated with this group.</p>
    <p>To add a student to this group select <em>In</em>, and to remove one select <em>Out</em>.</p>
    <p>When you have made all your selections, click a <em>save changes</em> button.</p>



    <table cellpadding="0" cellspacing="0">
    <tr>
      <td rowspan="2" valign="top">

      <table class="grid" cellpadding="2" cellspacing="1">
<?php
  // Get all the possible student members
  $module_user_ids = (array) $CIS->get_module_students_user_id([$_module_id]);

    // Get all the students who are allocated to some group in this collection
    $collection_member_ids = array_keys((array) $collection->get_members('member'));

    // Show the students who are IN this group
    $group_student_ids = array_keys((array) $group->get_members());

    $group_students = $CIS->get_user($group_student_ids);

    echo '<tr class="in_group"><th width="400">Students already in this group</th><th align="center" width="50">In</th><th align="center" width="50">Out</th></tr>';
    if (is_array($group_students)) {
        foreach ($group_students as $i => $member) {
            echo '<tr class="in_group">';
            echo "<td>{$member['lastname']}, {$member['forename']} (";
            if (!empty($member['id_number'])) {
                echo $member['id_number'];
            } else {
                echo $member['username'];
            }
            echo ')</td>';
            echo "<td class=\"radio\"><input type=\"radio\" name=\"student_{$member['user_id']}\" id=\"{$member['user_id']}_in\" value=\"1\" checked=\"checked\" /></td>";
            echo "<td class=\"radio\"><input type=\"radio\" name=\"student_{$member['user_id']}\" id=\"{$member['user_id']}_out\" value=\"0\" /></td>";
            echo '</tr>';
        }
    } else {
        echo '<tr class="in_group"><td colspan="3">This group has no members</td></tr>';
    }

    // Show the students who aren't in ANY group in this collection
    if ((is_array($module_user_ids)) && (is_array($collection_member_ids))) {
        $unalloc_student_ids = array_diff($module_user_ids, $collection_member_ids);
    } else {
        $unalloc_student_ids = $module_user_ids;
    }
    $unalloc_students = $CIS->get_user($unalloc_student_ids);

    echo '<tr class="no_group"><th>Students not yet assigned to a group</th><th align="center" width="50">In</th><th align="center" width="50">Out</th></tr>';
    if (is_array($unalloc_students)) {
        foreach ($unalloc_students as $i => $member) {
            echo '<tr class="no_group">';
            echo "<td>{$member['lastname']}, {$member['forename']} (";
            if (!empty($member['id_number'])) {
                echo $member['id_number'];
            } else {
                echo $member['username'];
            }
            echo ')</td>';
            echo "<td class=\"radio\"><input type=\"radio\" name=\"student_{$member['user_id']}\" id=\"{$member['user_id']}_in\" value=\"1\" /></td>";
            echo "<td class=\"radio\"><input type=\"radio\" name=\"student_{$member['user_id']}\" id=\"{$member['user_id']}_out\" value=\"0\" checked=\"checked\" /></td>";
            echo '</tr>';
        }
    } else {
        echo '<tr class="no_group"><td colspan="3">All the available students have been assigned</td></tr>';
    }

    // Show the students who are in OTHER groups in this collection
    if ((is_array($group_student_ids)) && (is_array($collection_member_ids))) {
        $collection_member_ids = array_diff($collection_member_ids, $group_student_ids);
    } else {
        $collection_member_ids = (array) $collection_member_ids;
    }
    $collection_students = $CIS->get_user($collection_member_ids);

    echo '<tr class="other_group"><th>Students assigned to other groups in this collection</th><th align="center" width="50">In</th><th align="center" width="50">Out</th></tr>';
    if (is_array($collection_students)) {
        foreach ($collection_students as $i => $member) {
            echo '<tr class="other_group">';
            echo "<td>{$member['lastname']}, {$member['forename']} (";
            if (!empty($member['id_number'])) {
                echo $member['id_number'];
            } else {
                echo $member['username'];
            }
            echo ')</td>';
            echo "<td class=\"radio\"><input type=\"radio\" name=\"student_{$member['user_id']}\" id=\"{$member['user_id']}_in\" value=\"1\" /></td>";
            echo "<td class=\"radio\"><input type=\"radio\" name=\"student_{$member['user_id']}\" id=\"{$member['user_id']}_out\" value=\"0\" checked=\"checked\" /></td>";
            echo '</tr>';
        }
    } else {
        echo '<tr class="other_group"><td colspan="3">There are no students allocated to any other groups</td></tr>';
    } ?>
      </table>

      </td>
      <td valign="top">
<?php
  if ($allow_edit) {
      ?>
        <div class="button_bar">
          <input type="button" name="savebutton1" id="savebutton1" value="<?php echo Generic::BTN__SAVE_CHANGES; ?>" onclick="do_command('save');" />
        </div>
<?php
  } ?>
      </td>
    </tr>
    <tr>
      <td valign="bottom">
<?php
  if ($allow_edit) {
      ?>
        <div class="button_bar">
          <input type="button" name="savebutton2" id="savebutton2" value="<?php echo Generic::BTN__SAVE_CHANGES; ?>" onclick="do_command('save');" />
        </div>
<?php
  } ?>
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
