<?php
/**
 * Class : WizardStep2  (Email students wizard)
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

use WebPA\includes\classes\GroupHandler;
use WebPA\includes\classes\ResultHandler;
use WebPA\includes\functions\Common;
use WebPA\includes\functions\Form;

class WizardStep2
{
    // Public
    public $wizard;

    public $step = 2;

    // CONSTRUCTOR
    public function __construct(&$wizard)
    {
        $this->wizard =& $wizard;

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

  function body_onload() {

  }// /body_onload()

//-->
</script>
<?php
    }

    // /->head()

    public function form()
    {
        $send_to_desc = ['all'    => 'everyone taking this assessment',
                 'groups' => 'selected groups taking this assessment',
                 'have'   => 'all the students who HAVE responded',
                 'havenot'  => 'any students who HAVE NOT responded',
                ];

        $send_email_to = $this->wizard->get_field('send_to');

        $assessment = $this->wizard->get_var('assessment');

        $group_handler = new GroupHandler();
        $collection = $group_handler->get_collection($assessment->get_collection_id());

        $collection_member_count = $collection->get_member_count();
        $num_targets = 0;

        if (array_key_exists($send_email_to, $send_to_desc)) {
            ?>
      <p>You have opted to send this email to <em><?php echo $send_to_desc["$send_email_to"]; ?></em>.</p>
<?php
      if ($send_email_to=='groups') {
          $email_groups = explode('|', $this->wizard->get_field('email_groups'));
          $num_targets = $collection_member_count; ?>
        <h2>Select Groups To Email</h2>
        <div class="form_section">
          <p>Tick all the groups you wish to include in this email. The number of students in each group is shown in brackets after the group name.</p>
<?php
        $groups_cbox_array = null;

          $groups = $collection->get_groups_array();
          if ($groups) {
              $group_member_counts = (array) $collection->get_member_count_by_group();

              foreach ($groups as $i => $group) {
                  if (array_key_exists($group['group_id'], $group_member_counts)) {
                      $num_group_members = $group_member_counts["{$group['group_id']}"];
                      $groups_cbox_array["{$group['group_id']}"] = "{$group['group_name']} <span style=\"font-size: 0.8em; font-weight: normal\">($num_group_members)</span>";
                  } else {
                      $num_group_members = 0;
                  }
              }
          }

          Form::render_checkbox_grid($groups_cbox_array, 'email_group', (array) $email_groups, false, 2); ?>
        </div>
<?php
      } else {
          switch ($send_email_to) {
          case 'all':
            $num_targets = $collection_member_count;
            echo "<p>This email will be sent to all $collection_member_count students.</p>";
            break;

          // --------------------
          case 'have':
            $resultHandlerDb = $this->wizard->get_var('db');

            $result_handler = new ResultHandler($resultHandlerDb);
            $result_handler->set_assessment($assessment);
            $num_responses = $result_handler->get_responses_count_for_assessment();
            $num_targets = $num_responses;
            echo "<p>This email will be sent to the $num_responses students who have taken this assessment.</p>";

            break;

          // --------------------
          case 'havenot':
            $resultHandlerDb = $this->wizard->get_var('db');

            $result_handler = new ResultHandler($resultHandlerDb);
            $result_handler->set_assessment($assessment);
            $num_responses = $result_handler->get_responses_count_for_assessment();
            $num_no_responses = $collection_member_count - $num_responses;
            $num_targets = $num_no_responses;
            echo "<p>This email will be sent to the $num_no_responses students who have not yet taken this assessment.</p>";
            break;
        }
      }

            if (!$num_targets) {
                $this->wizard->next_button = null;  // default NEXT to off
?>
        <div class="error_box">
          <p><strong>There are no students matching your criteria.</strong></p>
          <p>You may have groups with no students in them.</p>
          <p>Please go back and choose a different set of students to contact.</p>
        </div>
<?php
            } else {
                ?>
        <h2>Email Details</h2>
        <div class="form_section">
          <table class="form" cellpadding="2" cellspacing="2">
          <tr>
            <th><label for="email_subject">Subject</label></th>
            <td><input name="email_subject" id="email_subject" maxlength="100" size="64" value="<?php echo $this->wizard->get_field('email_subject'); ?>" /></td>
          </tr>
          <tr>
            <th style="vertical-align: top"><label for="email_text">Text</label></th>
            <td><textarea name="email_text" id="email_text" cols="60" rows="8"><?php echo $this->wizard->get_field('email_text'); ?></textarea></td>
          </tr>
          </table>
        </div>
<?php
            }
        }
    }

    // /->form()

    public function process_form()
    {
        $errors = null;

        $send_email_to = $this->wizard->get_field('send_to');

        if ($send_email_to=='groups') {
            $num_students = 0;
            $email_groups = null;

            // Find which groups were selected
            if ((array_key_exists('email_group', $_POST)) && (is_array($_POST['email_group']))) {
                // Find how many students those groups contain
                $assessment = $this->wizard->get_var('assessment');

                $group_handler = new GroupHandler();
                $collection = $group_handler->get_collection($assessment->get_collection_id());

                $group_member_counts = $collection->get_member_count_by_group();

                // Check that the groups actually contain students
                foreach ($_POST['email_group'] as $i => $group_id) {
                    if (array_key_exists($group_id, $group_member_counts)) {
                        $num_students += $group_member_counts[$group_id];
                    }
                } // /for

                $email_groups = implode('|', array_values($_POST['email_group']));
                $this->wizard->set_field('email_groups', Common::fetch_POST('email_groups'));
            }
            $this->wizard->set_field('email_groups', $email_groups);
            if (($num_students===0)) {
                $errors[] = 'The group(s) you have selected contain no students. There must be at least one recepient for this email.';
            }
        }

        $this->wizard->set_field('email_subject', Common::fetch_POST('email_subject'));
        if (empty($this->wizard->get_field('email_subject'))) {
            $errors[] = 'You must enter a subject for this email.';
        }

        $this->wizard->set_field('email_text', Common::fetch_POST('email_text'));
        if (empty($this->wizard->get_field('email_text'))) {
            $errors[] = 'You must enter the text of this email.';
        }

        return $errors;
    }

    // /->process_form()
}// /class: WizardStep2

?>
