<?php
/**
 * Preview a form
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../../includes/inc_global.php';

use WebPA\includes\classes\Form;
use WebPA\includes\classes\FormRenderer;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

// --------------------------------------------------------------------------------
// Process GET/POST

$form_id = Common::fetch_GET('f');

$intro_text = Common::fetch_GET('i', null);
if ($intro_text) {
    $intro_text = base64_decode($intro_text);
} else {
    $intro_text = '<< Your introduction text will go in here >>';
}

$form = new Form($DB);
$form->load($form_id);

$form_renderer = new FormRenderer();

$form_renderer->set_form($form);

$people = [
  'fake1' => '<em>Yourself</em>',
  'fake2' => 'Alice',
  'fake3' => 'Bob',
  'fake4' => 'Claire',
  'fake5' => 'David',
];

$form_renderer->set_participants($people);

// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME. ' Preview Form';
$UI->help_link = '?q=node/244';
$UI->head();
?>
<style type="text/css">
<!--

#side_bar { display: none; }
#main { margin: 0px; }

-->
</style>

<?php
$form_renderer->draw_head();

$UI->body();
$UI->content_start();
?>

<form action="#" method="post" name="preview_form">
<div class="content_box">

<div class="nav_button_bar">
  <table cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><a href="#fakelink"><img src="../../../images/buttons/arrow_green_left.gif" alt="back -"> back to assessments list</a> &lt;&lt;disabled in preview&gt;&gt;</td>
  </tr>
  </table>
</div>

<h3>Taking This Assessment</h3>
<div class="form_section">
  <p>Please complete the assessment below. For each question <em>you must give a mark to each group member</em>, including yourself.</p>
  <p>To save your marks, you must click the <em>Save Marks</em> button.  Once you have successfully submitted your responses you cannot go back and change your marks.</p>
  <p>To leave this assessment without saving, click the <em>back to assessments list</em> link above, or choosing an option from the menu.</p>
</div>

<h3>Marking Your Team</h3>
<div class="form_section">
  <?php $form_renderer->draw_description(); ?>
</div>

<?php
if (!empty($intro_text)) {
    ?>
  <h3>Introduction</h3>
  <div class="form_section">
    <p class="introduction"><?php echo nl2br(htmlentities($intro_text)); ?></p>
  </div>
  <?php
}
?>

<div class="form_line">
<h2>Assessment Criteria</h2>
  <?php
    $form_renderer->draw_form();
  ?>
</div>

<p>That concludes this peer assessment. To finish and submit your response click the <em>save marks</em> button below.</p>
<p>Once you have successfully submitted your responses you cannot go back and change your marks.</p>

<center>
  <input type="button" name="save_button" value="save marks" onclick="alert('disabled in preview');" />
</center>

</div>

<div style="margin-top: 20px; padding-top: 10px; border-top: 1px solid #ccc; text-align: center;">
    <input type="button" name="closebutton" value="close preview" onclick="window.close()" />
  </form>
</div>

</form>

<?php

$UI->content_end(false, false, false);

?>
