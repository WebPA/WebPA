<?php
/**
 *
 * View module information
 *
 * This page allows the user to see the module information that is held in the database
 * The page includes the generic code page to out put the data.
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 0.0.0.2
 * @since 21 May 2007
 *
 */

//get the include file required
require_once("../../../includes/inc_global.php");

if (!check_user($_user, APP__USER_TYPE_ADMIN)){
  header('Location:'. APP__WWW .'/logout.php?msg=denied');
  exit;
}

$table = "module";
$type = "module";
$rstitle = gettext("Module Data");

//set the page information
$UI->page_title = APP__NAME .' '.gettext("view module data");
$UI->menu_selected = gettext('view data');
$UI->breadcrumbs = array ('home' => '../../',gettext('review data')=>'../', gettext('module information')=>null);
$UI->help_link = '?q=node/237';
$UI->set_page_bar_button(gettext('View Student Data'), '../../../../images/buttons/button_student_user.png', '../student/index.php');
$UI->set_page_bar_button(gettext('View Staff Data'), '../../../../images/buttons/button_staff_user.png', '../staff/index.php');
$UI->set_page_bar_button(gettext('View Admin Data'), '../../../../images/buttons/button_admin_user.png', '../admin/index.php');
$UI->set_page_bar_button(gettext('View Module Data'), '../../../../images/buttons/button_view_modules.png', '../module/index.php');
$UI->set_page_bar_button(gettext('Search for a user'), '../../../../images/buttons/button_search_user.png', '../../search/index.php');
$UI->head();
$UI->body();
$UI->content_start();

?>

<div class="content_box">
<?php include '../all.php'; ?>
</div>
<?php

$UI->content_end();

?>
