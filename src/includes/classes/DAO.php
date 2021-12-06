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

use Doctrine\DBAL\Connection;
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
     * @param int $port
     *
     * @return void
     */
    public function __construct($host, $user, $password, $database, $port = 3306)
    {
        $connectionParams = [
            'dbname' => $database,
            'user' => $user,
            'password' => $password,
            'host' => $host,
            'driver' => 'mysqli',
            'port' => $port,
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
