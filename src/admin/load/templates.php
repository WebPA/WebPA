<?php
/**
 * Short Description of the file
 *
 * Long Description of the file (if any)...
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../includes/inc_global.php';

use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

//write to screen the page information
//set the page information
$UI->page_title = APP__NAME .' Upload templates';
$UI->menu_selected = 'upload data';
$UI->breadcrumbs = ['home' => '../../',
              'Upload'=>'../', ];
$UI->help_link = '?q=node/237';

$UI->head();
$UI->body();
$UI->content_start();

if ($_user->is_admin()) {
    $usersfile = 'users_a.csv';
} else {
    $usersfile = 'users.csv';
}
?>
<div class="content_box">
  <p>The following are the template files for the uploading of information to the WebPA database.</p>
  <p>To download the files right mouse button click on the link and 'Save link as...'</p>
  <div class="obj_list">
    <div class="obj">
    <tableclass="obj" cellpadding="2" cellspacing="2">
      <tr><td class="obj_info"><div class="obj_name"><a href="<?php echo $usersfile; ?>" target="_blank">Staff or student data</a></div><p>The information for the user is vital. The first five columns of information are the most important aspects of information. If not all of the columns are filled then please delete the column titles before uploading the file.</p></td></tr>
    </table>
    </div>
    <div class="obj">
    <tableclass="obj" cellpadding="2" cellspacing="2">
      <tr><td class="obj_info"><div class="obj_name"><a href="modules.csv" target="_blank">Module data</a></div><p>All elements of information for the module data is required. The module information can not be loaded unless fully complete.</p></td></tr>
    </table>
    </div>
  </div>
</div>
<?php

$UI->content_end();

?>
