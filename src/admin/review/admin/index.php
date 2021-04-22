<?php
/**
 * Shows the staff data that is held in the database
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

//get the include file required
require_once '../../../includes/inc_global.php';

use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_ADMIN)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

$table = 'user';
$type = APP__USER_TYPE_ADMIN;
$rstitle = 'Administrator Data';
$user_type = 'administrator';


//set the page information
$UI->page_title = APP__NAME . ' view administrator data';
$UI->menu_selected = 'view data';
$UI->breadcrumbs = ['home' => '../../', 'review data'=>'../', 'administrator information'=>null];
$UI->help_link = '?q=node/237';
$UI->set_page_bar_button('View Student Data', '../../../../images/buttons/button_student_user.png', '../student/index.php');
$UI->set_page_bar_button('View Staff Data', '../../../../images/buttons/button_staff_user.png', '../staff/index.php');
$UI->set_page_bar_button('View Admin Data', '../../../../images/buttons/button_admin_user.png', '../admin/index.php');
$UI->set_page_bar_button('View Module Data', '../../../../images/buttons/button_view_modules.png', '../module/index.php');
$UI->set_page_bar_button('Search for a user', '../../../../images/buttons/button_search_user.png', '../../search/index.php');
$UI->head();
$UI->body();
$UI->content_start();

?>

<div class="content_box">
<?php include '../all.php'; ?>
</div>

<?php

$UI->content_end();

?>
