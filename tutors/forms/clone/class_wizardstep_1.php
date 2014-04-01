<?php
/**
 * 
 * Class : WizardStep1  (Clone a form wizard)
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
		?>
<script language="JavaScript" type="text/javascript">
<!--

	function body_onload() {
	}// /body_onload()

//-->
</script>
<?php
	}// /->head()
	
	
	function form() {
		$DB =& $this->wizard->get_var('db');
		$user =& $this->wizard->get_var('user');

		$form_id = $this->wizard->get_field('form_id');
		
		$forms = $DB->fetch(	"SELECT *
								FROM form
								WHERE form_owner_id='{$user->id}'
								ORDER BY form_name ASC");

		if (!$forms) {
			$this->wizard->next_button = null;
			?>
			<p>You have not created any forms yet, so you cannot select one to clone.</p>
			<p>Please <a href="../create/">create a new form</a> instead.</p>
			<?php
		} else {
			?>
  		<p>To create a clone you must first select which assessment form you wish to copy. Please choose one from the list below.</p>
  
  		<h2>Choose a form to clone</h2>
  		<div class="form_section">
  			<table class="form" cellpadding="2" cellspacing="2">
  			<?php

  			foreach($forms as $i => $form) {
					$checked_str = ($form['form_id']==$form_id) ? 'checked="checked"' : '' ;
  				?>
  				<tr>
  					<td><input type="radio" name="form_id" id="form_id_<?php echo($form['form_id']); ?>"  value="<?php echo($form['form_id']); ?>" <?php echo($checked_str); ?>/></td>
  					<th style="text-align: left"><label class="small" for="form_id_<?php echo($form['form_id']); ?>"><?php echo($form['form_name']); ?></label></th>
  				</tr>
  				<?php
  			}
  			?>
  			</table>
  		</div>
  
  		<?php
		}
	}// /->form()

	
	function process_form() {
		$errors = null;
		
		$this->wizard->set_field('form_id',fetch_POST('form_id'));
		if (is_empty($this->wizard->get_field('form_id'))) { $errors[] = 'You must select which assessment form you wish to clone.'; }

		return $errors;
	}// /->process_form()
	
}// /class: WizardStep1


?>
