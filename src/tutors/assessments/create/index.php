<?php
/**
 * WIZARD : Create a new Assessment
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../../includes/inc_global.php';

use WebPA\includes\classes\Wizard;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

// --------------------------------------------------------------------------------
// Initialise wizard

$wizard = new Wizard('create a new assessment wizard');
$wizard->cancel_url = '../';

$wizard->add_step(1, 'class_wizardstep_1.php');
$wizard->add_step(2, 'class_wizardstep_2.php');
$wizard->add_step(3, 'class_wizardstep_3.php');
$wizard->add_step(4, 'class_wizardstep_4.php');
$wizard->add_step(5, 'class_wizardstep_5.php');
$wizard->add_step(6, 'class_wizardstep_6.php');

$wizard->set_var('db', $DB);
$wizard->set_var('config', $_config);
$wizard->set_var('user', $_user);
$wizard->set_var('module', $_module);
$wizard->set_var('moduleId', $_module_id);

$wizard->prepare();

$wiz_step = $wizard->get_step();

// --------------------------------------------------------------------------------
// Start the wizard

// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME . ' Create a new assessment';
$UI->menu_selected = 'my assessments';
$UI->help_link = '?q=node/235';
$UI->breadcrumbs = ['home'               => '../../',
               'my assessments'         => '../',
               'create a new assessment wizard' => null, ];

$UI->set_page_bar_button('List Assessments', '../../../../images/buttons/button_assessment_list.gif', '../');
$UI->set_page_bar_button('Create Assessments', '../../../../images/buttons/button_assessment_create.gif', '../create/');

$UI->head();
$wizard->head();
$UI->body('onload="body_onload()"');
$UI->content_start();
?>

<p>This wizard takes you through the process of creating a new assessment. When it is complete, you will have scheduled your assessment, and set the form and groups to assess.</p>

<?php
$wizard->title();
$wizard->draw_errors();
?>

<div class="content_box">

<?php
  $wizard->draw_wizard();
?>

</div>

<?php

$UI->content_end();

?>
