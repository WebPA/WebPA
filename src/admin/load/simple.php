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

require_once('../../includes/inc_global.php');
require_once('../../includes/classes/class_group_handler.php');
require_once('../../includes/functions/lib_string_functions.php');

if (!check_user($_user, APP__USER_TYPE_TUTOR) || ($_source_id != '')) {
 header('Location:'. APP__WWW .'/logout.php?msg=denied');
 exit;
}

$uploadtype = $_REQUEST["rdoFileContentType"];
$filename = $_FILES['uploadedfile']['tmp_name'];

//define variable we are using
$user_warning = '';
$user_msg = '';

$filecontenttype[1] = array('screen'=>'<strong>Student Data</strong>',);
$filecontenttype[2] = array('screen'=>'<strong>Staff Data</strong>',);
$filecontenttype[3] = array('screen'=>'<strong>Module Data</strong>');
$filecontenttype[4] = array('screen'=>'<strong>Student Data with Groups</strong>');

$expected_fields[1] = array(0=>'id_number', 'forename', 'lastname', 'email', 'username', 'password', 'department_id');
$expected_fields[2] = array(0=>'id_number', 'forename', 'lastname', 'email', 'username', 'password', 'department_id');
$expected_fields[3] = array(0=>'module_code', 'module_title');
$expected_fields[4] = array(0=>'id_number', 'forename', 'lastname', 'email', 'username', 'password', 'department_id', 'group_name');

if ($_user->is_admin()) {
 $expected_fields[1][] = 'module_code';
 $expected_fields[2][] = 'module_code';
 $expected_fields[4][] = 'module_code';
}
$flg_match = false;

//increase the execution time to handle large files
ini_set('max_execution_time', 120);

$row = 0;
$fields = array();
$final_rows = array();
if (($handle = fopen($filename, "r")) !== FALSE) {
  while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
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
        $final_rows[$row-1][$fields[$c]] = trim($data[$c]);
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
    if ($uploadtype=='2') {
      $user_type = APP__USER_TYPE_TUTOR;
    } else {
      $user_type = APP__USER_TYPE_STUDENT;
    }
    for ($counter = 0; $counter<count($final_rows); $counter++) {

      //if there are passwords in the list, they will need to be MD5 hashed
      if (!empty($final_rows[$counter]['password'])) {
        $final_rows[$counter]['password'] = md5($final_rows[$counter]['password']);
      } else {
        $final_rows[$counter]['password'] = md5(str_random());
      }
    }

    $_module_id = fetch_SESSION('_module_id', null);

    if ($uploadtype == '4') {

// get collection
      $collection = new GroupCollection($DB);
      $collection_id = null;
      if (isset($_REQUEST['collectionlist'])) {
        $collection_id = $_REQUEST['collectionlist'];
      }
      $modules = array();
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

      $module_code = '';
      $group_name = '';
      $els = array();
      $els[] = "source_id = '{$_source_id}'";
      for ($c = 0; $c < count($fields); $c++) {
        $key = $fields[$c];
        if (isset($i[$key])) {
          $val = $i[$key];
          if ($key == 'module_code') {
            $module_code = $val;
          } else if ($key == 'group_name') {
            $group_name = $val;
          } else {
            $els[] = "$key = '" . $DB->escape_str($val) . '\'';
          }
        }
      }
      $sql = 'INSERT INTO ' . APP__DB_TABLE_PREFIX . 'user SET ' . implode(', ',$els) . ', admin = 0';
      if ($_user->is_admin()) {
        $sql .= ' ON DUPLICATE KEY UPDATE ' . implode(', ',$els);
      }
      $DB->execute($sql);
      $id = $DB->get_insert_id();
      if (!$id) {
        $id = $DB->fetch_value('SELECT user_id FROM ' . APP__DB_TABLE_PREFIX . "user WHERE source_id = '{$_source_id}' AND username = '{$i['username']}'");
      }

      if ($_user->is_admin() && !empty($module_code)) {
        $sql = "SELECT module_id FROM " . APP__DB_TABLE_PREFIX . "module WHERE source_id = '{$_source_id}' AND module_code = '$module_code'";
        $module_id = $DB->fetch_value($sql);
      } else {
        $module_id = $_module_id;
      }
      if (!empty($module_id)) {
        $sql = "INSERT INTO " . APP__DB_TABLE_PREFIX . "user_module SET user_id = {$id}, module_id = {$module_id}, user_type = '{$user_type}'";
        $sql .= " ON DUPLICATE KEY UPDATE user_type = '{$user_type}'";
        $DB->execute($sql);
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

  //as we don't have staff or student then we must have "module"

    $fields = $expected_fields[$uploadtype];
    foreach($final_rows as $i) {

    //build the SQL
      $els = array();
      $els[] = "source_id = '{$_source_id}'";
      for ($c = 0; $c < count($fields); $c++) {
        $key = $fields[$c];
        $val = $i[$key];
        $els[] = "$key = '" . $DB->escape_str($val) . '\'';
      }
      $sql = 'INSERT INTO ' . APP__DB_TABLE_PREFIX . 'module SET ' . implode(', ',$els);
      $els = array();
      for ($c = 0; $c < count($fields); $c++) {
        $key = $fields[$c];
        $val = $i[$key];
        if ($key != 'module_code') {
          $els[] = "$key = '" . $DB->escape_str($val) . '\'';
        }
      }
      $sql .= ' ON DUPLICATE KEY UPDATE ' . implode(', ',$els);
      $DB->execute($sql);
    }

  }

  $user_msg = "<p>Successful upload of the {$filecontenttype[$uploadtype]['screen']} information to the database.</p>";

} else {
  //we want to notify that the information is not structured as expected therefore bounce back to the user
  $user_warning .= "The information supplied cannot be processed.</div><div><p>We suggest that you review the information to be uploaded and they <a href=\"../\">try again</a>. Alternatively you may wish to download a template to put the information in.</p>";
}

//write to screen the page information
//set the page information
$UI->page_title = APP__NAME;
$UI->menu_selected = 'upload data';
$UI->breadcrumbs = array ('home' => null);
$UI->help_link = '?q=node/237';
$UI->set_page_bar_button('View Student Data', '../../../images/buttons/button_student_user.png', '../review/student/index.php');
$UI->set_page_bar_button('View Staff Data', '../../../images/buttons/button_staff_user.png', '../review/staff/index.php');
if (check_user($_user, APP__USER_TYPE_ADMIN)) {
  $UI->set_page_bar_button('View Admin Data', '../../../images/buttons/button_admin_user.png', '../review/admin/index.php');
  $UI->set_page_bar_button('View Module Data', '../../../images/buttons/button_view_modules.png', '../review/module/index.php');
}
$UI->set_page_bar_button('Search for a user', '../../../images/buttons/button_search_user.png', '../review/search/index.php');

$UI->head();
$UI->body();
$UI->content_start();
?>
<div class="content_box">
<?php if(!empty($user_warning)){echo "<div class=\"warning_box\">{$user_warning}</div>";}?>

<?php if(!empty($user_msg)){echo $user_msg;}?>

</div>


<?php

$UI->content_end();

?>
