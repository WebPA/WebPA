<?php
/**
 * PetsAlgorithm
 *
 * A class implementing the PETS algorithm for calculating student grades.
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\includes\classes\algorithms;

// @todo : There appear to be grading problems with the PETS algorithm in peer-only mode.
// Until we have conducted a full investigation and nailed down what's happening
// this algorithm is disabled.

class PetsAlgorithm extends Algorithm
{
    // Public Properties
    protected $_group_split100;

    protected $_group_average_per_question;

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

    /* The code below has been written to try to show the individual, algorithmic steps clearly.
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

      /* (2)
       * Get the multiplication factor we need to calculate the WebPA scores
       * factor = num-members-total / num-members-submitted
       */

            $num_members = ((is_array($this->_group_members)) && (array_key_exists($group_id, $this->_group_members))) ? count($this->_group_members[$group_id]) : 0 ;

            $num_submitted = ((is_array($this->_calc_group_submitters)) && (array_key_exists($group_id, $this->_calc_group_submitters))) ? count($this->_calc_group_submitters[$group_id]) : 0 ;

            $multi_factor = ($num_submitted>0) ? ($num_members / $num_submitted) : 1 ;

            $pa_group_mark = ($this->_params['weighting']/100) * $group_mark;
            $nonpa_group_mark = ((100-$this->_params['weighting']) /100) * $group_mark;

            if (array_key_exists($group_id, $this->_group_members)) {
                $q_count = count($this->_questions);

                foreach ($this->_group_members[$group_id] as $i => $member_id) {
                    $total_score = $this->_calc_total_marks_received[$member_id];

                    /* (3)
                     * Get the WebPA score = (total received by a member / average total score if all students equal ) * multiplication-factor
                     */

                    $avg_total = $this->_group_split100[$group_id] * $q_count;

                    $this->_webpa_scores[$member_id] = ($total_score / $avg_total) * $multi_factor;

                    /* (4)
                     * Get the member's intermediate grade = WebPA score * weighted-group-mark   (does not include penalties)
                     */

                    if (is_array($this->_calc_group_submitters[$group_id])) {
                        $intermediate_grade = (($this->_webpa_scores[$member_id] * $pa_group_mark) + $nonpa_group_mark);
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

                    /* (5)
                     * Get the member's actual grade
                     *
                     * At this point, final grades are the same as intermediate grades (penalties are applied at the end)
                     */

                    $this->_grades[$member_id] = $intermediate_grade;
                }// /foreach(member)
            }// /if(are members)
        }// /foreach(group)

        /* (6)
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
     * Convert the responses from likert-scale types to split100 types.
     *
     * @return  boolean  The operation was successful.
     */
    protected function _convertFromLikert()
    {

    // If the form was using likert criteria questions, convert them to split100 style.
        // Convert the ->_calc_responses to a likert scale
        if ($this->_form_type=='likert') {

      // The form-type was likert, and we need to convert the scores accordingly.
            // Each split100 score = (group-average-total / total-marks-awarded-for-question-by-student) * likert-score

            foreach ($this->_responses as $i => $response) {
                $group_id = $response['group_id'];
                $member_id = $response['user_id'];
                $marked_user_id = $response['marked_user_id'];
                $question_id = $response['question_id'];
                $likert_score = (float) $response['score'];

                // Calculate Split100 score from Likert score
                $total_awarded = $this->_actual_marks_awarded_by_member_question[$member_id][$question_id];

                $proportional_score = ($likert_score / $total_awarded);

                $score =  $proportional_score * $this->_group_split100[$group_id];

                // Ensure future calculations use the converted score
                $this->_calc_responses[$group_id][$question_id][$member_id][$marked_user_id] = $score;
            }// /foreach(response)
        }// /if(likert questions)

        return true;
    }

    // ->_convertFromLikert()

    /**
     * Prepare the ->_calc_?? properties, ready for the algorithm to process a peer-only assessment.
     *
     * @return  boolean  The operation was successful.
     */
    protected function _preparePeerOnly()
    {

    // Get the number of marks available in each question.
        foreach ($this->_group_names as $group_id => $group_name) {
            $member_count = count($this->_group_members[$group_id]) - 1;
            $this->_group_split100[$group_id] = (100-(100 % $member_count));
            $this->_group_average_per_question[$group_id] = $this->_group_split100[$group_id] / $member_count;
        }

        // Convert likert marks to split100 marks (if required)
        $this->_convertFromLikert();

        // Process all the student responses
        // Set the correct values for the actual marks awarded, received, etc

        foreach ($this->_group_grades as $group_id => $group_mark) {

      // The score every member receives if their performance is equal
            $avg_score = $this->_group_average_per_question[$group_id];

            $num_members = count($this->_group_members[$group_id]);

            foreach ($this->_group_members[$group_id] as $i => $member_id) {

        // For the calculations, record everyone as submitting regardless of whether they did or not
                $this->_calc_submitters[] = $member_id;
                $this->_calc_group_submitters[$group_id][] = $member_id;

                foreach ($this->_questions as $i => $question_id) {
                    foreach ($this->_group_members[$group_id] as $k => $marked_user_id) {
                        if ($member_id!=$marked_user_id) {

              // If the student did not submit..
                            if (!in_array($member_id, $this->_actual_submitters)) {

                // Fake the response using the avg score / question
                                $score = $avg_score;
                                $this->_calc_responses[$group_id][$question_id][$member_id][$marked_user_id] = $score;
                            } else {

                // Get the score the student submitted
                                $selfpeer_score = $this->_calc_responses[$group_id][$question_id][$member_id][$marked_user_id];

                                // Get the total marks awarded per question

                                $total_awarded_peeronly = $this->_group_split100[$group_id];

                                // If there's a self mark, we need to subtract it to get the total peer-only marks awarded per question
                                if (array_key_exists($member_id, $this->_calc_responses[$group_id][$question_id][$member_id])) {
                                    $total_awarded_peeronly -= $this->_calc_responses[$group_id][$question_id][$member_id][$member_id];
                                }

                                // Adjusted for peeronly score
                                $score = $selfpeer_score / ($total_awarded_peeronly / ($num_members - 1)) * $this->_group_average_per_question[$group_id];
                            }// /if-else(student did submit)

                            $this->_calc_marks_awarded[$member_id][$marked_user_id] += $score;
                            $this->_calc_marks_received[$marked_user_id][$member_id] += $score;

                            $this->_calc_marks_awarded_by_member_question[$member_id][$question_id] += $score;
                            $this->_calc_marks_received_by_member_question[$marked_user_id][$question_id] += $score;

                            $this->_calc_total_marks_awarded[$member_id] += $score;
                            $this->_calc_total_marks_received[$marked_user_id] += $score;
                        }// /if(not awarded to self)
                    }// /foreach(marked member)
                }// /foreach(question)
            }// /foreach(member)
        }// /foreach(group)

        return true;

        // @todo: if there's a non-submission, fake the scores using avg / q
    // score = ( old-score / (total_marks_awarded_in_peeronly / num_members - 1) ) * peer_only_avg_per_question
    }

    // /->_preparePeerOnly()

    /**
     * Prepare the ->_calc_?? properties, ready for the algorithm to process a self-&-peer assessment.
     *
     * @return  boolean  The operation was successful.
     */
    protected function _prepareSelfPeer()
    {

    // Get the number of marks available in each question.
        foreach ($this->_group_names as $group_id => $group_name) {
            $member_count = count($this->_group_members[$group_id]);
            $this->_group_split100[$group_id] = (100-(100 % $member_count));
            $this->_group_average_per_question[$group_id] = $this->_group_split100[$group_id] / $member_count;
        }

        // Convert likert marks to split100 marks (if required)
        $this->_convertFromLikert();

        // Process all the student responses
        // Set the correct values for the actual marks awarded, received, etc

        foreach ($this->_group_grades as $group_id => $group_mark) {

      // The score every member receives if their performance is equal
            $avg_score = $this->_group_average_per_question[$group_id];

            foreach ($this->_group_members[$group_id] as $i => $member_id) {

        // For the calculations, record everyone as submitting regardless of whether they did or not
                $this->_calc_submitters[] = $member_id;
                $this->_calc_group_submitters[$group_id][] = $member_id;

                foreach ($this->_questions as $i => $question_id) {
                    foreach ($this->_group_members[$group_id] as $k => $marked_user_id) {

            // If the student did not submit..
                        if (!in_array($member_id, $this->_actual_submitters)) {
                            // Fake the response using the avg score / question
                            $score = $avg_score;
                            $this->_calc_responses[$group_id][$question_id][$member_id][$marked_user_id] = $score;
                        } else {
                            // Use the score the student submitted
                            $score = $this->_calc_responses[$group_id][$question_id][$member_id][$marked_user_id];
                        }

                        $this->_calc_marks_awarded[$member_id][$marked_user_id] += $score;
                        $this->_calc_marks_received[$marked_user_id][$member_id] += $score;

                        $this->_calc_marks_awarded_by_member_question[$member_id][$question_id] += $score;
                        $this->_calc_marks_received_by_member_question[$marked_user_id][$question_id] += $score;

                        $this->_calc_total_marks_awarded[$member_id] += $score;
                        $this->_calc_total_marks_received[$marked_user_id] += $score;
                    }// /foreach(marked member)
                }// /foreach(question)
            }// /foreach(member)
        }// /foreach(group)

        return true;
    }

    // /->_prepareSelfPeer()
}// /class
