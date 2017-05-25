<?php

/**
 *
 * Class : WizardStep3  (add new criterion wizard)
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 *
 */

class WizardStep3 {

  // Public
  public $wizard = null;
  public $step = 3;

  /*
  * CONSTRUCTOR
  */
  function WizardStep3(&$wizard) {
    $this->wizard =& $wizard;

    $this->wizard->back_button = null;
    $this->wizard->next_button = null;
    $this->wizard->cancel_button = null;

    ob_start();
  }// /WizardStep3()

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
    $config =& $this->wizard->get_var('config');
    $DB =& $this->wizard->get_var('db');
    $user =& $this->wizard->get_var('user');
    $form =& $this->wizard->get_var('form');

    $range_start = $this->wizard->get_field('question_range_start');
    $range_end = $this->wizard->get_field('question_range_end');

    $new_question['text']['_data'] = $this->wizard->get_field('question_text');
    $new_question['desc']['_data'] = $this->wizard->get_field('question_desc');

    $new_question['range']['_data'] = "{$range_start}-{$range_end}";

    for($i=$range_start; $i<=$range_end; $i++) {
      $scorelabel = trim( fetch_POST("scorelabel{$i}") );
      if (!empty($scorelabel)) { $new_question["scorelabel{$i}"]['_data'] = $scorelabel; }
    }

    $errors = null;
    if (!$form) {
      $errors[] = gettext('Unable to load the form that this question is to be added to.');
    } else {
      $form->add_question($new_question);
      $form->save();
    }

    // If errors, show them
    if (is_array($errors)) {
      $this->wizard->back_button = gettext('&lt; Back');
      $this->wizard->cancel_button = gettext('Cancel');
      echo('<p><strong>'.gettext('Unable to create your new criterion.').'</strong></p>');
      echo('<p>'.gettext('To correct the problem, click <em>back</em> and amend the details entered.').'</p>');
    } else {// Else.. create the form!
      ob_end_clean();
      header('Location: '. APP__WWW ."/tutors/forms/edit/edit_form.php?f={$form->id}#questions");
      exit;
?>
      <p><strong><?php echo gettext('Your new assessment criterion has been created.');?></strong></p>
      <p style="margin-top: 20px;"><?php echo sprintf(gettext('You can now return to <a href="../edit_form.php?f=%s">editing your form</a>.'), $form->id);?></p>
<?php
    }
  }// /->form()

  function process_form() {
    $this->wizard->_fields = array(); // kill the wizard's stored fields
    return null;
  }// /->process_form()

}// /class: WizardStep3

?>
