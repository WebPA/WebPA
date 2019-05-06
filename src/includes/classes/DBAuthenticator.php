<?php
/**
 * Class : DBAuthenticator
 *
 * Authenticates the given username and password against the internal database
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\includes\classes;

class DBAuthenticator extends Authenticator {

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

    //match the username and password to the values in the database.
    $password = md5($this->password);

    $sql = 'SELECT * FROM ' . APP__DB_TABLE_PREFIX . "user WHERE (username = '{$this->username}') AND (password = '$password') AND (source_id = '')";

    return $this->initialise($sql);

  }// /->authenticate()

/*
================================================================================
  PRIVATE
================================================================================
*/

}// /class DBAuthenticator
