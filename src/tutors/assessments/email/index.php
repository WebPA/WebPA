<?php
/**
 * WIZARD : Email students taking an assessment
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../../includes/inc_global.php';

use WebPA\includes\classes\Assessment;
use WebPA\includes\classes\Wizard;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

// --------------------------------------------------------------------------------

$assessment_id = Common::fetch_GET('a');

$tab = Common::fetch_GET('tab');
$year = Common::fetch_GET('y', date('Y'));

$list_url = "../index.php?tab={$tab}&y={$year}";

// --------------------------------------------------------------------------------

$assessment = new Assessment($DB);
if ($assessment->load($assessment_id)) {
    $assessment_qs = "a={$assessment->id}&tab={$tab}&y={$year}";
} else {
    $assessment = null;
}

// --------------------------------------------------------------------------------
// Initialise wizard

if ($assessment) {
    $wizard = new Wizard('email your students wizard');
    $wizard->set_wizard_url("index.php?a={$assessment->id}&tab={$tab}&y={$year}");

    $wizard->set_field('list_url', $list_url);
    $wizard->cancel_url = $wizard->get_field('list_url');

    $wizard->add_step(1, 'class_wizardstep_1.php');
    $wizard->add_step(2, 'class_wizardstep_2.php');
    $wizard->add_step(3, 'class_wizardstep_3.php');
    $wizard->add_step(4, 'class_wizardstep_4.php');

    $wizard->show_steps(3); // Hide the last step from the user

    $wizard->set_var('db', $DB);
    $wizard->set_var('config', $_config);
    $wizard->set_var('user', $_user);
    $wizard->set_var('cis', $CIS);
    $wizard->set_var('assessment', $assessment);

    $wizard->prepare();

    $wiz_step = $wizard->get_step();
}

// --------------------------------------------------------------------------------
// Start the wizard

// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME . ' email your students';
$UI->menu_selected = 'my assessments';
$UI->help_link = '?q=node/235';
$UI->breadcrumbs = [
  'home'        => '../../',
  'my assessments'  => '../',
  'email students'  => null,
];

$UI->set_page_bar_button('List Assessments', '../../../../images/buttons/button_assessment_list.gif', '../');
$UI->set_page_bar_button('Create Assessments', '../../../../images/buttons/button_assessment_create.gif', '../create/');

$UI->head();
if ($assessment) {
    $wizard->head();
    $UI->body('onload="body_onload()"');
} else {
    $UI->body();
}
$UI->content_start();
?>

<p>This wizard takes you through the process of sending an email to the students taking this assessment.</p>

<?php
if ($assessment) {
    $wizard->title();
    $wizard->draw_errors();
}
?>

<div class="content_box">

<?php
if ($assessment) {
    $wizard->draw_wizard();
} else {
    echo '<p>The given assessment failed to load so this wizard cannot be started.</p>';
}
?>

</div>

<?php

$UI->content_end();

?>
