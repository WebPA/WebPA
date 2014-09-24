<?php
/**
 *
 * Landing page for the admin section
 *
 * This is the landing page for the administration section and acts as a gate way
 * to the other sections within this area of the site.
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 0.0.0.1
 * @since 28 Mar 2007
 *
 */

//get the include file required
require_once("../includes/inc_global.php");

if (!check_user($_user, APP__USER_TYPE_TUTOR)){
  header('Location:'. APP__WWW .'/logout.php?msg=denied');
  exit;
}

 //set the page information
$UI->page_title = APP__NAME;
$UI->menu_selected = gettext('admin home');
$UI->breadcrumbs = array ('home' => null);
$UI->help_link = '?q=node/237';
$UI->head();
$UI->body();
$UI->content_start();

//build the content to be written to the screen

$page_intro = gettext('Welcome to the Administration Area for').' '. APP__NAME . '. '.gettext('In this section you are able to manage the users of the system (both adding new and editing existing)');
if ($_user->is_admin()) {
  $page_intro .= ' '.sprintf(gettext('as well as generate basic reports on the usage of %s (the metrics)'), APP__NAME);
}
$page_intro .= '.<br/><br/>'.gettext('The admin area contains the following sections:');
$menu = $UI->get_menu('Admin');
if (isset($menu['upload data'])) {
  $upload = $menu['upload data'];
  if ($_user->is_admin()) {
    $section_name = array(gettext('Upload Data'), gettext('View Data'), gettext('WebPA Metrics'));
    $section_link = array($upload, 'review/', 'metrics/');
  } else {
    $section_name = array(gettext('Upload Data'), gettext('View Data'));
    $section_link = array($upload, 'review/');
  }
} else {
  if ($_user->is_admin()) {
    $section_name = array(gettext('View Data'), gettext('WebPA Metrics'));
    $section_link = array('review/', 'metrics/');
  } else {
    $section_name = array(gettext('View Data'));
    $section_link = array('review/');
  }
}
$section_definition = array(gettext('This is where you can upload the data to the system.'),
    gettext('This area allows you to view the uploaded data as well as search and edit user information.'),
    gettext('This section allows you to generate reports on the usage of WebPA locally.'));
?>
<p><?php echo $page_intro; ?></p>



<table class="option_list" style="width: 500px;">
<?php
  for ($i = 0; $i < count($section_link); $i++) {
?>
<tr>
  <td><a href="<?php echo $section_link[$i]; ?>"><img src="../images/icons/load_data.gif" width="32" height="32" alt="" /></a></td>
  <td>
    <div class="option_list">
      <div class="option_list_title"><a class="hidden" href="<?php echo $section_link[$i]; ?>"><?php echo $section_name[$i]; ?></a></div>
      <p><?php echo $section_definition[$i]; ?></p>
    </div>
  </td>
</tr>
<?php
  }
?>
</table>

<?php

$UI->content_end();

?>
