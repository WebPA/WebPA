<?php
/**
 * Class : WizardStep4  (Create new assessment wizard)
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

use WebPA\includes\classes\Wizard;
use WebPA\includes\functions\Common;

class WizardStep4
{
    public $wizard;

    public $step = 4;

    // CONSTRUCTOR
    public function __construct(Wizard $wizard)
    {
        $this->wizard = $wizard;

        $this->wizard->back_button = '&lt; Back';
        $this->wizard->next_button = 'Next &gt;';
        $this->wizard->cancel_button = 'Cancel';
    }

    // /WizardStep4()

    public function head()
    {
        ?>
<script language="JavaScript" type="text/javascript">
<!--

  function body_onload() {
  }// /body_onload()

  function open_close(id) {
    id = document.getElementById(id);

      if (id.style.display == 'block' || id.style.display == '')
          id.style.display = 'none';
      else
          id.style.display = 'block';

      return;
  }

//-->
</script>
<?php
    }

    // /->head()

    public function form()
    {
        $assessment_type = $this->wizard->get_field('assessment_type', 1); ?>
    <h2>Assessment Type</h2>
    <div class="form_section">
      <table class="form" cellpadding="2" cellspacing="2">
      <tr>
        <td>
          <input type="radio" name="assessment_type" value="1" id="both" <?php echo (!$assessment_type)? 'checked="checked"' : ''; ?>>
        </td>
        <td>
          <label class="small" for="both">Self and peer assessment</label>
        </td>
      </tr>
      </table>
      <br/><br/>
      <div style="float:right"><b>Advanced Options</b> <a href="#" onclick="open_close('advanced')"><img src="../../../images/icons/advanced_options.gif" alt="view / hide advanced options"></a>
      <br/><br/></div>
      <div id="advanced" style="display:none;" class="advanced_options">
      <table class="form" cellpadding="2" cellspacing="2">
      <tr>
        <td>
          <input type="radio" name="assessment_type" value="0" id="peer" <?php echo ($assessment_type)? 'checked="checked"' : ''; ?>/>
        </td>
        <td>
          <label class="small" for="peer">Peer assessment only</label>
        </td>
      </tr>
      </table>
      </div>
    </div>

<?php
    }

    // /->form()

    public function process_form()
    {
        $errors = null;

        $this->wizard->set_field('assessment_type', Common::fetch_POST('assessment_type'));


        return $errors;
    }

    // /->process_form()
}// /class: WizardStep4

?>
