<?php
/**
 * My assessments index - show options to create, edit or report.
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../includes/inc_global.php';

use WebPA\includes\functions\AcademicYear;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

// --------------------------------------------------------------------------------

$years = $CIS->get_user_academic_years($_user_id);

$start_year = $years[0];
$last_year = $years[1];
$year = (int) Common::fetch_GET('y', Common::fetch_SESSION('year', AcademicYear::get_academic_year()));
$_SESSION['year'] = $year;

$academic_year = strval($year);
if (APP__ACADEMIC_YEAR_START_MONTH > 1) {
    $academic_year .= '/' . substr($year + 1, 2, 2);
}

$this_year = '-';
if (APP__ACADEMIC_YEAR_START_MONTH <= 10) {
    $this_year .= '0';
}
$this_year .= APP__ACADEMIC_YEAR_START_MONTH . '-01 00:00:00';
$next_year = (string) ($year + 1) . $this_year;
$this_year = (string) $year . $this_year;

$tabs = ['pending'  => "?tab=pending&y={$year}",
         'open'   => "?tab=open&y={$year}",
         'closed'   => "?tab=closed&y={$year}",
         'marked'   => "?tab=marked&y={$year}",
];

$tab = Common::fetch_GET('tab', 'pending');

switch ($tab) {
  case 'pending':
        $include_page = 'inc_list_pending.php';
        break;
  case 'open':
        $include_page = 'inc_list_open.php';
        break;
  case 'closed':
        $include_page = 'inc_list_closed.php';
        break;
  case 'marked':
        $include_page = 'inc_list_marked.php';
        break;
  default:
        $tab = 'pending';
        $include_page = 'inc_list_pending.php';
}

$qs = "tab={$tab}&y={$year}";

$page_url = APP__WWW . '/tutors/assessments/index.php';

// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME . ' my assessments';
$UI->menu_selected = 'my assessments';
$UI->help_link = '?q=node/235';
$UI->breadcrumbs = [
  'home'      => '../',
  'my assessments'  => null,
];

$UI->set_page_bar_button('List Assessments', '../../../images/buttons/button_assessment_list.gif', '');
$UI->set_page_bar_button('Create Assessments', '../../../images/buttons/button_assessment_create.gif', 'create/');

$UI->head();
$change_onclick = ' onclick="change_academic_year()"';
?>
<script language="JavaScript" type="text/javascript">
<!--

  function change_academic_year() {
    year_sbox = document.getElementById('academic_year');
    chosen_year = year_sbox.options[year_sbox.selectedIndex].value;
    if (chosen_year) { window.location.href='<?php echo $page_url ."?tab={$tab}&y="; ?>'+chosen_year; }
  }

//-->
</script>
<?php
$UI->body();
$UI->content_start();
?>

<p>Use the tabs below to manage your different categories of assessment.</p>
<p>You can also <a class="button" href="create/">create a new assessment</a></p>

<br />

<div class="tab_bar">
  <table class="tab_bar" cellpadding="0" cellspacing="0">
  <tr>
    <td>&nbsp;</td>
    <?php
      foreach ($tabs as $label => $url) {
          $tab_status = ($label==$tab) ? 'on' : 'off';
          echo "<td class=\"tab_{$tab_status}\" width=\"100\"><a class=\"tab\" href=\"{$url}\">". ucfirst($label) .'</a></td>';
      }
    ?>
    <td>&nbsp;</td>
  </tr>
  </table>
</div>
<div class="tab_content">

  <form action="#" method="post" name="assessment_list_form">

  <table cellpadding="2" cellspacing="2" style="font-size: 0.8em;">
  <tr>
    <td width="100%">&nbsp;</td>
    <td nowrap="nowrap"><label for="academic_year">Academic year to display</label></td>
    <td>
      <select name="academic_year" id="academic_year">
        <?php
          for ($i = $start_year; $i <= $last_year; $i++) {
              $selected_str = ($i == $year) ? 'selected="selected"' : '';
              echo "<option value=\"$i\" $selected_str>". $i;
              if (APP__ACADEMIC_YEAR_START_MONTH > 1) {
                  echo '/' . substr($i + 1, 2, 2);
              }
              echo '</option>';
          }
        ?>
      </select>
    </td>
    <td><input type="button" name="change_year" value="change"<?php echo $change_onclick; ?> /></td>
  </tr>
  </table>

<?php
include_once $include_page;
?>

  </form>
</div>

<?php

$UI->content_end();

?>
