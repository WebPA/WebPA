<?php
/**
 * Class : WizardStep3  (Clone a form wizard)
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

use WebPA\includes\classes\Form;
use WebPA\includes\classes\Wizard;

class WizardStep3
{
    public $wizard;

    public $step = 3;

    private $moduleId;

    // CONSTRUCTOR
    public function __construct(Wizard $wizard)
    {
        $this->wizard = $wizard;

        $this->moduleId = $this->wizard->get_var('moduleId');

        $this->wizard->back_button = null;
        $this->wizard->next_button = null;
        $this->wizard->cancel_button = null;
    }

    // /WizardStep3()

    public function head()
    {
        ?>
        <script language="JavaScript" type="text/javascript">
          <!--

          function body_onload () {
          }// /body_onload()

          //-->
        </script>
        <?php
    }

    // /->head()

    public function form()
    {
        $DB =& $this->wizard->get_var('db');
        $user =& $this->wizard->get_var('user');

        $errors = null;

        $existing_form = new Form($DB);
        $existing_form->load($this->wizard->get_field('form_id'));

        $clone_form =& $existing_form->get_clone();
        $clone_form->name = $this->wizard->get_field('clone_form_name');
        $clone_form->modules[] = $this->moduleId;

        // If errors, show them
        if (is_array($errors)) {
            $this->wizard->back_button = '&lt; Back';
            $this->wizard->cancel_button = 'Cancel';
            echo '<p><strong>Unable to create your new form.</strong></p>';
            echo '<p>To correct the problem, click <em>back</em> and amend the details entered.</p>';
        } else {// Else.. create the form!
            if ($clone_form->save()) {
                ?>
                <p><strong>Your new assessment form has been created.</strong></p>
                <p style="margin-top: 20px;">To add questions and marking information to your new form, you can use the
                    <a href="../edit/edit_form.php?f=<?php echo $clone_form->id; ?>">form editor</a>.</p>
                <p style="margin-top: 20px;">Alternatively, you can return to <a href="../">my forms</a>, or to the <a
                            href="../../../">WebPA home page</a>.</p>
                <?php
            } else {
                ?>
                <p><strong>An error occurred while trying to create your new assessment form.</strong></p>
                <p>You may be able to correct the problem by clicking <em>back</em>, and then <em>next</em> again.</p>
                <?php
            }
        }
    }

    // /->form()

    public function process_form()
    {
        $this->wizard->_fields = []; // kill the wizard's stored fields
        return null;
    }

    // /->process_form()
}// /class: WizardStep3

?>
