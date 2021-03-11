<?php
/**
 * WebPAAlgorithm
 *
 * A class implementing the Web-PA algorithm for calculating student grades.
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\includes\classes\algorithms;

class WebPAAlgorithm extends Algorithm
{
    /**
     * Constructor
     *
     * @return  object  A new instance of this class.
     */
    public function __construct()
    {
    }

    // /->__construct()



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
    public function calculate()
    {


    /*
     * The code below has been written to try to show the individual, algorithmic steps clearly.
     * You could probably improve the efficiency by combining loops, etc, but then the steps
     * are more difficult to follow.
     */


        /* (1)
         * Initialise the algorithm data and pre-fill most of the properties of this class.
         * Gets the total number of marks each member awarded, and to whom, etc.
         */

        $this->_initialise();


        // Take each group in turn
        foreach ($this->_group_grades as $group_id => $group_mark) {
            $group_member_frac_scores_awarded[$group_id] = [];
            $group_member_total_received[$group_id] =  [];

            // Take each member in turn
            foreach ($this->_group_members[$group_id] as $i => $member_id) {

        // Initialise each member's total fractional score
                $group_member_total_received[$group_id][$member_id] = 0;

                // If the member submitted a response, then we need to normalise the scores awarded
                if (in_array($member_id, $this->_calc_submitters)) {

          /* (2)
           * Get the normalised fraction awarded by each member to each member
           * If member-A gave member-B 4 marks, then the fraction awarded = 4 / total-marks-member-A-awarded
           */

                    // If the member gave more than 0 marks in total, calculate the fraction awarded
                    if ($this->_calc_total_marks_awarded[$member_id]>0) {
                        foreach ($this->_questions as $i => $question_id) {
                            foreach ($this->_calc_responses[$group_id][$question_id][$member_id] as $marked_user_id => $score) {
                                $group_member_frac_scores_awarded[$group_id][$member_id][$question_id][$marked_user_id] = $score / $this->_calc_total_marks_awarded[$member_id];
                            }// /foreach(member-response)
                        }// /foreach(question)
                    }// /if(member-total-award==0)
                }// /if(member-submitted)
            }// /foreach(member)


            // All the scores are now normalised. Time to calculate the actual Web-PA scores


            /* (3)
             * Get the multiplication factor we need to calculate the Web-PA scores
             * factor = num-members-total / num-members-submitted
             */

            $num_members = ((is_array($this->_group_members)) && (array_key_exists($group_id, $this->_group_members))) ? count($this->_group_members[$group_id]) : 0 ;
            $num_submitted = (array_key_exists($group_id, $this->_calc_group_submitters)) ? count($this->_calc_group_submitters[$group_id]) : 0 ;

            $multi_factor = ($num_submitted>0) ? ($num_members / $num_submitted) : 1 ;


            $pa_group_mark = ($this->_params['weighting']/100) * $group_mark;
            $nonpa_group_mark = ((100-$this->_params['weighting']) /100) * $group_mark;



            // @todo : apply criterion weightings here (empty method at present)
            $this->_applyCriterionWeightings();



            /* (4)
             * Get the total fractional score awarded to each member for each question
             */

            foreach ($group_member_frac_scores_awarded[$group_id] as $member_id => $q_array) {
                foreach ($q_array as $question_id => $marked_array) {
                    foreach ($marked_array as $marked_user_id => $frac_score) {
                        $group_member_total_received[$group_id][$marked_user_id] += $frac_score;
                    }
                }
            }


            if (array_key_exists($group_id, $group_member_total_received)) {
                foreach ($group_member_total_received[$group_id] as $member_id => $total_frac_score) {


          /* (5)
           * Get the Web-PA score = total fractional score awarded to a member * multiplication-factor
           */
                    $this->_webpa_scores[$member_id] = $total_frac_score * $multi_factor;


                    /* (6)
                     * Get the member's intermediate grade = Web-PA score * weighted-group-mark   (does not include penalties)
                     */
                    if (is_array($this->_calc_group_submitters) && array_key_exists($group_id, $this->_calc_group_submitters)) {
                        $intermediate_grade = (($total_frac_score * $multi_factor * $pa_group_mark) + $nonpa_group_mark);
                    } else {
                        $intermediate_grade = $pa_group_mark + $nonpa_group_mark;
                    }

                    if ($intermediate_grade<0) {
                        $intermediate_grade = 0;
                    } elseif ($intermediate_grade>100) {
                        $intermediate_grade = 100;
                    }

                    // Intermediate grades are whatever the algorithm thought the grade should be (before penalties)
                    $this->_intermediate_grades[$member_id] = $intermediate_grade;

                    /* (7)
                     * Get the member's actual grade
                     *
                     * At this point, final grades are the same as intermediate grades (penalties are applied at the end)
                     */

                    $this->_grades[$member_id] = $intermediate_grade;
                }
            }
        }// /foreach(group)


        /* (8)
         * Apply any penalties
         */
        $this->_applyPenalties();


        /*(9)
         * Make sure the grades conform to the requested grading style (% or A-F)
         */
        $this->_applyGradingStyle();


        return true;
    }

    // /->calculate()



    /*
    * ================================================================================
    * Private Methods
    * ================================================================================
    */

    /**
     * Prepare the ->_calc_?? properties, ready for the algorithm to process a peer-only assessment.
     *
     * @return  boolean  The operation was successful.
     */
    protected function _preparePeerOnly()
    {

    // Set the initial state of all the calc_?? properties
        $this->_calc_responses = $this->_actual_responses;

        $this->_calc_group_submitters = $this->_actual_group_submitters;
        $this->_calc_submitters = $this->_actual_submitters;

        $this->_calc_marks_awarded = $this->_actual_marks_awarded;
        $this->_calc_marks_received = $this->_actual_marks_received;

        $this->_calc_marks_awarded_by_member_question = $this->_actual_marks_awarded_by_member_question;
        $this->_calc_marks_received_by_member_question = $this->_actual_marks_received_by_member_question;

        $this->_calc_total_marks_awarded = $this->_actual_total_marks_awarded;
        $this->_calc_total_marks_received = $this->_actual_total_marks_received;



        // This is the score each student will receive if someone in their group does not submit.
        // It must be greater than 0, but as everyone will receive the same score, the actual value doesn't matter.
        $score = 5;


        // Flag if we find anyone who has submitted self-assessment marks
        $self_assessment = false;


        foreach ($this->_group_names as $group_id => $group_name) {
            $num_members = count($this->_group_members[$group_id]);
            $num_submitted = (array_key_exists($group_id, $this->_calc_group_submitters)) ? count($this->_calc_group_submitters[$group_id]) : 0 ;

            // If someone didn't submit..
            if ($num_members>$num_submitted) {

        // We have a non-submission.  We need to fake the submission data to avoid penalising students who did submit.
                // Faking the submission in this way means the algorithm can run normally.

                foreach ($this->_group_members[$group_id] as $j => $member_id) {
                    if (!in_array($member_id, $this->_calc_submitters)) {
                        $this->_calc_submitters[] = "$member_id";
                        $this->_calc_group_submitters[$group_id][] = "$member_id";

                        foreach ($this->_questions as $i => $question_id) {
                            foreach ($this->_group_members[$group_id] as $k => $marked_user_id) {

                // If it's not a self-mark, add it to the list
                                if ($member_id != $marked_user_id) {
                                    $this->_calc_responses[$group_id][$question_id][$member_id][$marked_user_id] = $score;

                                    $this->_calc_marks_awarded[$member_id][$marked_user_id] += $score;
                                    $this->_calc_marks_received[$marked_user_id][$member_id] += $score;

                                    $this->_calc_marks_awarded_by_member_question[$member_id][$question_id] += $score;
                                    $this->_calc_marks_received_by_member_question[$marked_user_id][$question_id] += $score;

                                    $this->_calc_total_marks_awarded[$member_id] += $score;
                                    $this->_calc_total_marks_received[$marked_user_id] += $score;
                                }
                            }// /foreach(group member)
                        }// /foreach(question)
                    }// /if(non-submitter)
                }// /foreach(group member)
            }// /if(non-submissions)
        }// /foreach(group)

        return true;
    }

    // /->_preparePeerOnly()

    /**
     * Prepare the ->_calc_?? properties, ready for the algorithm to process a self-&-peer assessment.
     *
     * @return  boolean  The operation was successful.
     */
    protected function _prepareSelfPeer()
    {

    // Set the initial state of all the calc_?? properties
        $this->_calc_responses = $this->_actual_responses;

        $this->_calc_group_submitters = $this->_actual_group_submitters;
        $this->_calc_submitters = $this->_actual_submitters;

        $this->_calc_marks_awarded = $this->_actual_marks_awarded;
        $this->_calc_marks_received = $this->_actual_marks_received;

        $this->_calc_marks_awarded_by_member_question = $this->_actual_marks_awarded_by_member_question;
        $this->_calc_marks_received_by_member_question = $this->_actual_marks_received_by_member_question;

        $this->_calc_total_marks_awarded = $this->_actual_total_marks_awarded;
        $this->_calc_total_marks_received = $this->_actual_total_marks_received;

        return true;
    }

    // /->_prepareSelfPeer()
}// /class
