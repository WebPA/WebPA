<?php
/**
 * Class : WizardStep1  (edit split100 criterion wizard)
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

use WebPA\includes\functions\Common;

class WizardStep1
{
    // Public
    public $wizard;

    public $step = 1;

    // CONSTRUCTOR
    public function __construct(&$wizard)
    {
        $this->wizard =& $wizard;

        $this->wizard->back_button = null;
        $this->wizard->next_button = 'Finish';
        $this->wizard->cancel_button = 'Cancel';
    }

    // /WizardStep1()

    public function head()
    {
        ?>
<script language="JavaScript" type="text/javascript">
<!--

	function body_onload() {
		document.getElementById('question_text').focus();
	}// /body_onload()

//-->
</script>
<?php
    }

    // /->head()

    public function form()
    {
        $config = $this->wizard->get_var('config');
        $form = $this->wizard->get_var('form');

        $question = $form->get_question($this->wizard->get_var('question_id'));

        if (!$this->wizard->get_field('set_original_data') && is_array($question)) {
            if (!$this->wizard->get_field('question_text')) {
                $this->wizard->set_field('question_text', $question['text']['_data']);
            }

            $question_desc = (array_key_exists('desc', $question)) ? $question['desc']['_data'] : '' ;

            if (!$this->wizard->get_field('question_desc')) {
                $this->wizard->set_field('question_desc', $question_desc);
            }

            $this->wizard->set_field('set_original_data', true);
        } ?>
		<p>Here you can edit the text and description of the criterion.</p>

		<div class="form_section">
			<table cellpadding="2" cellspacing="2" width="100%">
			<tr>
				<th width="100"><label for="question_text">Criterion Text</label></th>
				<td><input type="text" name="question_text" id="question_text" maxlength="255" size="50" value="<?php echo $this->wizard->get_field('question_text'); ?>" style="width: 90%;" /></td>
			</tr>
			<tr>
				<th valign="top" width="100"><label for="question_desc">Description</label><br /><span style="font-size: 0.8em; font-weight: normal;">(optional)</span></th>
				<td><textarea name="question_desc" id="question_desc" cols="60" rows="3" style="width: 90%;"><?php echo $this->wizard->get_field('question_desc'); ?></textarea></td>
			</tr>
			</table>
		</div>
		<?php
    }

    // /->form()

    public function process_form()
    {
        $errors = null;

        $this->wizard->set_field('question_text', Common::fetch_POST('question_text'));
        if (empty($this->wizard->get_field('question_text'))) {
            $errors[] = 'You must provide some text for your new criterion';
        }

        $this->wizard->set_field('question_desc', Common::fetch_POST('question_desc'));

        return $errors;
    }

    // /->process_form()
}// /class: WizardStep1


?>
