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
    private DAO $dao;

    public function __construct(EngCIS $cis, $username = null, $password = null)
    {
        parent::__construct($cis, $username, $password);
    }

    // Authenticate the user against the internal database
    public function authenticate()
    {
        $this->_error = null;

        //match the username and password to the values in the database.
        $this->dao = new DAO(APP__DB_HOST, APP__DB_USERNAME, APP__DB_PASSWORD, APP__DB_DATABASE, APP__DB_PORT);

        $dbConn = $this->dao->getConnection();

        $query = 'SELECT * FROM ' . APP__DB_TABLE_PREFIX . 'user WHERE username = ? AND source_id = ""';

        $user = $dbConn->fetchAssociative($query, [$this->username], [ParameterType::STRING]);

        // verify the password provided
        if (password_verify($this->password, $user['password'])) {
            if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                $this->rehashPassword($user['username'], $this->password);
            }

            $this->initialise($user);
        } else {
            // we could be using the old md5 hash. Check this and if it is the case, rehash the password
            $md5HashedPass = md5($this->password);

            if ($md5HashedPass === $user['password']) {
                $this->rehashPassword($user['username'], $this->password);

                $this->initialise($user);
            }
        }

        return $this->_authenticated;
    }

    /**
     * Rehash existing passwords to use the more secure default PHP password hash.
     *
     * @param string $username
     * @param string $password
     *
     * @throws \Doctrine\DBAL\Exception
     */
    private function rehashPassword($username, $password)
    {
        $newHash = password_hash($password, PASSWORD_DEFAULT);

        $updatePasswordQuery =
            'UPDATE ' . APP__DB_TABLE_PREFIX . 'user ' .
            'SET password = ? ' .
            'WHERE username = ?';

        $this->dao->getConnection()->executeQuery(
            $updatePasswordQuery,
            [$newHash, $username],
            [ParameterType::STRING, ParameterType::STRING]
        );
    }
}
