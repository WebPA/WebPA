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
  }// /body_onload()

//-->
</script>
HTMLEnd;

    echo($html);
  }// /->head()

  function form() {

    global $_module;

    $CIS = $this->wizard->get_var('CIS');
    $user = $this->wizard->get_var('user');
    $modules = $CIS->get_staff_modules($user->id);

    $arr_module_id = $this->wizard->get_field('module_id');

    $module_select = $this->wizard->get_field('module_select');

    if (!$modules) {
      ?>
      <p><?php echo gettext('You are not associated with any modules.');?></p>
      <p><?php echo gettext('You cannot create any groups without being associated with at least one module.');?></p>
<?php
      $this->wizard->next_button = null;
    } else {
      if ($module_select=='multiple') {
        echo('<p>'.gettext('You have opted to populate your groups with students chosen from multiple modules. Usually, groups need only contain students from a single module, but by selecting multiple modules your groups can contain a mixture of students from different modules.').'</p>');
        echo('<p>'.gettext('The modules below are those you are associated with, as either a lead or additional tutor.').'</p>');
        echo('<p>'.gettext('Select the modules to take students from by ticking the appropriate box:').'</p>');
      } else {
        echo('<p>'.gettext('The modules below are those you are associated with, as either a lead or additional tutor.').'</p>');
        echo('<p>'.gettext('Please select the module you want to take students from:').'</p>');
      }
?>
      <h2><?php echo gettext('Your Modules');?></h2>
      <div class="form_section">
        <table class="form" cellpadding="1" cellspacing="1">
<?php
      if ($module_select == 'multiple') {
        foreach ($modules as $i => $module) {
          $checked_str = ( (is_array($arr_module_id)) && (in_array($module['module_id'],$arr_module_id)) ) ? 'checked="checked"' : '' ;
          echo('<tr>');
          echo("<td><input type=\"checkbox\" name=\"module_id[]\" id=\"module_{$module['module_id']}\" value=\"{$module['module_id']}\" $checked_str /></td>");
          echo("<td><label style=\"font-weight: normal;\" for=\"module_{$module['module_id']}\">{$module['module_title']} [{$module['module_code']}]</label></td>");
          echo('</tr>');
        }
      } else {
        echo('<tr>');
        echo("<td><input type=\"radio\" name=\"module_id[]\" id=\"module_{$_module['module_id']}\" value=\"{$_module['module_id']}\" checked=\"checked\" /></td>");
        echo("<td><label style=\"font-weight: normal;\" for=\"module_{$_module['module_id']}\">{$_module['module_title']} [{$_module['module_code']}]</label></td>");
        echo('</tr>');
      }
?>
        </table>
      </div>
<?php
    }
  }// /->form()

  function process_form() {
    $errors = null;

    $this->wizard->set_field('module_id',fetch_POST('module_id'));
    if (is_empty($this->wizard->get_field('module_id'))) { $errors[] = gettext('You must select at least one module to take students from'); }

    return $errors;
  }// /->process_form()

}// /class: WizardStep2

?>
