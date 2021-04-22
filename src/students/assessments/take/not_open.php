<?php
/**
 * Assessment isn't open
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../../includes/inc_global.php';

use WebPA\includes\classes\Assessment;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_STUDENT)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

// --------------------------------------------------------------------------------
// Process GET/POST

$assessment_id = Common::fetch_GET('a');

$list_url = '../index.php';

// --------------------------------------------------------------------------------

$assessment = new Assessment($DB);
if ($assessment->load($assessment_id)) {
} else {
    $assessment = null;
}

// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME . ' ' . ($assessment) ? $assessment->name : 'not open';
$UI->menu_selected = 'my assessments';
$UI->help_link = '?q=node/329';
$UI->breadcrumbs = ['home'       => '/',
               $assessment->name  => null,
              ];

$UI->head();
$UI->content_start();
?>

<div class="content_box">

<?php
if (!$assessment) {
    ?>
  <div class="nav_button_bar">
    <a href="<?php echo $list_url ?>"><img src="/images/buttons/arrow_green_left.gif" alt="back -"> back to assessments list</a>
  </div>

  <p>The assessment you selected could not be loaded for some reason - However, in this case, the assessment is <em>not open and cannot accept student <?php echo APP__MARK_TEXT; ?></em>.</p>
  <p>If the problem loading this assessment persists, please use the contact system to <a href="/students/support/contact/index.php?q=bug">report the error</a>.</p>
<?php
} else {
        ?>
  <div class="nav_button_bar">
    <table cellpadding="0" cellspacing="0" width="100%">
    <tr>
      <td><a href="<?php echo $list_url; ?>"><img src="/images/buttons/arrow_green_left.gif" alt="back -"> back to assessments list</a></td>
    </tr>
    </table>
  </div>

  <p>This assessment is not open at the present time, so you cannot submit any <?php echo APP__MARK_TEXT; ?>.</p>
  <p>If you have missed the deadline, contact your lecturer. Otherwise, if you have another assessment to take, please select it from your <a href="<?php echo $list_url; ?>">assessments list</a>.</p>
<?php
    }
?>
</div>

<?php

$UI->content_end();

?>
