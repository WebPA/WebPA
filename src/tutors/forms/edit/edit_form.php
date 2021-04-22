<?php
/**
 * Edit Form
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../../includes/inc_global.php';

use WebPA\includes\classes\Form;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

// --------------------------------------------------------------------------------
// Process GET/POST

$form_id = Common::fetch_GET('f');

$command = Common::fetch_POST('command', Common::fetch_GET('command'));

// --------------------------------------------------------------------------------


$form = new Form($DB);
if ($form->load($form_id)) {
    $form_qs = "f={$form->id}";
} else {
    $form = null;
}


// --------------------------------------------------------------------------------
// Process Form

$errors = null;

if (($command) && ($form)) {
    switch ($command) {
    case 'save':
      // Change of name
      $form->name = Common::fetch_POST('form_name');
          if (empty($form->name)) {
              $errors[] = 'You must give this form a name.';
          }

      // If there were no errors, save the changes
      if (!$errors) {
          $form->save();
      }
      break;
    // --------------------
    case 'delete':
      if (!$_user->is_staff()) {
          header('Location:'. APP__WWW .'/logout.php?msg=illegal');
          exit;
      }
      $form->delete();
      header('Location: '. APP__WWW .'/tutors/forms/index.php');
      break;
    // --------------------
  }// /switch
}

// --------------------------------------------------------------------------------
// Begin Page

$page_title = ($form) ? "Edit form: {$form->name}" : 'Edit form';

$UI->page_title = APP__NAME . ' ' . $page_title;
$UI->menu_selected = 'my forms';
$UI->help_link = '?q=node/244';
$UI->breadcrumbs = [
  'home'         => '../../',
  'my forms'     => '../',
  $page_title    => null,
];

$UI->set_page_bar_button('List Forms', '../../../../images/buttons/button_form_list.gif', '../');
$UI->set_page_bar_button('Create Form', '../../../../images/buttons/button_form_create.gif', '../create/');
$UI->set_page_bar_button('Clone a Form', '../../../../images/buttons/button_form_clone.gif', '../clone/');
$UI->set_page_bar_button('Import a Form', '../../../../images/buttons/button_form_import.gif', '../import/');

$UI->head();
?>
<script language="JavaScript" type="text/javascript">
<!--

  function do_command(com) {
    switch (com) {
      case 'delete' :
        if (confirm('This assessment form will be deleted.\n\nClick OK to confirm.')) {
          document.assessmentform_form.command.value = 'delete';
          document.assessmentform_form.submit();
        }
        break;
      case 'preview' :
        var popupwin;
        popupwin = window.open('preview_form.php?f=<?php echo $form->id; ?>','preview');
        popupwin.focus();
        break;
      default :
        document.assessmentform_form.command.value = com;
        document.assessmentform_form.submit();
    }
  }// /do_command()

//-->
</script>
<?php
$UI->content_start();

$UI->draw_boxed_list($errors, 'error_box', 'The following errors were found:', 'No changes have been saved. Please check the details in the form, and try again.');

?>

<p>On this page you can change the name of this form, and add/remove assessment criteria.</p>

<div class="content_box">

<?php
if (!$form) {
    ?>
  <div class="nav_button_bar">
    <a href="../"><img src="../../../images/buttons/arrow_green_left.gif" alt="back -"> back to forms list</a>
  </div>

  <p>The form you selected could not be loaded for some reason - please go back and try again.</p>
<?php
} else {
        ?>
  <form action="edit_form.php?<?php echo $form_qs; ?>" method="post" name="assessmentform_form">
  <input type="hidden" name="command" value="none" />

  <div class="nav_button_bar">
    <table cellpadding="0" cellspacing="0" width="100%">
    <tr>
      <td><a href="../"><img src="../../../images/buttons/arrow_green_left.gif" alt="back -"> back to forms list</a></td>
      <td align="right"><input class="warning_button" type="button" name="" value="preview form" onclick="do_command('preview');" /></td>
      <td align="right"><input class="danger_button" type="button" name="" value="delete form" onclick="do_command('delete');" /></td>
    </tr>
    </table>
  </div>

  <h2>Form Name</h2>
  <div class="form_section form_line">
    <p>You can change this form's name using the box below. When you've made your changes, click the <em>save changes</em> button.</p>
    <table class="form" cellpadding="2" cellspacing="2">
    <tr>
      <th><label for="form_name">Name</label></th>
      <td><input type="text" name="form_name" id="form_name" maxlength="100" size="40" value="<?php echo $form->name?>" /></td>
    </tr>
    <tr>
      <th style="vertical-align: top;">Scoring Type</th>
      <td>
        <?= $form->type === 'likert' ? 'Likert Scale' : 'Split 100' ?>
      </td>
    </tr>
    </table>

    <div class="button_bar">
      <input type="button" name="savebutton1" id="savebutton1" value="save changes" onclick="do_command('save');" />
    </div>

  </div>

  <h2>Assessment Criteria</h2><a name="questions"></a>
  <div class="form_section">
<?php
  $question_count = (int) $form->get_question_count();
        if ($question_count==0) {
            ?>
      <p>You have not added any assessment criteria to this form yet. You need to <a class="button" href="../edit/add_question/index.php?<?php echo $form_qs; ?>">add&nbsp;a&nbsp;new&nbsp;criterion</a> before the form can be used.</p>
<?php
        } else {
            ?>
      <p>The group will rate themselves and each other against the assessment criteria you specify.</p>
      <p>e.g. <em>"Ability to communicate"</em> or <em>"Contribution to the analysis of the experimental data"</em>.</p>
      <p>You can edit a criterion by clicking on the <img src="../../../images/buttons/edit.gif" width="16" height="16" alt="edit question" title="edit" /> button, or you can <a class="button" href="../edit/add_question/index.php?<?php echo $form_qs; ?>">add&nbsp;a&nbsp;new&nbsp;criterion</a></p>

      <div class="obj_list">
<?php
    for ($i=0; $i<$question_count; $i++) {
        $question_qs = "{$form_qs}&q=$i";
        $question = $form->get_question($i);

        $edit_url = "edit_question/index.php?$question_qs"; ?>
        <div class="obj">
          <table class="obj" cellpadding="0" cellspacing="0">
          <tr>
            <td class="obj_info" valign="top">
              <div class="obj_name"><a class="text" href="<?php echo $edit_url; ?>"><?php echo $question['text']['_data']; ?></a></div>
<?php
      if (array_key_exists('desc', $question)) {
          $question_desc = (array_key_exists('desc', $question)) ? $question['desc']['_data'] : '' ;
          echo "<div class=\"obj_info_text\">$question_desc</div>";
      }

        if ($form->type!='split100' && isset($question['range'])) {
            ?>
                  <div class="obj_info_text">Scoring range: <?php echo $question['range']['_data']; ?></div>
<?php
        foreach ($question as $k => $v) {
            if (strpos($k, 'scorelabel')===0) {
                $num = str_replace('scorelabel', '', $k);
                echo "<div class=\"obj_info_text\">Score $num : {$v['_data']}</div>";
            }
        }
        } ?>
            </td>

            <td class="button"><a href="<?php echo $edit_url; ?>"><img src="../../../images/buttons/edit.gif" width="16" height="16" alt="edit question" title="edit" /></a></td>

            <td class="button" width="20">
<?php
      echo '<a href="question_action.php?' . $question_qs . '&a=clone"><img src="../../../images/buttons/clone.gif" width="16" height="16" alt="clone" title="clone" /></a>'; ?>
            </td>

            <td class="button" width="20">
<?php
      if ($i>0) {
          echo '<a href="question_action.php?' . $question_qs . '&a=up"><img src="../../../images/buttons/arrow_green_up.gif" width="16" height="16" alt="move up" title="move up" /></a>';
      } else {
          echo '<img src="../../../images/buttons/blank.gif" width="16" height="16" alt="" />';
      } ?>
            </td>

            <td class="button" width="20">
<?php
      if ($i<($question_count-1)) {
          echo '<a href="question_action.php?' . $question_qs . '&a=down"><img src="../../../images/buttons/arrow_green_down.gif" width="16" height="16" alt="move down" title="move down" /></a>';
      } else {
          echo '<img src="../../../images/buttons/blank.gif" width="16" height="16" alt="" />';
      } ?>
            </td>

            <td class="button" width="20"><a href="question_action.php?<?php echo $question_qs; ?>&a=delete"><img src="../../../images/buttons/cross.gif" width="16" height="16" alt="delete" title="delete" /></a></td>
          </tr>
          </table>
        </div>
<?php
    } ?>
      </div>
<?php
        } ?>
  </div>

  </form>
<?php
    }
?>
</div>


<?php

$UI->content_end();

?>
