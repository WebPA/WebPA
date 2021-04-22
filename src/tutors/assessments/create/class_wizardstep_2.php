<?php
/**
 * Class : WizardStep2  (Create new assessment wizard)
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

use Doctrine\DBAL\ParameterType;
use WebPA\includes\classes\Wizard;
use WebPA\includes\functions\Common;

class WizardStep2
{
    public $wizard;

    public $step = 2;

    private $moduleId;

    // CONSTRUCTOR
    public function __construct(Wizard $wizard)
    {
        $this->wizard = $wizard;

        $this->moduleId = $this->wizard->get_var('moduleId');

        $this->wizard->back_button = '&lt; Back';
        $this->wizard->next_button = 'Next &gt;';
        $this->wizard->cancel_button = 'Cancel';
    }

    // /WizardStep2()

    public function head()
    {
        ?>
        <script language="JavaScript" type="text/javascript">
          <!--

          function body_onload () {
          }// /body_onload()

          function open_close (id) {
            id = document.getElementById(id)

            if (id.style.display == 'block' || id.style.display == '')
              id.style.display = 'none'
            else
              id.style.display = 'block'

            return
          }

          //-->
        </script>
        <?php
    }

    // /->head()

    public function form()
    {
        $DB = $this->wizard->get_var('db');
        $user = $this->wizard->get_var('user');

        $allow_feedback = $this->wizard->get_field('allow_feedback', 0);

        $sql =
            'SELECT DISTINCT f.form_id, f.form_name, m.module_id, m.module_code, m.module_title ' .
            'FROM ' . APP__DB_TABLE_PREFIX . 'form f ' .
            'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'form_module fm ' .
            'ON f.form_id = fm.form_id ' .
            'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'user_module um ' .
            'ON fm.module_id = um.module_id ' .
            'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'module m ' .
            'ON um.module_id = m.module_id ' .
            'WHERE um.user_id = ? ' .
            'OR fm.module_id = ? ' .
            'ORDER BY f.form_name ASC';

        $forms = $DB->getConnection()->fetchAllAssociative($sql, [$user->id, $this->moduleId], [ParameterType::INTEGER, ParameterType::INTEGER]);

        $form_id = $this->wizard->get_field('form_id');
        $feedback_name = $this->wizard->get_field('feedback_name');

        if (!$forms) {
            $this->wizard->next_button = ''; ?>
            <p>You haven't yet created any assessment forms.</p>
            <p>You need to <a href="../../forms/create/">create a new form</a> before you will be able to run any peer
                assessments.</p>
            <?php
        } else {
            ?>
            <p>Now you have named and scheduled your new assessment, you need to select which form you will use when
                assessing your students.</p>
            <p>Please select a form from the list below. You can see how a form will look to students by clicking <em>preview</em>.
            </p>
            <p>The form you select will be copied into your new assessment. Subsequent changes to the form '''will
                not''' affect your assessment.</p>

            <h2>Your assessment forms</h2>
            <div class="form_section">
                <table cellpadding="0" cellspacing="0">
                    <?php
                    if (count($forms) == 1) {
                        $form_id = $forms[0]['form_id'];
                    }
            foreach ($forms as $i => $form) {
                $checked = ($form_id == $form['form_id']) ? 'checked="checked"' : '';
                $intro_text = base64_encode($this->wizard->get_field('introduction'));
                if ($form['module_id'] == $this->moduleId) {
                    $module = '';
                } else {
                    $module = " ({$form['module_title']} [{$form['module_code']}])";
                }
                echo '<tr>';
                echo "<td><input type=\"radio\" name=\"form_id\" id=\"form_{$form['form_id']}\" value=\"{$form['form_id']}\" $checked /></td>";
                echo "<td><label class=\"small\" for=\"form_{$form['form_id']}\">{$form['form_name']}{$module}</label></td>";
                echo "<td>&nbsp; &nbsp; (<a style=\"font-weight: normal; font-size: 84%;\" href=\"../../forms/edit/preview_form.php?f={$form['form_id']}&amp;i={$intro_text}\" target=\"_blank\">preview</a>)</td>";
                echo '</tr>';
            } ?>
                </table>
            </div>
            <?php

            //check that the system allows student Justification
            if (APP__ALLOW_TEXT_INPUT) {
                //provide the academic the option?>
                <div style="float:right"><b>Advanced Options</b> <a href="#" onclick="open_close('advanced')"><img
                                src="../../../images/icons/advanced_options.gif" alt="view / hide advanced options"></a>
                    <br/><br/></div>
                <div id="advanced" style="display:none;" class="advanced_options">
                    <h2>Feedback / justification</h2>
                    <p><label>Do you want students to be able to view feedback for this assesment?</label></p>
                    <p>Once an assessment is completed, students can login and view feedback related to their
                        performance within the group for this assessment. The feedback simply shows whether they were
                        rated as performing below, at, or above average for each criterion within the group for this
                        assessment.</p>
                    <div class="form_section">
                        <table class="form" cellpadding="2" cellspacing="2">
                            <tr>
                                <td><input type="radio" name="allow_feedback" id="allow_feedback_yes"
                                           value="1" <?php echo ($allow_feedback) ? 'checked="checked"' : ''; ?> />
                                </td>
                                <td valign="top"><label class="small" for="allow_feedback_yes">Yes, allow students to
                                        view feedback.</label></td>
                            </tr>
                            <tr>
                                <td><input type="radio" name="allow_feedback" id="allow_feedback_no"
                                           value="0" <?php echo (!$allow_feedback) ? 'checked="checked"' : ''; ?> />
                                </td>
                                <td valign="top"><label class="small" for="allow_feedback_no">No, there is no feedback
                                        for this assessment.</label></td>
                            </tr>
                        </table>
                    </div>
                    <p><label>Would you like students to enter feedback textually?</label></p>
                    <p>If you would like students to provide textual information as either feedback or justification on
                        the scores that they have assigned in the assessment, then you will need to select the option
                        from below. The default option is to provide <b>no</b> mechanism for students to comment.</p>
                    <div class="form_section">
                        <table cellpadding="0" cellspacing="0">
                            <tr>
                                <td><label class="small" for="feedback_name">Title </label></td>
                                <td><input type="text" name="feedback_name" id="feedback_name" maxlength="100" size="40"
                                           value="<?php echo $this->wizard->get_field('feedback_name'); ?>"></td>
                            </tr>
                            <tr>
                                <td><input type="radio" name="allow_text_input" id="allow_text_input_yes"
                                           value="1" <?php echo ($this->wizard->get_field('allow_student_input')) ? 'checked="checked"' : ''; ?>>
                                </td>
                                <td><label class="small" for="allow_text_input_yes"><b>Yes</b>, allow students to
                                        comment.</label></td>
                            </tr>
                            <tr>
                                <td><input type="radio" name="allow_text_input" id="allow_text_input_no"
                                           value="0" <?php echo (!$this->wizard->get_field('allow_student_input')) ? 'checked="checked"' : ''; ?>>
                                </td>
                                <td><label class="small" for="allow_text_input_no"><b>No</b>, don't allow students to
                                        comment.</label></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php
            }
        }
    }

    // /->form()

    public function process_form()
    {
        $errors = null;

        $this->wizard->set_field('form_id', Common::fetch_POST('form_id'));
        if (empty($this->wizard->get_field('form_id'))) {
            $errors[] = 'You must select a form to use with your new assessment';
        }

        $this->wizard->set_field('allow_feedback', Common::fetch_POST('allow_feedback'));
        $this->wizard->set_field('feedback_name', Common::fetch_POST('feedback_name'));

        if (APP__ALLOW_TEXT_INPUT) {
            $this->wizard->set_field('allow_student_input', Common::fetch_POST('allow_text_input'));
        }

        return $errors;
    }

    // /->process_form()
}// /class: WizardStep2

?>
