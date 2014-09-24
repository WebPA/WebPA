<?php
/**
 *
 * UI landing page for the review of information in the database
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 0.0.0.1
 * @since 21 May 2007
 *
 */

//get the include file required
require_once("../../includes/inc_global.php");


if (!check_user($_user, APP__USER_TYPE_TUTOR)) {
  header('Location:'. APP__WWW .'/logout.php?msg=denied');
  exit;
}

$filecontenttype = array();
$filecontenttype[] = array('link'=>'student','screen'=>gettext('Student'), 'def'=>gettext('View the student data currently in the system'),);
$filecontenttype[] = array('link'=>'staff','screen'=>gettext('Staff'), 'def'=>gettext('View the staff data currently in the system'),);
if (check_user($_user, APP__USER_TYPE_ADMIN)) {
  $filecontenttype[] = array('link'=>'admin','screen'=>gettext('Administrators'), 'def'=>gettext('View the administrator data currently in the system'),);
  $filecontenttype[] = array('link'=>'module','screen'=>gettext('Module'), 'def'=>gettext('View the module information currently in the system'),);
}
$filecontenttype[] = array('link'=>'../search', 'screen'=>gettext('Search'), 'def'=>gettext('Search for a student or staff user of the system'),);

//set the page information
$UI->page_title = APP__NAME .' '.gettext("view data");
$UI->menu_selected = gettext('view data');
$UI->breadcrumbs = array ('home' => null);
$UI->help_link = '?q=node/237';
$UI->set_page_bar_button(gettext('View Student Data'), '../../../images/buttons/button_student_user.png', 'student/index.php');
$UI->set_page_bar_button(gettext('View Staff Data'), '../../../images/buttons/button_staff_user.png', 'staff/index.php');
if (check_user($_user, APP__USER_TYPE_ADMIN)) {
  $UI->set_page_bar_button(gettext('View Admin Data'), '../../../images/buttons/button_admin_user.png', 'admin/index.php');
  $UI->set_page_bar_button(gettext('View Module Data'), '../../../images/buttons/button_view_modules.png', 'module/index.php');
}
$UI->set_page_bar_button(gettext('Search for a user'), '../../../images/buttons/button_search_user.png', '../search/index.php');
$UI->head();
$UI->body();
$UI->content_start();

?>
<div class="content_box">
<table class="option_list" style="width: 500px;">
<?php
  for($checkbox = 0; $checkbox<= count($filecontenttype)-1; $checkbox++){
?>
<tr>
  <td><a href="<?php echo $filecontenttype[$checkbox]['link']; ?>/index.php"><img src="../../images/icons/form.gif" width="32" height="32" alt="" /></a></td><td>
    <div class="option_list"> <div class="option_list_title"><a class="hidden" href="<?php echo $filecontenttype[$checkbox]['link']; ?>/index.php"><?php echo $filecontenttype[$checkbox]['screen']; ?></a></div>
      <p><?php echo $filecontenttype[$checkbox]['def']; ?></p>
    </div>
  </td>
</tr>
<?php
  }
?>
</table>
</div>
<?php

$UI->content_end();

?>
