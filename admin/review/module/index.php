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
 require_once("../../../include/inc_global.php");
 
if (!check_user($_user, 'staff')){
	header('Location:'. APP__WWW .'/logout.php?msg=denied');
	exit;
}
 
 $table = "module";
 $type = "Module";
 $rstitle = "Module Data";
 
 
  //set the page information
$UI->page_title = APP__NAME . " view module data";
$UI->menu_selected = 'view data';
$UI->breadcrumbs = array ('home' => '../../','review data'=>'../', ' module information'=>null);
$UI->help_link = '?q=node/237';
$UI->set_page_bar_button('View Student Data', '../../../../images/buttons/button_student_user.png', '../student/index.php');
$UI->set_page_bar_button('View Staff Data', '../../../../images/buttons/button_staff_user.png', '../staff/index.php');
$UI->set_page_bar_button('View Module Data', '../../../../images/buttons/button_view_modules.png', '../module/index.php');
$UI->set_page_bar_button('Search for a user', '../../../../images/buttons/button_search_user.png', '../../search/index.php');
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