<?php
/**
 * Report: Student Grades
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../../includes/inc_global.php';

use Doctrine\DBAL\ParameterType;
use WebPA\includes\classes\AlgorithmFactory;
use WebPA\includes\classes\Assessment;
use WebPA\includes\classes\XMLParser;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

// --------------------------------------------------------------------------------
// Process GET/POST

$assessment_id = Common::fetch_GET('a');

$type = Common::fetch_GET('t', 'view');

$tab = Common::fetch_GET('tab');
$year = Common::fetch_GET('y', date('Y'));

$marking_date = (int) Common::fetch_GET('md');

// --------------------------------------------------------------------------------

$assessment = new Assessment($DB);
if (!$assessment->load($assessment_id)) {
    $assessment = null;
    echo 'Error: The requested assessment could not be loaded.';
    exit;
}
    $xml_parser = new XMLParser();

    // ----------------------------------------
    // Get the marking parameters used for the marksheet this report will display
    $marking_params = $assessment->get_marking_params($marking_date);

    if (!$marking_params) {
        echo 'Error: The requested marksheet could not be loaded.';
        exit;
    }

    // ----------------------------------------
    // Get a list of the groups, and their marks, used in this assessment
    $groups_and_marks = $assessment->get_group_marks();

    // ----------------------------------------
    // Get the appropriate algorithm and calculate the grades
    $algorithm = AlgorithmFactory::get_algorithm($marking_params['algorithm']);

    if (!$algorithm) {
        echo 'Error: The requested algorithm could not be loaded.';
        exit;
    }
        $algorithm->set_grade_ordinals($ordinal_scale);
        $algorithm->set_assessment($assessment);
        $algorithm->set_marking_params($marking_params);
        $algorithm->calculate();

        $questions = $algorithm->get_questions();
        $submissions = $algorithm->get_submitters();
        $webpa_scores = $algorithm->get_webpa_scores();

        $intermediate_grades = $algorithm->get_intermediate_grades();
        $grades = $algorithm->get_grades();

        $penalties = $algorithm->get_penalties();
        if (!$penalties) {
            $penalties = [];
        }

        $group_names = $algorithm->get_group_names();
        $group_members = $algorithm->get_group_members();
        $member_ids = array_keys($webpa_scores);
    // /if-else(is algorithm)

    //------------------------------------------------------------
    //get the feedback / Justification
    $feedbackQuery =
      'SELECT * ' .
      'FROM ' . APP__DB_TABLE_PREFIX . 'user_justification ' .
      'WHERE assessment_id = ?';

    $fetch_comments = $DB->getConnection()->fetchAllAssociative($feedbackQuery, [$assessment->id], [ParameterType::STRING]);

    $feedback = null;

    foreach ($fetch_comments as $comment) {
        if (!is_array($comment)) {
            break;
        }

        $id = $CIS->get_user($comment['user_id']);
        $marker_id = $id['user_id'];
        $marker = $id['lastname'] . ', ' . $id['forename'];
        $id = $CIS->get_user($comment['marked_user_id']);
        $marked = $id['lastname'] . ', ' . $id['forename'];

        $feedback []  = [
            'marker_id' =>  $marker_id,
            'marker' =>  $marker,
            'marked' =>  $marked,
            'feedback' =>  $comment['justification_text'],
        ];
    }


/*
* --------------------------------------------------------------------------------
* If report type is HTML view
* --------------------------------------------------------------------------------
*/
if ($type == 'view') {
    // Begin Page

    $page_title = ($assessment) ? "{$assessment->name}" : 'report';

    $UI->page_title = APP__NAME . ' ' . $page_title;
    $UI->head(); ?>
  <style type="text/css">
  <!--

  #side_bar { display: none; }
  #main { margin: 0px; }

  table.grid th { padding: 8px; }
  table.grid td { padding: 8px; text-align: center; }

  table.grid td.important { background-color: #eec; }

  -->
  </style>
<?php
  $UI->body();
    $UI->content_start(); ?>

  <div class="content_box">

  <h2 style="font-size: 150%;">Student feedback and Justification (by Group)</h2>

<?php
  if (($assessment) && ($groups_and_marks)) {
      foreach ($group_members as $group_id => $g_members) {
          ?>
      <div style="margin-top: 40px;">
        <h3><?php echo $group_names[$group_id]; ?></h3>
        <p>Overall group mark: <?php echo $groups_and_marks[$group_id]; ?>%.</p>
        <table class="grid" cellpadding="2" cellspacing="1">
        <tr>
          <th>name</th>
          <th>feedback recipient</th>
          <th>feedback / justification<br/> comments</th>
        </tr>
<?php
      $j = 0;

          foreach ($g_members as $i => $member_id) {
              //loop round the array with all the user data, so that we can out put it

              if (!isset($feedback)) {
                  continue;
              }

              foreach ($feedback as $j) {
                  if ($j['marker_id'] == $g_members[$i]) {
                      echo '<tr>';
                      echo "<td style=\"text-align:left\"> {$j['marker']}</td>";
                      echo "<td style=\"text-align:left\">{$j['marked']}</td>";
                      echo "<td style=\"text-align:left\">{$j['feedback']}</td>";
                      echo '</tr>';
                  } else {
                      ?>
                        <tr>
                            <td style="text-align: left;">-</td>
                            <td style="text-align: left;">-</td>
                            <td style="text-align: left;">-</td>
                        </tr>
                        <?php
                  }
              }
          } ?>
        </table>
      </div>
<?php
      }
  } ?>

  </div>

<?php
  $UI->content_end(false, false, false);
}

/*
* --------------------------------------------------------------------------------
* If report type is download CSV
* --------------------------------------------------------------------------------
*/
if ($type == 'download-csv') {
    header('Content-Disposition: attachment; filename="webpa_student_feedback.csv"');
    header('Content-Type: text/csv');

    echo '"Student feedback and Justification (by Group)"'."\n\n";

    if (($assessment) && ($groups_and_marks)) {
        foreach ($group_members as $group_id => $g_members) {
            echo "\"Group\",\"{$group_names[$group_id]}\"\n";
            echo "\"Overall group mark\",\"{$groups_and_marks[$group_id]}\"\n";
            echo "\"Name\",\"feedback recipient\",\"feedback / justification comments\"\n";
            $j = 0;
            foreach ($g_members as $i => $member_id) {
                //loop round the array with all the user data, so that we can out put it
                foreach ($feedback as $j) {
                    if ($j['marker_id'] == $g_members[$i]) {
                        echo "\"{$j['marker']}\",\"{$j['marked']}\",\"{$j['feedback']}\"\n";
                    }
                }
            }
            echo "\n\n";
        }
    }
}
?>
