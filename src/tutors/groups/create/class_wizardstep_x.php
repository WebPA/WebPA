<?php
/**
 * Class : WizardStep2  (Create new groups wizard)
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

use WebPA\includes\classes\Wizard;
use WebPA\includes\functions\Common;

class WizardStep2
{
    public $wizard;

    public $step = 2;

    private $module;

    // CONSTRUCTOR
    public function __construct(Wizard $wizard)
    {
        $this->wizard = $wizard;

        $this->module = $wizard->get_var('moduleId');

        $this->wizard->back_button = '&lt; Back';
        $this->wizard->next_button = 'Next &gt;';
        $this->wizard->cancel_button = 'Cancel';
    }

    // /WizardStep2()

    public function head()
    {
        $html = <<<HTMLEnd
            <script language="JavaScript" type="text/javascript">
            <!--

              function body_onload() {
              }// /body_onload()

            //-->
            </script>
            HTMLEnd;

        echo $html;
    }

    // /->head()

    public function form()
    {
        $CIS = $this->wizard->get_var('CIS');
        $user = $this->wizard->get_var('user');
        $modules = $CIS->get_staff_modules($user->id);

        $arr_module_id = $this->wizard->get_field('module_id');

        $module_select = $this->wizard->get_field('module_select');

        if (!$modules) {
            ?>
            <p>You are not associated with any modules.</p>
            <p>You cannot create any groups without being associated with at least one module.</p>
            <?php
            $this->wizard->next_button = null;
        } else {
            if ($module_select == 'multiple') {
                echo '<p>You have opted to populate your groups with students chosen from multiple modules. Usually, groups need only contain students from a single module, but by selecting multiple modules your groups can contain a mixture of students from different modules.</p>';
                echo '<p>The modules below are those you are associated with, as either a lead or additional tutor.</p>';
                echo '<p>Select the modules to take students from by ticking the appropriate box:</p>';
            } else {
                echo '<p>The modules below are those you are associated with, as either a lead or additional tutor.</p>';
                echo '<p>Please select the module you want to take students from:</p>';
            } ?>
            <h2>Your Modules</h2>
            <div class="form_section">
                <table class="form" cellpadding="1" cellspacing="1">
                    <?php
                    if ($module_select == 'multiple') {
                        foreach ($modules as $i => $module) {
                            $checked_str = ((is_array($arr_module_id)) && (in_array($module['module_id'], $arr_module_id))) ? 'checked="checked"' : '';
                            echo '<tr>';
                            echo "<td><input type=\"checkbox\" name=\"module_id[]\" id=\"module_{$module['module_id']}\" value=\"{$module['module_id']}\" $checked_str /></td>";
                            echo "<td><label style=\"font-weight: normal;\" for=\"module_{$module['module_id']}\">{$module['module_title']} [{$module['module_code']}]</label></td>";
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr>';
                        echo "<td><input type=\"radio\" name=\"module_id[]\" id=\"module_{$this->module['module_id']}\" value=\"{$this->module['module_id']}\" checked=\"checked\" /></td>";
                        echo "<td><label style=\"font-weight: normal;\" for=\"module_{$this->module['module_id']}\">{$this->module['module_title']} [{$this->module['module_code']}]</label></td>";
                        echo '</tr>';
                    } ?>
                </table>
            </div>
            <?php
        }
    }

    // /->form()

    public function process_form()
    {
        $errors = null;

        $this->wizard->set_field('module_id', Common::fetch_POST('module_id'));
        if (empty($this->wizard->get_field('module_id'))) {
            $errors[] = 'You must select at least one module to take students from';
        }

        return $errors;
    }

    // /->process_form()
}// /class: WizardStep2

?>
