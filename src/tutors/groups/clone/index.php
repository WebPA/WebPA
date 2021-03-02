<?php
/**
 * WIZARD : Clone own groups
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../../includes/inc_global.php';

use WebPA\includes\classes\GroupHandler;
use WebPA\includes\classes\Wizard;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

// --------------------------------------------------------------------------------
// Initialise wizard

$wizard = new Wizard('clone own groups wizard');
$wizard->cancel_url = '../';

$wizard->add_step(1, 'class_wizardstep_1.php');
$wizard->add_step(2, 'class_wizardstep_2.php');
$wizard->add_step(3, 'class_wizardstep_3.php');

$wizard->set_var('config', $_config);
$wizard->set_var('module', $_module_id);

$group_handler = new GroupHandler();
$wizard->set_var('group_handler', $group_handler);

$wizard->prepare();

$wiz_step = $wizard->get_step();

// --------------------------------------------------------------------------------
// Start the wizard

// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME . ' Clone own groups';
$UI->menu_selected = 'my groups';
$UI->help_link = '?q=node/253';
$UI->breadcrumbs = [
  'home'                      => '../../',
  'my groups'                 => '../',
  'clone groups'              => '../clone/',
  'clone own groups wizard'   => null,
];

$UI->set_page_bar_button('List Groups', '../../../../images/buttons/button_group_list.gif', '../');
$UI->set_page_bar_button('Create Groups', '../../../../images/buttons/button_group_create.gif', '../create/');
$UI->set_page_bar_button('Clone Groups', '../../../../images/buttons/button_group_clone.gif', '../clone/');

$UI->head();
$wizard->head();
$UI->body('onload="body_onload()"');
$UI->content_start();
?>

<p>This wizard takes you through the process of cloning an existing collection of associated groups. When it is complete, you will be able to edit the groups and reassign students.</p>

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
