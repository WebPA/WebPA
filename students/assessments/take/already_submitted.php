<?php
/**
 *
 * Already Submitted Assessment
 *
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 *
 */

require_once("../../../includes/inc_global.php");
require_once('../../../includes/classes/class_assessment.php');

if (!check_user($_user, APP__USER_TYPE_STUDENT)){
  header('Location:'. APP__WWW .'/logout.php?msg=denied');
  exit;
}

// --------------------------------------------------------------------------------
// Process GET/POST

$assessment_id = fetch_GET('a');

$list_url = '../index.php';

// --------------------------------------------------------------------------------

$assessment = new Assessment($DB);
if ($assessment->load($assessment_id)) {

} else {
  $assessment = null;
}


// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = ($assessment) ? $assessment->name : "already submitted";
$UI->menu_selected = 'my assessments';
$UI->help_link = '?q=node/329';

$UI->breadcrumbs = array  ('home'       => '/' ,
               $assessment->name  => null ,
              );
$UI->head();
$UI->content_start();
?>

<div class="content_box">

<?php
if (!$assessment) {
  ?>
  <div class="nav_button_bar">
    <a href="<?php echo($list_url) ?>"><img src="/images/buttons/arrow_green_left.gif" alt="back -"> back to assessments list</a>
  </div>

  <p>The assessment you selected could not be loaded for some reason - However, you have already submitted your <?php echo APP__MARK_TEXT; ?> for this assessment.</p>
  <p>If the problem loading this assessment persists, please use the contact system to <a href="/students/support/contact/index.php?q=bug">report the error</a>.</p>
<?php
} else {
?>
  <div class="nav_button_bar">
    <table cellpadding="0" cellspacing="0" width="100%">
    <tr>
      <td><a href="<?php echo($list_url); ?>"><img src="/images/buttons/arrow_green_left.gif" alt="back -"> back to assessments list</a></td>
    </tr>
    </table>
  </div>

  <p>You have already submitted your <?php echo APP__MARK_TEXT; ?> for this assessment: <?php echo($assessment->name); ?>.</p>
  <p>If you have another assessment to take, please select it from your <a href="<?php echo($list_url); ?>">assessments list</a>.</p>
<?php
}
?>
</div>

<?php

$UI->content_end();

?>
