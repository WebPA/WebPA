<?php
/**
 * Class : WizardStep1  (Email students wizard)
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

  }// /body_onload()

//-->
</script>
<?php
    }

    // /->head()

    public function form()
    {
        $send_to = ['all'    => 'everyone taking this assessment',
               'groups' => 'selected groups taking this assessment',
               'have'   => 'students who HAVE responded',
               'havenot'  => 'students who HAVE NOT responded', ]; ?>
    <p>To begin, you need to select exactly which students should receive your email.</p>

    <h2>Send Email To</h2>
    <div class="form_section">
<?php
      Form::render_radio_boxes($send_to, 'send_to', $this->wizard->get_field('send_to')); ?>
    </div>
<?php
    }

    // /->form()

    public function process_form()
    {
        $errors = null;

        $this->wizard->set_field('send_to', Common::fetch_POST('send_to'));
        if (empty($this->wizard->get_field('send_to'))) {
            $errors[] = 'You must select who to send this email to.';
        }

        return $errors;
    }

    // /->process_form()
}// /class: WizardStep1

?>
