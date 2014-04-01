<?php
/**
 * 
 * Groups Index - List the user's collections
 *
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 * 
 */
require_once("../../../include/inc_global.php");

if (!check_user($_user, 'staff')){
	header('Location:'. APP__WWW .'/logout.php?msg=denied');
	exit;
}

// --------------------------------------------------------------------------------


// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME .' clone existing groups';
$UI->menu_selected = 'my groups';
$UI->help_link = '?q=node/253';
$UI->breadcrumbs = array	('home' 		=> '/' ,
							 'my groups'	=> '/tutors/groups/' ,
							 'clone groups'	=> null ,
							);
													
$UI->set_page_bar_button('List Groups', '../../../../images/buttons/button_group_list.gif', '../');
$UI->set_page_bar_button('Create Groups', '../../../../images/buttons/button_group_create.gif', '../create/');
$UI->set_page_bar_button('Clone Groups', '../../../../images/buttons/button_group_clone.gif', '../clone/');


$UI->head();
$UI->content_start();
?>

<p>Here you can clone groups, or copy those created by other staff members.</p>

<div class="content_box">

<p>Select the type of groups you want to clone.</p>

<table class="option_list" style="width: 500px;">
<tr>
	<td><a href="own/"><img src="../../../images/icons/groups_clone2.gif" width="32" height="32" alt="clone" /></a></td>
	<td>
		<div class="option_list">
			<div class="option_list_title"><a class="hidden" href="own/">Clone my own groups</a></div>
			<p>Clone a collection of groups you have already created. Once cloned, you can change the student groupings as much as you like while leaving the original groups untouched.</p>
		</div>
	</td>
</tr>
</table>

</div>

<?php
$UI->content_end();
?>