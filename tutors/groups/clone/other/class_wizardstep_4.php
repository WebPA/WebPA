<?php
/**
 * 
 * Class : WizardStep4  (Create new groups wizard)
 *
 * 			
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 * 
 */

class WizardStep4 {

	// Public
	public $wizard = null;
	public $step = 4;
	

	/*
	* CONSTRUCTOR
	*/
	function WizardStep4(&$wizard) {
		$this->wizard =& $wizard;
	
		$this->wizard->back_button = '&lt; Back';
		$this->wizard->next_button = 'Finish';
		$this->wizard->cancel_button = 'Cancel';
	}// /WizardStep4()


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
		$arr_module_id = $this->wizard->get_field('module_id');	// always an array, even if there's only 1

		$total_students = $CIS->get_module_students_count($arr_module_id);
		$students_plural = ($total_students==1) ? 'student' : 'students';
		
		$modules = $CIS->get_module($arr_module_id);
		?>
		<p>Please confirm the following settings are correct before proceeding.</p>

		<h2>Name</h2>
		<div style="margin: 0px 0px 16px 25px;">This collection of groups will be called: <?php echo($this->wizard->get_field('collection_name')); ?></div>
		
		<h2>Selected Modules</h2>
		<div style="margin: 0px 0px 16px 25px;">
		<?php
		foreach($modules as $i => $module) {
			echo("<div>{$module['module_id']} : {$module['module_title']}</div>");
		}
		?>
		</div>
		
		<h2>Students</h2>
		<div style="margin: 0px 0px 16px 25px;">
		<p><?php  echo("$total_students $students_plural"); ?> available.</p>
		</div>
		
		<h2>Groups</h2>
		<?php
		$num_groups = (int) $this->wizard->get_field('num_groups');
		if ($num_groups>0) {
			?>
			<div style="margin: 0px 0px 16px 25px;">
				<p>The following groups will be created in the new <strong><?php echo($this->wizard->get_field('groupset_name')); ?></strong> collection:</p>
				<div style="margin-left: 25px;">
				<?php
					$num_groups = (int) $this->wizard->get_field('num_groups');
					$group_names = GroupHandler::generate_group_names(	$num_groups, $this->wizard->get_field('group_name_stub'), $this->wizard->get_field('group_numbering') );
		
					if ($num_groups<=5) {
						foreach($group_names as $group_name) {
							echo("<div>$group_name</div>");
						}
					} else {
						echo("<div>{$group_names[0]}</div>");
						echo("<div>{$group_names[1]}</div>");
						echo("<div>&nbsp; ...</div>");
						echo('<div>'. $group_names[$num_groups-2] .'</div>');
						echo('<div>'. $group_names[$num_groups-1] .'</div>');
					}
				?>
				</div>
			</div>
			<?php
		} else {
			?>
			<div style="margin: 0px 0px 16px 25px;">You have chosen to not create any groups at this time.</div>
			<?php
		}
		?>

		<br />
		<p>If you wish to amend any details, click <em>back</em>. When you are ready to create your groups, click <em>Finish</em>.</p>		
		<?php
	}// /->form()
	
	function process_form() {
		$errors = null;
		return $errors;
	}// /->process_form()
	
}// /class: WizardStep4


?>
