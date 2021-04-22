<?php
/**
 * NewAlgorithm
 *
 * Responses are normalised by using the marks each member gave
 *
 * Fixed a bug where no one in a group submits anything, so the frac score totals 0
 * and causes the peer-moderated mark to equal 0.  It now checks for complete lack
 * of submissions and tweaks the final intermediate grade accordingly.
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\includes\classes;

class NewAlgorithm extends WebPAAlgorithm
{
    private $group_member_awarded;
    
    public function __construct()
    {
        WebPAAlgorithm::_init();
    }

    // /->NewAlgorithm()

    /*
    * ================================================================================
    * Public Methods
    * ================================================================================
    */

    // Calculate the WebPA and group scores for each member
    public function calculate()
    {

    // Perform a few different tasks and initialisations by looping through all the responses
        foreach ($this->_responses as $i => $response) {
            // Process the responses and re-factor the array into a more usable form
            $this->_group_member_responses["{$response['group_id']}"]["{$response['user_id']}"]["{$response['question_id']}"]["{$response['marked_user_id']}"] = $response['score'];

            // Record the fact this group member submitted
            $this->_group_member_submitted["{$response['group_id']}"]["{$response['user_id']}"] = true;

            // Record the fact this member submitted (separate from group-member)
            $this->_member_submitted["{$response['user_id']}"] = true;
        }

        /*
        * Begin the actual scoring algorithm.
        * Individual algorithmic steps are numbered
        */

        // Take each group in turn
        foreach ($this->_groups as $group_id => $group_mark) {
            $group_had_submissions = false;

            $this->_group_member_frac_scores_awarded["$group_id"] = [];
            $this->_group_member_total_received[$group_id] =  [];

            // Take each member in turn
            foreach ($this->_group_members["$group_id"] as $i => $member_id) {

        // Initialise each member's totals and scores
                $this->_group_member_total_received["$group_id"]["$member_id"] = 0;
                $this->_group_member_webpa_scores["$group_id"]["$member_id"] = 0;
                $this->_group_member_grades["$group_id"]["$member_id"] = 0;

                // If the member submitted a response, then we need to normalise the scores awarded
                if (array_key_exists($member_id, $this->_member_submitted)) {

          /*
           * We need to signal that at least one member submitted marks
           * If we don't we can't differentiate between 0-marks because someone was poor
           * and 0-marks because no one submitted anything - in which case every member
           * should automatically get a WebPA score of 1.
           */
                    $group_had_submissions = true;

                    /*
                     * (1)
                     * Get the total number of marks each member awarded
                     * This will form the basis for our normalisation in step (2)
                     */

                    $this->_group_member_total_awarded["$group_id"]["$member_id"] = 0;  // Initialise member-total
                    $this->group_member_awarded["$group_id"]["$member_id"] = [];

                    if (!array_key_exists($group_id, $this->_group_member_responses)) {

            // Do nothing, there's nothing to process
                    } else {
                        foreach ($this->_questions as $i => $question_id) {
                            foreach ($this->_group_member_responses["$group_id"]["$member_id"]["$question_id"] as $marked_user_id => $score) {
                                // Add the given score to the total
                                $this->_group_member_total_awarded["$group_id"]["$member_id"] += $score;
                                if (!array_key_exists($marked_user_id, $this->group_member_awarded["$group_id"]["$member_id"])) {
                                    $this->group_member_awarded["$group_id"]["$member_id"]["$marked_user_id"] = null;
                                } else {
                                    $this->group_member_awarded["$group_id"]["$member_id"]["$marked_user_id"] += $score;
                                }
                            }// /foreach(member-response)
                        }// /foreach(question)
                    }

                    /*
                     * (2)
                     * Get the normalised fraction awarded by each member to each member
                     * If member-A gave member-B 4 marks, then the fraction awarded = 4 / total-marks-member-A-awarded
                     */

                    // If the member gave more than 0 marks in total, calculate the fraction awarded
                    if ($this->_group_member_total_awarded["$group_id"]["$member_id"]>0) {
                        foreach ($this->_questions as $i => $question_id) {
                            foreach ($this->_group_member_responses["$group_id"]["$member_id"]["$question_id"] as $marked_user_id => $score) {
                                $this->_group_member_frac_scores_awarded["$group_id"]["$member_id"]["$question_id"]["$marked_user_id"] = $score / $this->_group_member_total_awarded["$group_id"]["$member_id"];
                            }// /foreach(member-response)
                        }// /foreach(question)
                    }// /if (member-total-award==0)
                }// /if(member-submitted)
            }// /foreach(member)

            // All the scores are now normalised. Time to calculate the actual WebPA scores

            /*
             * (3)
             * Get the multiplication factor we need to calculate the WebPA scores
             * factor = num-members-total / num-members-submitted
             */

            $num_members = ((is_array($this->_group_members)) && (array_key_exists($group_id, $this->_group_members))) ? count($this->_group_members["$group_id"]) : 0 ;
            $num_submitted = ((is_array($this->_group_member_submitted)) && (array_key_exists($group_id, $this->_group_member_submitted))) ? count($this->_group_member_submitted["$group_id"]) : 0 ;

            $multi_factor = ($num_submitted>0) ? ($num_members / $num_submitted) : 1 ;

            $pa_group_mark = ($this->_marking_params['weighting']/100) * $group_mark;
            $nonpa_group_mark = ((100-$this->_marking_params['weighting']) /100) * $group_mark;

            /*
             * (4)
             * Get the total fractional score awarded to each member for each question
             */

            foreach ($this->_group_member_frac_scores_awarded["$group_id"] as $member_id => $q_array) {
                foreach ($q_array as $question_id => $marked_array) {
                    foreach ($marked_array as $marked_user_id => $frac_score) {
                        $this->_group_member_total_received["$group_id"]["$marked_user_id"] += $frac_score;
                    }
                }
            }

            if (array_key_exists($group_id, $this->_group_member_total_received)) {
                foreach ($this->_group_member_total_received["$group_id"] as $member_id => $total_frac_score) {
                    /*
                     * (5)
                     * Get the WebPA score = total fractional score awarded to a member * multiplication-factor
                     */
                    $this->_group_member_webpa_scores["$group_id"]["$member_id"] = $total_frac_score * $multi_factor;

                    /*
                     * (6)
                     * Get the member's intermediate grade = WebPA score * weighted-group-mark     (does not include penalties)
                     */
                    if ($group_had_submissions) {
                        $intermediate_grade = (($total_frac_score * $multi_factor * $pa_group_mark) + $nonpa_group_mark);
                    } else {
                        $intermediate_grade = $pa_group_mark + $nonpa_group_mark;
                    }

                    if ($intermediate_grade<0) {
                        $intermediate_grade = 0;
                    } elseif ($intermediate_grade>100) {
                        $intermediate_grade = 100;
                    }

                    $this->_group_member_intermediate_grades["$group_id"]["$member_id"] = $intermediate_grade;


                    /* (7)
                     * Get the member's grade = WebPA score * weighted-group-mark * any penalty
                     */

                    $penalty = (array_key_exists($member_id, $this->_member_submitted)) ? 1 : 1-($this->_marking_params['penalty']/100) ;

                    // Don't need this bit now:
                    // $final_grade = (($total_frac_score * $multi_factor * $pa_group_mark) + $nonpa_group_mark) * $penalty;

                    $final_grade = $intermediate_grade * $penalty;

                    if ($final_grade<0) {
                        $final_grade = 0;
                    } elseif ($final_grade>100) {
                        $final_grade = 100;
                    }

                    $this->_group_member_grades["$group_id"]["$member_id"] = $final_grade;
                }
            }
        }// /foreach(group)
    }

    // /->calculate()

/*
* ================================================================================
* Private Methods
* ================================================================================
*/
}// /class: NewAlgorithm
