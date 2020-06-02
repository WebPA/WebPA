<?php
/**
 * Class : Authenticate
 *
 * Authenticates the given username and password against the LDAP server
 * In the event of an authentication error, ->get_error() will return:
 * 'connfailed' : A connection to the authentication server could not be established
 *  'invalid'   : The login details were invalid
 *
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */


namespace WebPA\includes\classes;

use WebPA\includes\functions\StringFunctions;

class LDAPAuthenticator extends Authenticator
{

    private $ldapRequiredFields;

    public function __construct($username = NULL, $password = NULL)
    {
        parent::__construct($username, $password);

        global $LDAP__INFO_REQUIRED;

        $this->ldapRequiredFields = $LDAP__INFO_REQUIRED;
    }

    /**
     * Authenticate users against the LDAP server.
     *
     * @return bool
     */
    function authenticate()
    {
        $this->_authenticated = false;
        $this->_error = null;

        //set the debug level
        ldap_set_option(null, LDAP_OPT_DEBUG_LEVEL, LDAP__DEBUG_LEVEL);

        //using the ldap function connect with the specified server
        $ldapconn = ldap_connect(LDAP__HOST, LDAP__PORT);

        //check the connection
        if (!$ldapconn) {
            $this->_error = 'connfailed';

            return false;
        }

        //Set this option to cope with Windows Server 2003 Active directories
        ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

        //Set the version of LDAP that we will be using
        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

        //construct login name
        $user = $this->username . LDAP__USERNAME_EXT;

        //bind with the username and password
        $bind = ldap_bind($ldapconn, $user, $this->password);

        //check the bind has worked
        if (!$bind) {
            // drop the ldap connection
            ldap_unbind($ldapconn);
            $this->_error = 'connfailed';

            return false;
        }

        $filter = str_replace('{username}', $this->username, LDAP__FILTER);

        $info_req = $this->ldapRequiredFields;
        $info_req[] = LDAP__USER_TYPE_ATTRIBUTE;
        $result = ldap_search($ldapconn, LDAP__BASE, $filter, $info_req);

        //check the bind has worked
        if (!$result) {
            //drop the ldap connection
            ldap_unbind($ldapconn);
            $this->_error = 'invalid';

            return false;
        }

        $info = ldap_get_entries($ldapconn, $result);

        ldap_unbind($ldapconn);

        if ($info['count'] == 0) {
            return false;
        }

        $_fields = [
            'forename' => $info[0]['givenname'][0],
            'lastname' => $info[0]['sn'][0],
            'email' => $info[0]['mail'][0],
            'user_type' => get_LDAP_user_type($info[0][LDAP__USER_TYPE_ATTRIBUTE]),
        ];

        $els = [];

        foreach ($_fields as $key => $val) {
            $els[] = "$key = '$val'";
        }

        $DAO = $this->get_DAO();
        if (LDAP__AUTO_CREATE_USER) {

            $sql = 'INSERT INTO ' . APP__DB_TABLE_PREFIX . 'user SET ' . implode(', ', $els) . ", username = '{$this->username}', password = '" . md5(StringFunctions::str_random()) . "', source_id = ''";
            $sql .= ' ON DUPLICATE KEY UPDATE ' . implode(', ', $els);
            $DAO->execute($sql);
            $id = $DAO->get_insert_id();
            if (!$id) {
                $sql = 'SELECT user_id FROM ' . APP__DB_TABLE_PREFIX . "user WHERE username = '{$this->username}' AND source_id = ''";
                $id = $DAO->fetch_value($sql);
            }
            $sql = 'SELECT * FROM ' . APP__DB_TABLE_PREFIX . "user WHERE user_id = $id";

        } else {

            $sql = 'UPDATE ' . APP__DB_TABLE_PREFIX . 'user SET ' . implode(', ', $els) . " WHERE username = '{$this->username}' AND source_id = ''";
            $DAO->execute($sql);
            $sql = 'SELECT * FROM ' . APP__DB_TABLE_PREFIX . "user WHERE username = '{$this->username}' AND source_id = ''";

            $sql = 'SELECT * FROM ' . APP__DB_TABLE_PREFIX . "user WHERE username = '{$this->username}' AND source_id = ''";
        }

        return $this->initialise($sql);
    }
}
