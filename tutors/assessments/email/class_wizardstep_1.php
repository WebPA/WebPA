<?php

/**
 *
 * Class : WizardStep1  (Email students wizard)
 *
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
    $this->wizard->next_button = gettext('Next &gt;');
    $this->wizard->cancel_button = gettext('Cancel');
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
    $send_to = array  ('all'    => gettext('everyone taking this assessment') ,
               'groups' => gettext('selected groups taking this assessment') ,
               'have'   => gettext('students who HAVE responded') ,
               'havenot'  => gettext('students who HAVE NOT responded') ,);
?>
    <p><?php echo gettext('To begin, you need to select exactly which students should receive your email.');?></p>

    <h2><?php echo gettext('Send Email To');?></h2>
    <div class="form_section">
<?php
      render_radio_boxes($send_to, 'send_to', $this->wizard->get_field('send_to'));
?>
    </div>
<?php
  }// /->form()

  function process_form() {
    $errors = null;

    $this->wizard->set_field('send_to',fetch_POST('send_to'));
    if (is_empty($this->wizard->get_field('send_to'))) { $errors[] = gettext('You must select who to send this email to.'); }

    return $errors;
  }// /->process_form()

}// /class: WizardStep1

?>
