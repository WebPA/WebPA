<?php
/**
 * Class : WizardStep2  (Clone a form wizard)
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

use WebPA\includes\classes\Form;
use WebPA\includes\classes\Wizard;
use WebPA\includes\functions\Common;

class WizardStep2
{
    public $wizard;

    public $step = 2;

    // CONSTRUCTOR
    public function __construct(Wizard $wizard)
    {
        $this->wizard = $wizard;

        $this->wizard->back_button = '&lt; Back';
        $this->wizard->next_button = 'Finish';
        $this->wizard->cancel_button = 'Cancel';
    }

    // /WizardStep2()

    public function head()
    {
        ?>
<script language="JavaScript" type="text/javascript">
<!--

  function body_onload() {
    document.wizard_form.clone_form_name.focus();
  }// /body_onload()

//-->
</script>
<?php
    }

    // /->head()

    public function form()
    {
        $DB = $this->wizard->get_var('db');

        $form = new Form($DB);
        $form->load($this->wizard->get_field('form_id'));

        if (!$form) {
            $this->wizard->next_button = null; ?>
      <p>The select form could not be loaded, please click <em>Back</em> and try again.</p>
      <?php
        } else {
            ?>
      <p>You have chosen to clone: <em><?php echo $form->name; ?></em></p>

      <p>Now enter a name for your new form.</p>

      <div class="form_section">
        <table class="form" cellpadding="2" cellspacing="2">
          <tr>
            <th><label for="clone_form_name">Name for new form</label></th>
            <td><input type="text" name="clone_form_name" id="clone_form_name" size="50" maxlength="100" value="<?php echo $this->wizard->get_field('clone_form_name'); ?>" /></td>
          </tr>
        </table>
      </div>

      <?php
        }
    }

    // /->form()

    public function process_form()
    {
        $errors = null;

        $this->wizard->set_field('clone_form_name', Common::fetch_POST('clone_form_name'));
        if (empty($this->wizard->get_field('clone_form_name'))) {
            $errors[] = 'You must enter a name for your new assessment form.';
        }

        return $errors;
    }

    // /->process_form()
}// /class: WizardStep2

?>
