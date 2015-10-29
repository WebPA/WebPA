<?php
/**
 *
 * Class : Authenticate
 *
 * Authenticates the given username and password against the LDAP server
 * In the event of an authentication error, ->get_error() will return:
 * 'connfailed' : A connection to the authentication server could not be established
 *  'invalid'   : The login details were invalid
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 *
 */

require_once(DOC__ROOT.'includes/functions/lib_string_functions.php');

class LDAPAuthenticator extends Authenticator {

/*
================================================================================
  PUBLIC
================================================================================
*/

  /*
  Authenticate the user against the LDAP directory
  */
  function authenticate() {

    global $LDAP__INFO_REQUIRED;

    $this->_authenticated = FALSE;
    $this->_error = NULL;

    //set the debug level
    ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, LDAP__DEBUG_LEVEL);

    //using the ldap function connect with the specified server
    $ldapconn = ldap_connect(LDAP__HOST, LDAP__PORT);

    //check the connection
    if (!$ldapconn) {
      $this->_error = 'connfailed';
      return FALSE;
    }

    //Set this option to cope with Windows Server 2003 Active directories
    ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

    //Set the version of LDAP that we will be using
    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

    if (LDAP__BINDRDN != '') {
      ldap_bind($ldapconn, LDAP__BINDRDN, LDAP__PASSWD);
    } else {
      ldap_bind($ldapconn);
    }
    //construct login name
    $user = $this->username . LDAP__USERNAME_EXT;
    $filter = str_replace('{username}', $user, LDAP__FILTER);

    $info_req = array_values($LDAP__INFO_REQUIRED);
    $info_req[] = LDAP__USER_TYPE_ATTRIBUTE;
    $result = ldap_search($ldapconn, LDAP__BASE, $filter, $info_req);

    //check the bind has worked
    if (!$result) {
      //drop the ldap connection
      ldap_close($ldapconn);
      $this->_error = 'invalid';
      return FALSE;
    }

    $count = ldap_count_entries($ldapconn, $result);
    if ($count == 0) {
      //drop the ldap connection
      ldap_close($ldapconn);
      $this->_error = 'noentriesfound';
      return FALSE;
    }

    $entry = ldap_first_entry($ldapconn, $result);
    $info = ldap_get_attributes($ldapconn, $entry);
    $info = $this->cleanup_entry($info);

    $dn = ldap_get_dn($ldapconn, $entry);

    //bind with the username and password
    $bind = ldap_bind($ldapconn, $dn, $this->password);

    //check the bind has worked
    if (!$bind) {
      // drop the ldap connection
      ldap_close($ldapconn);
      $this->_error = 'bindfailed';
      return FALSE;
    }

    ldap_close($ldapconn);

    $_fields = array('forename' => $info[$LDAP__INFO_REQUIRED['forename']],
      'lastname' => $info[$LDAP__INFO_REQUIRED['lastname']],
      'email' => $info[$LDAP__INFO_REQUIRED['email']],
//            'user_type' => get_LDAP_user_type($info[LDAP__USER_TYPE_ATTRIBUTE]),
    );
    $els = array();
    foreach($_fields as $key => $val) {
      $els[] = "$key = '$val'";
    }

    $DAO = $this->get_DAO();
    if (LDAP__AUTO_CREATE_USER) {

      $sql = 'INSERT INTO ' . APP__DB_TABLE_PREFIX . 'user SET ' . implode(', ', $els) . ", username = '{$this->username}', password = '" . md5(str_random()) . "', source_id = ''";
      $sql .= ' ON DUPLICATE KEY UPDATE ' . implode(', ', $els);
      $DAO->execute($sql);
      $id = $DAO->get_insert_id();
      if (!$id) {
        $sql = 'SELECT user_id FROM '.APP__DB_TABLE_PREFIX."user WHERE username = '{$this->username}' AND source_id = ''";
        $id = $DAO->fetch_value($sql);
      }
      $sql = 'SELECT * FROM ' . APP__DB_TABLE_PREFIX . "user WHERE user_id = $id";

    } else {

      $sql = 'UPDATE ' . APP__DB_TABLE_PREFIX . 'user SET ' . implode(', ', $els) . " WHERE username = '{$this->username}' AND source_id = ''";
      $DAO->execute($sql);
      $sql = 'SELECT * FROM ' . APP__DB_TABLE_PREFIX . "user WHERE username = '{$this->username}' AND source_id = ''";

    }

    return $this->initialise($sql);

  }// /->authenticate()

/*
================================================================================
  PRIVATE
================================================================================
*/
  /**
   * Take an LDAP and make an associative array from it.
   *
   * This function takes an LDAP entry in the ldap_get_entries() style and
   * converts it to an associative array like ldap_add() needs.
   *
   * @param array $entry is the entry that should be converted.
   *
   * @return array is the converted entry.
   */
  private function cleanup_entry($entry) {
    $retEntry = array();

    for ($i = 0; $i < $entry['count']; $i++) {
      $attribute = $entry[$i];
      if ($entry[$attribute]['count'] == 1) {
        $retEntry[$attribute] = $entry[$attribute][0];
      } else {
        for ($j = 0; $j < $entry[$attribute]['count']; $j++) {
          $retEntry[$attribute][] = $entry[$attribute][$j];
        }
      }
    }
    return $retEntry;
  }

}// /class LDAPAuthenticator

?>
