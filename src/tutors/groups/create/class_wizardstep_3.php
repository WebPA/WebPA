<?php
/**
 * Class : WizardStep3  (Create new groups wizard)
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

use WebPA\includes\classes\GroupHandler;
use WebPA\includes\classes\Wizard;

class WizardStep3
{
    public $wizard;

    public $step = 3;

    private $moduleId;

    // CONSTRUCTOR
    public function __construct(Wizard $wizard)
    {
        $this->wizard = $wizard;

        $this->moduleId = $wizard->get_var('moduleId');

        $this->wizard->back_button = '&lt; Back';
        $this->wizard->next_button = 'Finish';
        $this->wizard->cancel_button = 'Cancel';
    }

    // /WizardStep3()

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

        $total_students = $CIS->get_module_students_count($this->moduleId);
        if (!$total_students) {
            $total_students = 0;
        }
        $students_plural = ($total_students == 1) ? 'student' : 'students'; ?>
        <p>Please confirm the following settings are correct before proceeding.</p>
        <p>If you wish to amend any details, click <em>Back</em>. When you are ready to create your groups, click <em>Finish</em>.
        </p>

        <h2>Name</h2>
        <div style="margin: 0px 0px 16px 25px;">This collection of groups will be
            called: <?php echo $this->wizard->get_field('collection_name'); ?></div>

        <h2>Students</h2>
        <div style="margin: 0px 0px 16px 25px;">
            <p><?php echo "$total_students $students_plural"; ?> available.</p>
        </div>

        <h2>Groups</h2>
        <?php
        $num_groups = (int) $this->wizard->get_field('num_groups');
        if ($num_groups > 0) {
            ?>
            <div style="margin: 0px 0px 16px 25px;">
                <p>The following groups will be created in the new
                    <strong><?php echo $this->wizard->get_field('groupset_name'); ?></strong> collection:</p>
                <div style="margin-left: 25px;">
                    <?php
                    $num_groups = (int) $this->wizard->get_field('num_groups');

            $groupHandler = new GroupHandler();

            $group_names = $groupHandler->generate_group_names(
                $num_groups,
                $this->wizard->get_field('group_name_stub'),
                $this->wizard->get_field('group_numbering')
            );

            if ($num_groups <= 5) {
                foreach ($group_names as $group_name) {
                    echo "<div>$group_name</div>";
                }
            } else {
                echo "<div>{$group_names[0]}</div>";
                echo "<div>{$group_names[1]}</div>";
                echo '<div>&nbsp; ...</div>';
                echo '<div>' . $group_names[$num_groups - 2] . '</div>';
                echo '<div>' . $group_names[$num_groups - 1] . '</div>';
            } ?>
                </div>
            </div>
            <?php
        } else {
            ?>
            <div style="margin: 0px 0px 16px 25px;">You have chosen not to create any groups at this time.</div>
            <?php
        }
    }

    // /->form()

    public function process_form()
    {
        $errors = null;
        return $errors;
    }

    // /->process_form()
}// /class: WizardStep3

?>
