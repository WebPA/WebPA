<?php
/**
 *
 * This area provide the edit location for the users held in the database
 *
 * Dependant on the information held in the WebPA system the administrator
 * who is the person able to access the system through a number of routes may
 * or may not be shown all of the information.
 *
 * On saving the edit the information is processed via the User class for the majority
 * of the information and then the module information is processed.
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
use WebPA\includes\functions\Form;
use WebPA\includes\functions\StringFunctions;

if (!Common::check_user($_user, APP__USER_TYPE_TUTOR)) {
    header('Location:'. APP__WWW .'/logout.php?msg=denied');
    exit;
}

//set the page information
$UI->page_title = APP__NAME . ' Edit system users';
$UI->menu_selected = 'view data';
$UI->set_page_bar_button('View Student Data', '../../../images/buttons/button_student_user.png', '../review/student/index.php');
$UI->set_page_bar_button('View Staff Data', '../../../images/buttons/button_staff_user.png', '../review/staff/index.php');
if (Common::check_user($_user, APP__USER_TYPE_ADMIN)) {
    $UI->set_page_bar_button('View Admin Data', '../../../images/buttons/button_admin_user.png', '../review/admin/index.php');
    $UI->set_page_bar_button('View Module Data', '../../../images/buttons/button_view_modules.png', '../review/module/index.php');
}
$UI->set_page_bar_button('Search for a user', '../../../images/buttons/button_search_user.png', '../search/index.php');
$UI->breadcrumbs = ['home' => '../', 'review data'=>'../review/', 'edit'=>null];
$UI->help_link = '?q=node/237';

//build the content to be written to the screen

//get the posted information

$action = Common::fetch_POST('save');

//get the passed user ID passed as u
$user_id = Common::fetch_POST('u', Common::fetch_GET('u'));
$username = Common::fetch_POST('username');
$new_user = empty($user_id);
$type = Common::fetch_POST('t', Common::fetch_GET('t', ''));
$user_found = false;

$sScreenMsg = '';

//-----------------------------------------------------------------------
//collect all the information about the person to populate the fields
$edit_user = new User();
if (!$new_user) {
    $user_row = $CIS->get_user($user_id);
    $edit_user->load_from_row($user_row);
} else {
    if (!empty($username)) {
        $user_row = $CIS->get_user_for_username($username);
        if ($user_row) {
            $user_found = true;
            $edit_user->load_from_row($user_row);
            $user_id = $edit_user->id;
        }
    }
    if ($_user->is_admin() && ($type == APP__USER_TYPE_ADMIN)) {
        if ($user_found) {
            $user_found = false;
            $user_id = '';
            $edit_user = new User();
            $action = '';
            $sScreenMsg = 'The username already exists; please use another.';
        }
        $edit_user->admin = 1;
    }
}
if ($edit_user->is_admin() && ($type != APP__USER_TYPE_ADMIN)) {
    $user_found = false;
    $user_id = '';
    $edit_user = new User();
    $action = '';
    $sScreenMsg = 'Administrators cannot be enrolled in modules.';
}

//----------------------------------------------------------------------
//process form

$canEdit = ($_source_id == '') && (($new_user || $_user->is_admin()) && !$user_found);

if ($canEdit) {

  //put all the elements back into the structures
    $value = Common::fetch_POST('name');
    if (!empty($value)) {
        $edit_user->forename = $value;
    }
    $value = Common::fetch_POST('lastname');
    if (!empty($value)) {
        $edit_user->lastname = $value;
    }
    $value = Common::fetch_POST('id_number');
    if (!empty($value)) {
        $edit_user->id_number = $value;
    }
    $value = Common::fetch_POST('department_id');
    if (!empty($value)) {
        $edit_user->department_id = $value;
    }
    $value = Common::fetch_POST('email');
    if (!empty($value)) {
        $edit_user->email = $value;
    }
    //check to see if the password needs to be saved
    $password = Common::fetch_POST('password', '');

    if ((($password != '!!!!!!') && !empty($password)) || empty($user_id)) {
        if (($password == '!!!!!!') || empty($password)) {
            $password = StringFunctions::str_random();
        }

        $edit_user->update_password($password);
    }

    if ((($new_user && !$user_found) || $_user->is_admin()) && Common::fetch_POST('username')) {
        $edit_user->update_username(Common::fetch_POST('username'));
    }
}

if ($action) {          //incase we want to do more than save changes in the future
    switch ($action) {
    case 'Save Changes':

      $complete = !empty($edit_user->forename) && !empty($edit_user->lastname) &&
                  !empty($edit_user->password) && !empty($edit_user->username);
        
      if (!$complete) {
          $sScreenMsg = 'Unable to save user: please make sure the user has a username, first name, last name and password.';
      } elseif ($edit_user->email != '' && !Form::is_email($edit_user->email)) {
          $sScreenMsg = 'Unable to save user: email address is not valid.';
      } else {
          //send notification to the screen that the save has occured.
          if ($new_user && !$user_found) {
              $sScreenMsg = 'The user has been created';
          } else {
              $sScreenMsg = 'The changes made for the user have been saved';
          }

          if ($canEdit) {
              $edit_user->set_dao_object($DB);

              //save all of the data
              if ($new_user && !$user_found) {
                  $user = $edit_user->add_user();
                  $user_row = $CIS->get_user($user);
                  //reload user
                  $edit_user = new User();
                  $edit_user->load_from_row($user_row);
                  $user_id = $edit_user->id;
              } else {
                  $edit_user->save_user();
              }
          }

          if (!$new_user && !$edit_user->is_admin()) {
              $modules = $CIS->get_module(null, 'name');
              $user_modules = $CIS->get_user_modules($user_id);

              foreach ($modules as $module) {
                  if (empty($user_modules) || !array_key_exists($module['module_id'], $user_modules)) {
                      $old_role = '';
                  } else {
                      $old_role = $user_modules[$module['module_id']]['user_type'];
                  }
                  if (isset($_POST["rdo_type_{$module['module_id']}"])) {
                      $new_role = $_POST["rdo_type_{$module['module_id']}"];
                  } else {
                      $new_role = '';
                  }
                  if ($old_role != $new_role) {
                      if (empty($new_role)) {
                          $delete[] = $module['module_id'];
                      } else {
                          $update[$new_role][] = $module['module_id'];
                      }
                  }
              }
          } elseif (!$edit_user->is_admin() && ($type != APP__USER_TYPE_ADMIN)) {
              $user_modules = $CIS->get_user_modules($user_id);
              if (!is_array($user_modules)) {
                  $user_modules = [];
              }
              if (!array_key_exists($_module_id, $user_modules)) {
                  $update[$type][] = $_module_id;
              } elseif ($user_modules[$_module_id]['user_type'] == $type) {
                  $sScreenMsg = 'User is already enrolled';
              } else {
                  $sScreenMsg = 'User currently has a different role in this module - change not saved';
              }
          }
          if (isset($update)) {
              if (isset($update[APP__USER_TYPE_TUTOR])) {
                  $createUserModuleQuery =
                'INSERT INTO ' . APP__DB_TABLE_PREFIX . 'user_module ' .
                '(user_id, module_id, user_type) ' .
                'VALUES (?, ? , ?) ' .
                'ON DUPLICATE KEY UPDATE user_type = ?';

                  foreach ($update[APP__USER_TYPE_TUTOR] as $module) {
                      $DB->getConnection()->executeQuery(
                          $createUserModuleQuery,
                          [$user_id, $module, APP__USER_TYPE_TUTOR, APP__USER_TYPE_TUTOR],
                          [ParameterType::INTEGER, ParameterType::INTEGER, ParameterType::STRING, ParameterType::STRING]
                      );
                  }
              }

              if (isset($update[APP__USER_TYPE_STUDENT])) {
                  $createUserModuleQuery =
                'INSERT INTO ' . APP__DB_TABLE_PREFIX . 'user_module ' .
                '(user_id, module_id, user_type) ' .
                'VALUES (?, ?, ?) ' .
                'ON DUPLICATE KEY UPDATE user_type = ?';

                  foreach ($update[APP__USER_TYPE_STUDENT] as $module) {
                      $DB->getConnection()->executeQuery(
                          $createUserModuleQuery,
                          [$user_id, $module, APP__USER_TYPE_STUDENT, APP__USER_TYPE_STUDENT],
                          [ParameterType::INTEGER, ParameterType::INTEGER, ParameterType::STRING, ParameterType::STRING]
                      );
                  }
              }
          }
          if (isset($delete)) {
              $queryBuilder = $DB->getConnection()->createQueryBuilder();

              $queryBuilder
                ->delete(APP__DB_TABLE_PREFIX . 'user_module')
                ->where(
                    $queryBuilder->expr()->and(
                        $queryBuilder->expr()->eq('user_id', '?'),
                        $queryBuilder->expr()->in('module_id', '?')
                    )
                );

              $queryBuilder->setParameter(0, $user_id, ParameterType::INTEGER);
              $queryBuilder->setParameter(1, $delete, $DB->getConnection()::PARAM_INT_ARRAY);

              $queryBuilder->execute();

              if (in_array($_module_id, $delete)) {
                  header('Location: ../review/');
              }
          }

          if (isset($update) || isset($delete)) {
              //collect all the updated information
              $user_row = $CIS->get_user($user_id);
              $edit_user = new User();
              $edit_user->load_from_row($user_row);
          }
      }

      break;

  }
}

$UI->head();
$UI->body();
$UI->content_start();

//-----------------------------------------------------------------------
//build the page and fill in the spaces

$page_intro = 'Here you are able to edit the details of a user within the system. There may be some elements of the information which do not appear' .
    '   to have been completed and this will be dependant on the information stored in the system.';
if (empty($user_id)) {
    $page_intro .= ' Just enter the username for users already added to the system.';
}

$page_conditions = 'N.B. Users authenticated directly from the WebPA <strong>database</strong> must be given a password.';

echo '<p>' . $page_intro . '</p>';

if ($type == APP__USER_TYPE_ADMIN) {
    $cancel = '../review/admin/';
} elseif ($type == APP__USER_TYPE_TUTOR) {
    $cancel = '../review/staff/';
} elseif ($type == APP__USER_TYPE_STUDENT) {
    $cancel = '../review/student/';
} else {
    $cancel = '../review/';
}

?>

<div class="content_box">

<?php
echo '<p>' . $page_conditions . '</p>';

if (!empty($sScreenMsg)) {
    echo "<div class=\"success_box\">{$sScreenMsg}</div>";
}
?>

<form action="index.php?u=<?php echo $user_id; ?>" method="post" name="edit_user">
<table class="option_list" style="width: 100%;">
<tr><td colspan=2><h2>User Details</h2></td></tr>
  <tr>
    <td><label for="username">Username</label></td>
    <td colspan="3">
<?php
$canEdit = ($_source_id == '') && (empty($user_id) || ($_user->is_admin()));

if (!$canEdit) {
    $disabled = ' disabled="disabled"';
} else {
    $disabled = '';
}
?>
      <input type="text" name="username" id="username" value="<?php echo $edit_user->username; ?>"<?php echo $disabled; ?>>
    </td>
  </tr>
  <tr>
    <td><label for="name">First name</label>
    </td>
    <td>
      <input type="text" id="name" name="name" value="<?php echo $edit_user->forename; ?>" size="20"<?php echo $disabled; ?>>
    </td>

    <td><label for="lastname">Last name</label>
    </td>
    <td>
      <input type="text" id="lastname" name="lastname" value="<?php echo $edit_user->lastname; ?>" size="30"<?php echo $disabled; ?>>
    </td>
  </tr>
  <tr>
    <td><label for="email">Email</label>
    </td>
    <td colspan="4">
      <input type="text" name="email" id="email" value="<?php echo $edit_user->email; ?>" size="50"<?php echo $disabled; ?>>
    </td>
  </tr>
  <tr>
    <td><label for="id_number">ID number</label></td>
    <td>
      <input type="text" name="id_number" id="id_number" value="<?php echo $edit_user->id_number; ?>"<?php echo $disabled; ?>>
    </td>

    <td><label for="department_id">Department ID</label></td>
    <td>
      <input type="text" name="department_id" id="department_id" value="<?php echo $edit_user->department_id; ?>"<?php echo $disabled; ?>>
    </td>
  </tr>
  <tr>
    <td><label for="password">Password</label></td>
    <td>
<?php
if (!empty($edit_user->password)) {
    $show = '!!!!!!';
} else {
    $show = '';
}
?>
      <input id="password" name="password" type="password" value="<?php echo $show; ?>"<?php echo $disabled; ?>>
    </td>
    <td colspan="2">
      <p style="font-size:xx-small;">N.B. If a password is present in the system then 6 characters are shown. For security reasons the password is not displayed in clear text.</p>
    </td>
  </tr>

<?php
if (!empty($user_id) && !Common::check_user($edit_user, APP__USER_TYPE_ADMIN)) {
    ?>
  <tr><td colspan="4"><hr/></td></tr>
  <tr><td colspan="4"><h2>Module</h2></td></tr>
  <tr>
    <td colspan="4">

<?php
  $canEdit = ($_source_id == '') && ($user_id != $_user_id);
    if (!$canEdit) {
        $disabled = ' disabled="disabled"';
    } else {
        $disabled = '';
    }

    echo "<table>\n";
    echo "<tr>\n";
    echo "  <th>Title</th><th style=\"width: 5em; text-align: center;\">NA</th><th style=\"width: 5em; text-align: center;\">Tutor</th><th style=\"width: 5em; text-align: center;\">Student</th>\n";
    echo "</tr>\n";

    $modules = $CIS->get_module(null, 'name');
    $user_modules = $CIS->get_user_modules($user_id);

    foreach ($modules as $module) {
        if (empty($user_modules) || !array_key_exists($module['module_id'], $user_modules)) {
            $role = '';
        } else {
            $role = $user_modules[$module['module_id']]['user_type'];
        }
        if ($module['module_id'] == $_module_id) {
            $tagStart = '<em>';
            $tagEnd = '</em>';
        } else {
            $tagStart = '';
            $tagEnd = '';
        }

        echo "<tr>\n";
        echo "  <td>\n    {$tagStart}{$module['module_title']}{$tagEnd}&nbsp;&nbsp;&nbsp;\n  </td>\n";
        echo "  <td style=\"text-align: center;\">\n";
        echo '    <input type="radio" value="" name="rdo_type_' . $module['module_id'] . '"';
        if (empty($role)) {
            echo ' checked="checked"';
        }
        echo "{$disabled}>\n";
        echo "  </td>\n";
        echo "  <td style=\"text-align: center;\">\n";
        echo '    <input type="radio" value="' . APP__USER_TYPE_TUTOR . '" name="rdo_type_' . $module['module_id'] . '"';
        if ($role == APP__USER_TYPE_TUTOR) {
            echo ' checked="checked"';
        }
        echo "{$disabled}>\n";
        echo "  </td>\n";
        echo "  <td style=\"text-align: center;\">\n";
        echo '    <input type="radio" value="' . APP__USER_TYPE_STUDENT . '" name="rdo_type_' . $module['module_id'] . '"';
        if ($role == APP__USER_TYPE_STUDENT) {
            echo ' checked="checked"';
        }
        echo "{$disabled}>\n";
        echo "  </td>\n";
        echo "</tr>\n";
    }

    echo "</table>\n"; ?>
    </td>
  </tr>
<?php
}
?>
  <tr><td colspan="4"><hr/></td></tr>
  <tr>

    <td>
      &nbsp;
    </td>
    <td colspan="3">
      <input type="submit" value="Save Changes" name="save" id="save">&nbsp;&nbsp;&nbsp;
      <input type="button" value="Cancel" onclick="location.href='<?php echo $cancel; ?>';">
      <input type="hidden" name="t" value="<?php echo $type; ?>" />
    </td>
  </tr>
</table>
</form>
</div>
<?php

$UI->content_end();

?>
