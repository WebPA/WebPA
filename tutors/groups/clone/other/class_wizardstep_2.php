<?php
/**
 * 
 * Class : WizardStep2  (Create new groups wizard)
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 * 
 */
class WizardStep2 {

	// Public
	public $wizard = null;
	public $step = 2;

	
	/*
	* CONSTRUCTOR
	*/
	function WizardStep2(&$wizard) {
		$this->wizard =& $wizard;
	
		$this->wizard->back_button = '&lt; Back';
		$this->wizard->next_button = 'Next &gt;';
		$this->wizard->cancel_button = 'Cancel';
	}// /WizardStep2()


	function head() {
		$html = <<<HTMLEnd
<script language="JavaScript" type="text/javascript">
<!--

	function body_onload() {
	}// /body_onload()

//-->
</script>
HTMLEnd;

		echo($html);
	}// /->head()
	
	
	function form() {
		$CIS = $this->wizard->get_var('CIS');
		$config = $this->wizard->get_var('config');
		$user = $this->wizard->get_var('user');
		
		global $group_handler;
		$group_handler = $this->wizard->get_var('group_handler');

		// Get the user we're cloning from
		$clone_user = $CIS->get_staff(null, $this->wizard->get_field('username') );
		$clone_user_id = "staff_{$clone_user['staff_id']}";
	
		// Get which modules both the clone-user and current user share
		$clone_modules = array_extract_column( $CIS->get_staff_modules($clone_user['staff_id']), 'module_id');
		$user_modules = array_extract_column( $CIS->get_staff_modules($user->staff_id), 'module_id');
		$allowed_modules = array_intersect($user_modules, $clone_modules);

		// Get all the user's webpa collections
		require_once("../../../../library/classes/class_simple_object_iterator.php");
		
		$collections = $group_handler->get_user_collections($clone_user_id, $config['app_id']);
		$collection_iterator =& new SimpleObjectIterator($collections, 'GroupCollection', "&\$GLOBALS['group_handler']->_DAO");
		?>
		<p>You have chosen to clone groups from <em><?php echo("{$clone_user['forename']} {$clone_user['surname']}"); ?></em>.</p>

		<h2>Groups available for cloning</h2>

		
		<p>Below are the collections of groups that this user has created, and which you can clone. If you cannot see the groups you want to clone, make sure that you are both associated with the correct modules on LEARN.</p>
		<div class="form_section">
			<table cellpadding="2" cellspacing="2">
			<?php
			for($collection_iterator->reset(); $collection_iterator->is_valid(); $collection_iterator->next() ) {
				$collection = $collection_iterator->current();

				$group_count = count($collection->get_groups_array());
				$group_plural = ($group_count==1) ? 'group' : 'groups';
				$modules = $collection->get_modules();
				$str_modules = (is_array($collection->get_modules())) ? implode(', ',$collection->get_modules()) : 'none' ;

				$invalid_module = array_diff($modules, $allowed_modules);
				if (empty($invalid_module)) {
					?>
						<tr>
							<td valign="top"><input type="radio" name="collection_id" id="collection_<?php echo($collection->id); ?>" value="<?php echo($collection->id); ?>" /></td>
							<td valign="top"><div><label style="font-weight: normal;" for="collection_<?php echo($collection->id); ?>"><?php echo($collection->name); ?></label>
								<div style="margin-left: 20px; font-size: 0.9em;">
									Associated Modules: <?php echo($str_modules);?><br />
									Number of Groups: <?php echo("$group_count $group_plural");?>
								</div>
							</td>
						</tr>
					<?php
				}
			}
		?>
		</table>
		</div>
		<?php
	}// /->form()

	
	function process_form() {
		$errors = null;
		
		$this->wizard->set_field('collection_id',fetch_POST('collection_id'));
		if (is_empty($this->wizard->get_field('collection_id'))) { $errors[] = 'You must select a collection of groups to clone.'; }

		return $errors;
	}// /->process_form()

	
}// /class: WizardStep2


?>
