<?php
/**
 * Class : WizardStep2  (edit split100 criterion wizard)
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */
class WizardStep2
{
    // Public
    public $wizard;

    public $step = 2;

    // CONSTRUCTOR
    public function __construct(&$wizard)
    {
        $this->wizard =& $wizard;

        $this->wizard->back_button = null;
        $this->wizard->next_button = null;
        $this->wizard->cancel_button = null;
    }

    // /WizardStep2()

    public function head()
    {
        ?>
<script language="JavaScript" type="text/javascript">
<!--

	function body_onload() {
	}// /body_onload()

//-->
</script>
<?php
    }

    // /->head()

    public function form()
    {
        $form = $this->wizard->get_var('form');

        $new_question['text']['_data'] = $this->wizard->get_field('question_text');
        $new_question['desc']['_data'] = $this->wizard->get_field('question_desc');

        $errors = null;

        if (!$form) {
            $errors[] = 'Unable to load the form that this question belongs to.';
        } else {
            $form->set_question($this->wizard->get_var('question_id'), $new_question);
            $form->save();
        }

        // If errors, show them
        if (is_array($errors)) {
            $this->wizard->back_button = '&lt; Back';
            $this->wizard->cancel_button = 'Cancel';
            echo '<p><strong>Unable to create your new assessment criterion.</strong></p>';
            echo '<p>To correct the problem, click <em>back</em> and amend the details entered.</p>';
        } else {// Else.. create the form!
            ?>
			<p><strong>Your changes to this criterion have been saved.</strong></p>
			<p style="margin-top: 20px;">You can now return to <a href="/tutors/forms/edit/edit_form.php?f=<?php echo $form->id; ?>">editing your form</a>.</p>
			<?php
        }
    }

    // /->form()

    public function process_form()
    {
        $this->wizard->_fields = [];	// kill the wizard's stored fields
        return null;
    }

    // /->process_form()
}// /class: WizardStep2
