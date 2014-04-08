<?php
/**
 *
 * Clone an assessment
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 *
 */

require_once("../../../includes/inc_global.php");

if (!check_user($_user, APP__USER_TYPE_TUTOR)){
  header('Location:'. APP__WWW .'/logout.php?msg=denied');
  exit;
}

// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME .' Create a new assessment';
$UI->menu_selected = 'my assessments';
$UI->help_link = '?q=node/235';
$UI->breadcrumbs = array  ('home'         => '../../' ,
               'my assessments'   => '../' ,
               'clone an assessment'  => null ,);

$UI->set_page_bar_button('List Assessments', '../../../../images/buttons/button_assessment_list.gif', '../');
$UI->set_page_bar_button('Create Assessments', '../../../../images/buttons/button_assessment_create.gif', '../create/');
$UI->head();
$UI->body('onload="body_onload()"');
$UI->content_start();
?>

<p>This function is not yet available</p>

<div class="content_box">

</div>

<?php

$UI->content_end();

?>
