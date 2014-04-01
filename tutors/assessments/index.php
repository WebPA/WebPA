<?php
/**
 * 
 * My assessments index - show options to create, edit or report.
 * 			
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 * 
 */

require_once("../../include/inc_global.php");
require_once(DOC__ROOT . '/library/classes/class_simple_object_iterator.php');
require_once(DOC__ROOT . '/include/classes/class_assessment.php');
require_once(DOC__ROOT . '/include/classes/class_result_handler.php');
require_once(DOC__ROOT . '/library/functions/lib_form_functions.php');
require_once(DOC__ROOT . '/library/functions/lib_university_functions.php');

if (!check_user($_user, 'staff')){
	header('Location:'. APP__WWW .'/logout.php?msg=denied');
	exit;
}

// --------------------------------------------------------------------------------

$start_year = 2007;

$year = (int) fetch_GET('y',get_academic_year());

$academic_year = $year.'/'. substr($year+1,2,2);
$next_year = $year+1;

$tabs = array ('pending'	=> "?tab=pending&y={$year}" ,
			   'open'	 	=> "?tab=open&y={$year}" ,
			   'closed'		=> "?tab=closed&y={$year}" ,
			   'marked'		=> "?tab=marked&y={$year}" ,
);

$tab = fetch_GET('tab','pending');

switch($tab) {
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

$page_url = APP__WWW . "/tutors/assessments/index.php";

														
// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME . ' my assessments';
$UI->menu_selected = 'my assessments';
$UI->help_link = '?q=node/235';
$UI->breadcrumbs = array	(
	'home' 			=> '../' ,
	'my assessments'	=> null ,
);

$UI->set_page_bar_button('List Assessments', '../../../images/buttons/button_assessment_list.gif', '');
$UI->set_page_bar_button('Create Assessments', '../../../images/buttons/button_assessment_create.gif', 'create/');

$UI->head();
?>
<script language="JavaScript" type="text/javascript">
<!--

	function change_academic_year() {
		year_sbox = document.getElementById('academic_year');
		chosen_year = year_sbox.options[year_sbox.selectedIndex].value;
		if (chosen_year) { window.location.href='<?php echo($page_url ."?tab={$tab}&y="); ?>'+chosen_year; }
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
			foreach($tabs as $label => $url) {
				$tab_status = ($label==$tab) ? 'on' : 'off';
				echo("<td class=\"tab_{$tab_status}\" width=\"100\"><a class=\"tab\" href=\"{$url}\">". ucfirst($label) .'</a></td>');
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
					$todays_year = date('Y');
					for($i=$start_year; $i<=$todays_year; $i++) {
						$selected_str = ($i==$year) ? 'selected="selected"' : '';
						echo("<option value=\"$i\" $selected_str>". $i .'/' . substr($i+1,2,2) .'</option>');
					}
				?>
			</select>
		</td>
		<td><input type="button" name="change_year" value="change" onclick="change_academic_year()" /></td>
	</tr>
	</table>

<?php
	include_once($include_page);
?>

	</form>
</div>

<?php
$UI->content_end();
?>