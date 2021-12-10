<?php
/**
 * Class : GroupHandler
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\includes\classes;

use Doctrine\DBAL\ParameterType;

class GroupHandler
{
    public $_DAO;  // [pmn] due to a poor iterator implementation, this is currently public

    private $dbConn;

    /**
     * CONSTRUCTOR
     */
    public function __construct()
    {
        $this->_DAO = new DAO(APP__DB_HOST, APP__DB_USERNAME, APP__DB_PASSWORD, APP__DB_DATABASE, APP__DB_PORT);
        $this->dbConn = $this->_DAO->getConnection();
    }

    /*
    * ================================================================================
    * Public Methods
    * ================================================================================
    */

    /**
     * function to generate the group names
     * @param string $num_groups
     * @param string $group_name_stub
     * @param string $group_numbering
     * @return array group names
     */
    public function generate_group_names($num_groups, $group_name_stub = 'Group', $group_numbering = 'numeric')
    {
        $group_names = null;

        /**
         * Select which numbering function to use
         * @param string $group_numbering
         */
        switch ($group_numbering) {
            case 'alphabetic':
                $suffix_function = 'group_suffix_alphabetic';

                break;
            case 'hashed':
                $suffix_function = 'group_suffix_hashed';

                break;
            default:
                $suffix_function = 'group_suffix_numeric';

                break;
        }

        // Generate the names
        for ($i = 1; $i <= $num_groups; $i++) {
            $group_suffix = $this->$suffix_function($i);
            $group_names[] = "$group_name_stub $group_suffix";
        }

        return $group_names;
    }

    /**
     *  Generate group numbering (alphabetic) - LIMITED TO A thru AA to ZZ ONLY!
     * @param string $group_num
     * @return array group names
     */
    private function group_suffix_alphabetic($group_num)
    {
        $group_num--;
        $prefix = '';

        if ($group_num >= 26) {
            $prefix = chr(64 + (int) ($group_num / 26));
        }

        $suffix = chr(65 + ($group_num % 26));

        return "$prefix$suffix";
    }

    /**
     * Generate group numbering (hashed-numeric)
     * @param string $group_num
     * @return string
     */
    private function group_suffix_hashed($group_num)
    {
        return "#$group_num";
    }

    /**
     * Generate group numbering (numeric)
     * @param string $group_num
     * @param string
     */
    private function group_suffix_numeric($group_num)
    {
        return $group_num;
    }

    /*
    * --------------------------------------------------------------------------------
    * GroupCollection Manipulation Methods
    * --------------------------------------------------------------------------------
    */

    /**
     * Clone an existing collection
     *
     * Creates a copy of the GroupCollection (including settings/members), saves it to the database, and returns the new object
     *
     * @param string $collection_id collection to clone
     * @param array $include_roles roles to include when copying members (members with other roles are not copied)
     *
     * @return object  GroupCollection object
     */
    public function & clone_collection($collection_id, $include_roles = null)
    {
        // get the collection to clone
        $org_collection = new GroupCollection($this->_DAO);
        $org_collection->load($collection_id);

        $group_iterator =& $org_collection->get_groups_iterator();

        // clone the collection
        $clone_collection = new GroupCollection($this->_DAO);
        $clone_collection->load($collection_id);
        $clone_collection->create();  // create a new ID number
        $clone_collection->save();

        // clone all the groups
        $group_iterator =& $org_collection->get_groups_iterator();
        for ($group_iterator->reset(); $group_iterator->is_valid(); $group_iterator->next()) {
            $org_group = $group_iterator->current();

            /*
            The groups we have are attached to the original collection
            By giving them a new ID number and setting their owner collection object, we can clone them!
            */

            $org_group->set_dao_object($this->_DAO);
            $org_group->set_collection_object($org_collection);
            $org_group->refresh_members();

            // clone the group
            $clone_group =& $org_group;
            $org_group->set_collection_object($clone_collection);
            $clone_group->create(); // create a new ID number

            // Kill all members but those in the given roles
            if ($include_roles) {
                $clone_group->purge_members(null, $include_roles);
            }
            $clone_group->save();
        }

        return $clone_collection;
    }

    // /->clone_collection()

    /**
     * Create a new GroupCollection object (gives it a new UUID and returns the object)
     *
     * WARNING : The new GroupCollection object is NOT SAVED automatically
     *
     * @return array
     */
    public function & create_collection()
    {
        $new_collection = new GroupCollection($this->_DAO);
        $new_collection->create();
        return $new_collection;
    }

    // /->create_collection()

    /**
     * Get a GroupCollection object corresponding to the given group_set_id
     *
     * @param string $collection_id ID of GroupCollection to fetch
     * @return object GroupCollection object
     */
    public function get_collection($collection_id)
    {
        $collection = new GroupCollection($this->_DAO);
        return ($collection->load($collection_id)) ? $collection : null;
    }

    // /->get_collection()

    /**
     * Get collections belonging to the given user
     *
     * @param string $user_id ID number of the owner
     * @param string $application_id (optional) name of owner-application to search for
     *
     * @return array array of collections
     */
    public function get_user_collections($user_id)
    {
        $query =
            'SELECT c.* ' .
            'FROM ' . APP__DB_TABLE_PREFIX . 'collection c ' .
            'INNER JOIN ' . APP__DB_TABLE_PREFIX . 'user_module um ' .
            'ON c.module_id = um.module_id ' .
            'LEFT OUTER JOIN ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
            'ON c.collection_id = a.collection_id ' .
            'WHERE um.user_id = ? ' .
            'AND a.collection_id IS NULL';

        return $this->dbConn->fetchAllAssociative($query, [$user_id], [ParameterType::INTEGER]);
    }

    /**
     * Get collections belonging to the given module
     *
     * @param string $module_id ID number of the module
     *
     * @return array array of collections
     */
    public function get_module_collections($module_id)
    {
        $query =
            'SELECT c.*, a.assessment_id AS collection_assessment_id ' .
            'FROM ' . APP__DB_TABLE_PREFIX . 'collection c ' .
            'LEFT OUTER JOIN ' . APP__DB_TABLE_PREFIX . 'assessment a ' .
            'ON c.collection_id = a.collection_id ' .
            'WHERE c.module_id = ? ' .
            'AND a.collection_id IS NULL ' .
            'ORDER BY c.collection_name';

        return $this->dbConn->fetchAllAssociative($query, [$module_id], [ParameterType::INTEGER]);
    }

    /**
     * function to get member collections
     * @param string $user_id ID of the member
     * @param string $owner_type (optional) type of collection-owner to filter against
     *
     * @return array array of collections
     */
    public function get_member_collections($user_id, $owner_type)
    {
        if ($owner_type === 'user') {
            $sql = 'SELECT DISTINCT c.*, NULL AS collection_assessment_id FROM ' . APP__DB_TABLE_PREFIX .
                'collection c INNER JOIN ' . APP__DB_TABLE_PREFIX .
                'user_group ug ON c.collection_id = ug.collection_id INNER JOIN ' . APP__DB_TABLE_PREFIX .
                'user_group_member ugm ON ug.group_id = ugm.group_id LEFT OUTER JOIN ' . APP__DB_TABLE_PREFIX .
                'assessment a ON a.collection_id = c.collection_id WHERE ugm.user_id = ? AND a.collection_id IS NULL';
        } elseif ($owner_type === 'assessment') {
            $sql = 'SELECT DISTINCT c.*, a.assessment_id AS collection_assessment_id FROM ' . APP__DB_TABLE_PREFIX .
                'collection c INNER JOIN ' . APP__DB_TABLE_PREFIX .
                'user_group ug ON c.collection_id = ug.collection_id INNER JOIN ' . APP__DB_TABLE_PREFIX .
                'user_group_member ugm ON ug.group_id = ugm.group_id INNER JOIN ' . APP__DB_TABLE_PREFIX .
                'assessment a ON a.collection_id = c.collection_id WHERE ugm.user_id = ?';
        }

        return $this->dbConn->fetchAllAssociative($sql, [$user_id], [ParameterType::INTEGER]);
    }

    /*
    * ================================================================================
    * Private Methods
    * ================================================================================
    */
}// /->class: GroupHandler
