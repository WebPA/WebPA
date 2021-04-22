<?php
/**
 * Delete marks
 *
 * This page allows the tutor to see the list of students that have submited
 * and then delete the submission that the student made. This ties into the
 * feature request no 1817881 on the relevant section at http://sourceforge.net/projects/webpa/
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../includes/inc_global.php';

use Doctrine\DBAL\ParameterType;
use WebPA\includes\classes\Assessment;
use WebPA\includes\classes\GroupHandler;
use WebPA\includes\classes\ResultHandler;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

// --------------------------------------------------------------------------------

$year = Common::fetch_GET('y');
$tab = Common::fetch_GET('tab', 'open');

$assessment_id = Common::fetch_GET('a');

$user_id = Common::fetch_GET('u');

$list_url = "index.php?tab={$tab}&y={$year}";

// --------------------------------------------------------------------------------

//check the content of 'u'/ $user_id if a user ID is present then we delete this before proceeding
if (!empty($user_id)) {
    //we have posted the delete responses for the assessment to this form so now we need to delete the
    //information before carrying on to output the form.
    $dbConn = $DB->getConnection();

    $dbConn->executeQuery(
        'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'user_mark WHERE user_id = ? AND assessment_id = ?',
        [$user_id, $assessment_id],
        [ParameterType::INTEGER, ParameterType::STRING]
    );

    $dbConn->executeQuery(
        'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'user_justification WHERE user_id = ? AND assessment_id = ?',
        [$user_id, $assessment_id],
        [ParameterType::INTEGER, ParameterType::STRING]
    );

    $dbConn->executeQuery(
        'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'user_response WHERE user_id = ? AND assessment_id = ?',
        [$user_id, $assessment_id],
        [ParameterType::INTEGER, ParameterType::STRING]
    );
}

$assessment = new Assessment($DB);

if ($assessment->load($assessment_id)) {
    $assessment_qs = "a={$assessment->id}&tab={$tab}&y={$year}";

    $group_handler = new GroupHandler();
    $collection = $group_handler->get_collection($assessment->get_collection_id());

    $groups_iterator = $collection->get_groups_iterator();

    $result_handler = new ResultHandler($DB);
    $result_handler->set_assessment($assessment);

    $responded_users = $result_handler->get_responded_users();

    $members = $collection->get_members();
} else {
    $assessment = null;
}

// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME . ' ' . 'students who responded';
$UI->menu_selected = 'my assessments';
$UI->help_link = '?q=node/235';
$UI->breadcrumbs = ['home'           => '/',
               'my assessments'     => $list_url,
               'students who responded' => null, ];

$UI->set_page_bar_button('List Assessments', '../../../images/buttons/button_assessment_list.gif', '../');
$UI->set_page_bar_button('Create Assessments', '../../../images/buttons/button_assessment_create.gif', '../create/');

$UI->head();
?>
<style type="text/css">
<!--

tr.responded td { }
tr.notresponded td { background-color: #ecc; }

-->
</style>
<?php
$UI->body();
$UI->content_start();
?>

<p>This page shows all the students assigned this assessment and which have responded.</p>

<div class="content_box">

  <div class="nav_button_bar">
    <a href="<?php echo $list_url ?>"><img src="../../images/buttons/arrow_green_left.gif" alt="back -"> back to assessments list</a>
  </div>

  <p>The following list shows which students in each group have submitted their responses to the assessment.</p>
  <p>To email an individual student, click on the email link next to their name.</p>
  <p>To delete the entries provided by a student click on the <img src="../../images/icons/cancel.png" width="16" height="16" alt="Delete submission" /> image (if not greyed out).</p>
<?php
  if ($groups_iterator->size()>0) {
      for ($groups_iterator->reset(); $groups_iterator->is_valid(); $groups_iterator->next()) {
          $group =& $groups_iterator->current();

          $members = $CIS->get_user($group->get_member_ids());
          echo "<h2>{$group->name}</h2>";

          if (!$members) {
              echo '<p>This group has no members.</p>';
          } else {
              ?>
        <table class="grid" cellspacing="1" cellpadding="2" style="width: 90%">
        <tr>
          <th>name</th>
          <th>responded</th>
          <th>delete submission</th>
        </tr>
        <?php
        foreach ($members as $i => $member) {
            $flgResponse = false;
            if (in_array($member['user_id'], (array) $responded_users)) {
                $responded_img = '<img src="../../images/icons/tick.gif" width="16" height="16" alt="Responded" />';
                $responded_class = 'class="responded"';
                $allowDelete = "<a href=\"delete_marks.php?a={$assessment_id}&tab={$tab}&y={$year}&u={$member['user_id']}\" ><img src=\"../../images/icons/cancel.png\" width=\"16\" height=\"16\" alt=\"Delete submission\" /></a>";
            } else {
                $responded_img = '<img src="../../images/icons/cross.gif" width="16" height="16" alt="Not Responded"/>';
                $responded_class = 'class="notresponded"';
                $allowDelete = '<img src="../../images/icons/cancel_greyed.png" width="16" height="16" alt="Delete submission" />';
            }
            echo "<tr $responded_class><td>{$member['lastname']}, {$member['forename']}";
            if (!empty($member['id_number'])) {
                echo " ({$member['id_number']})";
            }
            echo "</td><td align=\"center\">$responded_img</td><td>{$allowDelete}</td></tr>";
        }
              echo '</table><br />';
          }// /if
      }// /for
  }
?>

</div>

<?php

$UI->content_end();

?>
