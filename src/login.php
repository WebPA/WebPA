<?php
/**
 * Login page
 *
 * Added a link to /accounts/reset.php ('Forgotten your password?')
 * made by Morgan Harris [morgan@snowproject.net] as of 15/10/09
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

require_once 'includes/inc_global.php';

use WebPA\includes\functions\Common;

// --------------------------------------------------------------------------------
// Process GET/POST

$msg = Common::fetch_GET('msg', null);

switch ($msg) {
  case 'connfailed':
        $message_class = 'warning';
        $message = 'A connection to the authentication server could not be established.<br />Please try again later.';
        break;
  // --------------------
  case 'denied':
        $message_class = 'warning';
        $message = 'You attempted to access a restricted page.<br />It may be that your session has timed out so please re-enter your details.';
        break;
  // --------------------
  case 'no access':
        $message_class = 'warning';
        $message = 'Your account has been disabled.<br />Please contact support if you do not think this should be the case.';
        break;
  // --------------------
  case 'invalid':
        $message_class = 'warning';
        $message = 'Your username and password were rejected.<br />Please check your details and try again.';
        break;
  // --------------------
  case 'cookies':
        $message_class = 'warning';
        $message = 'Unable to connect to ' . APP__NAME . '; please ensure that your browser is not blocking third-party cookies';
        break;
  // --------------------
  case 'logout':
        $message_class = 'info';
        $message = 'You have logged out.<br />If you wish to log back in, please re-enter your details.';
        break;
  // --------------------
  default:
        $message_class = 'info';
        $message = 'To start using ' . APP__NAME . ' you have to log in.';
        break;
}

// --------------------------------------------------------------------------------
// Begin Page

$UI->page_title = APP__NAME . ' Login';
$UI->menu_selected = '';
$UI->help_link = '?q=node/26';
$UI->breadcrumbs = [
  'login page'  => null,
];


$UI->head();
?>
<style type="text/css">
<!--

p.warning { color: #f00; }
p.info { color: #000; }

-->
</style>
<script language="JavaScript" type="text/javascript">
<!--

  var username_focussed = false;
  var password_focussed = false;

  function username_focus() {
    if ( (!username_focussed) && (!password_focussed) ) { document.getElementById('username').focus(); }
  }

//-->
</script>
<?php
$UI->body('onload="username_focus();"');
$UI->content_start();
?>


<?php echo "<p class=\"$message_class\">$message</p>"; ?>

<div class="content_box">

  <p>Please enter your details below:</p>

  <form action="login_check.php" method="post" name="login_form" style="margin-bottom: 2em;">
  <div style="width: 300px;">
    <table class="form" cellpadding="2" cellspacing="1" width="100%">
    <tr>
      <th><label for="username">Username</label></th>
      <td><input type="text" name="username" id="username" maxlength="30" size="10" value="" onfocus="username_focussed=true" onblur="username_focussed=false" /></td>
    </tr>
    <tr>
      <th><label for="password">Password</label></th>
      <td><input type="password" name="password" id="password" maxlength="16" size="10" value="" onfocus="password_focussed=true" onblur="password_focussed=false" /></td>
    </tr>
    </table>

    <div class="form_button_bar">
      <input class="safe_button" type="submit" name="submit" value="login" />
    </div>
  </div>
  </form>
  <p><strong><a href="accounts/reset.php">Forgotten your password?</a></strong></p>
  <p>This site requires cookies - If you have trouble logging in, please check your cookie settings.</p>

</div>

<?php
$UI->content_end(false);
?>
