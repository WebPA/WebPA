<?php
/**
 *
 * Preview a form
 *
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 *
 */

require_once("../../../includes/inc_global.php");
require_once(DOC__ROOT . 'includes/classes/class_form.php');
require_once(DOC__ROOT . 'includes/classes/class_form_renderer.php');
require_once(DOC__ROOT . 'includes/functions/lib_form_functions.php');

if (!check_user($_user, APP__USER_TYPE_TUTOR)){
  header('Location:'. APP__WWW .'/logout.php?msg=denied');
  exit;
}

// --------------------------------------------------------------------------------
// Process GET/POST

$form_id = fetch_GET('f');

$intro_text = fetch_GET('i', null);
if ($intro_text) {
  $intro_text = base64_decode($intro_text);
} else {
  $intro_text = gettext('<< Your introduction text will go in here >>');
}

$form = new Form($DB);
$form->load($form_id);

$form_renderer = new FormRenderer();

$form_renderer->set_form($form);

$people = array (
  'fake1' => '<em>'.gettext('Yourself').'</em>' ,
  'fake2' => 'Alice' ,
  'fake3' => 'Bob' ,
  'fake4' => 'Claire' ,
  'fake5' => 'David' ,
);

$form_renderer->set_participants($people);

// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME.' '.gettext('Preview Form');
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
    <td><a href="#fakelink"><img src="../../../images/buttons/arrow_green_left.gif" alt="<?php echo gettext('back');?> -"> "<?php echo gettext('back to assessments list');?></a> &lt;&lt;<?php echo gettext('disabled in preview');?>&gt;&gt;</td>
  </tr>
  </table>
</div>

<h3><?php echo gettext('Taking This Assessment');?></h3>
<div class="form_section">
  <p><?php echo gettext('Please complete the assessment below. For each question <em>you must give a mark to each group member</em>, including yourself.');?></p>
  <p><?php echo gettext('To save your marks, you must click the <em>Save Marks</em> button.  Once you have successfully submitted your responses you cannot go back and change your marks.');?></p>
  <p><?php echo gettext('To leave this assessment without saving, click the <em>back to assessments list</em> link above, or choosing an option from the menu.');?></p>
</div>

<h3><?php echo gettext('Marking Your Team');?></h3>
<div class="form_section">
  <?php $form_renderer->draw_description(); ?>
</div>

<?php
if (!empty($intro_text)) {
  ?>
  <h3><?php echo gettext('Introduction');?></h3>
  <div class="form_section">
    <p class="introduction"><?php echo(nl2br(htmlentities($intro_text))); ?></p>
  </div>
  <?php
}
?>

<div class="form_line">
<h2><?php echo gettext('Assessment Criteria');?></h2>
  <?php
    $form_renderer->draw_form();
  ?>
</div>

<p><?php echo gettext('That concludes this peer assessment. To finish and submit your response click the <em>save marks</em> button below.');?></p>
<p><?php echo gettext('Once you have successfully submitted your responses you cannot go back and change your marks.');?></p>

<center>
  <input type="button" name="save_button" value="<?php echo gettext('save marks');?>" onclick="'alert('<?php echo gettext('disabled in preview');?>'" />
</center>

</div>

<div style="margin-top: 20px; padding-top: 10px; border-top: 1px solid #ccc; text-align: center;">
    <input type="button" name="closebutton" value="<?php echo gettext('close preview');?>" onclick="window.close()" />
  </form>
</div>

</form>

<?php

$UI->content_end(false, false, false);

?>
