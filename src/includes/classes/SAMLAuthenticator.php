<?php
/**
 *
 * Class : SAMLAuthenticator
 *
 * Authenticates the given username and password against SAML
 */

namespace WebPA\includes\classes;

class SAMLAuthenticator extends Authenticator {

/*
================================================================================
  PUBLIC
================================================================================
*/

  /*
  Authenticate the user against the internal database
  */
  function authenticate() {

    $this->_error = NULL;

    $sql = 'SELECT * FROM ' . APP__DB_TABLE_PREFIX . "user WHERE username = '{$this->username}'";

    return $this->initialise($sql);

  }// /->authenticate()

/*
================================================================================
  PRIVATE
================================================================================
*/

}// /class DBAuthenticator

?>
