<?php

/**
 * 
 * engCIS local version
 * 			
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 * 
 */
 function rel($struc, &$file) {
 	return file_exists( ( $file = ( dirname($struc).'/'.$file ) ) );
 }
 
 function relativetome($structure, $filetoget){
 	return rel($structure,$filetoget) ? require_once($filetoget) : null;
 }
 
relativetome(__FILE__, 'inc_global.php');

require_once(DOC__ROOT . '/library/classes/class_dao.php');
require_once(DOC__ROOT . '/library/functions/lib_array_functions.php');


class EngCIS {
	// Public Vars

	
	// Private Vars
	private $_DAO = null;
	private $_ordering_types = null;
	
	/**
	* CONSTRUCTOR
	*/
	function EngCIS() {
		$this->_DAO = new DAO(APP__DB_HOST,APP__DB_USERNAME,APP__DB_PASSWORD,APP__DB_DATABASE);
		$this->_DAO->set_debug(false);
	}// /->EngCIS()


/*
* ================================================================================
* Public Methods
* ================================================================================
*/


/*
* --------------------------------------------------------------------------------
* Course Methods
* --------------------------------------------------------------------------------
*/


	/**
	* Get array of courses for a department
	* @param string $department_id	ID of department to search
	* @param string $ordering	 ordering mode
	* @return array
	*/
/**	function get_department_courses($department_id, $ordering = 'name') {
		$order_by_clause = $this->_order_by_clause('course', $ordering);

		return $this->_DAO->fetch(	"
			SELECT lcc.*
			FROM live_cis_course lcc
			WHERE department_id='$department_id'
			$order_by_clause
		");
	}// /->get_department_courses()
*/

	/**
	* Get course info as an array
	* @param integer $course_id
	* @return array
	*/
/**	function get_course($course_id) {
		return $this->_DAO->fetch_row(	"SELECT lcc.*
										FROM live_cis_course lcc
										WHERE course_id='$course_id'
										");
	}// /->get_course()
*/

	/**
	* Get array of students for course
	*
	* @param string $ordering ordering mode
	*
	* @return array assoc-array of student info
	*/
/**	function get_course_students($course_id, $ordering = 'name') {
		$order_by_clause = $this->_order_by_clause('student', $ordering);

		return $this->_DAO->fetch(	"
																SELECT lcs.*
																FROM live_cis_student lcs INNER JOIN live_student_module lcsm ON lcs.student_id=lcsm.student_id AND module_id='$module_id'
																WHERE course_id='$course_id'
																$order_by_clause
															");
	}// /->get_course_students()
*/

/*
* --------------------------------------------------------------------------------
* Module Methods
* --------------------------------------------------------------------------------
*/


	/**
	* Get module info as an array
	*
	* @param string/array $module_id module ID(s) to search for
	* @param string $ordering	ordering mode
	*
	* @return array  either an assoc-array of module info or an array of assoc-arrays, containing many modules' info
	*/
	function get_module($modules = null, $ordering = 'id') {
		$module_search = $this->_DAO->build_filter('module_code', (array) $modules);
		$order_by_clause = $this->_order_by_clause('module', $ordering);
	
		// If there's more than one module to search for, get all the rows
		if (is_array($modules)) {
			return $this->_DAO->fetch("SELECT lcm.module_code AS module_id, lcm.module_title
										FROM module lcm
										WHERE $module_search
										$order_by_clause");
		} else {	// else, just return one row
			if (!empty($modules)) {
				return $this->_DAO->fetch_row("SELECT lcm.module_code AS module_id, lcm.module_title
												FROM module lcm
												WHERE $module_search
												LIMIT 1");
			}
		}
	}// /->get_module()

	/**
	 * Get all the module info as an array
	 * 
	 * @return array
	 */
	 function get_all_modules(){
	 	return $this->_DAO->fetch("SELECT lcm.module_code AS module_id, lcm.module_title
										FROM module lcm");
	 }
	
	/**
	* Get array of staff for module
	* @param integer $module_id
	* @param string $ordering
	* @return array
	*/
	function get_module_staff($module_id, $ordering) {
		$order_by_clause = $this->_order_by_clause('staff', $ordering);

		return $this->_DAO->fetch	("SELECT lcs.*
									FROM user lcs 
									INNER JOIN user_module lcsm ON lcs.user_id = lcsm.user_id
									WHERE lcs.user_type = 'staff'
										AND module_id='$module_id'
									$order_by_clause");
	}// /->get_module_staff()


	/**
	* Get array of students for one or more modules
	* @param integer $modules
	* @param string $ordering
	* @return array
	*/
	function get_module_students($modules, $ordering = 'name') {
		$module_set = $this->_DAO->build_set($modules);
		$order_by_clause = $this->_order_by_clause('student', $ordering);
		
		return $this->_DAO->fetch("	SELECT DISTINCT lcs.*, lcs.lastname AS surname, institutional_reference AS student_id
									FROM user lcs
									INNER JOIN user_module lcsm ON lcs.user_id=lcsm.user_id AND module_id IN $module_set
									WHERE lcs.user_type = 'student'
									$order_by_clause
									");
	}// /->get_module_students()

	
	/**
	* Get total number of students on one or more modules
	*
	* @param array $modules	 modules to count students for
	* @return array
	*/
	function get_module_students_count($modules) {
		$module_set = $this->_DAO->build_set($modules);
		
		$sql = "SELECT COUNT(DISTINCT u.user_id)
				FROM user u
  					INNER JOIN user_module um ON u.user_id=um.user_id
  					INNER JOIN module m ON m.module_code=um.module_id
				WHERE m.module_code IN $module_set
					AND u.user_type = 'student'";
		return $this->_DAO->fetch_value($sql);
	}// /->get_module_students_count


	/**
	* Get an array of student IDs for students on the given modules
	* @param array $modules	 modules to count students for
	* @return array
	*/
	function get_module_students_id($modules) {
		if (!empty($modules)) {
			$module_set = $this->_DAO->build_set( (array) $modules);
			return $this->_DAO->fetch_col("SELECT DISTINCT u.institutional_reference AS staff_id
											FROM user u
											INNER JOIN user_module um ON u.user_id=um.user_id
											INNER JOIN module m ON m.module_code=um.module_id
											WHERE m.module_code IN $module_set
												AND u.user_type = 'student'
											ORDER BY u.user_id ASC");
		}
	}// /->get_module_students_id()
	
	
	/**
	* Get an array of user IDs for students on the given modules (user_id = 'student_{studentID}'
	* @param array $modules	 modules to count students for
	* @return array
	*/
	function get_module_students_user_id($modules) {
		if (!empty($modules)) {
			$module_set = $this->_DAO->build_set( (array) $modules);
			$sql = "SELECT DISTINCT u.user_id
					FROM user u
						INNER JOIN user_module um ON u.user_id=um.user_id
						INNER JOIN module m ON m.module_code=um.module_id
					WHERE m.module_code IN $module_set
						AND u.user_type = 'student'
					ORDER BY u.user_id ASC
					";
			return $this->_DAO->fetch_col($sql);
		}
	}// /->get_module_students_user_id()


	/**
	* Get number of students on individual multiple modules, grouped by module
	*
	* @param array  $modules	modules to count students for
	* @return array 
	*/
	function get_module_grouped_students_count($modules) {
		$module_search = $this->_DAO->build_filter('module_id', (array) $modules, 'OR');

		return $this->_DAO->fetch_assoc("SELECT module_id, COUNT(user_id)
										FROM user_module lcsm
										WHERE $module_search
										GROUP BY module_id
										ORDER BY module_id");
	}// ->get_modules_grouped_students_count()


/*
* --------------------------------------------------------------------------------
* Staff Methods
* --------------------------------------------------------------------------------
*/

	
	/**
	* Get staff info
	* Can work with either staff_id or staff_username alone (staff_id takes precedent)
	*
	* @param string/array $staff_id	 staff ID(s) to search for (use NULL if searching on username)
	* @param string/array $staff_username	 staff username(s) to search for
	* @param string $ordering ordering mode
	*
	* @return array either an assoc-array of staff member info or an array of assoc-arrays, containting many staff members' info
	*/
	function get_staff($staff_id, $staff_username = null, $ordering = 'name') {
		if ($staff_id) {
			$staff_set = $this->_DAO->build_set($staff_id);
			$sql_WHERE = "user_id IN $staff_set ";
		}	else {
			if ($staff_username) {
				$staff_set = $this->_DAO->build_set($staff_username);
				$sql_WHERE = "username IN $staff_set ";
			}	else { return null; }
		}

		$order_by_clause = $this->_order_by_clause('staff', $ordering);
		
		// If there's more than one staff member to search for, get all the rows
		if ( (is_array($staff_id)) || (is_array($staff_username)) ) {
			return $this->_DAO->fetch("SELECT lcs.*, lcs.lastname AS surname, institutional_reference AS staff_id
										FROM user lcs
										WHERE $sql_WHERE
										$order_by_clause");
		} else {	// else, just return one row
			return $this->_DAO->fetch_row("SELECT lcs.*, lcs.lastname AS surname, institutional_reference AS staff_id
											FROM user lcs
											WHERE $sql_WHERE
											LIMIT 1");
		}
	}// /->get_staff()


	/**
	* Get array of modules for the given staff member(s)
	* Can work with either staff_id or staff_username alone (staff_id takes precedent)
	*
	* @param string/array $staff_id	staff ID(s) to search for (use NULL if searching on username)
	* @param string/array $staff_username	staff username(s) to search for
	* @param string $ordering	ordering mode
	*
	* @return array	 an array of assoc-arrays, containting many module info
	*/
	function get_staff_modules($staff_id, $staff_username = null, $ordering = 'id') {
		if ($staff_id) {
			$staff_set = $this->_DAO->build_set($staff_id);
			$sql_WHERE = "user_id IN $staff_set ";
		}	else {
			if ($staff_username) {
				$staff_set = $this->_DAO->build_set($staff_username);
				$sql_WHERE = "username IN $staff_set ";
			}	else { return null; }
		}
		
		$order_by_clause = $this->_order_by_clause('module', $ordering);
	
		return $this->_DAO->fetch(	"SELECT DISTINCT lcm.module_code AS module_id, lcm.module_title
									FROM module lcm INNER JOIN user_module lcsm ON lcm.module_code=lcsm.module_id
									WHERE $sql_WHERE
									$order_by_clause");
	}// /->get_staff_modules


	/**
	* Is the given staff member associated with the given modules?
	*
	* @param string $staff_id staff id of member being checked
	* @param string/array $module_id	either a single module_id, or an array of module_ids
	* @return integer
	*/
	function staff_has_module($staff_id, $module_id) {
		$module_id = (array) $module_id;
		$staff_modules = $this->get_staff_modules($staff_id);
		if (!$staff_modules) {
			return false;
		} else {
			$arr_module_id = array_extract_column($staff_modules, 'module_id');
			$diff = array_diff($module_id, $arr_module_id);
			
			// If the array is empty, then the staff member has those modules
			return (count(array_diff($module_id, $arr_module_id))===0);  
		}
	}// /->staff_has_module()

	
/*
* --------------------------------------------------------------------------------
* Student Methods
* --------------------------------------------------------------------------------
*/


	/**
	* Get student info
	* Can work with either student_id or student_username alone (student_id takes precedent)
	*
	* @param string/array $student_id	 student ID(s) to search for (use NULL if searching on username)
	* @param string/array $student_username	 student
	* @param string $ordering	 ordering mode
	*
	* @returns	array either an assoc-array of student info	or an array of assoc-arrays, containting many students info
	*/
	function get_student($student_id = null, $student_username = null, $ordering = 'name') {
		if ($student_id) {
			$student_set = $this->_DAO->build_set($student_id);
			$sql_WHERE = "user_id IN $student_set ";
		}	else {
			if ($student_username) {
				$student_set = $this->_DAO->build_set($student_username);
				$sql_WHERE = "username IN $student_set ";
			}	else { return null; }
		}
		
		// If there's more than one student to search for, get all the rows
		if ( (is_array($student_id)) || (is_array($student_username)) ) {
			$order_by_clause = $this->_order_by_clause('student', $ordering);
			return $this->_DAO->fetch("SELECT lcs.*, lcs.lastname AS surname, institutional_reference AS student_id
										FROM user lcs
										WHERE $sql_WHERE
										$order_by_clause");
		} else {	// else, just return one row
			return $this->_DAO->fetch_row("SELECT lcs.*, lcs.lastname AS surname, institutional_reference AS student_id
											FROM user lcs
											WHERE $sql_WHERE
											LIMIT 1");
		}
	}// /->get_student()


	/**
	* Get array of modules for the given student(s)
	* Can work with either student_id or student_username alone (student_id takes precedent)
	*
	* @param string/array $student_id	student ID(s) to search for (use NULL if searching on username)
	* @param string/array $student_username	student username(s) to search for
	* @param string $ordering	ordering mode
	*
	* @return array an array of module info arrays
	*/
	function get_student_modules($student_id, $student_username = null, $ordering = 'id') {
		if ($student_id) {
			$student_set = $this->_DAO->build_set($student_id);
			$sql_WHERE = "user_id IN $student_set ";
		}	else {
			if ($student_username) {
				$student_set = $this->_DAO->build_set($student_username);
				$sql_WHERE = "username IN $student_set ";
			}	else { return null; }
		}
		
		$order_by_clause = $this->_order_by_clause('module', $ordering);
		
		return $this->_DAO->fetch(	"SELECT DISTINCT lcm.module_code AS module_id, lcm.module_title
									FROM module lcm INNER JOIN user_module lcsm ON lcm.module_code=lcsm.module_id
									WHERE $sql_WHERE
									$order_by_clause");
	}// /->get_student_modules()


/*
* --------------------------------------------------------------------------------
* User Methods
* --------------------------------------------------------------------------------
*/


	/**
	* Get a user's info
	*
	* @param string/array $user_id user ID(s) to search for
	* $ordering	: (string) - ordering mode
	*
	* Returns	: either an assoc-array of user info
	*						or an array of assoc-arrays, containting many users' info
	*/
	function get_user($user_id, $ordering = 'name') {
		$user_set = $this->_DAO->build_set($user_id);
		$sql_WHERE = "user_id IN $user_set ";
		
		
		// If there's more than one user to search for, get all the rows
		if (is_array($user_id)) {
			$order_by_clause = $this->_order_by_clause('user', $ordering);
			$sql = "SELECT scu.*, scu.lastname AS surname
					FROM user scu
					WHERE $sql_WHERE
					$order_by_clause";
					
			return $this->_DAO->fetch($sql);
		} else {	// else, just return one row
			$sql = "SELECT scu.*, scu.lastname AS surname
					FROM user scu
					WHERE $sql_WHERE
					LIMIT 1";
			return $this->_DAO->fetch_row($sql);
		}
	}// /->get_user()
	
	
	
	/**
	* Get a user's info by searching on email address
	*
	* @param string $email email address to search for
	*
	* Returns	: an assoc-array of user info
	*/
	function get_user_for_email($email) {
		return $this->_DAO->fetch_row("SELECT scu.*
									   FROM user scu
									   WHERE email='$email'
									   LIMIT 1");
	}// /->get_user_for_email()
	
	

	
/*
* ================================================================================
* Private Methods
* ================================================================================
*/


	/**
	* Return an ORDER BY clause matching the given parameters
	*
	* @param string $row_type type of row being ordered. ['course','module','staff','student']
	* @param string $ordering	 type of ordering to do. ['id','name']
	*
	* @return	string	SQL ORDER BY clause of the form 'ORDER BY fieldname' or NULL if row_type/ordering are invalid
	*/
	function _order_by_clause($row_type, $ordering = null) {
		if (!is_array($this->_ordering_types)) {
			// All available ordering types
			$this->_ordering_types = array (
/**				'course'	=> array	(
									'id'	=> 'lcc.course_id' ,
									'name'	=> 'lcc.course_title' ,
								) ,
*/
				'module'	=> array	(
									'id'	=> 'lcm.module_id' ,
									'name'	=> 'lcm.module_title' ,
								) ,
				'staff'		=> array	(
									'id'	=> 'lcs.user_id' ,
									'name'	=> 'lcs.lastname, lcs.forename' ,
								) ,
				'student'	=> array	(
									'id'	=> 'lcs.user_id' ,
									'name'	=> 'lcs.lastname, lcs.forename' ,
								) ,
				'user'		=> array	(
									'id'	=> 'scu.user_id' ,
									'name'	=> 'scu.lastname, scu.forename' ,
								) ,
			);
		}

		if ( (array_key_exists($row_type, $this->_ordering_types)) && (array_key_exists($ordering, $this->_ordering_types["$row_type"])) ) {
			return 'ORDER BY '. $this->_ordering_types["$row_type"]["$ordering"];
		} else {
			return null;
		}
	}// /->_order_by_clause()


}// /class: EngCIS

?>
