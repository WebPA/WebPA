<?php

/**
 * The database access object which serves a Doctrine DBAL connection.
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\includes\classes;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\DriverManager;

class DAO
{
    private Connection $_conn;

    /**
     * Set up the doctrine connection.
     *
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $database
     * @param boolean $persistent
     *
     * @return void
     */
    function __construct($host, $user, $password, $database, $persistent = false)
    {
        $connectionParams = [
            'dbname' => $database,
            'user' => $user,
            'password' => $password,
            'host' => $host,
            'driver' => 'mysqli'
        ];

        $this->_conn = DriverManager::getConnection($connectionParams);
    }

    /**
     * Return the Doctrine database connection object.
     *
     * @return Connection
     */
    public function getConnection()
    {
        return $this->_conn;
    }
}