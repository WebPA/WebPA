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
		$module_select = $this->wizard->get_field('module_select');
		?>
		<p>Firstly, we need to give this new collection of groups a name. To avoid confusion, the name should be unique, but you can create collections using the same name if you wish.</p>
		<p>The name should be describe what the groups are for. For example, if the students are doing coursework for module 05ABC123, then name the collection, <em>05ABC123 - Coursework Groups</em>.</p>

		<table class="form" cellpadding="2" cellspacing="2">
		<tr>
			<th><label for="collection_name">Name for this new collection</label></th>
			<td><input type="text" name="collection_name" id="collection_name" maxlength="50" size="40" value="<?php echo( $this->wizard->get_field('collection_name') ); ?>" /></td>
		</tr>
		</table>

		<br />
		<p>Now choose how you will populate your new groups.</p>
		<p><label>I will populate my new groups with ...</label></p>
		<table class="form" cellpadding="2" cellspacing="2" style="margin-left: 30px;">
		<tr>
			<td><input type="radio" name="module_select" id="single_module" value="single" <?php echo( ( ($module_select=='single') || (empty($module_select)) ) ? 'checked="checked"' : '' ); ?> /></td>
			<td><label style="font-weight: normal;" for="single_module">students chosen from a single module.</label></td>
		</tr>
		<tr><td style="height: 10px;"></td></tr>
		<tr>
			<td><input type="radio" name="module_select" id="multiple_modules" value="multiple" <?php echo( ($module_select=='multiple') ? 'checked="checked"' : '' ); ?> /></td>
			<td><label style="font-weight: normal;" for="multiple_modules">a mixture of students from two or more modules.</label></td>
		</tr>
		</table>
		
		<br />
		<p>For research purposes, we are trying to study how academics construct their groups. Please indicate how your groups were allocated.</p>
		<p><label>The method I used to allocate students to these groups was:</label></p>
		<table cellpadding="0" cellspacing="0" style="line-height: 1.5em;">
		<tr>
			<td><input type="radio" name="creation_method" id="cm_random" value="random" <?php echo( ($this->wizard->get_field('creation_method')=='random') ? 'checked="checked"' : '' ); ?> /></td>
			<td><label style="font-weight: normal;" for="cm_random"><strong>Random</strong> : Students were allocated in a random manner.</label></td>
		</tr>
		<tr>
			<td><input type="radio" name="creation_method" id="cm_seeded" value="seeded" <?php echo( ($this->wizard->get_field('creation_method')=='seeded') ? 'checked="checked"' : '' ); ?> /></td>
			<td><label style="font-weight: normal;" for="cm_seeded"><strong>Seeding</strong> : Each group has at least one strong student (or a spread of abilities).</label></td>
		</tr>
		<tr>
			<td><input type="radio" name="creation_method" id="cm_self" value="self" <?php echo( ($this->wizard->get_field('creation_method')=='self') ? 'checked="checked"' : '' ); ?> /></td>
			<td><label style="font-weight: normal;" for="cm_self"><strong>Self-Selected</strong> : Students chose their own groups.</label></td>
		</tr>
		<tr>
			<td><input type="radio" name="creation_method" id="cm_other" value="other" <?php echo( ($this->wizard->get_field('creation_method')=='other') ? 'checked="checked"' : '' ); ?> /></td>
			<td><label style="font-weight: normal;" for="cm_other"><strong>Other</strong> : Please specify</label> <input type="text" name="creation_method_other" size="30" maxlength="240" value="<?php echo($this->wizard->get_field('creation_method_other')); ?>" /></td>
		</tr>
		</table>
		<?php
	}// /->form()

	
	function process_form() {
		$errors = null;
		
		$this->wizard->set_field('collection_name', fetch_POST('collection_name'));
		if (is_empty($this->wizard->get_field('collection_name'))) { $errors[] = 'You must provide a name for your new collection of groups'; }

		$this->wizard->set_field('module_select', fetch_POST('module_select'));
		if (is_empty($this->wizard->get_field('module_select'))) { $errors[] = 'You must select where students for these new groups will come from.'; }

		$this->wizard->set_field('creation_method', fetch_POST('creation_method'));
		if (is_empty($this->wizard->get_field('creation_method'))) { $errors[] = 'You must indicate how your groups were allocated.'; }
		
		$this->wizard->set_field('creation_method_other', fetch_POST('creation_method_other'));
		if ( ($this->wizard->get_field('creation_method')=='other') && (is_empty($this->wizard->get_field('creation_method_other'))) ) {
			$errors[] = 'If you chose "Other" for your creation method, then you should specify which method you used.';
		}
	
		
		return $errors;
	}// /->process_form()
	
}// /class: WizardStep1


?>
