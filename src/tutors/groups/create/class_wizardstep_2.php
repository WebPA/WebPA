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
use WebPA\includes\functions\Form;

class WizardStep2
{
    public $wizard;

    public $step = 2;

    private $moduleId;

    // CONSTRUCTOR
    public function __construct(Wizard $wizard)
    {
        $this->wizard = $wizard;

        $this->moduleId = $wizard->get_var('moduleId');

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
                document.getElementById('num_groups').focus();
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
        $config = $this->wizard->get_var('config');

        $total_students = $CIS->get_module_students_count($this->moduleId);

        $students_plural = ($total_students == 1) ? 'student' : 'students';

        if ($total_students == 0) {
            echo '<div class="warning_box"><p><strong>Warning!</strong></p><p>There are no students associated with the module you have selected.</p><p>You can continue to create your groups if you wish but there are no students available, so your groups cannot be populated at this time.</p><p>To choose a different module, click <em>back</em> to view the list of modules available.</p></div>';
        } else {
            echo "<p>The module contains <strong>$total_students $students_plural</strong> in total.</p>";
        } ?>
        <p>Now you can set how the new groups will be created. To save time, the system can automatically create
            sequentially named groups for you. If you do not want to use sequential names, or if you just want to create
            all your groups yourself, select <em>0</em> in the <em>Number of groups to create</em> box below.</p>

        <h2>Auto-create groups</h2>
        <div class="form_section">
            <p>Select how many groups you want to create.</p>

            <table class="form" cellpadding="1" cellspacing="1">
                <tr>
                    <th><label for="num_groups">Number of groups to create</label></th>
                    <td>
                        <select name="num_groups" id="num_groups">
                            <?php Form::render_options_range(0, 100, 1, (int) $this->wizard->get_field('num_groups')); ?>
                        </select>
                    </td>
                </tr>
            </table>

            <br/>
            <p>If you are auto-creating groups, decide how the groups will be named, e.g. <em>Group X</em> or <em>Team
                    X</em>.</p>
            <table class="form" cellpadding="1" cellspacing="1">
                <tr>
                    <th><label for="group_name_stub">Group names begin with</label></th>
                    <td><input type="text" name="group_name_stub" id="group_name_stub" maxlength="40" size="25"
                               value="<?php echo $this->wizard->get_field('group_name_stub'); ?>"/></td>
                </tr>
            </table>

            <br/>
            <p>Select the style of numbering to use for your new groups.</p>
            <table class="form" cellpadding="1" cellspacing="1">
                <tr>
                    <th><label for="group_numbering">Numbering Style</label></th>
                    <td>
                        <select name="group_numbering" id="group_numbering">
                            <?php
                            $options = ['alphabetic' => 'Alphabetic (Group A, Group B, ..)',
                                'numeric' => 'Numeric (Group 1, Group 2, ..)',
                                'hashed' => 'Hashed-Numeric (Group #1, Group #2, ..)',
                            ];
        Form::render_options($options, $this->wizard->get_field('group_numbering')); ?>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    // /->form()

    public function process_form()
    {
        $errors = null;

        $this->wizard->set_field('num_groups', Common::fetch_POST('num_groups', null));
        if (is_null($this->wizard->get_field('num_groups'))) {
            $errors[] = 'You must choose how many groups to create';
        }

        if ($this->wizard->get_field('num_groups') > 0) {
            $this->wizard->set_field('group_name_stub', trim(Common::fetch_POST('group_name_stub')));
            if (empty($this->wizard->get_field('group_name_stub'))) {
                $errors[] = 'You must provide a name for your new groups';
            }

            $this->wizard->set_field('group_numbering', Common::fetch_POST('group_numbering'));
            if (empty($this->wizard->get_field('group_numbering'))) {
                $errors[] = 'You must choose how to number your groups';
            }
        }

        return $errors;
    }

    // /->process_form()
}// /class: WizardStep2

?>
