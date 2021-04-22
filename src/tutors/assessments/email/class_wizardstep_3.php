<?php
/**
 * Class : WizardStep3  (Email students wizard)
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

use WebPA\includes\classes\GroupHandler;
use WebPA\includes\classes\ResultHandler;

class WizardStep3
{
    // Public
    public $wizard;

    public $step = 3;

    // CONSTRUCTOR
    public function __construct(&$wizard)
    {
        $this->wizard =& $wizard;

        $this->wizard->back_button = '&lt; Back';
        $this->wizard->next_button = 'Send Email';
        $this->wizard->cancel_button = 'Cancel';
    }

    // /WizardStep3()

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
                 'havenot'  => 'any students who HAVE NOT responded', ];

        $send_email_to = $this->wizard->get_field('send_to');

        $assessment = $this->wizard->get_var('assessment');

        $group_handler = new GroupHandler();
        $collection = $group_handler->get_collection($assessment->get_collection_id());

        $collection_member_count = $collection->get_member_count();
        $num_targets = 0; ?>
    <p>Please confirm the following settings are correct before proceeding.</p>
    <p>If you wish to amend any details, click <em>Back</em>. When you are ready to create your groups, click <em>Send Email</em>.</p>

    <h2>Recipients</h2>
    <div class="form_section">
      <p>You have opted to send this email to <em><?php echo $send_to_desc["$send_email_to"]; ?></em>.</p>
<?php
    switch ($send_email_to) {
      case 'all':
        $num_targets = $collection_member_count;
        echo "<p>This email will be sent to all $collection_member_count students.</p>";
        break;
      // --------------------
      case 'groups':
        $email_groups = explode('|', $this->wizard->get_field('email_groups'));
        $groups = $collection->get_groups_array();
        $group_member_counts = $collection->get_member_count_by_group();
        $num_targets = 0;
?>
            <p>The groups you selected were:</p>
            <ul>
<?php
        foreach ($groups as $i => $group) {
            if (in_array($group['group_id'], $email_groups)) {
                $num_group_members = (array_key_exists($group['group_id'], $group_member_counts)) ? $group_member_counts["{$group['group_id']}"] : 0 ;
                $num_targets += $num_group_members;
                echo "<li>{$group['group_name']} <span style=\"font-size: 0.8em; font-weight: normal\">($num_group_members)</span></li>";
            }
        }
?>
              </ul>
<?php
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
    } ?>
    </div>

<?php
    if (!$num_targets) {
        $this->wizard->next_button = null;  // default NEXT to off
?>
      <div class="error_box">
        <p><strong>There are no students matching your criteria.</strong></p>
        <p>Please go back and choose a different set of students to contact.</p>
      </div>
<?php
    } else {
        ?>
      <h2>Email Preview</h2>
      <div class="form_section">
        <div style="padding: 4px; background-color: #eee; border: 1px solid #999;">
          Subject:  <?php echo $this->wizard->get_field('email_subject'); ?><br /><br />
          <?php echo nl2br($this->wizard->get_field('email_text')); ?>
        </div>
      </div>
<?php
    }
    }

    // /->form()

    public function process_form()
    {
        $errors = null;

        // Send Email

        return $errors;
    }

    // /->process_form()
}// /class: WizardStep3

?>
