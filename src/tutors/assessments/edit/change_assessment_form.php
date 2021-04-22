<?php
/**
 *
 * Change assessment form
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 *
 */

require_once '../../../includes/inc_global.php';

use Doctrine\DBAL\ParameterType;
use WebPA\includes\classes\Assessment;
use WebPA\includes\classes\Form;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

// --------------------------------------------------------------------------------
// Process GET/POST

$assessment_id = Common::fetch_GET('a');

$tab = Common::fetch_GET('tab');
$year = Common::fetch_GET('y', date('Y'));

$form_id = Common::fetch_POST('form_id');
$command = Common::fetch_POST('command');

// --------------------------------------------------------------------------------

$assessment = new Assessment($DB);
if ($assessment->load($assessment_id)) {
    $assessment_qs = "a={$assessment->id}&tab={$tab}&y={$year}";
    $assessment_url = "edit_assessment.php?{$assessment_qs}";

    $form = new Form($DB);
    $form_xml =& $assessment->get_form_xml();
    $form->load_from_xml($form_xml);
} else {
    $assessment = null;
    $assessment_url = '../';
}

// --------------------------------------------------------------------------------
// Process Form

$errors = null;

if (($command) && ($assessment)) {
    switch ($command) {
    case 'save':
      if ($assessment->is_locked()) {
          $errors[] = 'This assessment has been marked, and therefore the assessment form being used cannot be changed.';
      } else {
          if (!$form_id) {
              $errors[] = 'You must select an assessment form to use.';
          } else {
              // Change of name
              $new_form = new Form($DB);
              $new_form->load($form_id);
              $assessment->set_form_xml($new_form->get_xml());
          }

          // If there were no errors, save the changes
          if (!$errors) {
              $assessment->save();
          }

          header("Location: $assessment_url");
      }
      break;
  }// /switch
}

// --------------------------------------------------------------------------------
// Begin Page

$page_title = 'change form';
$manage_text = ($assessment) ? "manage: {$assessment->name}" : 'manage assessment';

$UI->page_title = APP__NAME . ' ' . $page_title;
$UI->menu_selected = 'my assessments';
$UI->help_link = '?q=node/235';
$UI->breadcrumbs = ['home'       => '../../',
               'my assessments' => '../',
               $manage_text   => $assessment_url,
               'change form'    => null, ];

$UI->set_page_bar_button('List Assessments', '../../../../images/buttons/button_assessment_list.gif', '../');
$UI->set_page_bar_button('Create Assessments', '../../../../images/buttons/button_assessment_create.gif', '../create/');

$UI->head();
?>
<style type="text/css">
<!--

table.grid th { text-align: center; }
table.grid td { text-align: center; }

div.question { padding-bottom: 4px; }
span.question_range { font-size: 0.8em; }

-->
</style>
<script language="JavaScript" type="text/javascript">
<!--

  function do_command(com) {
    switch (com) {
      case 'save' :
            document.assessment_form.command.value = com;
            document.assessment_form.submit();
    }
  }// /do_command()

//-->
</script>
<?php
$UI->content_start();

$UI->draw_boxed_list($errors, 'error_box', 'The following errors were found:', 'No changes have been saved. Please check the details in the form and try again.');

?>

<p>On this page you can change the assessment form being used.</p>

<div class="content_box">

<?php
if (!$assessment) {
    ?>
  <div class="nav_button_bar">
    <a href="<?php echo $assessment_url ?>"><img src="../../../images/buttons/arrow_green_left.gif" alt="back -"> back to the assessment</a>
  </div>

  <p>The assessment you selected could not be loaded for some reason - please go back and try again.</p>
<?php
} else {
        ?>
  <div class="nav_button_bar">
    <table cellpadding="0" cellspacing="0" width="100%">
    <tr>
      <td><a href="<?php echo $assessment_url; ?>"><img src="../../../images/buttons/arrow_green_left.gif" alt="back -"> back to the assessment</a></td>
    </tr>
    </table>
  </div>

<?php
  if ($assessment->is_locked()) {
      ?>
    <div class="warning_box">
      <p><strong>Marks have been recorded for this assessment.</strong></p>
      <p>You can still edit the assessment's name, schedule information and introductory text, but you can no longer change which form, or collection of groups, is used in this assessment.</p>
    </div>
    <?php
  } else {
      ?>

    <form action="change_assessment_form.php?<?php echo $assessment_qs; ?>" method="post" name="assessment_form">
    <input type="hidden" name="command" value="none" />

    <h2>Current Form</h2>
    <div class="form_section form_line">
<?php
    echo "<p><label>You are currently using form: </label><em>{$form->name}</em></p>";

      $question_count = (int) $form->get_question_count();
      if ($question_count==0) {
          ?>
        <p>This form has no questions.</p>
<?php
      } else {
          ?>
        <p>Below are the questions that your students will use to mark each other.</p>

        <ul class="compact">
<?php
        for ($i=0; $i<$question_count; $i++) {
            $question = $form->get_question($i); ?>
          <li><div class="question"><?php echo $question['text']['_data']; ?> <span class="question_range">(scoring range: <?php echo $question['range']['_data']; ?>)</span></div></li>
<?php
        }
          echo '</ul>';
      } ?>
    </div>

    <h2>Available Forms</h2>
    <div class="form_section">
<?php
      $formsQuery =
          'SELECT f.* ' .
          'FROM ' . APP__DB_TABLE_PREFIX . 'form f ' .
          'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'form_module fm ' .
          'ON f.form_id = fm.form_id ' .
          'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'user_module um ' .
          'ON fm.module_id = um.module_id ' .
          'WHERE um.user_id = ? ' .
          'ORDER BY f.form_name ASC';

      $forms = $DB->getConnection()->fetchAllAssociative($formsQuery, [$_user->id], [ParameterType::INTEGER]);

      if (!$forms) {
          ?>
        <p>You haven't yet created any assessment forms.</p>
<?php
      } else {
          ?>
        <p>Please select a form from the list below. You can see how the form appears to students by clicking <em>preview</em>.</p>

        <div class="form_section">
          <table cellpadding="2" cellspacing="2">
<?php
        foreach ($forms as $i => $new_form) {
            $checked = ($form->id==$new_form['form_id']) ? 'checked="checked"' : '' ;
            $intro_text = base64_encode($assessment->introduction);
            echo '<tr>';
            echo "<td><input type=\"radio\" name=\"form_id\" id=\"form_{$new_form['form_id']}\" value=\"{$new_form['form_id']}\" $checked /></td>";
            echo "<td><label class=\"small\" for=\"form_{$new_form['form_id']}\">{$new_form['form_name']}</label></td>";
            echo "<td>&nbsp; &nbsp; (<a style=\"font-weight: normal; font-size: 84%;\" href=\"/tutors/forms/edit/preview_form.php?f={$new_form['form_id']}&amp;i={$intro_text}\" target=\"_blank\">preview</a>)</td>";
            echo '</tr>';
        } ?>
          </table>
        </div>
<?php
      } ?>

      <div style="text-align: right">
        <input type="button" name="savebutton1" id="savebutton1" value="save changes" onclick="do_command('save');" />
      </div>
    </div>

    </form>
<?php
  }
    }
?>
</div>

<?php

$UI->content_end();

?>
