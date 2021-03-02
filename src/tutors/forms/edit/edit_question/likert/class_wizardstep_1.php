<?php
/**
 * Class : WizardStep1  (edit criterion wizard)
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

use WebPA\includes\functions\Common;
use WebPA\includes\functions\Form;

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
        $this->wizard->next_button = 'Next &gt;';
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
        $form = $this->wizard->get_var('form');

        $question = $form->get_question($this->wizard->get_var('question_id'));

        if ((!$this->wizard->get_field('set_original_data')) && (is_array($question))) {
            $range_bits = explode('-', $question['range']['_data']);
            $range_start = $range_bits[0];
            $range_end = $range_bits[1];

            if (!$this->wizard->get_field('question_text')) {
                $this->wizard->set_field('question_text', $question['text']['_data']);
            }

            $question_desc = (array_key_exists('desc', $question)) ? $question['desc']['_data'] : '' ;
            if (!$this->wizard->get_field('question_desc')) {
                $this->wizard->set_field('question_desc', $question_desc);
            }

            if (!$this->wizard->get_field('question_range_start')) {
                $this->wizard->set_field('question_range_start', $range_start);
            }
            if (!$this->wizard->get_field('question_range_end')) {
                $this->wizard->set_field('question_range_end', $range_end);
            }

            for ($i=$range_start; $i<=$range_end; $i++) {
                if (array_key_exists("scorelabel{$i}", $question)) {
                    $this->wizard->set_field("scorelabel{$i}", $question["scorelabel{$i}"]);
                }
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

		<p>Here you can select the range of scores you will allow for this assessment criterion.</p>
		<div class="form_section">
			<table class="form" cellpadding="2" cellspacing="2">
			<tr>
				<th><label for="question_range_start">Scores range from</label></th>
				<td>
					<select name="question_range_start" id="question_range_start">
						<?php Form::render_options_range(0, 1, 1, (int) $this->wizard->get_field('question_range_start')); ?>
					</select>
				</td>
				<th><label>to</label></th>
				<td>
					<select name="question_range_end" id="question_range_end">
						<?php Form::render_options_range(3, 10, 1, (int) $this->wizard->get_field('question_range_end')); ?>
					</select>
				</td>
			</tr>
			</table>
		</div>
		<p><strong>Please Note</strong> - allowing 0 scores means students can receive no marks if they failed to contribute at all.</p>
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

        $this->wizard->set_field('question_range_start', Common::fetch_POST('question_range_start'));
        $this->wizard->set_field('question_range_end', Common::fetch_POST('question_range_end'));

        return $errors;
    }

    // /->process_form()
}// /class: WizardStep1


?>
