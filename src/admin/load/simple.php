<?php
/**
 * Simple.php is a re-write of the previously used file upload process.
 *
 * A file with the table elements correctly named at the top of a cvs file
 * can be uploaded to the database. The system checks and links the module information with the students
 * The database is then checked for duplicates and removes them.
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once '../../includes/inc_global.php';

use Doctrine\DBAL\ParameterType;
use WebPA\includes\classes\GroupCollection;
use WebPA\includes\functions\Common;
use WebPA\includes\functions\StringFunctions;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR) || ($_source_id != '')) {
    header('Location:' . APP__WWW . '/logout.php?msg=denied');
    exit;
}

$uploadtype = $_REQUEST['rdoFileContentType'];
$filename = $_FILES['uploadedfile']['tmp_name'];

//define variable we are using
$user_warning = '';
$user_msg = '';

$filecontenttype[1] = ['screen' => '<strong>Student Data</strong>'];
$filecontenttype[2] = ['screen' => '<strong>Staff Data</strong>'];
$filecontenttype[3] = ['screen' => '<strong>Module Data</strong>'];
$filecontenttype[4] = ['screen' => '<strong>Student Data with Groups</strong>'];

$expected_fields[1] = [0 => 'id_number', 'forename', 'lastname', 'email', 'username', 'password', 'department_id'];
$expected_fields[2] = [0 => 'id_number', 'forename', 'lastname', 'email', 'username', 'password', 'department_id'];
$expected_fields[3] = [0 => 'module_code', 'module_title'];
$expected_fields[4] = [0 => 'id_number', 'forename', 'lastname', 'email', 'username', 'password', 'department_id', 'group_name'];

if ($_user->is_admin()) {
    $expected_fields[1][] = 'module_code';
    $expected_fields[2][] = 'module_code';
    $expected_fields[4][] = 'module_code';
}
$flg_match = false;

//increase the execution time to handle large files
ini_set('max_execution_time', 120);

$row = 0;
$fields = [];
$final_rows = [];

if (($handle = fopen($filename, 'r')) !== false) {
    while (($data = fgetcsv($handle, 2000, ',')) !== false) {
        $num = count($data);

        //if in the first row we should be getting the field names
        //make them into an array
        if ($row == 0) {
            for ($c = 0; $c < $num; $c++) {
                $fields[$c] = trim($data[$c]);
            }

            //check to see that we have the correct information in the array
            if (array_diff($expected_fields[$uploadtype], $fields)) {

                //we have diferences. We need to check to see if any of the key elements have matched
                foreach ($expected_fields[$uploadtype] as $key_field) {
                    if (array_search($key_field, $fields) !== false) {
                        $flg_match = true;
                    }
                }
                if (!$flg_match) {
                    for ($c = 0; $c < $num; $c++) {
                        $final_rows[$row][$c] = trim($data[$c]);
                        $fields[$c] = $c;
                    }
                    $row++;
                }
            } else {
                $flg_match = true;
            }
        } else {
            //build the associative array for the table entrys
            for ($c = 0; $c < $num; $c++) {
                $final_rows[$row - 1][$fields[$c]] = trim($data[$c]);
            }
        }
        $row++;
    }

    //now we have the information in arrays continue to process.
    fclose($handle);
}

//check which array we are being given at this point (the one with the named fields or the other)
if ($flg_match) {
    //we can process this very easily into the database

    //check to see if we have staff or student.
    if (($uploadtype == '1') || ($uploadtype == '2') || ($uploadtype == '4')) {
        //set the user type
        if ($uploadtype == '2') {
            $user_type = APP__USER_TYPE_TUTOR;
        } else {
            $user_type = APP__USER_TYPE_STUDENT;
        }

        $finalRowsCount = count($final_rows);

        for ($counter = 0; $counter < $finalRowsCount; $counter++) {
            // if there are passwords in the list, they will need to be hashed
            if (!empty($final_rows[$counter]['password'])) {
                $final_rows[$counter]['password'] = password_hash($final_rows[$counter]['password'], PASSWORD_DEFAULT);
            } else {
                $final_rows[$counter]['password'] = password_hash(StringFunctions::str_random(), PASSWORD_DEFAULT);
            }
        }

        $_module_id = Common::fetch_SESSION('_module_id', null);

        if ($uploadtype == '4') {
            // get collection
            $collection = new GroupCollection($DB);
            $collection_id = null;

            if (isset($_REQUEST['collectionlist'])) {
                $collection_id = $_REQUEST['collectionlist'];
            }

            $modules = [];

            if (empty($collection_id)) {
                $collection_name = $_REQUEST['collection'];

                if (!empty($collection_name)) {
                    $collection->create();
                    $collection->module_id = $_module_id;
                    $collection->name = $collection_name;
                    $collection->save();
                    $collection_id = $collection->id;
                }
            } else {
                $collection->load($collection_id);
                $modules = $collection->get_modules();
            }

            $module_count = count($modules);
        }

        $fields = $expected_fields[$uploadtype];

        foreach ($final_rows as $i) {
            $module_code = $i['module_code'] ?? '';
            $group_name = $i['group_name'] ?? '';

            $insertUserQuery =
                'INSERT INTO ' . APP__DB_TABLE_PREFIX . 'user ' .
                '(id_number, forename, lastname, email, username, password, department_id, source_id, admin) ' .
                'VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)';

            if ($_user->is_admin()) {
                $insertUserQuery .=
                   ' ON DUPLICATE KEY UPDATE ' .
                   'id_number = ?, ' .
                   'forename = ?, ' .
                   'lastname = ?, ' .
                   'email = ?, ' .
                   'username = ?, ' .
                   'password = ?, ' .
                   'department_id = ?, ' .
                   'source_id = ? ';
            }

            $stmt = $DB->getConnection()->prepare($insertUserQuery);

            $stmt->bindValue(1, $i['id_number']);
            $stmt->bindValue(2, $i['forename']);
            $stmt->bindValue(3, $i['lastname']);
            $stmt->bindValue(4, $i['email']);
            $stmt->bindValue(5, $i['username']);
            $stmt->bindValue(6, $i['password']);
            $stmt->bindValue(7, $i['department_id']);
            $stmt->bindValue(8, $_source_id);

            if ($_user->is_admin()) {
                $stmt->bindValue(9, $i['id_number']);
                $stmt->bindValue(10, $i['forename']);
                $stmt->bindValue(11, $i['lastname']);
                $stmt->bindValue(12, $i['email']);
                $stmt->bindValue(13, $i['username']);
                $stmt->bindValue(14, $i['password']);
                $stmt->bindValue(15, $i['department_id']);
                $stmt->bindValue(16, $_source_id);
            }

            $stmt->execute();

            $id = $DB->getConnection()->lastInsertId();

            if (!$id) {
                $getUserIdQuery =
                    'SELECT user_id ' .
                    'FROM ' . APP__DB_TABLE_PREFIX . 'user ' .
                    'WHERE source_id = ? ' .
                    'AND username = ?';

                $id = $DB->getConnection()->fetchOne($getUserIdQuery, [$_source_id, $i['username']], [ParameterType::STRING, ParameterType::STRING]);
            }

            if ($_user->is_admin() && !empty($module_code)) {
                $sql =
                    'SELECT module_id ' .
                    'FROM ' . APP__DB_TABLE_PREFIX . 'module ' .
                    'WHERE source_id = ? ' .
                    'AND module_code = ?';

                $module_id = $DB->getConnection()->fetchOne($sql, [$_source_id, $module_code], [ParameterType::STRING, ParameterType::STRING]);
            } else {
                $module_id = $_module_id;
            }
            if (!empty($module_id)) {
                $insertUserModuleQuery =
                    'INSERT INTO ' . APP__DB_TABLE_PREFIX . 'user_module ' .
                    'VALUES (?, ?, ?) ' .
                    'ON DUPLICATE KEY UPDATE user_type = ?';

                $DB->getConnection()->executeQuery(
                    $insertUserModuleQuery,
                    [$id, $module_id, $user_type, $user_type],
                    [ParameterType::INTEGER, ParameterType::INTEGER, ParameterType::STRING, ParameterType::STRING]
                );

                if (!empty($collection_id) && !empty($group_name)) {
                    if (!in_array($module_id, $modules)) {
                        $modules[] = $module_id;
                    }
                    if (!$collection->group_exists($group_name)) {
                        $group = $collection->new_group($group_name);
                        $group->save();
                        $collection->refresh_groups();
                    }
                    $collection->add_member($id, $group_name);
                }
            }
        }
    } else {
        // as we don't have staff or student then we must have "module"
        $fields = $expected_fields[$uploadtype];

        foreach ($final_rows as $i) {
            $insertModuleQuery =
                'INSERT INTO ' . APP__DB_TABLE_PREFIX . 'module ' .
                '(module_code, module_title, source_id) ' .
                'VALUES (?, ? , ?) ' .
                'ON DUPLICATE KEY UPDATE ' .
                'module_title = ?, ' .
                'source_id = ?';

            $stmt = $DB->getConnection()->prepare($insertModuleQuery);

            $stmt->bindValue(1, $i['module_code']);
            $stmt->bindValue(2, $i['module_title']);
            $stmt->bindValue(3, $_source_id);
            $stmt->bindValue(4, $i['module_title']);
            $stmt->bindValue(5, $_source_id);

            $stmt->execute();
        }
    }

    $user_msg = "<p>Successful upload of the {$filecontenttype[$uploadtype]['screen']} information to the database.</p>";
} else {
    //we want to notify that the information is not structured as expected therefore bounce back to the user
    $user_warning .= 'The information supplied cannot be processed.</div><div><p>We suggest that you review the information to be uploaded and they <a href="../">try again</a>. Alternatively you may wish to download a template to put the information in.</p>';
}

//write to screen the page information
//set the page information
$UI->page_title = APP__NAME;
$UI->menu_selected = 'upload data';
$UI->breadcrumbs = ['home' => null];
$UI->help_link = '?q=node/237';
$UI->set_page_bar_button('View Student Data', '../../../images/buttons/button_student_user.png', '../review/student/index.php');
$UI->set_page_bar_button('View Staff Data', '../../../images/buttons/button_staff_user.png', '../review/staff/index.php');
if (Common::check_user($_user, APP__USER_TYPE_ADMIN)) {
    $UI->set_page_bar_button('View Admin Data', '../../../images/buttons/button_admin_user.png', '../review/admin/index.php');
    $UI->set_page_bar_button('View Module Data', '../../../images/buttons/button_view_modules.png', '../review/module/index.php');
}
$UI->set_page_bar_button('Search for a user', '../../../images/buttons/button_search_user.png', '../review/search/index.php');

$UI->head();
$UI->body();
$UI->content_start();
?>
<div class="content_box">
    <?php if (!empty($user_warning)) {
    echo "<div class=\"warning_box\">{$user_warning}</div>";
} ?>

    <?php if (!empty($user_msg)) {
    echo $user_msg;
} ?>

</div>


<?php

$UI->content_end();
