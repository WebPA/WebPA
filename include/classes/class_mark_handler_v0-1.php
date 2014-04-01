<?php

/**
 * 
 * Class :  MarkHandler
 *
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 * @since 10-11-2005
 * 
 */
require_once("./include/inc_global.php");
require_once(DOC__ROOT.'/library/classes/class_dao.php');
require_once(DOC__ROOT.'/include/classes/class_assessment.php');
require_once(DOC__ROOT.'/include/classes/class_form.php');
require_once(DOC__ROOT.'/library/classes/class_group_handler.php');


class ResultHandler {
	// Public Vars
	

	// Private Vars
	private $_DAO = null;

	private $_group_handler = null;
	private $_assessment = null;
	private $_collection = null;
	private $_collection_id = null;
	

	/**
	* CONSTRUCTOR for the result handler
	* @param mixed $DAO
	*/
	function ResultHandler(&$DAO) {
		$this->_DAO =& $DAO;
	}// /->ResultHandler()


/*
* ================================================================================
* Public Methods
* ================================================================================
*/


/*
* --------------------------------------------------------------------------------
* Accessor Methods
* --------------------------------------------------------------------------------
*/


	/**
	* Set Assessment object
	* @param object $assessment assessment object to use
	*/
	function set_assessment(& $assessment) {
		$this->_assessment =& $assessment;

		$this->_collection_id = $this->_assessment->get_collection_id();
		$this->_group_handler =& new GroupHandler();
		$this->_collection =& $this->_group_handler->get_collection($this->_collection_id);
	}// /->set_assessment()

	
	/**
	* Return the Assessment object being used
	* @return object assessment object being used
	*/
	function & get_assessment() {
		return $this->_assessment;
	}// /->get_assessment()


/*
* --------------------------------------------------------------------------------
* Methods
* --------------------------------------------------------------------------------
*/


	/**
	* Load the assessment results (if any)
	*/
	function load_results() {
		$this->_results = $this->_DAO->fetch(	"SELECT group_id, user_id, group_id, marked_user_id, criteria_id, score
												 FROM user_mark
												 WHERE assessment_id='{$this->_assessment->id}'
												 AND collection_id='{$this->_collection_id}'
												 ORDER BY group_id, user_id, marked_user_id, criteria_id");
	}// /->load_results()
	
	
	/**
	* Fetch a count of the responses for the given group
	* @param integer $group_id
	* @return integer  count of responses
	*/
	function get_responses_count($group_id) {
		if ($this->_collection->group_id_exists($group_id)) {
			return $this->_DAO->fetch_value("SELECT COUNT(DISTINCT user_id)
											 FROM user_mark
											 WHERE assessment_id='{$this->_assessment->id}'
											 AND collection_id='{$this->_collection->id}'
											 AND group_id='$group_id'");
		} else {
			return null;
		}
	}// /->get_responses_count()

	
	/**
	* Fetch a count of the responses for all this user's assessments (that opened this academic year)
	* @param integer $user_id
	* @param date $year
	* @return array  assoc array ( assessment_id => response_count )
	*/
	function get_responses_count_for_user($user_id, $year) {
		$next_year = $year+1;
		return $this->_DAO->fetch_assoc("SELECT a.assessment_id, COUNT(DISTINCT um.user_id)
										 FROM assessment a LEFT JOIN user_mark um ON a.assessment_id=um.assessment_id
										 AND a.owner_id='$user_id'
										 AND a.open_date>='{$year}-09-01 00:00:00'
										 AND a.open_date<='{$next_year}-08-31 23:59:59'
										 GROUP BY assessment_id");
	}// /->get_responses_count_for_user()
	

	/**
	* Fetch a count of the members for all this user's assessments (that opened this academic year)
	* @param integer $user_id
	* @param date $year
	* @return array assoc array ( assessment_id => member_count )
	*/
	function get_members_count_for_user($user_id, $year) {
		$next_year = $year+1;
		return $this->_DAO->fetch_assoc("SELECT a.assessment_id, COUNT(DISTINCT m.user_id)
										 FROM assessment a 
										 LEFT JOIN user_group_member m ON a.collection_id=m.collection_id
										 AND a.owner_id='$user_id'
										 AND a.open_date>='{$year}-09-01 00:00:00'
										 AND a.open_date<='{$next_year}-08-31 23:59:59'
										 GROUP BY assessment_id");
	}// /->get_members_count_for_user()
	

	/**
	* Has the user submitted marks for the given assessment already
	* 
	* @param string $user_id user to check
	* @param string $assessment_id  assessment to check
	* @return boolean has the user responded
	*/
	function user_has_responded($user_id, $assessment_id) {
		$num_responses = $this->_DAO->fetch_value("SELECT COUNT(DISTINCT um.marked_user_id)
												   FROM assessment a 
												   LEFT JOIN user_mark um ON a.assessment_id=um.assessment_id
												   AND a.assessment_id='$assessment_id'
												   AND um.user_id='$user_id'");
		return ($num_responses>0);
	}// /->user_has_responded()


	/*
	*
	* assessment has been taken by the user
	* @param integer $user)id
	*
	*/
	function assessments_taken_by_user($user_id) {
	
	
	}
	
	
/*
* ================================================================================
* Private Methods
* ================================================================================
*/


}// /class: ResultHandler


?>
