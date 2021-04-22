<?php
/**
 * View log information
 *
 * This page allows the tutor to see the log information that is held in the database
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

//get the include file required
require_once '../../includes/inc_global.php';

use Doctrine\DBAL\ParameterType;
use WebPA\includes\classes\User;
use WebPA\includes\functions\Common;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

$type = 'log';
$rstitle = 'Log Data';

//set the page information
$UI->page_title = APP__NAME . ' view log data';
$UI->menu_selected = 'view data';
$UI->breadcrumbs = ['home' => '../', 'review data'=>'./', 'log data'=>null];
$UI->help_link = '?q=node/237';
$UI->set_page_bar_button('View Student Data', '../../../images/buttons/button_student_user.png', 'student/index.php');
$UI->set_page_bar_button('View Staff Data', '../../../images/buttons/button_staff_user.png', 'staff/index.php');
if (Common::check_user($_user, APP__USER_TYPE_ADMIN)) {
    $UI->set_page_bar_button('View Admin Data', '../../../images/buttons/button_admin_user.png', 'admin/index.php');
    $UI->set_page_bar_button('View Module Data', '../../../images/buttons/button_view_modules.png', 'module/index.php');
}
$UI->set_page_bar_button('Search for a user', '../../../images/buttons/button_search_user.png', '../search/index.php');
$UI->head();
$UI->body();
$UI->content_start();

?>

<div class="content_box">

<?php

//get the passed user ID passed as u
$user_id = Common::fetch_GET('u', '');
$user_info = $CIS->get_user($user_id);
$user = new User();
$user->load_from_row($user_info);

if ($_user->is_admin()) {
    $query = 'SELECT datetime, description, ip_address AS `ip address`, object_id AS object ';
} else {
    $query = 'SELECT datetime, description, object_id AS object ';
}

$query .= 'FROM ' . APP__DB_TABLE_PREFIX . 'user_tracking ' .
          'WHERE user_id = ? AND ((module_id = ?) OR (module_id IS NULL)) ' .
          'ORDER BY datetime DESC, description';

$dbConn = $DB->getConnection();

$stmt = $dbConn->prepare($query);

$stmt->bindValue(1, $user_id, ParameterType::INTEGER);
$stmt->bindValue(2, $_module_id, ParameterType::INTEGER);

$results = $stmt->execute();

$rs = $results->fetchAllAssociative();

echo "<h2>{$rstitle} for {$user->forename} {$user->lastname} ({$user->username})</h2>";

echo '<div class="obj">';

echo '<table class="obj" cellpadding="2" cellspacing="2">';

//work through the recordset if it is not empty
for ($recordcounter = 0; $recordcounter < count($rs); $recordcounter++) {
    if ($recordcounter == 0) {
        //write the table field headers to the screen
        echo '<tr>';
        foreach ($rs[$recordcounter] as $field_name => $field_value) {
            echo "<th>{$field_name}</th>";
        }
        echo "</tr>\n";
    }
    echo '<tr>';
    foreach ($rs[$recordcounter] as $field_name => $field_value) {
        echo '<td class="obj_info_text">'.$field_value.'</td>';
    }
    echo "</tr>\n";
}
if (count($rs) <= 0) {
    echo "<tr><td>No records</td></tr>\n";
}

echo '</table>';
echo '</div>';
?>

</div>
<?php

$UI->content_end();

?>
