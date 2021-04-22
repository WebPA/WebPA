<?php

/**
 * Class : WizardStep2  (add new split100 criterion wizard)
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

        ob_start();
    }

    // /WizardStep3()

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
        $config =& $this->wizard->get_var('config');
        $DB =& $this->wizard->get_var('db');
        $user =& $this->wizard->get_var('user');
        $form =& $this->wizard->get_var('form');

        $new_question['text']['_data'] = $this->wizard->get_field('question_text');
        $new_question['desc']['_data'] = $this->wizard->get_field('question_desc');

        $errors = null;
        if (!$form) {
            $errors[] = 'Unable to load the form that this question is to be added to.';
        } else {
            $form->add_question($new_question);
            $form->save();
        }

        // If errors, show them
        if (is_array($errors)) {
            $this->wizard->back_button = '&lt; Back';
            $this->wizard->cancel_button = 'Cancel';
            echo '<p><strong>Unable to create your new criterion.</strong></p>';
            echo '<p>To correct the problem, click <em>back</em> and amend the details entered.</p>';
        } else {// Else.. create the form!
            ob_end_clean();
            header('Location: '. APP__WWW ."/tutors/forms/edit/edit_form.php?f={$form->id}#questions");
            exit; ?>
      <p><strong>Your new assessment criterion has been created.</strong></p>
      <p style="margin-top: 20px;">You can now return to <a href="../edit_form.php?f=<?php echo $form->id; ?>">editing your form</a>.</p>
      <?php
        }
    }

    // /->form()

    public function process_form()
    {
        $this->wizard->_fields = []; // kill the wizard's stored fields
        return null;
    }

    // /->process_form()
}// /class: WizardStep2

?>
