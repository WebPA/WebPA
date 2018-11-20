<?php
/**
 *
 * Authenticate user against SAML Identity Prvider
 *
 * The file will also check the type of login that is taking place.
 * 
 * This file is based on login_check.php developed by Loughborough University
 * and extended by ProofID to include SAML authentication 
 *
 * @copyright 2007 Loughborough University
 * Portions @copyright 2014 ProofID Ltd
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.1
 * 
 * 
 * INSTALLATION NOTES
 * The WebPA session name (SESSION_NAME) must match the session name within SimpleSAMLPHP
 * otherwise session information will be lost
 *
 */

// --------------------------------------------------------------------------------
// Process Get/Post

require_once("./includes/inc_global.php");


$simplesaml_autoload = SAML__SIMPLESAMLPATH . "/lib/_autoload.php";

if (!file_exists($simplesaml_autoload))
{
    echo "<h2>Path to simpleSAMLphp is incorrect please fix configuration</h2>";
    exit;
}

require_once ($simplesaml_autoload);


$msg ='';

// --------------------------------------------------------------------------------
// Attempt Login

$auth_success = false;

try
{
    $as = new SimpleSAML_Auth_Simple(SAML__SP_NAME);
    $as->requireAuth();
    $valid_saml_session = $as->isAuthenticated();
    $saml_attributes = $as->getAttributes();
}
catch (Exception $e)
{
    saml_error($e->getMessage(), $urltogo, $pluginconfig->samllogfile);
}

if ( $valid_saml_session )
{

    $authenticated = FALSE;
    
    require_once(DOC__ROOT . 'includes/classes/class_authenticator.php');

    for ($i = 0; $i < count($LOGIN_AUTHENTICATORS); $i++) {
        $classname = $LOGIN_AUTHENTICATORS[$i];
        require_once(DOC__ROOT . 'includes/classes/class_' . strtolower($classname) . '_authenticator.php');
        $classname .= "Authenticator";
        $_auth = new $classname($saml_attributes[SAML__USERNAME_ATTRIBUTE][0]);
        if ($_auth->authenticate()) {
            $authenticated = TRUE;
            break;
        }
    }
    
    if (!$authenticated) {

        $msg = 'invalid';
    
        //but just to be sure check for an authorisation failed message
        $auth_failed = $_auth->get_error();
    
        if(strlen($auth_failed) > 0){
            $msg = $auth_failed;
        }
    
    } else if ($_auth->is_disabled()) {

        $msg = 'no access';
    
    } else {
    
        $_SESSION = array();
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
    
        logEvent('Login');
        logEvent('Enter module', $_auth->module_id);
    
        header('Location: ' . APP__WWW . "/index.php?id={$_user_id}"); // This doesn't log them in, the user_id just shows as a debug check
        exit;
    }


} else {

  $msg = 'no access';

}

header('Location: ' . APP__WWW . "/login.php?msg={$msg}");
exit;

?>