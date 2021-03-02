<?php
/**
 * Algorithm
 *
 * An abstract Algorithm that, as well as performing the appropriate grading calculations,
 * also organises assessment information into suitable formats for report production.
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\includes\classes\algorithms;

use WebPA\includes\classes\GroupHandler;
use WebPA\includes\classes\ResultHandler;

abstract class Algorithm
{
    // Private Vars
    protected $_assessment;

    protected $_grade_ordinals = [];

    protected $_params;

    protected $_peeronly = false;

    protected $_group_grades;

    protected $_group_members;

    protected $_group_names;

    protected $_form_type;

    protected $_questions;

    protected $_question_info;

    protected $_responses;

    protected $_ordinal_scale;

    // These properties are used for checking what's actually happened in the assessment
    // Sub-classes should not use these for their calculations

    protected $_actual_responses;

    protected $_actual_group_submitters = [];

    protected $_actual_submitters = [];

    protected $_actual_marks_awarded;

    protected $_actual_marks_received;

    protected $_actual_marks_awarded_by_member_question;

    protected $_actual_marks_received_by_member_question;

    protected $_actual_total_marks_awarded;

    protected $_actual_total_marks_received;

    // These properties are used in the algorithm calculations
    // They can be manipulated to correct scoring problems (like peer-only and poor submissions)

    protected $_calc_responses;

    protected $_calc_group_submitters = [];

    protected $_calc_submitters = [];

    protected $_calc_marks_awarded;

    protected $_calc_marks_received;

    protected $_calc_marks_awarded_by_member_question;

    protected $_calc_marks_received_by_member_question;

    protected $_calc_total_marks_awarded;

    protected $_calc_total_marks_received;

    // These properties contain the final outputs from the algorithm

    protected $_webpa_scores;          // The mutiplication factor based on relative performance

    protected $_intermediate_grades;   // The intermediate grades (before penalties are applied)

    protected $_grades;                // The final grades

    protected $_penalties;             // Textual description of the penalties each member incurred

    /**
     * Constructor
     *
     * @return  Algorithm
     */
    public function __construct()
    {
    }

    // /->Algorithm()



    /*
    * ================================================================================
    * Public Methods
    * ================================================================================
    */

    /**
     * Calculate the student's final grades.
     *
     * As well as calculating grades, this method populates many of the properties
     * available via the get methods.
     *
     * @return  boolean  The operation was successful.
     */
    abstract public function calculate();

    /**
     * Get the final grades for every student.
     *
     * Output is of the form  array ( student_id => grade )
     * The type of grade (% or A-F) depends on the marking parameters supplied.
     *
     * @return  mixed  An assoc-array of grades. On fail, null.
     */
    public function get_grades()
    {
        return $this->_grades;
    }

    // /->get_grades()

    /**
     * Get the names of the groups involved in the assessment.
     *
     * Output is of the form array ( group_id => group_name )
     *
     * @return  mixed  An assoc-array of group names. On fail, null.
     */
    public function get_group_names()
    {
        return $this->_group_names;
    }

    // /->get_group_names()

    /**
     * Get the names of the groups involved in the assessment.
     *
     * Output is of the form  array ( group_id => array ( member_id ) )
     *
     * @return  mixed  An assoc-array of members in each group. On fail, null.
     */
    public function get_group_members()
    {
        return $this->_group_members;
    }

    // /->get_group_members()

    /**
     * Get the intermediate grades for every student.
     *
     * An intermediate grade is the grade a student receives before any penalties are applied.
     * Output is of the form  array ( person_id => intermediate_grade )
     * The type of grade (% or A-F) depends on the marking parameters supplied.
     *
     * @return  mixed  An assoc array of students and their grades. On fail, null.
     */
    public function get_intermediate_grades()
    {
        return $this->_intermediate_grades;
    }

    // /->get_intermediate_grades()

    public function get_marks_awarded()
    {
        return $this->_actual_marks_awarded;
    }

    // /->get_marks_awarded()

    public function get_marks_received()
    {
        return $this->_actual_marks_received;
    }

    // /->get_marks_received()

    public function get_total_marks_awarded()
    {
        return $this->_actual_total_marks_awarded;
    }

    // /->get_total_marks_awarded()

    public function get_total_marks_received()
    {
        return $this->_actual_total_marks_received;
    }

    // /->get_total_marks_received()

    /**
     * Get the score given by one member of a group to another member for a particular question
     *
     * @param  integer  $group_id  The group to check.
     * @param  integer  $member_id  The member awarding the score.
     * @param  integer  $question_id  The question the score was for.
     * @param  integer  $target_member_id  The member receiving the score.
     *
     * @return  array  The score awarded. On fail, null.
     */
    public function get_member_response($group_id, $member_id, $question_id, $target_member_id)
    {
        $score = null;
        if (array_key_exists($group_id, $this->_actual_responses)) {
            if (array_key_exists($question_id, $this->_actual_responses[$group_id])) {
                if (array_key_exists($member_id, $this->_actual_responses[$group_id][$question_id])) {
                    if (array_key_exists($target_member_id, $this->_actual_responses[$group_id][$question_id][$member_id])) {
                        $score = $this->_actual_responses[$group_id][$question_id][$member_id][$target_member_id];
                    }
                }
            }
        }
        return $score;
    }

    // /->get_member_response()

    /**
     * Get a list of penalised students, and the amount they were penalised.
     *
     * Output is of the form  array ( person_id => penalty )
     * Penalties are always numeric but may be 0.
     * Students without a penalty are not included in the list.
     *
     * @return  mixed  An assoc array of students and their penalties. On fail, null.
     */
    public function get_penalties()
    {
        return $this->_penalties;
    }

    // /->get_penalties()

    /**
     * Get the questions used in the assessment.
     *
     * Output is of the form array ( group_id => group_name )
     *
     * @return  mixed  An assoc-array of question information. On fail, null.
     */
    public function get_questions()
    {
        return $this->_question_info;
    }

    // /->get_questions()

    /**
     * Get a list of students who submitted.
     *
     * Output is of the form  array ( person_id )
     *
     * @return  mixed  An assoc array of students. On fail, null.
     */
    public function get_submitters()
    {
        return (!empty($this->_actual_submitters)) ? $this->_actual_submitters : null ;
    }

    // /->get_submitters()

    /**
     * Get the WebPA scores for every student.
     *
     * Web-PA scores are the multiplication factors produced by analysings the scores received
     * in the peer assessment.  The average group score is 1.0.  Scores above 1 mean above average performance,
     * and vice-versa.
     * Output is of the form  array ( student_id => webpa socre )
     *
     * @return  mixed  An assoc-array of webpa scores. On fail, null.
     */
    public function get_webpa_scores()
    {
        return $this->_webpa_scores;
    }

    // /->get_webpa_scores()

    /**
     * Set which assessment to use.
     *
     * @param  object  The assessment.
     *
     * @return  boolean  The operation was successful.
     */
    public function set_assessment($assessment)
    {
        $this->_assessment = $assessment;

        $db = $this->_assessment->get_db();

        // Set peer-only status
        $this->_peeronly = ($this->_assessment->assessment_type == 0);

        // Get group overall grades information
        $this->_group_grades = $this->_assessment->get_group_marks();


        // Get a list of the members who took this assessment (grouped by 'group')
        $group_members = null;
        $group_names = null;

        $group_handler = new GroupHandler();
        $collection = $group_handler->get_collection($this->_assessment->get_collection_id());
        $groups_iterator = $collection->get_groups_iterator();
        if ($groups_iterator->size()>0) {
            for ($groups_iterator->reset(); $groups_iterator->is_valid(); $groups_iterator->next()) {
                $group =& $groups_iterator->current();
                $this->_group_names["{$group->id}"] = $group->name;
                $this->_group_members["{$group->id}"] = $group->get_member_ids();
            }
        }


        // Get the number of questions used in this assessment, and create an array of that size
        $form = $this->_assessment->get_form();

        $this->_form_type = ($form->type=='split100') ? 'split100' : 'likert' ;

        $question_count = (int) $form->get_question_count();

        $this->_questions = ($question_count>0) ? range(0, $question_count-1) : [];
        foreach ($this->_questions as $i => $question_id) {
            $this->_question_info[$question_id] = $form->get_question($question_id);
        }

        // Get the student submissions for this assessment
        $result_handler = new ResultHandler($db);
        $result_handler->set_assessment($this->_assessment);

        $this->_responses = $result_handler->get_responses();

        return true;
    }

    // /->set_assessment()

    /**
     * Set grade letter ordinal scales.
     *
     * @param  array  $ordinal_scale.
     *
     * @return  boolean  The operation was successful.
     */
    public function set_grade_ordinals($ordinal_scale)
    {
        $this->_ordinal_scale = $ordinal_scale;
        return true;
    }

    // /->set_grade_ordinals()

    /**
     * Set marking parameters.
     *
     * @param  array  Assoc-array of marking parameters for this report.
     *
     * @return  boolean  The operation was successful.
     */
    public function set_marking_params($marking_params)
    {
        $this->_params = $marking_params;
        return true;
    }

    // /->set_marking_params()



    /*
    * ================================================================================
    * Private Methods
    * ================================================================================
    */

    /**
     * Apply any criterion weightings to the students' fractional marks.
     *
     * @return  boolean  The operation was successful.
     */
    protected function _applyCriterionWeightings()
    {

    // @todo : criterion weightings should make an appearnce in a future version.

        return true;
    }

    // /->_applyCriterionWeightings()

    /**
     * Convert the current grades to the required display format.
     *
     * @return  boolean  The operation was successful.
     */
    protected function _applyGradingStyle()
    {
        $grade_mode = ($this->_params['grading']=='grade_af') ? 'grade_af' : 'numeric' ;

        switch ($grade_mode) {
      // ----------------------------------------
      // Grades should be Alphabetic (A-F and the rest)
      case 'grade_af':

        $grade_letters = array_keys($this->_ordinal_scale);
        $top_grade = $grade_letters[0];

        if ($this->_intermediate_grades) {
            foreach ($this->_intermediate_grades as $id => $grade) {
                $grade_set = false;
                foreach ($this->_ordinal_scale as $grade_letter => $grade_numeric) {
                    if ($grade>$grade_numeric) {
                        break;
                    }
                    $this->_intermediate_grades[$id] = $grade_letter;
                    $grade_set = true;
                }

                // If we didn't set at least one grade-letter, then the student must have the top grade
                if (!$grade_set) {
                    $this->_intermediate_grades[$id] = $top_grade;
                }
            }
        }

        if ($this->_grades) {
            foreach ($this->_grades as $id => $grade) {
                $grade_set = false;
                foreach ($this->_ordinal_scale as $grade_letter => $grade_numeric) {
                    if ($grade>$grade_numeric) {
                        break;
                    }
                    $this->_grades[$id] = $grade_letter;
                    $grade_set = true;
                }

                // If we didn't set at least one grade-letter, then the student must have the top grade
                if (!$grade_set) {
                    $this->_grades[$id] = $top_grade;
                }
            }
        }
        break;
      // ----------------------------------------
      // Grades should be numeric (%)
      case 'numeric':
      default:
        if ($this->_intermediate_grades) {
            foreach ($this->_intermediate_grades as $id => $grade) {
                $this->_intermediate_grades[$id] = sprintf(APP__REPORT_DECIMALS, $grade) .'%';
            }
        }

        if ($this->_grades) {
            foreach ($this->_grades as $id => $grade) {
                $this->_grades[$id] = sprintf(APP__REPORT_DECIMALS, $grade) .'%';
            }
        }
      break;
    }// /switch()

        return true;
    }

    // /->__applyGradingStyle()

    /**
     * Apply any applicable penalties to the final grades
     *
     * @return  boolean  The operation was successful.
     */
    protected function _applyPenalties()
    {
        if ($this->_grades) {
            if (is_null($this->_actual_submitters)) {
                $this->_actual_submitters = [];
            }

            // If the penalty is a percentage..
            if ($this->_params['penalty_type']=='%') {
                $penalty = ($this->_params['penalty']==0) ? 1 : 1-($this->_params['penalty']/100) ;

                foreach ($this->_grades as $person_id => $grade) {
                    // If the person did not submit, penalise them
                    if (!in_array($person_id, $this->_actual_submitters)) {
                        $this->_penalties[$person_id] = ($this->_params['penalty']==0) ? 'no penalty' : "-{$this->_params['penalty']}%" ;

                        // Multiply the grade by the penalty
                        $final_grade = $grade * $penalty;

                        if ($final_grade<0) {
                            $final_grade = 0;
                        } elseif ($final_grade>100) {
                            $final_grade = 100;
                        }

                        // Update the final grade
                        $this->_grades[$person_id] = $final_grade;
                    }
                }// /foreach(grade)
            } else {   // Else.. Penalty is percentage-points

        $penalty = $this->_params['penalty'];

                foreach ($this->_grades as $person_id => $grade) {
                    // If the person did not submit, penalise them
                    if (!in_array($person_id, $this->_actual_submitters)) {
                        $this->_penalties[$person_id] = ($this->_params['penalty']==0) ? 'no penalty' : "-{$this->_params['penalty']} pp" ;

                        // Subtract the penalty from the grade
                        $final_grade = $grade - $penalty;

                        if ($final_grade<0) {
                            $final_grade = 0;
                        } elseif ($final_grade>100) {
                            $final_grade = 100;
                        }

                        // Update the final grade
                        $this->_grades[$person_id] = $final_grade;
                    }
                }// /foreach(grade)
            }
        }// /if(grades)

        if (empty($this->_actual_submitters)) {
            $this->_actual_submitters = null;
        }

        return true;
    }

    // /->_applyPenalties()

    /**
     * Initialise some of the algorithm's properties based prior to calculating the grades.
     *
     * Places the real student response information in the ->_actual_?? properties.
     * Response information for the algorithm calculations is placed in the ->_calc_?? properties.
     * ->_calc_?? properties can/will be altered by specific algorithms to provide proper grading.
     * For example, see ->_preparePeerOnly()
     *
     * @return  boolean  The operation was successful.
     */
    protected function _initialise()
    {


    // Initial marks for each question. Saves looping through questions every time when initialising scores.
        $initial_marks_per_question = null;
        foreach ($this->_questions as $i => $question_id) {
            $initial_marks_per_question[$question_id] = null;   // A null means no score
        }


        $this->_actual_submitters = [];
        $this->_actual_group_submitters = [];

        $this->_calc_submitters = [];
        $this->_calc_group_submitters = [];


        // Initialise awarded/received scores
        foreach ($this->_group_names as $group_id => $group_name) {

      // Initial marks from each student to each student.
            // This array is built in the following loop, so it is specific to each group.
            $initial_students_marks = [];

            foreach ($this->_group_members[$group_id] as $i => $member_id) {
                $this->_actual_marks_awarded_by_member_question[$member_id] = $initial_marks_per_question;
                $this->_actual_marks_received_by_member_question[$member_id] = $initial_marks_per_question;

                $this->_calc_marks_awarded_by_member_question[$member_id] = $initial_marks_per_question;
                $this->_calc_marks_received_by_member_question[$member_id] = $initial_marks_per_question;


                foreach ($this->_group_members[$group_id] as $j => $marked_user_id) {
                    $initial_students_marks[$member_id][$marked_user_id] = null;

                    $this->_actual_marks_awarded[$member_id][$marked_user_id] = 0;
                    $this->_actual_marks_received[$marked_user_id][$member_id] = 0;

                    $this->_calc_marks_awarded[$member_id][$marked_user_id] = 0;
                    $this->_calc_marks_received[$marked_user_id][$member_id] = 0;
                }

                $this->_actual_total_marks_awarded[$member_id] = 0;
                $this->_actual_total_marks_received[$member_id] = 0;

                $this->_calc_total_marks_awarded[$member_id] = 0;
                $this->_calc_total_marks_received[$member_id] = 0;
            }

            // Set the initial marks for this group, for each question
            foreach ($this->_questions as $i => $question_id) {
                $this->_actual_responses[$group_id][$question_id] = $initial_students_marks;
            }
        }// /foreach(group)



        // Process all the student responses
        // Set the correct values for the actual marks awarded, received, etc
        if (!empty($this->_responses)) {
            foreach ($this->_responses as $i => $response) {
                $group_id = $response['group_id'];
                $member_id = $response['user_id'];
                $marked_user_id = $response['marked_user_id'];
                $question_id = $response['question_id'];
                $score = (float) $response['score'];

                // Record the fact this member submitted
                if (!in_array($member_id, $this->_actual_submitters)) {
                    $this->_actual_submitters[] = $member_id;
                    $this->_actual_group_submitters[$group_id][] = $member_id;
                }

                // Re-factor the responses into more usable forms
                $this->_actual_responses[$group_id][$question_id][$member_id][$marked_user_id] = $score;

                // Keep a running total of the marks

                $this->_actual_marks_awarded[$member_id][$marked_user_id] += $score;
                $this->_actual_marks_received[$marked_user_id][$member_id] += $score;

                $this->_actual_marks_awarded_by_member_question[$member_id][$question_id] += $score;
                $this->_actual_marks_received_by_member_question[$marked_user_id][$question_id] += $score;

                $this->_actual_total_marks_awarded[$member_id] += $score;
                $this->_actual_total_marks_received[$marked_user_id] += $score;
            }
        }


        // Now the actual responses have been recorded, we can generate the appropriate ->_calc_?? properties

        // Get the peer-only or self-&-peer marks ready for the algorithm

        if ($this->_peeronly) {
            $this->_preparePeerOnly();
        } else {
            $this->_prepareSelfPeer();
        }


        // Remove the original responses.
        $this->_responses = null;


        return true;
    }

    // /->_initialise()

    /**
     * Prepare the ->_calc_?? properties, ready for the algorithm to process a peer-only assessment.
     *
     * @return  boolean  The operation was successful.
     */
    abstract protected function _preparePeerOnly();

    /**
     * Prepare the ->_calc_?? properties, ready for the algorithm to process a self-&-peer assessment.
     *
     * @return  boolean  The operation was successful.
     */
    abstract protected function _prepareSelfPeer();
}// /class
