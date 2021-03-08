<?php
/**
 *
 * Class :  Group
 *
 * WARNING : Group objects should only be accessed via their parent GroupCollection
 *                object. This allows them to:
 *
 *                (a) check the LOCK status, and prevent possibly damaging updates.
 *                (b) share the DAO object of the parent collection
 *                (c) access the GroupCollection-ID on demand
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\includes\classes;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use WebPA\includes\functions\Common;

class Group
{
    // Public Vars
    public $id;

    public $name = '';

    public $collection_id = '';   // READONLY

    // Private Vars
    private DAO $_DAO;

    private Connection $dbConn;

    private $_collection;

    private $_members;

    /**
    * CONSTRUCTOR for group
    */
    public function __construct()
    {
        $this->_collection = null;
        $this->_members = null;
    }

    // /->Group()

    /*
    * ================================================================================
    * Public Methods
    * ================================================================================
    */

    /*
    * --------------------------------------------------------------------------------
    * Load/Save Functions
    * --------------------------------------------------------------------------------
    */

    /**
     *  Create a new group id
    */
    public function create()
    {
        // generate a new project_id
        while (true) {
            $new_id = Common::uuid_create();

            $countGroupsQuery =
          'SELECT COUNT(group_id) ' .
          'FROM ' . APP__DB_TABLE_PREFIX . 'user_group ' .
          'WHERE group_id = ?';

            $groupCount = $this->dbConn->fetchOne($countGroupsQuery, [$new_id], [ParameterType::STRING]);

            if ($groupCount == 0) {
                break;
            }
        }
        $this->id = $new_id;
    }

    // ->create()

    /**
    * Delete this Group (and all its members)
    * @return boolean
    */
    public function delete()
    {
        if ($this->_collection->is_locked()) {
            return false;
        }
        $this->dbConn->executeQuery(
                'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'user_group_member WHERE group_id = ?',
                [$this->id],
                [ParameterType::STRING]
            );

        $this->dbConn->executeQuery(
                'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'user_group WHERE group_id = ?',
                [$this->id],
                [ParameterType::STRING]
            );

        return true;
    }

    /*
    * Load the Group from the database
    *
    * @param string $id  id of Group to load
    *
    * @return boolean did load succeed
    */
    public function load($id)
    {
        $groupQuery =
        'SELECT * ' .
        'FROM ' . APP__DB_TABLE_PREFIX . 'user_group ' .
        'WHERE group_id = ? ' .
        'LIMIT 1';

        $row = $this->dbConn->fetchAssociative($groupQuery, [$id], [ParameterType::STRING]);

        return ($row) ? $this->load_from_row($row) : false;
    }

    /**
    * Load the Group from an array row
    *
    * @param array $row assocated array ( field => value, ... ) - corresponds to row in database
    *
    * @return boolean did load succeed
    */
    public function load_from_row(&$row)
    {
        $this->id = $row['group_id'];
        $this->name = $row['group_name'];
        $this->collection_id = $row['collection_id'];
        return true;
    }

    // /->load_from_row()

    /*
    * Save this Group
    *
    * @return boolean did save succeed
    */
    public function save()
    {
        if ((!$this->id) || ($this->_collection->is_locked())) {
            return false;
        }

        if (!is_array($this->_members)) {
            $this->refresh_members();
        }

        // check if the group already exists in the database
        $groupId = $this->dbConn->fetchOne(
            'SELECT group_id FROM ' . APP__DB_TABLE_PREFIX . 'user_group WHERE group_id = ?',
            [$this->id],
            [ParameterType::STRING]
        );

        $queryBuilder = $this->dbConn->createQueryBuilder();

        if (!empty($groupId)) {
            // group already exists so update it
            $queryBuilder
            ->update(APP__DB_TABLE_PREFIX . 'user_group')
            ->set('group_name', '?')
            ->where('group_id = ?')
            ->setParameter(0, $this->name)
            ->setParameter(1, $this->id);
        } else {
            // the group doesn't exist so create it
            $queryBuilder
            ->insert(APP__DB_TABLE_PREFIX . 'user_group')
            ->values([
               'group_id' => '?',
               'collection_id' => '?',
               'group_name' => '?',
            ])
            ->setParameter(0, $this->id)
            ->setParameter(1, $this->_collection->id)
            ->setParameter(2, $this->name);
        }

        $queryBuilder->execute();

        // Save the Group's members by deleting them all, then re-inserting
        $this->dbConn->executeQuery(
            'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'user_group_member WHERE group_id = ?',
            [$this->id],
            [ParameterType::STRING]
        );

        // If there are members to re-insert, do it
        if (count($this->_members) > 0) {
            $fields = null;

            foreach ($this->_members as $user_id => $role) {
                $fields[] = ['group_id'   => $this->id,
                     'user_id'    => $user_id,
                    ];

                // This double-delete nonsense solves a referential integrity problem
                // with students managing to get themselves into TWO groups at once.
                $deleteQuery =
                'DELETE FROM ' . APP__DB_TABLE_PREFIX . 'user_group_member ' .
                'WHERE user_id = ? ' .
                'AND group_id IN ' .
                '( ' .
                'SELECT group_id ' .
                'FROM ' . APP__DB_TABLE_PREFIX . 'user_group ' .
                'WHERE collection_id = ? ' .
                ')';

                $this->dbConn->executeQuery(
                    $deleteQuery,
                    [$user_id, $this->_collection->id],
                    [ParameterType::INTEGER, ParameterType::STRING]
                );
            }

            foreach ($fields as $values) {
                $this->dbConn
                ->createQueryBuilder()
                ->insert(APP__DB_TABLE_PREFIX . 'user_group_member')
                ->values([
                    'group_id' => '?',
                    'user_id' => '?',
                ])
                ->setParameter(0, $this->id)
                ->setParameter(1, $values['user_id'])
                ->execute();
            }
        }

        return true;
    }

    /*
    * --------------------------------------------------------------------------------
    * Accessor Methods
    * --------------------------------------------------------------------------------
    */

    /**
    * Get this Group object's data as an array
    */
    public function get_as_array()
    {
        return ['group_id'   => $this->id,
             'collection_id'=> $this->_collection->id,
             'group_name' => $this->name, ];
    }

    // /->get_as_array()

    /**
    * Set this group to use the given DAO object
    */
    public function set_dao_object(&$DAO)
    {
        $this->_DAO =& $DAO;
        $this->dbConn = $this->_DAO->getConnection();
    }

    /**
    * Set this group to use the given GroupCollection as a parent
    */
    public function set_collection_object(&$collection)
    {
        $this->_collection =& $collection;
        $this->collection_id = $this->_collection->id;
    }

    // /->set_collection_object()

    /*
    * --------------------------------------------------------------------------------
    * Member-Manipulation Methods
    * --------------------------------------------------------------------------------
    */

    /**
    * Add a member to this group
    *
    *
    * @param string $user_id   User ID to add
    * @param string $role   Role within the group
    */
    public function add_member($user_id, $role)
    {
        $this->_members[$user_id] = $role;
    }

    /**
    * Returns the member list belonging to this group
    *
    * @return array array[0..n] of array array ( user_id => user_role )
    */
    public function get_members()
    {
        if (!is_array($this->_members)) {
            $this->refresh_members();
        }
        return $this->_members;
    }

    // /->get_members()

    /**
    * Returns the member list belonging to this group
    *
    * @return array  array[0..n] of array ( user_id => user_role )
    */
    public function get_member_ids()
    {
        if (!is_array($this->_members)) {
            $this->refresh_members();
        }
        return array_keys($this->_members);
    }

    // /->get_member_ids()

    /**
    * Returns a count of the number of members belonging to this group
    *
    * @return integer count of members
    */
    public function get_members_count()
    {
        if (!is_array($this->_members)) {
            $this->refresh_members();
        }
        return count($this->_members);
    }

    // ->get_members_count()

    /**
    * Purge the group of members using include/exclude lists
    * Due to the way the lists work, you need only use one of them at a time
    *
    * @param string|array $target_roles     role to purge
    *                  array of roles to purge
    *                   If a $target_roles list is given (not null), and a user's role is not in it, they are kept regardless of the $protect_list
    *                   If a user's role is in the $target_roles list, they are removed
    *
    * @param string|array $protect_roles  single role to keep
    *                   array of roles to keep
    *                   If a user's role is in the $protect_roles list, they are kept
    *@return boolean
    */
    public function purge_members($target_roles = null, $protect_roles = null)
    {
        if (!is_array($this->_members)) {
            $this->refresh_members();
        }

        // If we're purging everything, do it
        if ((!$target_roles) && (!$protect_roles)) {
            $this->_members = [];  // empty array, not NULL, as that would usually trigger a ->refresh_members() call in other member functions
        } else {
            $post_purge_members = [];

            // If there's a target list.. save the untargetted roles
            if ($target_roles) {
                $target_roles = (array) $target_roles;
                foreach ($this->_members as $user_id => $role) {
                    if (!in_array($role, $target_roles)) {
                        $post_purge_members[$user_id] = $role;
                    }
                }// /for
            } else {  // Else.. save every user with a protected role
        $protect_roles = (array) $protect_roles;
                foreach ($this->_members as $user_id => $role) {
                    if (in_array($role, $protect_roles)) {
                        $post_purge_members[$user_id] = $role;
                    }
                }// /for
            }
            $this->_members = $post_purge_members;
        }
        return true;
    }

    // /->purge_members()

    /**
    * Refresh the member list for this object from the database
    * Any unsaved-changes made to the members list are lost
    */
    public function refresh_members()
    {
        $membersQuery =
        'SELECT user_id, "member" AS user_role ' .
        'FROM ' . APP__DB_TABLE_PREFIX . 'user_group_member ' .
        'WHERE group_id = ? ' .
        'ORDER BY user_id ASC';

        $this->_members = $this->dbConn->fetchAllKeyValue($membersQuery, [$this->id], [ParameterType::STRING]);

        if (!$this->_members) {
            $this->_members = [];
        }
    }

    /**
    * Remove a member from this group
    *
    * @param string $user_id  User ID to remove
    */
    public function remove_member($user_id)
    {
        if (!is_array($this->_members)) {
            $this->refresh_members();
        }

        if (array_key_exists($user_id, $this->_members)) {
            unset($this->_members[$user_id]);
        }
    }
}
