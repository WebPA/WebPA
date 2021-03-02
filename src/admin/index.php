<?php
/**
 * Landing page for the admin section
 *
 * This is the landing page for the administration section and acts as a gate way
 * to the other sections within this area of the site.
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

//get the include file required
require_once '../includes/inc_global.php';

use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

 //set the page information
$UI->page_title = APP__NAME;
$UI->menu_selected = 'admin home';
$UI->breadcrumbs = ['home' => null];
$UI->help_link = '?q=node/237';
$UI->head();
$UI->body();
$UI->content_start();

//build the content to be written to the screen

$page_intro = 'Welcome to the Administration Area for ' . APP__NAME . '. In this section you are able to manage the users of the system (both adding new and editing existing)';
if ($_user->is_admin()) {
    $page_intro .= ' as well as generate basic reports on the usage of ' . APP__NAME . ' (the metrics)';
}
$page_intro .= '.<br/><br/>The admin area contains the following sections:';
$menu = $UI->get_menu('Admin');
if (isset($menu['upload data'])) {
    $upload = $menu['upload data'];
    if ($_user->is_admin()) {
        $section_name = ['Upload Data', 'View Data', 'WebPA Metrics'];
        $section_link = [$upload, 'review/', 'metrics/'];
    } else {
        $section_name = ['Upload Data', 'View Data'];
        $section_link = [$upload, 'review/'];
    }
} else {
    if ($_user->is_admin()) {
        $section_name = ['View Data', 'WebPA Metrics'];
        $section_link = ['review/', 'metrics/'];
    } else {
        $section_name = ['View Data'];
        $section_link = ['review/'];
    }
}
$section_definition = ['This is where you can upload the data to the system.',
              'This area allows you to view the uploaded data as well as search and edit user information.',
              'This section allows you to generate reports on the usage of WebPA locally.', ];
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
