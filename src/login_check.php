<?php
/**
 * Check login credentials
 *
 * The file will also check the type of login that is taking place.
 *
 * Given that the DBAuthenticator never returns an error message, changed the returned
 * message in case of incorrect username/password to 'invalid', as with an empty username
 * or password, made by Morgan Harris [morgan@snowproject.net] as of 15/10/09
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA;

require_once './includes/inc_global.php';

use WebPA\includes\functions\Common;

// --------------------------------------------------------------------------------
// Process Get/Post

$username = (string) Common::fetch_POST('username');
$password = (string) Common::fetch_POST('password');

$msg ='';

// --------------------------------------------------------------------------------
// Attempt Login

$auth_success = false;

if (($username) && ($password)) {
    $authenticated = false;

    // Authenticate...
    for ($i = 0; $i < count($LOGIN_AUTHENTICATORS); $i++) {
        $classname = 'WebPA\includes\classes\\' . $LOGIN_AUTHENTICATORS[$i] . 'Authenticator';

        $_auth = new $classname($CIS, $username, $password);

        if ($_auth->authenticate()) {
            $authenticated = true;
            break;
        }
    }

    if (!$authenticated) {
        $msg = 'invalid';

        //but just to be sure check for an authorisation failed message
        $auth_failed = $_auth->get_error();

        if (strlen($auth_failed) > 0) {
            $msg = $auth_failed;
        }
    } elseif ($_auth->is_disabled()) {
        $msg = 'no access';
    } else {
        $_SESSION = [];
        session_destroy();
        session_name(SESSION_NAME);
        session_start();

        // Save session data
        $_SESSION['_user_id'] = $_auth->user_id;
        $_SESSION['_user_source_id'] = $_auth->source_id;
        $_SESSION['_user_type'] = $_auth->user_type;
        $_SESSION['_source_id'] = $_auth->source_id;
        $_SESSION['_module_id'] = $_auth->module_id;
        $_SESSION['_user_context_id'] = $_auth->module_code;

        Common::logEvent($DB, 'Login');
        Common::logEvent($DB, 'Enter module', $_auth->module_id);

        header('Location: ' . APP__WWW . "/index.php?id={$_user_id}"); // This doesn't log them in, the user_id just shows as a debug check
        exit;
    }
} else {
    $msg = '';
}

header('Location: ' . APP__WWW . "/login.php?msg={$msg}");
exit;
