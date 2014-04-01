<?php
/**
 * 
 * Class :  GroupCollection
 *  
 * Applications using GroupCollection should call ->lock() once a group has been used
 * to prevent any changes being made to the group from then on.
 * 
 * Once locked, the GroupCollection will not allow SAVING or DELETING, or MEMBER editing
 * 			
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.1.0
 * @since 11-10-2005
 * 
 */

require_once('class_dao.php');
require_once('class_group.php');


class GroupCollection {
	// Public Vars
	public $id = null;
	public $name = '';

	// Private Vars
	private $_DAO = null;

	private $_groups = null;
	private $_group_objects = null;
	private $_modules = null;
	
	private $_created_on = null;
	private $_locked_on = null;
	
	private $_owner_id = null;
	private $_owner_app = null;
	private $_owner_type = null;


	/**
	* CONSTRUCTOR for the Group collection function
	*/
	function GroupCollection(&$DAO) {
		$this->_DAO =& $DAO;
		$this->_created_on = mktime();
		$this->_groups = null;
		$this->_group_objects = null;
		$this->_modules = null;
		$this->_owner_id = null;
		$this->_owner_app = null;
		$this->_owner_type = null;
	}// /->GroupCollection()


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
	 * 	Create a new GroupCollection (generates unique collection_id)
	*/	
	function create() {
		while (true) {
			$new_id = uuid_create();
			if ($this->_DAO->fetch_value("SELECT COUNT(collection_id) AS num_id FROM user_collection WHERE collection_id='$new_id' ")==0) {	break; }
		}
		$this->id = $new_id;
	}// ->create()


	/**
	* Load the GroupCollection from the database
	*
	* @param string $id	 id of GroupCollection to load
	*
	* @return boolean did load succeed
	*/
	function load($id) {
		$row = $this->_DAO->fetch_row("SELECT * FROM user_collection WHERE collection_id='$id' LIMIT 1");
		return ($row) ? $this->load_from_row($row) : false;
	}// /->load()


	/**
	* Load the GroupCollection from an array row
	*
	* @param array $row	assoc array of ( field => value ) - corresponds to row in database
	*
	* @return boolean
	*/
	function load_from_row(&$row) {
		$this->id = $row['collection_id'];
		$this->name = $row['collection_name'];
		$this->_created_on	= strtotime($row['collection_created_on']);
		$this->_locked_on	= ( (is_null($row['collection_locked_on'])) ? null : strtotime($row['collection_locked_on']) );
		$this->_owner_id = $row['collection_owner_id'];
		$this->_owner_app = $row['collection_owner_app'];
		$this->_owner_type = $row['collection_owner_type'];
		return true;
	}// /->load_from_row()


	/**
	* Delete this GroupCollection (and all its groups, members, and module links)
	* If the collection is locked, the delete will fail
	*
	* @retutn  boolean did deletion succeed
	*/
	function delete() {
		if ($this->is_locked()) {
			return false;
		} else { 
			$this->_DAO->execute("DELETE FROM user_collection WHERE collection_id='{$this->id}' ");
			$this->_DAO->execute("DELETE FROM user_collection_module WHERE collection_id='{$this->id}' ");
			$this->_DAO->execute("DELETE FROM user_group WHERE collection_id='{$this->id}' ");
			$this->_DAO->execute("DELETE FROM user_group_member WHERE collection_id='{$this->id}' ");
			return true;
		}
	}// /->delete()


	/**
	* Save this GroupCollection
	*
	* @return boolean did save succeed
	*/
	function save() {
		if ( (!$this->id) || ($this->is_locked()) ) {
			return false;
		}	else {
			// load all necessary info
			$this->get_modules();
		
			$fields = array ('collection_id'			=> $this->id ,
							 'collection_name'			=> $this->name ,
							 'collection_created_on'	=> date(MYSQL_DATETIME_FORMAT,$this->_created_on) ,
							 'collection_locked_on'		=> ( (!$this->_locked_on) ? null : date(MYSQL_DATETIME_FORMAT,$this->_locked_on) ) ,
							 'collection_owner_id'		=> $this->_owner_id ,
							 'collection_owner_app'		=> $this->_owner_app ,
							 'collection_owner_type'	=> $this->_owner_type ,
							);

			// Save this collection
			$this->_DAO->do_insert("REPLACE INTO user_collection ({fields}) VALUES ({values}) ",$fields);

			// Save this collection's modules by deleting them all, then re-inserting
			$this->_DAO->execute("DELETE FROM user_collection_module WHERE collection_id='{$this->id}' ");
			if (is_array($this->_modules)) {
				$fields = null;
				foreach($this->_modules as $module_id) {
					$fields[] = array (
															'collection_id'	=> $this->id ,
															'module_id'			=> $module_id ,
														);
				}
				if (is_array($fields)) {
					$this->_DAO->do_insert_multi('INSERT INTO user_collection_module ({fields}) VALUES {values} ', $fields);
				}
			}
			return true;
		}
	}// /->save()

	
	/**
	* Save open Group objects attached to this GroupCollection
	* Loops through the group objects opened through the GroupCollection and saves any changes
	*/
	function save_groups() {
		if (is_array($this->_group_objects)) {
			foreach ($this->_group_objects as $group_id => $group) {
				if (is_object($this->_group_objects[$group_id])) { $this->_group_objects[$group_id]->save(); }
			}
		}
	}// /->save_groups()


/*
* --------------------------------------------------------------------------------
* Accessor Methods
* --------------------------------------------------------------------------------
*/


	/**
	* Returns owner application id
	*
	* @return  string owner application id
	*/
	function get_owner_app() {
		return $this->_owner_app;
	}// /->get_owner_app()
	

	/**
	* Returns owner user id
	*
	* @return string owner user id
	*/
	function get_owner_id() {
		return $this->_owner_id;
	}// /->get_owner_id()

	
	/**
	* Set the GroupCollection's owner info
	*/
	function set_owner_info($id, $application, $type) {
		$this->_owner_id = $id;
		$this->_owner_app = $application;
		$this->_owner_type = $type;
	}// /->set_owner_info()


	/**
	* Is this GroupCollection locked?
	*
	* @return boolean is the collection locked
	*/
	function is_locked() {
		return ( (!is_null($this->_locked_on)) || ($this->_locked_on) ) ;
	}// /->is_locked()

	
	/**
	* Is this GroupCollection owned by the given id/application/type?
	*
	* @param string $id ID of the user/thing being checked
	* @param string $application string Application being checked (optional)
	* @param string $type 'thing' type being checked (optional)
	*
	* @return boolean is this the owner
	*/
	function is_owner($id, $application = null, $type = null) {
		return ( ($this->_owner_id==$id) && ( (is_null($application)) xor ($this->_owner_app==$application) ) && ( (is_null($type)) xor ($this->_owner_type==$type) ) );
	}// /->is_owner()


/*
* --------------------------------------------------------------------------------
* Other Methods
* --------------------------------------------------------------------------------
*/

	
	/**
	* Lock the collection - should prevent applications editing/deleting the groups involved
	* The locked_on datetime is IMMEDIATELY SAVED to the database (no other fields are saved)
	*/
	function lock() {
		$this->_locked_on = mktime();
		if ($this->id) {
			$_fields = array	(
													'collection_locked_on'	=> date(MYSQL_DATETIME_FORMAT,$this->_locked_on) ,
												);
			$this->_DAO->do_update("UPDATE user_collection ({fields}) WHERE collection_id='{$this->id}' ",$_fields);
		}
	}// /->lock()


/*
* --------------------------------------------------------------------------------
* Group Manipulation Methods
* --------------------------------------------------------------------------------
*/

/*
* ----------------------------------------
* These methods only manipulate the group data
* ----------------------------------------
*/

	/**
	* Returns an array of groups belonging to this GroupCollection
	*
	* @return assoc array ( group_name => group_id )
	*/
	function get_groups_array() {
		if (!$this->_groups) { $this->refresh_groups(); }
		return $this->_groups;
	}// /->get_groups_array()
	
	
	/**
	* Check if there is a group in this GroupCollection with the given name
	* @param string $group_name
	* @return	boolean
	*/
	function group_exists($group_name) {
		if (!$this->_groups) { $this->refresh_groups(); }
		return (array_key_exists($group_name, $this->_groups));
	}// /->group_exists()

	
	/**
	 * Check if the group exists
	 * @param mixed $group_id
	 * @return bool  does the group exist in this collection
	*/
	function group_id_exists($group_id) {
		if (!$this->_groups) { $this->refresh_groups(); }

		$is_valid_group = false;
		foreach($this->_groups as $i => $group_row) {
			if ($group_row['group_id']==$group_id) { $is_valid_group = true; break; }
		}

		return $is_valid_group;
	}// /->group_id_exists()

	

	/**
	* Refresh this collection's list of groups
	*/
	function refresh_groups() {
		$this->_groups = $this->_DAO->fetch("
			SELECT *
			FROM user_group
			WHERE collection_id='{$this->id}'
			ORDER BY LENGTH(group_name) ASC, group_name ASC
		");
		if (!$this->_groups) { $this->_groups = array(); }
	}// /->refresh_groups()


/*
* ----------------------------------------
* These methods manipulate the group objects
* ----------------------------------------
*/


	/**
	* Add the given Group object to the GroupCollection object
	* If the Group's ID already exists, the existing reference will be replaced
	*
	* @param object $group_object The group to add
	*/
	function add_group_object(&$group_object) {
		if (!is_array($this->_groups)) { $this->refresh_groups(); }
		
		if (is_object($group_object)) {
			$this->_groups[$group_object->id] = $group_object->get_as_array();
			$this->_group_objects["{$group_object->id}"] =& $group_object;
			$group_object->set_collection_object($this);
		}
	}// /->add_group_object()


	/**
	* Get the group object corresponding to the given group_id
	* If you want to get a list of all the objects, use ->get_group_iterator() instead
	*
	* @param string $group_id	Group ID to fetch
	*
	* @return object Group object (or NULL)
	*/
	function & get_group_object($group_id) {
		if (!is_array($this->_groups)) { $this->refresh_groups(); }

		// If this group exists in this collection
		if ($this->group_id_exists($group_id)) {
			// If we already have a copy of the Group object, return it
			if ( (array_key_exists($group_id, (array) $this->_group_objects)) && (is_object($this->_group_objects[$group_id])) ) {
				return $this->_group_objects[$group_id];
			} else {
				$new_group =& new Group();
				$new_group->set_dao_object($this->_DAO);
				$new_group->set_collection_object($this);
				$new_group->load($group_id);
				$this->_group_objects[$group_id] =& $new_group;
				return $new_group;
			}
		}
		return null;
	}// /->get_group_object()

	
	/**
	* Create a new Group object using this GroupCollection as the parent
	* Adds the new Group to the GroupCollection's group list
	*
	* @param string $group_name	Name of new group to add
	* @return array
	*/
	function & new_group($group_name = 'new group') {
		$new_group =& new Group();
		$new_group->set_dao_object($this->_DAO);
		$new_group->create();
		$new_group->name = $group_name;
		$new_group->set_collection_object($this);

		$this->_group[$new_group->id] = $new_group->get_as_array();
		$this->_group_objects[$new_group->id] =& $new_group;
		return $new_group;
	}// /->new_group()

	
	/**
	* Get an iterator object containg the groups belonging to this collection
	*
	* @return object GroupIterator object
	*/
	function & get_groups_iterator() {
		if (!$this->_groups) { $this->refresh_groups(); }

		require_once('class_simple_iterator.php');
		foreach($this->_groups as $i => $group_row) {
			$this->get_group_object($group_row['group_id']);
		}
		$iterator =& new SimpleIterator( $this->_group_objects );
		return $iterator;
	}// /->get_groups_iterator()
	
	
/*
* --------------------------------------------------------------------------------
* Member-Manipulation Methods
* --------------------------------------------------------------------------------
*/


	/**
	* Get a count of the members in this collection
	*
	* @param string $role	 (optional) user role to search for
	*
	* @return integer
	*/
	function get_member_count($role = null) {
		$role_clause = ($role) ? " AND user_role='$role'" : '' ;
		
		return $this->_DAO->fetch_value("SELECT COUNT(user_id)
										FROM user_group_member
										WHERE collection_id='{$this->id}' $role_clause
										");
	}// /->get_member_count()
	
	
	/**
	* Get a count of the members in this collection by group
	*
	* @param string $role	(optional) user role to search for
	*
	* @return array  array ( group_id => member_count )
	*/
	function get_member_count_by_group($role = null) {
		$role_clause = ($role) ? " AND user_role='$role'" : '' ;
		
		return $this->_DAO->fetch_assoc("SELECT group_id, COUNT(user_id)
										FROM user_group_member
										WHERE collection_id='{$this->id}' $role_clause
										GROUP BY group_id
										ORDER BY group_id");
	}// /->get_member_count_by_group()

	
	/**
	* Get the members actually contained within this collection's groups
	*
	* @param string $role		(optional) user role to search for
	*
	* @return	array - assoc array ( user_id => user_role )
	*/
	function get_members($role = null) {
		$role_clause = ($role) ? " AND user_role='$role'" : '' ;
		
		return $this->_DAO->fetch_assoc("SELECT user_id, user_role
										FROM user_group_member
										WHERE collection_id='{$this->id}' $role_clause
										ORDER BY user_id ASC");
	}// /->get_members()
	
	
	/**
	* Get row data for this collection's members
	*
	* @return	array - array ( group_id, user_id, user_role )
	*/
	function get_member_rows() {
		return $this->_DAO->fetch("SELECT group_id, user_id, user_role
									FROM user_group_member
									WHERE collection_id='{$this->id}'
									ORDER BY group_id ASC");
	}// /->get_member_rows()
	
	
	/**
	* Get group objects for all the groups the given member belongs to
	*
	* @param string $user_id	 user id of the member
	*
	* @return array array of group objects
	*/
	function & get_member_groups($user_id) {
		$member_roles = $this->get_member_roles($user_id);
		if ($member_roles) {
			$groups = null;
			foreach($member_roles as $group_id => $role) {
				$groups[] =& $this->get_group_object($group_id);
			}
			return $groups;
		}
	}// /->get_member_groups()
	
	
	/**
	* Get a user's roles for each group in this collection
	*
	* @param string $user_id	 user to search for
	*
	* @return	array - assoc array ( group_id => user_role );
	*/
	function get_member_roles($user_id) {
		return $this->_DAO->fetch_assoc("SELECT group_id, user_role
										FROM user_group_member
										WHERE collection_id='{$this->id}' AND user_id='$user_id'
										ORDER BY group_id ASC");
	}// /->get_member_roles()


	/**
	* Purge a collection of its members using include/exclude lists
	* Due to the way the lists work, you need only use one of them at a time
	*
	* @param string $target_roles	 single role to purge (optional)
	*										(array) - array of roles to purge
	* 									If a $target_roles list is given (not null), and a user's role is not in it, they are kept regardless of the $protect_list
	* 									If a user's role is in the $target_roles list, they are removed
	*
	* @param string $protect_roles	 single role to keep (optional)
	*										(array) - array of roles to keep
	*										If a user's role is in the $protect_roles list, they are kept
	*/
	function purge_members($target_roles = null, $protect_roles = null) {
		if ($this->is_locked()) { return false; }
		
		$groups_iterator = $this->get_groups_iterator();
		if ($groups_iterator->size()>0) {
			for ($groups_iterator->reset(); $groups_iterator->is_valid(); $groups_iterator->next()) {
				$group =& $groups_iterator->current();
				$group->purge_members($target_roles, $protect_roles);
			}
		}
	}// /->purge_members()


	/**
	* Remove members from the collection
	* Deletes the given user and role immediately from the database
	*
	* @param string $user_id	The ID of the user to remove
	*								(array) - An array of IDs to remove (all of the same user_type)
	* @param string $role		(optional) Group to remove members from. If unused, remove from all groups
	*/
	function remove_member($user_id, $role = null) {
		$user_set = $this->_DAO->build_set( (array) $user_id);
		$role_clause = ($role) ? " AND user_role='$role'" : '' ;
		$this->_DAO->execute("DELETE FROM user_group_member WHERE 
							  collection_id='{$this->id}' AND (user_id IN $user_set) $role_clause");
		
	}// /->remove_member()

	/**
	 * Get the group for which a user is the member of
	 * @param string $user_id	The id of the user whos group information we want to get
	 * @return array - assoc array ( group_id => user_role );
	 */
	 function get_group_details($user_id){
	 	return $this->_DAO->fetch("SELECT group_id, user_id
											FROM user_group_member
											WHERE group_id=(SELECT group_id
											FROM user_group_member
											WHERE collection_id='{$this->id}' AND user_id='{$user_id}' LIMIT 1)"
	 									);

	 }

/*
* --------------------------------------------------------------------------------
* Module-Manipulation Methods
* --------------------------------------------------------------------------------
*/


	/**
	* Returns the groups belonging to this collection
	*
	* @param boolean $refresh	force refresh of group list from database
	*
	* @return array assoc array  ( group_name => group_id )
	*/
	function get_modules($refresh = false) {
		if (!is_array($this->_modules)) { $this->refresh_modules(); }
		return $this->_modules;
	}// /->get_modules()


	/**
	* Load modules into this GroupCollection object
	*/
	function refresh_modules() {
		$this->_modules = $this->_DAO->fetch_col(	"SELECT module_id
													 FROM user_collection_module
													 WHERE collection_id='{$this->id}'");
	}// /->refresh_modules()


	/**
	* Set the modules this GroupCollection should use for its members
	*
	* WARNING : Any student members who belong to modules that are no longer used, will remain in the groups
	*
	* @param string|array $modules	 module IDs to assign
	*/
	function set_modules($modules) {
		$this->_modules = (array) $modules;
	} // /->set_modules()


/*
* ================================================================================
* Private Methods
* ================================================================================
*/

	
}// /class: GroupCollection

?>
