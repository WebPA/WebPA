<?php
/**
 * INDEX - Tutor index
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../includes/inc_global.php';

use WebPA\includes\functions\Common;
use WebPA\lang\en\Generic;
use WebPA\lang\en\tutors\Tutors;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME;
$UI->menu_selected = 'home';
$UI->help_link = '?q=node/233';
$UI->breadcrumbs = [
  'home'      => null,
];
$UI->head();
$UI->body();

$UI->content_start();
?>

<?php
echo '<p>' . Tutors::WELCOME . '</p>';
echo '<p>' . Tutors::SECTIONS__INTRO . '</p>';
?>

<table class="option_list" style="width: 500px;">
<tr>
  <td><a href="forms/"><img src="../images/icons/form.gif" width="32" height="32" alt="<?php echo Generic::MY__FORMS; ?>" /></a></td>
  <td>
    <div class="option_list">
      <div class="option_list_title"><a class="hidden" href="forms/"><?php echo Generic::MY__FORMS; ?></a></div>
      <p><?php echo Tutors::OPT__FORMS__DESC; ?></p>
    </div>
  </td>
</tr>
<tr>
  <td><a href="groups/"><img src="../images/icons/groups.gif" width="32" height="32" alt="<?php echo Generic::MY__GROUPS; ?>" /></a></td>
  <td>
    <div class="option_list">
      <div class="option_list_title"><a class="hidden" href="groups/"><?php echo Generic::MY__GROUPS; ?></a></div>
      <p><?php echo Tutors::OPT__GROUPS__DESC; ?></p>
    </div>
  </td>
</tr>
<tr>
  <td><a href="assessments/"><img src="../images/icons/assessments.gif" width="32" height="32" alt="<?php echo Generic::MY__ASSESSMENTS; ?>" /></a></td>
  <td>
    <div class="option_list">
      <div class="option_list_title"><a class="hidden" href="assessments/"><?php echo Generic::MY__ASSESSMENTS; ?></a></div>
      <p><?php echo Tutors::OPT__ASSESSMENTS__DESC ?></p>
    </div>
  </td>
</tr>
</table>

<h2><?php echo Tutors::GETTING__STARTED__TITLE ; ?></h2>
<p><?php echo Tutors::GETTING__STARTED__DESC ; ?></p>


<?php

$UI->content_end();
