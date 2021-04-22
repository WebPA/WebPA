<?php

/**
 *
 * reset.php - Password reset facility
 * @author Morgan Harris [morgan@snowproject.net]
 * @license http://www.gnu.org/licenses/gpl.txt
 *
 */

/* This page serves four functions, based on the $_POST['action'] variable.
If not set, the page simply displays a form confirming that student would like to reset their password.
  -This form sends the 'init' action to the page.
If set to 'init', the page generates a random hash, which is part of a link sent to the user's email.
  -This link is retrieved via GET, so there is no POSTDATA.
If not set, but $_GET['hash'] is set, the page displays the password reset form.
  -This form sends the 'reset' action to the page.
If set to 'reset', the page performs the password reset.

This script relies on the existence of the `user_reset_request` table in MySQL.
This table has the CREATE definition:
*/

require_once '../includes/inc_global.php';

use Doctrine\DBAL\ParameterType;
use WebPA\includes\classes\User;
use WebPA\includes\functions\Common;

$action = Common::fetch_POST('action');

switch ($action) {
  case 'init':
    //phase 2
    //first, we create a random hash for this user.
    isset($_POST['username']) or exit('Username not set.');

    $hash = password_hash(mt_rand(), PASSWORD_DEFAULT);

    $sql =
        'SELECT user_id ' .
        'FROM ' . APP__DB_TABLE_PREFIX . 'user ' .
        'WHERE username = ? ' .
        'AND source_id = "" ' .
        'AND email IS NOT NULL ' .
        'AND email <> ""';

    $uid = $DB->getConnection()->fetchOne($sql, [$_POST['username']], [ParameterType::STRING]);

    if (!$uid) {
        $content = 'Unable to reset the password for this account.';
        break;
    }
    // inserts the user/hash pair into the database
    $insertUserHashPairQuery =
        'INSERT INTO ' . APP__DB_TABLE_PREFIX . 'user_reset_request ' .
        'SET hash = ?, user_id = ?';

    $appname = APP__NAME;
    $appwww = APP__WWW;

    $DB->getConnection()->executeQuery(
        $insertUserHashPairQuery,
        [$hash, $uid],
        [ParameterType::STRING, ParameterType::INTEGER]
    );

    $email = <<<TXT
        You have requested for your password to be reset on {$appname}. Please click or copy and paste the following link into your browser to continue the password reset process.

        {$appwww}/accounts/reset.php?u=$uid&hash=$hash

        If you have not requested a password reset, please ignore this email - your password will not be reset without further action.
        TXT;
    $userEmailQuery = 'SELECT email FROM ' . APP__DB_TABLE_PREFIX . 'user WHERE user_id = ?';

    $uemail = $DB->getConnection()->fetchOne($userEmailQuery, [$uid], [ParameterType::INTEGER]);

    mail($uemail, APP__NAME. ' Password Reset', $email, 'From: ' . $BRANDING['email.noreply']);
    $content = "An email has been sent to $uemail.";
    break;
  case 'reset':
    //phase 4
    $hash = $_POST['hash'];
    $uid = $_POST['uid'];

    $query =
        'SELECT COUNT(*) ' .
        'FROM ' . APP__DB_TABLE_PREFIX . 'user_reset_request ' .
        'WHERE hash = ? ' .
        'AND user_id = ?';

    $rslt = $DB->getConnection()->fetchOne($query, [$hash, $uid], [ParameterType::STRING, ParameterType::INTEGER]);

    if ($rslt) {
        if ($_POST['newpass']==$_POST['confirmpass']) {
            $user = new User();

            $dbConn = $DB->getConnection();

            $userQuery = 'SELECT * FROM ' . APP__DB_TABLE_PREFIX . 'user WHERE user_id = ?';

            $userRow = $dbConn->fetchAssociative($userQuery, [$uid], [ParameterType::INTEGER]);

            $user->load_from_row($userRow);
            $user->set_dao_object($DB);
            $user->update_password($_POST['newpass']);
            $user->save_user();

            $DB->getConnection()->executeQuery(
                'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'user_reset_request WHERE user_id = ?',
                [$uid],
                [ParameterType::INTEGER]
            );

            $content = 'Your password has been reset. <a href="'.APP__WWW.'/login.php">Click here</a> to log in again.';
        } else {
            $content = 'The two passwords did not match.';
        }
    } else {
        $content = 'There was an error resetting this password.';
    }
    break;
  default:
    if (isset($_GET['hash'])) {
        //phase 3
        $hash = $_GET['hash'];
        $uid = $_GET['u'];
        if ((!isset($_GET['hash'])) || (!isset($_GET['u']))) {
            $content = 'Error: reset link incorrect. If you copied and pasted the link from your mail client, be sure you did so correctly.';
            break;
        }

        $query =
          'SELECT COUNT(*) ' .
          'FROM ' . APP__DB_TABLE_PREFIX . 'user_reset_request ' .
          'WHERE hash = ? ' .
          'AND user_id = ?';

        $rslt = $DB->getConnection()->fetchOne($query, [$hash, $uid], [ParameterType::STRING, ParameterType::INTEGER]);

        if ($rslt) {
            $content = <<<HTML
                      <form action="reset.php" method="post">
                        <table>
                          <tr>
                            <th scope="row">New Password</th>
                            <td><input type="password" name="newpass" value="" id="newpass"/></td>
                          </tr>
                          <tr>
                            <th scope="row">New Password (again)</th>
                            <td><input type="password" name="confirmpass" value="" id="confirmpass"/></td>
                          </tr>
                          <tr>
                            <td></td>
                            <td><input type="submit" value="Reset Password" /></td>
                          </tr>
                        </table>
                        <input type="hidden" name="hash" value="$hash"/>
                        <input type="hidden" name="uid" value="$uid"/>
                        <input type="hidden" name="action" value="reset" />
                      </form>
                HTML;
        } else {
            $content = 'There was an error resetting this password. Please contact the site administrator.';
        }
        break;
    }
    //phase 1
    //just display the form confirming the password reset.
    $content = <<<HTML
        <strong>You are about to reset your password.</strong> In order to do so, a link will be sent to your student email account. This link will take you to a page that will enable you to reset your password.<br/>
        <br/>
        <form action="reset.php" method="post">
          <table>
            <tr>
              <th scope="row">Username</th>
              <td><input type="text" name="username" /></td>
            </tr>
            <tr>
              <td></td>
              <td>
                <input type="submit" value="Reset My Password"/>
              </td>
            </tr>
          </table>
          <input type="hidden" name="action" value="init"/>
        </form>
        HTML;
    break;
}

$UI->page_title = 'Password Reset';
$UI->menu_selected = '';
$UI->breadcrumbs = ['login page' => '../', 'Password Reset' => null];
$UI->help_link = null;

$UI->head();
$UI->body();

$UI->content_start();
echo "<p>\n";
echo $content;
echo "</p>\n";
$UI->content_end(false);
