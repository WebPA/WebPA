<?php
/**
 * Class : Module
 *
 * This is a lightweight module class and does not contain the database access stuff
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\includes\classes;

use Doctrine\DBAL\ParameterType;

class Module
{
    // Public Vars
    public $module_code;

    public $module_title;

    public $module_id;

    public DAO $DAO;

    /**
    * CONSTRUCTOR for the class function
    * @param string $code
    * @param string $title
    */
    public function __construct($module_code = null, $module_title = null)
    {
        $this->module_code = $module_code;
        $this->module_title = $module_title;
        $this->module_id = null;
    }

    // /->Module()

    /*
    * ================================================================================
    * PUBLIC
    *================================================================================
    */

    /**
    * Load the object from the given data
    *
    * @param array $module_info  assoc-array of Module info
    *
    * @return boolean did the load succeed
    */
    public function load_from_row($module_info)
    {
        if (is_array($module_info) && isset($module_info['module_id'])) {
            $this->module_id = $module_info['module_id'];
            $this->module_code = $module_info['module_code'];
            $this->module_title = $module_info['module_title'];
        }
        return true;
    }

    // /->load_from_row()

    /**
     * Function to update the module details
     */
    public function save_module()
    {
        $dbConn = $this->DAO->getConnection();

        $stmt = $dbConn->prepare('UPDATE ' . APP__DB_TABLE_PREFIX . 'module SET module_code = ?, module_title = ? WHERE module_id = ?');

        $stmt->bindValue(1, $this->module_code);
        $stmt->bindValue(2, $this->module_title);
        $stmt->bindValue(3, $this->module_id, ParameterType::INTEGER);

        $stmt->execute();

        return true;
    }

    /**
     * Function to set the database connection to be used
     * @param DAO connection $DB
     */
    public function set_dao_object(DAO $DB)
    {
        $this->DAO = $DB;
    }

    /**
     * Function to add new module details
     */
    public function add_module()
    {
        $addModuleQuery =
        'INSERT INTO ' . APP__DB_TABLE_PREFIX . 'module ' .
        '(module_code, module_title) ' .
        'VALUES (?, ?)';

        $stmt = $this->DAO->getConnection()->prepare($addModuleQuery);

        $stmt->bindValue(1, $this->module_code);
        $stmt->bindValue(2, $this->module_title);

        $stmt->execute();

        return $this->DAO->getConnection()->lastInsertId('module_id');
    }

    /**
     * Function to delete a module
     */
    public function delete()
    {
        $deleteModuleQuery =
           'SELECT collection_id ' .
           'FROM ' . APP__DB_TABLE_PREFIX . 'collection ' .
           'WHERE module_id = ?';

        $collections = $this->DAO->getConnection()->fetchFirstColumn($deleteModuleQuery, [$this->module_id], [ParameterType::INTEGER]);

        $group_handler = new GroupHandler();

        for ($i=0; $i<count($collections); $i++) {
            $collection = $group_handler->get_collection($collections[$i]);
            $collection->delete();
        }

        $dbConn = $this->DAO->getConnection();

        $dbConn->executeQuery(
            'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'user_module WHERE module_id = ?',
            [$this->module_id],
            [ParameterType::INTEGER]
        );

        $dbConn->executeQuery(
            'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'module WHERE module_id = ?',
            [$this->module_id],
            [ParameterType::INTEGER]
        );

        $this->module_code = null;
        $this->module_title = null;
        $this->module_id = null;
    }

    /*
    * ================================================================================
    * PRIVATE
    * ================================================================================
    */
}// /class: Module
