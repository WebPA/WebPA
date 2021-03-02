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

use Doctrine\DBAL\ParameterType;

class DBAuthenticator extends Authenticator
{
    public function __construct(EngCIS $cis, $username = null, $password = null)
    {
        parent::__construct($cis, $username, $password);
    }

    // Authenticate the user against the internal database
    public function authenticate()
    {
        $this->_error = null;

        //match the username and password to the values in the database.
        $password = md5($this->password);

        $DAO = new DAO(APP__DB_HOST, APP__DB_USERNAME, APP__DB_PASSWORD, APP__DB_DATABASE);

        $dbConn = $DAO->getConnection();

        $query = 'SELECT * FROM ' . APP__DB_TABLE_PREFIX . 'user WHERE username = ? AND password = ? AND source_id = ""';

        $user = $dbConn->fetchAssociative($query, [$this->username, $password], [ParameterType::STRING, ParameterType::STRING]);

        return $this->initialise($user);
    }

    /*
    ================================================================================
      PRIVATE
    ================================================================================
    */
}// /class DBAuthenticator
