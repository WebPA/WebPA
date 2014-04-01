<?php

/**
 * 
 * Class : WizardStep1  (Create new groups wizard)
 * 			
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 * 
 */
class WizardStep1 {

	// Public
	public $wizard = null;
	public $step = 1;


	/*
	* CONSTRUCTOR
	*/
	function WizardStep1(&$wizard) {
		$this->wizard =& $wizard;
	
		$this->wizard->back_button = null;
		$this->wizard->next_button = 'Next &gt;';
		$this->wizard->cancel_button = 'Cancel';
	}// /WizardStep1()


	function head() {
		$html = <<<HTMLEnd
<script language="JavaScript" type="text/javascript">
<!--

	function body_onload() {
		document.getElementById('collection_name').focus();
	}// /body_onload()

//-->
</script>
HTMLEnd;

		echo($html);
	}// /->head()
	
	
	function form() {
		?>
		<p>Firstly, you must choose who to clone the groups from. You will only be able to clone groups if they are associated with modules that you also have access to.</p>

		<h2>Person to clone from</h2>
		<p>Please enter the username of the person you wish to clone groups from.</p>
		<div class="form_section">
			<table class="form" cellpadding="1" cellspacing="1">
			<tr>
				<th><label for="username">LEARN Username</label></th>
				<td><input type="text" name="username" id="username" size="10" maxlength="10" value="<?php echo($this->wizard->get_field('username'));?>" /></td>
			</tr>
			</table>
		</div>
		<?php
	}// /->form()
	
	function process_form() {
		$errors = null;
		
		$this->wizard->set_field('username',fetch_POST('username'));
		if (is_empty($this->wizard->get_field('username'))) { $errors[] = 'You must enter the username of the person to clone groups from.'; }
		
		return $errors;
	}// /->process_form()
	
}// /class: WizardStep1


?>
