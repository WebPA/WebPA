<?php
/**
 * Class : WizardStep1  (Create new assessment wizard)
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

use WebPA\includes\classes\Wizard;
use WebPA\includes\functions\Common;
use WebPA\includes\functions\Form;

class WizardStep1
{
    public $wizard;

    public $step = 1;

    public function __construct(Wizard $wizard)
    {
        $this->wizard = $wizard;

        $this->wizard->back_button = null;
        $this->wizard->next_button = 'Next &gt;';
        $this->wizard->cancel_button = 'Cancel';
    }

    public function head()
    {
        ?>
<script language="JavaScript" type="text/javascript">
<!--

  function body_onload() {
    document.getElementById('assessment_name').focus();
  }

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

    public function form()
    {
        $today = time();

        $open_date = $this->wizard->get_field('open_date');

        if (is_null($open_date)) {
            $open_date = mktime(9, 0, 0);
        }  // default start time, today @ 9am

        $close_date = $this->wizard->get_field('close_date');
        if (is_null($close_date)) {
            $close_date = mktime(17, 0, 0, date('m', $today), date('d', $today)+13, date('Y', $today));
        } // default start time, two-weeks today @ 5pm


        $email = $this->wizard->get_field('email', 0);
        $email_opening = $this->wizard->get_field('email_opening', 0);
        $email_closing = $this->wizard->get_field('email_closing', 0);

        // Render a set of dropdown boxes for datetime selection
        $renderTimeBoxes = function ($field_name, $selected_datetime) {
            echo '<table cellpadding="0" cellspacing="0"><tr>';

            // Draw day box
            echo "<td><select name=\"{$field_name}_day\">";
            Form::render_options_range(1, 31, 1, date('j', $selected_datetime));
            echo '</select></td>';

            $form_months = [1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

            // Draw month box
            echo "<td><select name=\"{$field_name}_month\">";
            Form::render_options($form_months, date('n', $selected_datetime));
            echo '</select></td>';

            // Draw year box
            echo "<td><select name=\"{$field_name}_year\">";
            Form::render_options_range(date('Y', time()), date('Y', time())+1, 1, date('Y', $selected_datetime));
            echo '</select></td>';

            echo '<th>at</th>';

            // Draw time box
            $time = date('H:i', $selected_datetime);
            $time_parts = explode(':', $time);
            $time_h = (int) $time_parts[0];
            $time_m = (int) $time_parts[1];

            echo "<td><select name=\"{$field_name}_time\">";
            for ($i=0; $i<=23; $i++) {
                for ($j=0; $j<=45; $j=$j+15) {
                    $selected = (($i == $time_h) && ($j == $time_m)) ? 'selected="selected"' : '' ;
                    printf('<option value="%1$02d:%2$02d" '. $selected .'> %1$02d:%2$02d </option>', $i, $j);
                }
            }
            echo '</select></td>';

            echo '</tr></table>';
        } ?>
    <p>To create a new assessment you must first give it a name. To avoid confusion, the name should be unique, but you can create assessments using the same name if you wish.</p>

    <div class="form_section">
      <table class="form" cellpadding="2" cellspacing="2">
      <tr>
        <th><label for="assessment_name">Name for this new assessment</label></th>
        <td><input type="text" name="assessment_name" id="assessment_name" maxlength="100" size="40" value="<?php echo $this->wizard->get_field('assessment_name'); ?>" /></td>
      </tr>
      </table>
    </div>

    <p>Now choose a schedule for when, and for how long, this assessment will run.</p>
    <div class="form_section">
      <table class="form" cellpadding="2" cellspacing="2">
      <tr>
        <th><label>Opening date</label></th>
        <td><?php $renderTimeBoxes('open_date', $open_date); ?></td>
      </tr>
      <tr>
        <th><label>Closing date</label></th>
        <td><?php $renderTimeBoxes('close_date', $close_date); ?></td>
      </tr>
      </table>
    </div>

    <p>Now enter the text to use as the introduction to your assessment (optional).</p>
    <p>This text will act as a pre-amble to your assessment, and is an opportunity to instruct students on how you want them to complete the assessment, and score each criteria.</p>
    <div class="form_section">
      <table class="form" cellpadding="2" cellspacing="2" width="100%">
      <tr>
        <th valign="top" style="padding-top: 2px; vertical-align: top;"><label for="introduction">Introduction</label></th>
        <td width="100%"><textarea name="introduction" id="introduction" rows="6" cols="40" style="width: 90%;"><?php echo $this->wizard->get_field('introduction'); ?></textarea></td>
      </tr>
      </table>
    </div>

    <div style="float:right"><b>Advanced Options</b> <a href="#" onclick="open_close('advanced')"><img src="../../../images/icons/advanced_options.gif" alt="view / hide advanced options"></a>
    <br/><br/></div>
    <div id="advanced" style="display:none;" class="advanced_options">

      <h2>notification emails</h2>

      <p><label>Do you want to email all students when this assessment is created?</label></p>
      <p>This option will send an email to all of the students who are in the groups for this assessment as soon as you click the finish button at the end of the wizard.</p>
      <div class="form_section">
        <table class="form" cellpadding="2" cellspacing="2">
        <tr>
          <td><input type="radio" name="email" id="email_yes" value="1" <?php echo ($email) ? 'checked="checked"' : ''; ?> /></td>
          <td valign="top"><label class="small" for="email_yes">Yes, email all students.</label></td>
        </tr>
        <tr>
          <td><input type="radio" name="email" id="email_no" value="0" <?php echo (!$email) ? 'checked="checked"' : ''; ?> /></td>
          <td valign="top"><label class="small" for="email_no">No, don't email all students.</label></td>
        </tr>
        </table>
      </div>

<?php
    if (APP__REMINDER_OPENING) {
        ?>
      <p><label>Do you want an email reminder sent to all students 48 hours before the assessment is opened?</label></p>
      <p>This option will send an email to all of the students who are in the groups for this assessment 48 hours (approx.) before the assessment opens.</p>
      <div class="form_section">
        <table class="form" cellpadding="2" cellspacing="2">
        <tr>
          <td><input type="radio" name="email_opening" id="email_opening_yes" value="1" <?php echo ($email_opening) ? 'checked="checked"' : ''; ?> /></td>
          <td valign="top"><label class="small" for="email_opening_yes">Yes, email all students.</label></td>
        </tr>
        <tr>
          <td><input type="radio" name="email_opening" id="email_opening_no" value="0" <?php echo (!$email_opening) ? 'checked="checked"' : ''; ?> /></td>
          <td valign="top"><label class="small" for="email_opening_no">No, don't email all students.</label></td>
        </tr>
        </table>
      </div>
<?php
    }
        if (APP__REMINDER_CLOSING) {
            ?>
      <p><label>Do you want to email all students 48 hours before the assessment closes?</label></p>
      <p>This option will send an email to all of the students who are in the groups for this assessment 48 hours (approx.) before the assessment closes.</p>
      <div class="form_section">
        <table class="form" cellpadding="2" cellspacing="2">
        <tr>
          <td><input type="radio" name="email_closing" id="email_closing_yes" value="1" <?php echo ($email_closing) ? 'checked="checked"' : ''; ?> /></td>
          <td valign="top"><label class="small" for="email_closing_yes">Yes, email all students.</label></td>
        </tr>
        <tr>
          <td><input type="radio" name="email_closing" id="email_closing_no" value="0" <?php echo (!$email_closing) ? 'checked="checked"' : ''; ?> /></td>
          <td valign="top"><label class="small" for="email_closing_no">No, don't email all students.</label></td>
        </tr>
        </table>
      </div>
<?php
        } ?>

    </div>


<?php
    }

    // /->form()

    public function process_form()
    {
        $errors = null;

        $this->wizard->set_field('assessment_name', Common::fetch_POST('assessment_name'));
        if (empty($this->wizard->get_field('assessment_name'))) {
            $errors[] = 'You must enter a name for your new assessment';
        }


        // open_date
        $time_parts = explode(':', Common::fetch_POST('open_date_time'));
        $time_h = $time_parts[0];
        $time_m = $time_parts[1];
        $open_date = mktime($time_h, $time_m, 0, Common::fetch_POST('open_date_month'), Common::fetch_POST('open_date_day'), Common::fetch_POST('open_date_year'));

        // close_date
        $time_parts = explode(':', Common::fetch_POST('close_date_time'));
        $time_h = $time_parts[0];
        $time_m = $time_parts[1];
        $close_date = mktime($time_h, $time_m, 0, Common::fetch_POST('close_date_month'), Common::fetch_POST('close_date_day'), Common::fetch_POST('close_date_year'));


        $this->wizard->set_field('open_date', $open_date);
        $this->wizard->set_field('close_date', $close_date);
        if ($open_date>=$close_date) {
            $errors[] = 'You must select a closing date/time that is after your opening date';
        }

        $this->wizard->set_field('introduction', Common::fetch_POST('introduction'));

        $this->wizard->set_field('email', Common::fetch_POST('email'));
        $this->wizard->set_field('email_opening', Common::fetch_POST('email_opening'));
        $this->wizard->set_field('email_closing', Common::fetch_POST('email_closing'));

        return $errors;
    }
}
