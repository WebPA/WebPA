<?php
/**
 *
 * Abstract Class :  WebPAAlgorithm
 *
 *
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.1.0.0
 *
 */

class WebPAAlgorithm {
  // Public Vars
  public $_groups = null;
  public $_group_members = null;
  public $_questions = null;
  public $_responses = null;
  public $_marking_params = null;

  public $_group_member_responses = null;
  public $_group_member_total_awarded = null;
  public $_group_member_frac_scores_awarded = null;
  public $_group_member_total_received = null;
  public $_group_member_webpa_scores = null;
  public $_group_member_intermediate_grades = null;
  public $_group_member_grades = null;

  public $_group_member_submitted = null;
  public $_member_submitted = null;

  // Private Vars

  /**
  * CONSTRUCTOR
  */
  function __construct() {
    $this->_init();
  }// /->WebPAAlgorithm()

/**
* ================================================================================
* Public Methods
* ================================================================================
*/

/**
* --------------------------------------------------------------------------------
* Accessor Methods (GET)
* --------------------------------------------------------------------------------
*/

  /**
  * Function to get WebPA scores
  *
  * @param int $group_id
  * @return array $scores
  */
  function get_webpa_scores($group_id = null) {

    if (!is_array($this->_group_member_webpa_scores)) {
      return array();
    } else {
      if ($group_id) {
        return $this->_group_member_webpa_scores["$group_id"];
      } else {
        $scores = array();
        foreach($this->_group_member_webpa_scores as $group_id => $member_scores) {
          // [pmn] FIXED : needs this foreach to list scores by member_id

          foreach($member_scores as $member_id => $score) {
            $scores[$member_id] = $score;

          }
          //$scores = array_merge($scores, $member_scores);
        }
        ksort($scores);
        return $scores;
      }
    }
  } // /->get_webpa_scores()

  /**
  * Function to Get grades
  *
  * @param int $group_id
  * @return array $grades
  */
  function get_intermediate_grades($group_id = null) {
    if ($group_id) {
      return $this->_group_member_intermediate_grades["$group_id"];
    } else {
      $grades = array();
      foreach($this->_group_member_intermediate_grades as $group_id => $member_grades) {
        foreach($member_grades as $member_id => $grade) {
          $grades[$member_id] = $grade;
        }
      }
      ksort($grades);
      return $grades;
    }
  } // /->get_intermediate_grades()

  /**
  * Function to Get grades
  *
  * @param int $group_id
  * @return array $grades
  */
  function get_grades($group_id = null) {
    if ($group_id) {
      return $this->_group_member_grades["$group_id"];
    } else {
      $grades = array();
      foreach($this->_group_member_grades as $group_id => $member_grades) {
        foreach($member_grades as $member_id => $grade) {
          $grades[$member_id] = $grade;
        }
      }
      ksort($grades);
      return $grades;
    }
  } // /->get_grades()

  /**
   * Function to get members submitting
   *
   * @param int $group_id
   * @return mixed returns the members of the group that have submitted the assessment
   */
  function get_members_submitting($group_id = null) {
    if ($group_id) {
      return (array_key_exists($group_id, $this->_group_member_submitted)) ? $this->_group_member_submitted["$group_id"] : null ;
    } else {
      return $this->_member_submitted;
    }
  }// /->get_members_submitting()

  /**
   * Get the response given by one member of a group to another member for a particular question
   *
   * @param int $group_id
   * @param int $member_id
   * @param int $question_id
   * @param int $target_member_id
   *
   * @return array $response_score
   */
  function get_member_response($group_id, $member_id, $question_id, $target_member_id) {
    $response_score = null;
    if (array_key_exists($group_id, $this->_group_member_responses)) {
      if (array_key_exists($member_id, $this->_group_member_responses[$group_id])) {
        if (array_key_exists($question_id, $this->_group_member_responses[$group_id][$member_id])) {
          if (array_key_exists($target_member_id, $this->_group_member_responses[$group_id][$member_id][$question_id])) {
            $response_score = $this->_group_member_responses[$group_id][$member_id][$question_id][$target_member_id];
          }
        }
      }
    }
    return $response_score;
  }

/**
* --------------------------------------------------------------------------------
* Accessor Methods (SET)
* --------------------------------------------------------------------------------
*/

  /**
  * Set groups
  *
  *@param array $groups groups to use in this algorithm : array[group_id] = group_mark
  */
  function set_groups(& $groups) {
    $this->_groups =& $groups;
  }// /->set_groups()

  /*
  * Set group members
  *
  * $group_members  : (array) - members to use in this algorithm : array[group_id] = array ( member_id, ... )
  */
  function set_group_members(& $group_members) {
    $this->_group_members =& $group_members;
  }// /->set_group_members()

  /*
  * Set marking parameters
  *
  * $params : (array) - marking parameters for this report : array ( int weighting , int penalty )
  */
  function set_marking_params(& $marking_params) {
    $this->_marking_params =& $marking_params;
  }// /->set_marking_params()

  /*
  * Set questions
  * This could've just accepted an integer, but for future proofing (just in case) it takes an array (0-based)
  *
  * $questions  : (array) - questions in this assessment : array ( question_id, ... )
  */
  function set_questions(& $questions) {
    $this->_questions =& $questions;
  }// /->set_questions()

  /*
  * Set member responses
  *
  * $responses  : (array) - responses given for this assessment : array[member_id][question_id][marked_member_id] = score
  */
  function set_responses(& $responses) {
    $this->_responses =& $responses;
  }// /->set_responses()

/*
* --------------------------------------------------------------------------------
* Methods
* --------------------------------------------------------------------------------
*/

  /*
  * Calculate the WebPA and group scores for each student
  */
  function calculate() {
    echo('<p>ERROR: WebPAAlgorithm->calculate() is an abstract method and should be overridden.</p>');
    exit;
  }// /->calculate()

/*
* ================================================================================
* Private Methods
* ================================================================================
*/

  function _init() {
    $this->_groups = null;
    $this->_group_members = null;
    $this->_questions = null;
    $this->_responses = null;
    $this->_marking_params = null;

    $this->_group_member_responses = null;
    $this->_group_member_total_awarded = null;
    $this->_group_member_frac_scores_awarded = null;
    $this->_group_member_total_received = null;
    $this->_group_member_webpa_scores = null;
    $this->_group_member_intermediate_grades = null;
    $this->_group_member_grades = null;

    $this->_group_member_submitted = null;
    $this->_member_submitted = null;
  }// /->_init()

}// /class: WebPAAlgorithm

?>
