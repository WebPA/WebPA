<?php
/**
 *
 * Class : WizardStep1  (add new criterion wizard)
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
    document.getElementById('question_text').focus();
  }// /body_onload()

//-->
</script>
<?php
  }// /->head()

  function form() {
    $config = $this->wizard->get_var('config');
    require_once('../../../../includes/functions/lib_form_functions.php');
    if (!$this->wizard->get_field('question_range_start')) { $this->wizard->set_field('question_range_start',1); }
    if (!$this->wizard->get_field('question_range_end')) { $this->wizard->set_field('question_range_end',5); }
?>
    <p><?php echo gettext('To create a new marking question, you firstly need to enter the text of the question.');?></p>

    <div class="form_section">
      <p><?php echo gettext('You can phrase the assessment criteria however you like, either as statements or questions. The important thing is that it\'s clear what criteria the student is assessing.');?></p>

      <p><?php echo gettext('Use the description box to further clarify what you want the student\'s to assess when scoring this criterion.');?></p>
      <table cellpadding="2" cellspacing="2">
      <tr>
        <th><label for="question_text"><?php echo gettext('Criterion Text');?></label></th>
        <td><input type="text" name="question_text" id="question_text" maxlength="255" size="50" value="<?php echo( $this->wizard->get_field('question_text') ); ?>" /></td>
      </tr>
      <tr>
        <th valign="top" width="100"><label for="question_desc"><?php echo gettext('Description');?></label><br /><span style="font-size: 0.8em; font-weight: normal;"><?php echo gettext('(optional)');?></span></th>
        <td><textarea name="question_desc" id="question_desc" cols="60" rows="3" style="width: 90%;"><?php echo( $this->wizard->get_field('question_desc') ); ?></textarea></td>
      </tr>
      </table>
    </div>

    <p><?php echo gettext('Now select the range of scores you will allow for this question.');?></p>
    <div class="form_section">
      <table class="form" cellpadding="2" cellspacing="2">
      <tr>
        <th><label for="question_range_start"><?php echo gettext('Scores can range from');?></label></th>
        <td>
          <select name="question_range_start" id="question_range_start">
            <?php render_options_range(0,1,1,(int) $this->wizard->get_field('question_range_start')); ?>
          </select>
        </td>
        <td><label><?php echo gettext('to');?></label></td>
        <td>
          <select name="question_range_end" id="question_range_end">
            <?php render_options_range(3,10,1,(int) $this->wizard->get_field('question_range_end')); ?>
          </select>
        </td>
      </tr>
      </table>
    </div>
    <p><?php echo gettext('<strong>Please Note</strong> - allowing 0 scores means students can receive no marks if they failed to contribute at all.');?></p>
<?php
  }// /->form()

  function process_form() {
    $errors = null;

    $this->wizard->set_field('question_text',fetch_POST('question_text'));
    if (is_empty($this->wizard->get_field('question_text'))) { $errors[] = gettext('You must provide some text for your new criterion'); }

    $this->wizard->set_field('question_desc',fetch_POST('question_desc'));

    $this->wizard->set_field('question_range_start',fetch_POST('question_range_start'));
    $this->wizard->set_field('question_range_end',fetch_POST('question_range_end'));

    return $errors;
  }// /->process_form()

}// /class: WizardStep1


?>
