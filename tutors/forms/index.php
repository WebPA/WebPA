<?php
/**
 * 
 * Index : forms
 * 			
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.2
 * 
 */

require_once('../../include/inc_global.php');
require_once(DOC__ROOT . '/include/classes/class_form.php');

if (!check_user($_user, 'staff')){
	header('Location:'. APP__WWW .'/logout.php?msg=denied');
	exit;
}

$generic_form = $DB->fetch("SELECT *
					FROM form
					WHERE form_owner_id='0'
					ORDER BY form_name ASC");

$forms = $DB->fetch("SELECT *
					FROM form
					WHERE form_owner_id='{$_user->id}'
					ORDER BY form_name ASC");


// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME . ' my forms';
$UI->menu_selected = 'my forms';
$UI->help_link = '?q=node/244';
$UI->breadcrumbs = array	(
	'home'      => '../' ,
	'my forms'  => null ,
);

$UI->set_page_bar_button('List Forms', '../../../images/buttons/button_form_list.gif', '');
$UI->set_page_bar_button('Create a new Form', '../../../images/buttons/button_form_create.gif', 'create/');
$UI->set_page_bar_button('Clone a Form', '../../../images/buttons/button_form_clone.gif', 'clone/');
$UI->set_page_bar_button('Import a Form', '../../../images/buttons/button_form_import.gif', 'import/');

$UI->head();
$UI->body();

$UI->content_start();
?>
	<p>Please select from the following options:</p>

	<div class="content_box">
		<h2>Existing forms</h2>
		<div class="form_section">
			<?php
			if (!$forms) {
				?>
				<p>You do not have any assessment forms at the moment. Please <a href="create/">create a new form</a>.</p>
				<?php
			} else {
				?>
				<p>These are the forms you have already created. To edit a form, click on <img src="../../images/buttons/edit.gif" width="16" height="16" alt="edit form" title="edit" /> in the list below.</p>

				<div class="obj_list">
				
				
				<?php
				// @pmn - check to see if there are generic forms
				if ($generic_form) {
					?>
					<h3>generic / example form</h3>
  
				<?php
				//out put the generic form
				foreach ($generic_form as $i => $form) {
					$edit_url = "clone/clone_example.php?f={$form['form_id']}";
					
					?>
					<div class="obj">
						<table class="obj" cellpadding="2" cellspacing="2">
						<tr>
							<td class="icon" width="24"><a href="<?php echo($edit_url); ?>"><img src="../../images/icons/form.gif" width="24" height="24" alt="Form" /></a></td>
							<td class="obj_info">
								<div class="obj_name"><a class="text" href="<?php echo($edit_url); ?>"><?php echo($form['form_name']); ?></a></div>
							</td>
							<td class="button" width="24"><a href="<?php echo($edit_url); ?>"><img src="../../images/buttons/edit.gif" width="16" height="16" alt="edit form" title="edit" /></a></td>
						</tr>
						</table>
					</div>
					<?php
				} // @pmn -  /if (generic forms)
				?>
  <h3>your forms</h3>
  
				<?php
				}
				//out put the form that the user owns
				foreach ($forms as $i => $form) {
					$edit_url = "edit/edit_form.php?f={$form['form_id']}";
					$export_url = "export/export_form.php?f={$form['form_id']}";
					?>
					<div class="obj">
						<table class="obj" cellpadding="2" cellspacing="2">
						<tr>
							<td class="icon" width="24"><a href="<?php echo($edit_url); ?>"><img src="../../images/icons/form.gif" width="24" height="24" alt="Form" /></a></td>
							<td class="obj_info">
								<div class="obj_name"><a class="text" href="<?php echo($edit_url); ?>"><?php echo($form['form_name']); ?></a></div>
							</td>
							<td class="button" width="24"><a href="<?php echo($export_url); ?>"><img src="../../images/file_icons/package_go.png" width="16" height="16" alt="export form" title="export" /></a></td>
							<td class="button" width="24"><a href="<?php echo($edit_url); ?>"><img src="../../images/buttons/edit.gif" width="16" height="16" alt="edit form" title="edit" /></a></td>
						</tr>
						</table>
					</div>
					<?php
				}
				?>
				</div>
				<?php
			}
			?>
		</div>
	</div>

<?php
$UI->content_end();
?>