<?php
/**
 * 
 * Class : WizardStep5  (Create new groups wizard)
 * 			
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 * 
 */

class WizardStep5 {

	// Public
	public $wizard = null;
	public $step = 5;
	

	/*
	* CONSTRUCTOR
	*/
	function WizardStep5(&$wizard) {
		$this->wizard =& $wizard;
	
		$this->wizard->back_button = null;
		$this->wizard->next_button = null;
		$this->wizard->cancel_button = null;
	}// /WizardStep5()


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
		$db = $this->wizard->get_var('db');
		$user = $this->wizard->get_var('user');
		$config = $this->wizard->get_var('config');
		$arr_module_id = $this->wizard->get_field('module_id');	// always an array, even if there's only 1

		$group_handler = new GroupHandler();
		
		// Run a load of checks to see if we can create these new groups!
		$errors = null;
		
		// If the staff isn't allocated some of the modules given (hack-attempt), show error
		if (!$CIS->staff_has_module($user->staff_id, $arr_module_id)) { $errors[] = 'At least one of the modules you selected is not available to you. You cannot create groups for modules you are not associated with'; }

		// Generate the names of the new groups
		$num_groups = (int) $this->wizard->get_field('num_groups');
		if ($num_groups>0) {
			$group_names = $group_handler->generate_group_names($num_groups, $this->wizard->get_field('group_name_stub'), $this->wizard->get_field('group_numbering'));
		}

		$collection =& $group_handler->create_collection();
		$collection->name = $this->wizard->get_field('collection_name');
		$collection->set_owner_info($user->id, $config['app_id'], 'user');
		$collection->set_modules($arr_module_id);

		// If errors, show them
		if (is_array($errors)) {
			$this->wizard->back_button = '&lt; Back';
			$this->wizard->cancel_button = 'Cancel';
			echo('<p><strong>Unable to create your new collection of groups.</strong></p>');
			echo('<p>To correct the problem, click <em>back</em> and amend the details entered.</p>');
		} else {// Else.. create the groups!
			if ($collection->save()) {
				if ($num_groups>0) {
					foreach($group_names as $group_name) {
						$new_group = $collection->new_group($group_name);
						$new_group->save();
					}
				}
				
				
				$fields = array (
					'collection_id'    => $collection->id ,
					'creation_dt'      => date('Y-m-d H:i:s', mktime() ) ,
					'username'         => $user->username ,
					'user_id'          => $user->id ,
					'creation_method'  => $this->wizard->get_field('creation_method') ,
					'other_text'       => $this->wizard->get_field('creation_method_other') ,
				);
				
				$db->do_insert("REPLACE INTO collection_creation_method ({fields}) VALUES ({values}) ", $fields);
			} else {
				echo('<p><strong>An error occurred while trying to create your new collection of groups.</strong></p>');
				echo('<p>You may be able to correct the problem by clicking <em>back</em>, and then <em>next</em> again.</p>');
			}

			?>
			<p><strong>Your new groups have been created.</strong></p>
			<p style="margin-top: 20px;">To allocate students to your new groups, you can use the <a href="../edit/edit_collection.php?c=<?php echo($collection->id); ?>">group editor</a>.</p>
			<p style="margin-top: 20px;">Alternatively, you can return to <a href="../">my groups</a>, or to the <a href="../../index.php">Web-PA home page</a>.</p>
			<?php	
		}
	}// /->form()
	
	function process_form() {
		$this->wizard->_fields = array();	// kill the wizard's stored fields
		return null;
	}// /->process_form()
	
}// /class: WizardStep5


?>
