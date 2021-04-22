<?php
/**
 * Groups Index - List the user's collections
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../includes/inc_global.php';

use WebPA\includes\classes\GroupHandler;
use WebPA\includes\classes\SimpleObjectIterator;
use WebPA\includes\functions\Common;
use WebPA\lang\en\Generic;
use WebPA\lang\en\tutors\Tutors;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

// --------------------------------------------------------------------------------

$group_handler = new GroupHandler();
$collections = $group_handler->get_module_collections($_module_id);

// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME. ' ' . Generic::MY__GROUPS;;
$UI->menu_selected = Generic::MY__GROUPS;
$UI->help_link = '?q=node/253';
$UI->breadcrumbs = [
  'home'        => '../../',
  'my groups'   => '../',
];

$UI->set_page_bar_button(Generic::BTN__LIST_GROUPS, '../../../images/buttons/button_group_list.gif', '');
$UI->set_page_bar_button(Generic::BTN__CREATE_GROUPS, '../../../images/buttons/button_group_create.gif', 'create/');
$UI->set_page_bar_button(Generic::BTN__CLONE_GROUPS, '../../../images/buttons/button_group_clone.gif', 'clone/');

$UI->head();
$UI->content_start();
?>

<p><?php echo Tutors::GROUPS__WELCOME; ?></p>

<div class="content_box">

<h2><?php echo Tutors::GROUPS__TITLE; ?></h2>
<div class="form_section">
<?php
if (!$collections) {
    echo '<p>' . Tutors::NO__GROUPS__DESC . '</p>';
} else {
    $collection_iterator = new SimpleObjectIterator($collections, 'GroupCollection', $DB); ?>
    <p><?php echo Tutors::GROUPS__INSTRUCT__1; ?><img src="../../images/buttons/edit.gif" width="16" height="16" alt="<?php echo Generic::EDIT_QUESTION; ?>" title="edit" /> <?php echo Tutors::GROUPS__INSTRUCT__2; ?></p>
    <div class="info_box">
      <p><?php echo Generic::PLEASE__NOTE; ?></p>
      <p><?php echo Tutors::GROUPS__NOTE; ?></p>
    </div>
    <div class="obj_list">
<?php
  for ($collection_iterator->reset(); $collection_iterator->is_valid(); $collection_iterator->next()) {
      $collection = $collection_iterator->current();

      $group_count = count($collection->get_groups_array());

      $edit_url = "edit/edit_collection.php?c={$collection->id}"; ?>
        <div class="obj">
          <table class="obj" cellpadding="2" cellspacing="2">
          <tr>
            <td class="obj_icon" width="24"><a class="text" href="<?php echo $edit_url; ?>"><img src="../../images/icons/groups.gif" alt="<?php Generic::GROUPS; ?>" height="24" width="24" /></a></td>
            <td class="obj_info">
              <div class="obj_name"><a class="text" href="<?php echo $edit_url; ?>"><?php echo $collection->name; ?></a></div>
              <div class="obj_info_text"><?php echo Generic::NO__GROUPS; ?> : <?php echo $group_count; ?></div>
            </td>
            <td class="buttons">
              <a href="<?php echo $edit_url; ?>"><img src="../../images/buttons/edit.gif" width="16" height="16" alt="<?php echo Generic::EDIT__GROUP; ?>" title="edit" /></a>
              <a href="<?php echo $edit_url; ?>&command=delete" onclick="return confirm('This collection will be deleted.\n\nClick OK to confirm.');"><img src="../../images/buttons/cross.gif" width="16" height="16" alt="<?php echo Generic::DELETE__GROUP; ?>" title="delete" /></a>
            </td>
          </tr>
          </table>
        </div>
<?php
  } ?>
    </div>
<?php
}
?>
</div>

</div>

<?php

$UI->content_end();
