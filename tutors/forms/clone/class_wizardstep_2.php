<?php
/**
 *
 * Class : WizardStep2  (Clone a form wizard)
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 *
 */

class WizardStep2 {

  // Public
  public $wizard = null;
  public $step = 2;

  /*
  * CONSTRUCTOR
  */
  function WizardStep2(&$wizard) {
    $this->wizard =& $wizard;

    $this->wizard->back_button = gettext('&lt; Back');
    $this->wizard->next_button = gettext('Finish');
    $this->wizard->cancel_button = gettext('Cancel');
  }// /WizardStep2()

  function head() {
    ?>
<script language="JavaScript" type="text/javascript">
<!--

  function body_onload() {
    document.wizard_form.clone_form_name.focus();
  }// /body_onload()

//-->
</script>
<?php
  }// /->head()

  function form() {
    $DB =& $this->wizard->get_var('db');
    $user =& $this->wizard->get_var('user');

    $form = new Form($DB);
    $form->load($this->wizard->get_field('form_id'));

    if (!$form) {
      $this->wizard->next_button = null;
      ?>
      <p><?php echo gettext('The select form could not be loaded, please click <em>Back</em> and try again.');?></p>
      <?php
    } else {
      ?>
      <p><?php echo gettext('You have chosen to clone:');?> <em><?php echo($form->name); ?></em></p>

      <p><?php echo gettext('Now enter a name for your new form.');?></p>

      <div class="form_section">
        <table class="form" cellpadding="2" cellspacing="2">
          <tr>
            <th><label for="clone_form_name"><?php echo gettext('Name for new form');?></label></th>
            <td><input type="text" name="clone_form_name" id="clone_form_name" size="50" maxlength="100" value="<?php echo($this->wizard->get_field('clone_form_name')); ?>" /></td>
          </tr>
        </table>
      </div>

      <?php
    }
  }// /->form()

  function process_form() {
    $errors = null;

    $this->wizard->set_field('clone_form_name',fetch_POST('clone_form_name'));
    if (is_empty($this->wizard->get_field('clone_form_name'))) { $errors[] = gettext('You must enter a name for your new assessment form.'); }

    return $errors;
  }// /->process_form()

}// /class: WizardStep2

?>
