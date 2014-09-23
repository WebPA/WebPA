<?php

/**
 *
 * Class : WizardStep2  (Create new groups wizard)
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
    $this->wizard->next_button = gettext('Next &gt;');
    $this->wizard->cancel_button = gettext('Cancel');
  }// /WizardStep2()

  function head() {
    $html = <<<HTMLEnd
<script language="JavaScript" type="text/javascript">
<!--

  function body_onload() {
    document.getElementById('num_groups').focus();
  }// /body_onload()

//-->
</script>
HTMLEnd;

    echo($html);
  }// /->head()

  function form() {

    global $_module_id;

    $CIS = $this->wizard->get_var('CIS');
    $config = $this->wizard->get_var('config');

    require_once("../../../includes/functions/lib_form_functions.php");

    $total_students = $CIS->get_module_students_count($_module_id);

    $students_plural = ($total_students==1) ? gettext('student') : gettext('students');

    if ($total_students==0) {
      echo("<div class=\"warning_box\"><p>".gettext('<strong>Warning!</strong></p><p>There are no students associated with the module you have selected.</p><p>You can continue to create your groups if you wish but there are no students available, so your groups cannot be populated at this time.</p><p>To choose a different module, click <em>back</em> to view the list of modules available.')."</p></div>");
    } else {
      echo("<p>".sprintf(gettext('The module contains <strong>%d %s</strong> in total.'), $total_students, $students_plural)."</p>");
    }
?>
    <p><?php echo gettext('Now you can set how the new groups will be created. To save time, the system can automatically create sequentially named groups for you. If you do not want to use sequential names, or if you just want to create all your groups yourself, select <em>0</em> in the <em>Number of groups to create</em> box below.');?></p>

    <h2><?php echo gettext('Auto-create groups');?></h2>
    <div class="form_section">
      <p><?php echo gettext('Select how many groups you want to create.');?></p>

      <table class="form" cellpadding="1" cellspacing="1">
      <tr>
        <th><label for="num_groups"><?php echo gettext('Number of groups to create');?></label></th>
        <td>
          <select name="num_groups" id="num_groups">
          <?php render_options_range(0,100,1,(int) $this->wizard->get_field('num_groups')); ?>
          </select>
        </td>
      </tr>
      </table>

      <br />
      <p><?php echo gettext('If you are auto-creating groups, decide how the groups will be named, e.g.  <em>Group X</em> or <em>Team X</em>.');?></p>
      <table class="form" cellpadding="1" cellspacing="1">
      <tr>
        <th><label for="group_name_stub"><?php echo gettext('Group names begin with');?></label></th>
        <td><input type="text" name="group_name_stub" id="group_name_stub" maxlength="40" size="25" value="<?php echo($this->wizard->get_field('group_name_stub')); ?>" /></td>
      </tr>
      </table>

      <br />
      <p><?php echo gettext('Select the style of numbering to use for your new groups.');?></p>
      <table class="form" cellpadding="1" cellspacing="1">
      <tr>
        <th><label for="group_numbering"><?php echo gettext('Numbering Style');?></label></th>
        <td>
          <select name="group_numbering" id="group_numbering">
<?php
    $options = array  ('alphabetic' => gettext('Alphabetic (Group A, Group B, ..)') ,
               'numeric'    => gettext('Numeric (Group 1, Group 2, ..)') ,
               'hashed'   => gettext('Hashed-Numeric (Group #1, Group #2, ..)'),
              );
    render_options($options, $this->wizard->get_field('group_numbering'));
?>
          </select>
        </td>
      </tr>
      </table>
    </div>
<?php
  }// /->form()

  function process_form() {
    $errors = null;

    $this->wizard->set_field('num_groups', fetch_POST('num_groups',null));
    if (is_null($this->wizard->get_field('num_groups'))) { $errors[] = gettext('You must choose how many groups to create'); }

    if ($this->wizard->get_field('num_groups')>0) {
      $this->wizard->set_field('group_name_stub', trim( fetch_POST('group_name_stub') ) );
      if (is_empty($this->wizard->get_field('group_name_stub'))) { $errors[] = gettext('You must provide a name for your new groups'); }

      $this->wizard->set_field('group_numbering', fetch_POST('group_numbering'));
      if (is_empty($this->wizard->get_field('group_numbering'))) { $errors[] = gettext('You must choose how to number your groups'); }
    }

    return $errors;
  }// /->process_form()

}// /class: WizardStep2

?>
