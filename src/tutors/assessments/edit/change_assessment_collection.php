<?php
/**
 * Change assessment Collection
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../../includes/inc_global.php';

use WebPA\includes\classes\Assessment;
use WebPA\includes\classes\GroupHandler;
use WebPA\includes\classes\SimpleObjectIterator;
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

$new_collection_id = Common::fetch_POST('collection_id');
$command = Common::fetch_POST('command');

// --------------------------------------------------------------------------------

$group_handler = new GroupHandler();

$assessment = new Assessment($DB);

if ($assessment->load($assessment_id)) {
    $assessment_qs = "a={$assessment->id}&tab={$tab}&y={$year}";
    $assessment_url = "edit_assessment.php?{$assessment_qs}";
    $collection = $group_handler->get_collection($assessment->get_collection_id());
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
          // Change the collection of groups to assess

          if (!$new_collection_id) {
              $errors[] = 'You must select a collection of associated groups to use.';
          } else {
              // If the new collection is the same as the old collection, do nothing
              if ($new_collection_id!=$collection->id) {
                  if (!$collection) {
                      $errors[] = 'There was an error when trying to remove the original collection of groups - please use the contact system to report the error!';
                  }

                  // clone the collection
                  $new_collection = $group_handler->clone_collection($new_collection_id);

                  if (!$new_collection) {
                      $errors[] = 'There was an error when loading the new collection of groups - please use the contact system to report the error!';
                  }

                  if (!$errors) {
                      // Connect the collection to the assessment
                      $new_collection->set_owner_info($assessment->id, APP__COLLECTION_ASSESSMENT);
                      $new_collection->save();
                      $assessment->set_collection_id($new_collection->id);
                      $assessment->save();

                      // Delete the old collection
                      $collection->delete();
                  }
              }
          }

          // If there were no errors, reload the assessment
          if (!$errors) {
              header("Location: $assessment_url");
          }
      }
      break;
  }
}

// --------------------------------------------------------------------------------
// Begin Page

$page_title = 'change collection';
$manage_text = ($assessment) ? "manage: {$assessment->name}" : 'manage assessment';

$UI->page_title = APP__NAME . ' ' . $page_title;
$UI->menu_selected = 'my assessments';
$UI->help_link = '?q=node/235';
$UI->breadcrumbs = ['home'         => '/',
               'my assessments'   => '/tutors/assessments/',
               $manage_text     => $assessment_url,
               'change collection'  => null, ];

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

    <form action="change_assessment_collection.php?<?php echo $assessment_qs; ?>" method="post" name="assessment_form">
    <input type="hidden" name="command" value="none" />

    <h2>Current Collection</h2>
    <div class="form_section form_line">
<?php
    echo "<p><label>You are currently using collection: </label><em>{$collection->name}</em></p>";

//    $modules = (is_array($collection->get_modules())) ? implode(', ',$collection->get_modules()) : 'none' ;
      $group_count = count($collection->get_groups_array());

//    echo("  <div style=\"margin-left: 50px; font-size: 84%;\"><div>Associated Modules : $modules</div><div>Number of Groups : $group_count</div></div>");
      echo "  <div style=\"margin-left: 50px; font-size: 84%;\"><div>Number of Groups : $group_count</div></div>"; ?>
    </div>


    <h2>Available Collections</h2>
    <div class="form_section">
<?php

    $group_handler = new GroupHandler();
      $collections = $group_handler->get_user_collections($_user->id);

      if (!$collections) {
          ?>
        <p>You haven't yet created any group collections.</p>
        <p>You need to <a href="../../groups/create/">create some groups</a> before you will be able to run any peer assessments.</p>
<?php
      } else {
          $collection_iterator = new SimpleObjectIterator($collections, 'GroupCollection', $DB); ?>
        <p>Please select the collection of groups you wish to use in this assessment from from the list below.</p>
        <div class="form_section">
          <table class="form" cellpadding="0" cellspacing="0">
<?php
      for ($collection_iterator->reset(); $collection_iterator->is_valid(); $collection_iterator->next()) {
          $new_collection = $collection_iterator->current();

          $group_count = count($new_collection->get_groups_array());
//        $modules = (is_array($new_collection->get_modules())) ? implode(', ',$new_collection->get_modules()) : 'none' ;

          echo '<tr>';
          echo "  <td><input type=\"radio\" name=\"collection_id\" id=\"collection_{$new_collection->id}\" value=\"{$new_collection->id}\" /></td>";
          echo "  <td><label class=\"small\" for=\"collection_{$new_collection->id}\">{$new_collection->name}</label>";
//        echo("  <div style=\"margin-left: 10px; font-size: 84%;\"><div>Associated Modules : $modules</div><div>Number of Groups : $group_count</div></div></td>");
          echo "  <div style=\"margin-left: 10px; font-size: 84%;\"><div>Number of Groups : $group_count</div></div></td>";
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
