<?php
/**
 *
 * Class : SAMLAuthenticator
 *
 * Authenticates the given username and password against SAML
 *
 *
 *
 */

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

    $sql = 'SELECT * FROM ' . APP__DB_TABLE_PREFIX . "user WHERE username = '{$this->username}' AND password = '" . md5($this->password). "'";

    return $this->initialise($sql);

  }// /->authenticate()

/*
================================================================================
  PRIVATE
================================================================================
*/

}// /class DBAuthenticator

?>
