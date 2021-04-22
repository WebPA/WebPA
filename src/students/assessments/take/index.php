<?php
/**
 * Take an assessment
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../../includes/inc_global.php';

use Doctrine\DBAL\ParameterType;
use WebPA\includes\classes\Assessment;
use WebPA\includes\classes\Form;
use WebPA\includes\classes\FormRenderer;
use WebPA\includes\classes\GroupHandler;
use WebPA\includes\classes\ResultHandler;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_STUDENT)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

// --------------------------------------------------------------------------------
// Process GET/POST

$assessment_id = Common::fetch_GET('a');

$command = Common::fetch_POST('command');

$list_url = '../index.php';

// --------------------------------------------------------------------------------

$assessment = new Assessment($DB);
if ($assessment->load($assessment_id)) {
    $assessment_qs = "a={$assessment->id}";

    // Check if the assessment is open
    if ($assessment->get_status()!='open') {
        header('Location: '. APP__WWW ."/students/assessments/take/not_open.php?{$assessment_qs}");
        exit;
    }

    // Check if the user has submitted his results already
    $result_handler = new ResultHandler($DB);
    $result_handler->set_assessment($assessment);

    if ($result_handler->user_has_responded($_user->id, $assessment->id)) {
        header('Location: '. APP__WWW ."/students/assessments/take/already_submitted.php?{$assessment_qs}");
        exit;
    }

    // Get the form to be displayed
    $form = new Form($DB);

    $form_xml = $assessment->get_form_xml();
    $form->load_from_xml($form_xml);

    // Get the collection being used
    $group_handler = new GroupHandler();
    $collection = $group_handler->get_collection($assessment->get_collection_id());

    if ($collection) {
        // Get the group this user belongs to
        $groups = $collection->get_member_groups($_user->id);
        if ($groups) {
            $group =& $groups[0];

            // Get the members and process them for use in the assessment
            $members = $group->get_members();
            $users = $CIS->get_user(array_keys($members));

            $people = null;
            $people["{$_user->id}"] = 'Yourself';   // Current user comes first

            foreach ($users as $i => $user) {
                if ($user['user_id']!=$_user->id) {
                    $people["{$user['user_id']}"] = $user['lastname'].', '.$user['forename'];
                }
            }

            //check the assessment type that is being carried out and remove
            // If this is a peer-only assessment, remove the Yourself option
            if ($assessment->assessment_type == '0') {
                $index = array_search('Yourself', $people);
                if ($index !== false) {
                    unset($people[$index]);
                }
            }

            // Create the form_renderer
            $form_renderer = new FormRenderer();
            $form_renderer->set_form($form);
            $form_renderer->participant_id = $_user->id;
            $form_renderer->set_participants($people);
            $form_renderer->assessment_feedback = $assessment->allow_assessment_feedback;
            $form_renderer->assessment_feedback_title = $assessment->feedback_name;
        }
    }
} else {
    $assessment = null;
}

// --------------------------------------------------------------------------------
// Process Form

$errors = null;
$error_style_ids = null;

$results = null;
$justification = null;

if (($command) && ($assessment)) {
    switch ($command) {
    case 'save':
      // Check date/time of submission
      $now = time();
      if ($now>$assessment->close_date) {
          $errors[] = 'You were too late in submitting your answers, the assessment is now closed.';
      } else {
          $participant_count = count($people);
          $remainder = 100 % $participant_count;
          $group_total = 100 - $remainder;

          $question_count = $form->get_question_count();
          $questions = null;

          if (!$question_count>0) {
              $errors[] = 'Unable to load question data!';
          } else {
              if ($form->type == 'split100') {

            // Loop through every question and total up the scores for each one

                  $q_total_marks = [];

                  for ($q=0; $q<$question_count; $q++) {
                      $q_total_marks[$q] = 0;

                      $q_num = $q + 1;

                      // Loop through every person
                      foreach ($people as $id => $name) {
                          $q_id = "q_{$q}_{$id}";
                          $score = Common::fetch_POST($q_id, null);

                          if (($score!=0) && (empty($score))) {
                              $errors[] = "You didn't give a " . APP__MARK_TEXT . " for '$name' in Q{$q_num}.";
                              $error_style_ids["$q_id"] = 1;
                          } else {
                              if (!is_numeric($score)) {
                                  $errors[] = "You didn't give a valid " . APP__MARK_TEXT . " for '$name' in Q{$q_num}.";
                                  $error_style_ids["$q_id"] = 1;
                              } else {
                                  $score = (int) $score;
                                  if (($score<0) || ($score>$group_total)) {
                                      $errors[] = 'Your ' . APP__MARK_TEXT . " of $score for '$name' in Q{$q_num} is invalid.<br /> " . APP__MARK_TEXT . " must be between 0 and $group_total.";
                                      $error_style_ids["$q_id"] = 1;
                                  }
                                  $results["$q"]["$id"] = $score;
                                  $q_total_marks[$q] += $score;
                              }
                          }
                      }// /foreach $people
                  } // /for $q

            // Loop through the questions again, and check the totals add up to the valid group total
                  for ($q=0; $q<$question_count; $q++) {
                      $q_total = $q_total_marks[$q];

                      $q_num = $q + 1;

                      if ($q_total!==$group_total) {
                          $errors[] = 'The total ' . APP__MARK_TEXT . " for Q{$q_num} should be $group_total.  You have " . APP__MARK_TEXT . " totalling $q_total.";
                      }
                  }
              } else {
                  // Loop through every question
                  for ($q=0; $q<$question_count; $q++) {
                      $question = $form->get_question($q);

                      $q_num = $q + 1;

                      $range = explode('-', $question['range']['_data']);
                      $min_score = $range[0];
                      $max_score = $range[1];

                      // Loop through every person
                      foreach ($people as $id => $name) {
                          $q_id = "q_{$q}_{$id}";
                          $score = Common::fetch_POST($q_id, null);

                          if (is_null($score)) {
                              $errors[] = "You didn't give a " . APP__MARK_TEXT . " for '$name' in Q{$q_num}.";
                              $error_style_ids["$q_id"] = 1;
                          } else {
                              // Check the score is within the allowed range
                              $score = (int) $score;
                              if (($score>=$min_score) && ($score<=$max_score)) {
                                  $results["$q"]["$id"] = $score;
                              } else {
                                  $errors[] = 'The ' . APP__MARK_TEXT . " of '$score' given in Q{$q_num} for '$name' is not allowed. " . APP__MARK_TEXT . " must be between $min_score and $max_score.";
                                  $error_style_ids["$q_id"] = 1;
                              }
                          }
                      }// /foreach $people
                  } // /for $q
              }// /if-else(likert)

          //check the options to see if the justification is provided
              if (APP__ALLOW_TEXT_INPUT) {
                  if ($assessment->allow_assessment_feedback) {
                      //get the results and add them all to an array
                      foreach ($people as $id => $name) {
                          $justification_fetch = strip_tags(Common::fetch_POST($id));

                          if (!is_null($justification_fetch)) {
                              $justification[] = ['assessment_id'    =>  $assessment->id,
                                   'group_id'       =>  $group->id,
                                   'user_id'        =>  $_user->id,
                                   'marked_user_id'   =>  $id,
                                   'justification_text' =>  $justification_fetch,
                                   'date_marked'      =>  date(MYSQL_DATETIME_FORMAT, time()), ];
                          }
                      }
                  }
              }
          }
      }

      $form_renderer->set_results($results);

      // If there were no errors, save the changes
      if (!$errors) {
          // Save the results
          $now = date(MYSQL_DATETIME_FORMAT, time());

          // Get IP and Computer name of the student saving the marks
          $ip_address = Common::fetch_SERVER('REMOTE_ADDR', '');
          $computer_name = Common::fetch_SERVER('REMOTE_HOST', '');
          $date_opened = Common::fetch_POST('date_opened');

          // find out if a user response already exists
          $dbConn = $DB->getConnection();

          $existingResponseQuery =
            'SELECT assessment_id ' .
            'FROM ' . APP__DB_TABLE_PREFIX . 'user_response ' .
            'WHERE assessment_id = ? ' .
            'AND group_id = ? ' .
            'AND user_id = ?';

          $userResponse = $dbConn->fetchOne(
              $existingResponseQuery,
              [$assessment->id, $group->id, $_user->id],
              [ParameterType::STRING, ParameterType::STRING, ParameterType::INTEGER]
          );

          $queryBuilder = $dbConn->createQueryBuilder();

          if (!$userResponse) {
              // user response does not exist so create it
              $queryBuilder
                ->insert(APP__DB_TABLE_PREFIX . 'user_response')
                ->values([
                    'assessment_id'   => '?',
                    'group_id'        => '?',
                    'user_id'         => '?',
                    'ip_address'      => '?',
                    'comp_name'       => '?',
                    'date_responded'  => '?',
                    'date_opened'     => '?',
                ])
                ->setParameter(0, $assessment->id)
                ->setParameter(1, $group->id)
                ->setParameter(2, $_user->id, ParameterType::INTEGER)
                ->setParameter(3, $ip_address)
                ->setParameter(4, $computer_name)
                ->setParameter(5, $now)
                ->setParameter(6, $date_opened);
          } else {
              // user response exists so update it
              $queryBuilder
                ->update(APP__DB_TABLE_PREFIX . 'user_response')
                ->set('ip_address', '?')
                ->set('comp_name', '?')
                ->set('date_responded', '?')
                ->set('date_opened', '?')
                ->where('assessment_id', '?')
                ->andWhere('group_id', '?')
                ->andWhere('user_id', '?')
                ->setParameter(0, $ip_address)
                ->setParameter(1, $computer_name)
                ->setParameter(2, $now)
                ->setParameter(3, $date_opened)
                ->setParameter(4, $assessment->id)
                ->setParameter(5, $group->id)
                ->setParameter(6, $_user->id, ParameterType::INTEGER);
          }

          $queryBuilder->execute();

          foreach ($results as $q => $q_results) {
              foreach ($q_results as $id => $score) {
                  $DB->getConnection()
                  ->createQueryBuilder()
                  ->insert(APP__DB_TABLE_PREFIX . 'user_mark')
                  ->values([
                      'assessment_id'  => '?',
                      'group_id'       => '?',
                      'user_id'        => '?',
                      'marked_user_id' => '?',
                      'question_id'    => '?',
                      'score'          => '?',
                      'date_marked'    => '?',
                  ])
                  ->setParameter(0, $assessment->id)
                  ->setParameter(1, $group->id)
                  ->setParameter(2, $_user->id, ParameterType::INTEGER)
                  ->setParameter(3, $id, ParameterType::INTEGER)
                  ->setParameter(4, $q, ParameterType::INTEGER)
                  ->setParameter(5, $score, ParameterType::INTEGER)
                  ->setParameter(6, $now)
                  ->execute();
              }
          }

          // along with the saved marks we want to save the justification section
          if (is_iterable($justification)) {
              foreach ($justification as $userJustification) {
                  $DB->getConnection()
                      ->createQueryBuilder()
                      ->insert(APP__DB_TABLE_PREFIX . 'user_justification')
                      ->values([
                          'assessment_id' => '?',
                          'group_id' => '?',
                          'user_id' => '?',
                          'marked_user_id' => '?',
                          'justification_text' => '?',
                          'date_marked' => '?',
                      ])
                      ->setParameter(0, $userJustification['assessment_id'])
                      ->setParameter(1, $userJustification['group_id'])
                      ->setParameter(2, $userJustification['user_id'])
                      ->setParameter(3, $userJustification['marked_user_id'])
                      ->setParameter(4, $userJustification['justification_text'])
                      ->setParameter(5, $userJustification['date_marked'])
                      ->execute();
              }
          }


          Common::logEvent($DB, 'Assessment submission successful', $_module_id, $assessment->id);

          header('Location: '. APP__WWW ."/students/assessments/take/finished.php?{$assessment_qs}");
          exit;
      }
          Common::logEvent($DB, 'Assessment submission failed', $_module_id, $assessment->id);
      

      break;
  }// /switch
}

// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME . ' ' . $assessment->name;
$UI->menu_selected = 'my assessments';
$UI->help_link = '?q=node/329';
$UI->breadcrumbs = [
  'home'             => '/',
  $assessment->name  => null,
];

$UI->head();
?>
<style type="text/css">
<!--

<?php

// rows to highlight because they have errors:
if (is_array($error_style_ids)) {
    foreach ($error_style_ids as $id => $v) {
        echo "#{$id} td { background-color: #fcc; }\n";
    }
}

?>

-->
</style>
<script language="JavaScript" type="text/javascript">
<!--

  function do_command(com) {
    switch (com) {
      default :
            document.assessment_form.command.value = com;
            document.assessment_form.submit();
    }
  }// /do_command()

//-->
</script>
<?php
if ($assessment) {
    $form_renderer->draw_head();
}
$UI->content_start();

$UI->draw_boxed_list($errors, 'error_box', 'The following errors were found:', 'Your responses have not been saved. Please check the details in the form, and try again.');
?>

<div class="content_box">

<?php
if (!$assessment) {
    ?>
  <div class="nav_button_bar">
    <a href="<?php echo $list_url ?>"><img src="../../../images/buttons/arrow_green_left.gif" alt="back -"> back to assessments list</a>
  </div>

  <p>The assessment you selected could not be loaded for some reason - please go back and try again.</p>
  <p>If the problem persists, please use the contact system to <a href="/students/support/contact/index.php?q=bug">report the error</a>.</p>
<?php
} else {
        ?>
  <form action="index.php?<?php echo $assessment_qs; ?>" method="post" name="assessment_form">
  <input type="hidden" name="command" value="none" />
  <input type="hidden" name="date_opened" value="<?php echo date(MYSQL_DATETIME_FORMAT, time()); ?>" />

  <div class="nav_button_bar">
    <table cellpadding="0" cellspacing="0" width="100%">
    <tr>
      <td><a href="<?php echo $list_url; ?>"><img src="../../../images/buttons/arrow_green_left.gif" alt="back -"> back to assessments list</a></td>
    </tr>
    </table>
  </div>


  <h3>Taking This Assessment</h3>
  <div class="form_section">
    <p>Please complete the assessment below. For each question <em>you must give a <?php echo APP__MARK_TEXT; ?> to each group member</em><?php if ($assessment->assessment_type != '0') {
            echo ', including yourself';
        } ?>.</p>
    <p>To save your <?php echo APP__MARK_TEXT; ?>, you must click the <em>Save <?php echo APP__MARK_TEXT; ?></em> button.  Once you have successfully submitted your responses you cannot go back and change your <?php echo APP__MARK_TEXT; ?>.</p>
    <p>To leave this assessment without saving, click the <em>back to assessments list</em> link above, or choosing an option from the menu.</p>
  </div>


  <h3><?php echo APP__MARK_TEXT; ?> Your Team</h3>
  <div class="form_section">
<?php
  $form_renderer->draw_description(); ?>
  </div>

<?php
  if (!empty($assessment->introduction)) {
      ?>
    <h3>Introduction</h3>
    <div class="form_section">
      <p class="introduction"><?php echo nl2br(htmlentities($assessment->introduction)); ?></p>
    </div>
<?php
  } ?>

  <div class="form_line">
  <h2>Assessment Criteria</h2>
<?php
  $form_renderer->draw_form(); ?>
  </div>

  <p>That concludes this peer assessment. To finish and submit your response click the <em>save <?php echo APP__MARK_TEXT; ?></em> button below.</p>
  <p>Once you have successfully submitted your responses you cannot go back and change your <?php echo APP__MARK_TEXT; ?>.</p>

  <center>
    <input type="button" name="save_button" value="save <?php echo APP__MARK_TEXT; ?>" onclick="do_command('save');" />
  </center>

  </form>
<?php
  Common::logEvent($DB, 'Assessment started', $_module_id, $assessment->id);
    }
?>
</div>

<?php

$UI->content_end();

?>
