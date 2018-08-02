<?php
/**
 * Class : WizardStep1  (Clone a form wizard)
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

class WizardStep1 {

  // Public
  public $wizard = null;
  public $step = 1;

  /*
  * CONSTRUCTOR
  */
  function __construct(&$wizard) {
    $this->wizard =& $wizard;

    $this->wizard->back_button = null;
    $this->wizard->next_button = 'Next &gt;';
    $this->wizard->cancel_button = 'Cancel';
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
    global $_module_id, $_user, $_source_id;
    $DB =& $this->wizard->get_var('db');
    $user =& $this->wizard->get_var('user');

    $form_id = $this->wizard->get_field('form_id');

    if (!$_user->is_admin()) {
      $sql = 'SELECT f.*, m.module_id, m.module_title FROM ' . APP__DB_TABLE_PREFIX .
         'form f INNER JOIN ' . APP__DB_TABLE_PREFIX .
         'form_module fm ON f.form_id = fm.form_id INNER JOIN ' . APP__DB_TABLE_PREFIX .
         "user_module um ON fm.module_id = um.module_id INNER JOIN " . APP__DB_TABLE_PREFIX .
         'module m ON um.module_id = m.module_id ' .
         "WHERE um.user_id = {$user->id} ORDER BY f.form_name ASC";
    } else {
      $sql = 'SELECT f.*, m.module_id, m.module_title FROM ' . APP__DB_TABLE_PREFIX .
         'form f INNER JOIN ' . APP__DB_TABLE_PREFIX .
         'form_module fm ON f.form_id = fm.form_id INNER JOIN ' . APP__DB_TABLE_PREFIX .
         'module m ON fm.module_id = m.module_id ' .
         "WHERE m.source_id = '{$_source_id}' ORDER BY f.form_name ASC";
    }
    $forms = $DB->fetch($sql);
    if (!$forms) {
      $this->wizard->next_button = null;
?>
      <p>You have not created any forms yet, so you cannot select one to clone.</p>
      <p>Please <a href="../create/">create a new form</a> instead.</p>
<?php
    } else {
?>
      <p>To create a clone you must first select which assessment form you wish to copy. Please choose one from the list below.</p>

      <h2>Choose a form to clone</h2>
      <div class="form_section">
        <table class="form" cellpadding="2" cellspacing="2">
<?php

        foreach($forms as $i => $form) {
          $checked_str = ($form['form_id'] == $form_id) ? ' checked="checked"' : '';
          $title_str = ($form['module_id'] == $_module_id) ? '' : " [{$form['module_title']}]";

?>
          <tr>
            <td><input type="radio" name="form_id" id="form_id_<?php echo($form['form_id']); ?>"  value="<?php echo($form['form_id']); ?>"<?php echo($checked_str); ?>/></td>
            <th style="text-align: left"><label class="small" for="form_id_<?php echo($form['form_id']); ?>"><?php echo("{$form['form_name']}{$title_str}"); ?></label></th>
          </tr>
<?php
        }
?>
        </table>
      </div>

<?php
    }
  }// /->form()

  function process_form() {
    $errors = null;

    $this->wizard->set_field('form_id',fetch_POST('form_id'));
    if (empty($this->wizard->get_field('form_id'))) { $errors[] = 'You must select which assessment form you wish to clone.'; }

    return $errors;
  }// /->process_form()

}// /class: WizardStep1

?>
