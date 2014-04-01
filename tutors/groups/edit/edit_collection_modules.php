<?php
/**
 * 
 * Edit Collection modules
 * 			
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 * 
 */
require_once("../../../include/inc_global.php");
require_once(DOC__ROOT . '/library/classes/class_group_handler.php');
require_once(DOC__ROOT . '/library/functions/lib_form_functions.php');

if (!check_user($_user, 'staff')){
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
$collection = $group_handler->get_collection($collection_id);

$allow_edit = false;

if ($collection) {
	// Check if the user can edit this collection
	$allow_edit = ($collection->is_owner($_user->id, $_config['app_id']));
}


// --------------------------------------------------------------------------------
// Process Form

$errors = null;

if ($allow_edit) {
	switch ($command) {
		case 'save':
					// Delete all the modules from this collection
					$new_modules = null;

					// Get the modules that should be attached to this collection
					foreach ($_POST as $k => $v) {
						$s = strpos($k,'module_');
						if ( ($s !== false) && ($v==1) ) {
							$new_modules[] = substr($k,7,15);
						}
					}

					$current_students = array_keys( (array) $collection->get_members('member') );
					// if we're changing the modules, remove any orphaned students
					if (is_array($new_modules)) {
						$allowed_students = (array) $CIS->get_module_students_user_id($new_modules);
						$students_to_remove = array_diff( $current_students, $allowed_students);
					} else { // else, no modules at all, so remove all students
						$students_to_remove =& $current_students;
					}
					
					if (is_array($students_to_remove)) {
						$collection->remove_member($students_to_remove, 'member');
					}
					
					$collection->set_modules($new_modules);
					$collection->save();
					break;
		// --------------------
	}// /switch
}


// --------------------------------------------------------------------------------
// Begin Page

$collection_name = ($collection) ? $collection->name : 'Unknown Collection';
$collection_title = "Editing: $collection_name";
$page_title = ($collection) ? "Modules: {$collection->name}" : 'Modules';


$UI->page_title = APP__NAME . ' ' . $page_title;
$UI->menu_selected = 'my groups';
$UI->help_link = '?q=node/253';
$UI->breadcrumbs = array	(
	'home' 						=> '../../' ,
	'my groups'					=> '../' ,
	"Editing: $collection_name"	=> "../edit/edit_collection.php?c={$collection->id}" ,
	$page_title					=> null ,
);

$UI->set_page_bar_button('List Groups', '../../../../images/buttons/button_group_list.gif', '../');
$UI->set_page_bar_button('Create Groups', '../../../../images/buttons/button_group_create.gif', '../create/');
$UI->set_page_bar_button('Clone Groups', '../../../../images/buttons/button_group_clone.gif', '../clone/');
													
$UI->head();
?>
<style type="text/css">
<!--

td.radio { text-align: center; }

tr.in_collection td { background-color: #beb; }
tr.in_collection th { background-color: #9c9; font-weight: bold; }

tr.out_collection td {  }
tr.out_collection th { font-weight: bold; }

-->
</style>
<script language="JavaScript" type="text/javascript">
<!--

	function do_command(com) {
		document.collection_modules_form.command.value = com;
		document.collection_modules_form.submit();
	}// /do_command()

//-->
</script>
<?php
$UI->content_start();
?>

<div class="content_box">

<div class="nav_button_bar">
	<a href="<?php echo($collection_url); ?>"><img src="../../../images/buttons/arrow_green_left.gif" alt="back -"> back to <?php echo($collection_name); ?></a>
</div>

<?php
if (!$collection) {
	?>

	<p>The collection you selected could not be loaded for some reason - please go back and try again.</p>
	<?php
} else {
	$collection_qs = "c={$collection->id}";
	?>

	<form action="edit_collection_modules.php?<?php echo($collection_qs); ?>" method="post" name="collection_modules_form">
	<input type="hidden" name="command" value="none" />
	
	<h2>Available Modules</h2>
	<div class="form_section">
		<p>Below are all the modules available for you to use.</p>
		<p>To add a module to this collection, select <em>In</em>, to remove one, select <em>Out</em>. If you remove a module, all students associated with that module will also be removed.</p>
		<p>When you have made all your selections, click a <em>save changes</em> button.</p>
		
		<table cellpadding="0" cellspacing="0">
		<tr>
			<td rowspan="2" valign="top">

			<table class="grid" cellpadding="2" cellspacing="1">
			<?php
				// Show all the modules attached to this collection
				$collection_module_ids = (array) $collection->get_modules();
				$collection_modules = $CIS->get_module($collection_module_ids);
				
				echo('<tr class="in_collection"><th>Modules already attached to this collection</th><th align="center" width="50">In</th><th align="center" width="50">Out</th></tr>');
				if (is_array($collection_modules)) {
					foreach($collection_modules as $i => $module) {
						echo('<tr class="in_collection">');
						echo("<td>{$module['module_id']} - {$module['module_title']}</td>");
						echo("<td class=\"radio\"><input type=\"radio\" name=\"module_{$module['module_id']}\" id=\"module_{$module['module_id']}_in\" value=\"1\" checked=\"checked\" /></td>");
						echo("<td class=\"radio\"><input type=\"radio\" name=\"module_{$module['module_id']}\" id=\"module_{$module['module_id']}_out\" value=\"0\" /></td>");
						echo('</tr>');
					} 
				} else {
					echo('<tr class="in_collection"><td colspan="3">This collection is not associated with any modules</td></tr>');
				}
				
				// Show all the other possible modules
				$modules = $CIS->get_staff_modules($_user->staff_id);

				echo('<tr class="out_collection"><th width="450">Modules not attached to this collection</th><th align="center" width="50">In</th><th align="center" width="50">Out</th></tr>');
				if (is_array($modules)) {
					foreach($modules as $i => $module) {
						if (!in_array($module['module_id'], $collection_module_ids)) {
							echo('<tr class="out_collection">');
							echo("<td>{$module['module_id']} - {$module['module_title']}</td>");
							echo("<td class=\"radio\"><input type=\"radio\" name=\"module_{$module['module_id']}\" id=\"module_{$module['module_id']}_in\" value=\"1\" /></td>");
							echo("<td class=\"radio\"><input type=\"radio\" name=\"module_{$module['module_id']}\" id=\"module_{$module['module_id']}_out\" value=\"0\" checked=\"checked\" /></td>");
							echo('</tr>');
						}
					} 
				} else {
					echo('<tr class="out_collection"><td colspan="3">There are no other modules available to use</td></tr>');
				}			?>
			</table>
	
			</td>
			<td valign="top">
				<?php if ( ($allow_edit) && (count($modules)>5) ) { ?>
				<div class="button_bar">
					<input type="button" name="savebutton1" id="savebutton1" value="save changes" onclick="do_command('save');" />
				</div>
				<?php } ?>
			</td>
		</tr>
		<tr>
			<td valign="bottom">
				<?php if ($allow_edit) { ?>
				<div class="button_bar">
					<input type="button" name="savebutton2" id="savebutton2" value="save changes" onclick="do_command('save');" />
				</div>
				<?php } ?>
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