<?php
/**
 * Class :  FormRenderer
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\includes\classes;

use WebPA\includes\functions\Common;

class FormRenderer
{
    // Public Vars
    public $participant_name = '';

    public $participant_id;

    public $assessment_feedback = '0';

    public $assessment_feedback_title;

    // Private Vars
    private $_form;

    private $_questions;

    private $_participants;

    private $_results;

    // CONSTRUCTOR
    public function __construct()
    {
    }

    // /->FormRenderer()

    /*
    * ================================================================================
    * Public Methods
    * ================================================================================
    */

    /*
    * Set the form to render
    *
    * @param object $form_object  form object to use when rendering
    */
    public function set_form(&$form_object)
    {
        $this->_form =& $form_object;
    }

    // /->set_form()

    /**
     * Set the participants
     * @param array $participants_array
    */
    public function set_participants($participants_array)
    {
        $this->_participants = $participants_array;
    }

    // /->set_participants()

    /**
    * Set the results to display
    *
    * @param array $results results to load
    *
    */
    public function set_results($results_array)
    {
        $this->_results = $results_array;
    }

    // /->set_results()

    /**
     * Draw the <head> section for this form
     */
    public function draw_description()
    {
        if ($this->_form->type =='split100') {
            $participant_count = count($this->_participants);
            $remainder = 100 % $participant_count;
            $group_total = 100 - $remainder;
            $default_score = floor(100 / $participant_count); ?>
      <p>For each criterion you have <?php echo $group_total; ?> marks to split between your teammates.  If everyone contributed equally, then everyone should receive the same score, <?php echo $default_score; ?> marks.  However, you can take points away from teammates who performed poorly, and re-allocate them to those you thought performed better.</p>

      <p>For each criterion, the total number of marks you allocate must equal <?php echo $group_total; ?>.</p>
      <?php
        } else {
            ?>
      <p>For each criterion you must rate your teammates using the scale provided.  High <?php echo APP__MARK_TEXT; ?> indicate better performance in the criteria.</p>
      <?php
        }
    }

    // /->draw_description()

    /**
     * Draw the <head> section for this form
     */
    public function draw_head()
    {
        if ($this->_form->type =='split100') {
            $participant_count = count($this->_participants);
            $remainder = 100 % $participant_count;
            $group_total = 100 - $remainder; ?>
      <script type="text/javascript"><!--

        var group_total = <?php echo $group_total; ?>;

        function calcTotal(q_id, participant_count) {
          var total_obj = document.getElementById('total_'+q_id);
          if (total_obj) {

            var total = 0;
            var score = 0;

            for(i=1; i<=participant_count; i++) {
              score_box = document.getElementById('q_'+q_id+'_'+i);
              if (score_box) {
                score = parseInt(score_box.value);
                if (!isNaN(score)) {
                  total = total + score;
                }
              }
            }

            // Output the total
            if (typeof total_obj.textContent != 'undefined') {
              total_obj.textContent = total;
            } else {
              total_obj.innerText = total;
            }

            if (typeof total_obj.style.backgroundColor != 'undefined') {
              if (total==group_total) {
                total_obj.style.backgroundColor='transparent';
              } else {
                if (total>group_total) {
                  total_obj.style.backgroundColor='#f99';
                } else {
                  total_obj.style.backgroundColor='#ff9';
                }
              }
            } else {
              if (total==group_total) {
                total_obj.style.bgColor='transparent';
              } else {
                if (total>100) {
                  total_obj.style.bgColor='#f99';
                } else {
                  total_obj.style.bgColor='#ff9';
                }
              }
            }
          }

        }// /calcTotal()

      // -->
      </script>
      <?php
        }
    }

    // /->draw_head()

    /**
     * Function to draw the form
    */
    public function draw_form()
    {
        $question_count = $this->_form->get_question_count();

        
        if ($this->_form->type=='split100') {
            $participant_count = count($this->_participants);
            $remainder = 100 % $participant_count;
            $group_total = 100 - $remainder;
            $default_score = floor(100 / $participant_count);

            for ($q=0; $q<$question_count; $q++) {
                $question = $this->_form->get_question($q);

                $q_num = $q + 1; ?>
        <div class="question">

          <div class="question_text"><?php echo "{$q_num}. {$question['text']['_data']}"; ?></div>
<?php
        if (array_key_exists('desc', $question)) {
            $question_desc = (array_key_exists('desc', $question)) ? $question['desc']['_data'] : '' ;
            echo "<div>$question_desc</div>";
        } ?>

          <div class="info_box" style="float: right; width: 250px; margin: 2em 2em 0 2em; font-size: 0.9em;">
<?php
        if ($remainder==0) {
            ?>
              <p>You have <span style="font-weight: bold;"><?php echo $group_total; ?> marks</span> to divide between your teammates.</p>
<?php
        } else {
            ?>
              <p>For a group of <?php echo $participant_count; ?>, you have <span style="font-weight: bold;"><?php echo $group_total; ?> marks</span> to divide between your teammates.</p>
<?php
        } ?>
            <p style="font-weight: bold;">For equal performance, every student should receive <?php echo $default_score; ?> marks.</p>
          </div>

          <table class="question_grid" cellpadding="3" cellspacing="1">
<?php
        // header row
        echo '<tr><td style="background-color: transparent">&nbsp;</td><th align="center">score</th></tr>';

                // show participant rows
                $initial_total = 0;
                $index = 1;

                foreach ($this->_participants as $id => $name) {
                    $class = ($id==$this->participant_id) ? 'class="this_participant"' : '';

                    $score = Common::fetch_POST("q_{$q}_{$id}");

                    $initial_total += (int) $score;

                    echo "<tr id=\"q_{$q}_{$id}\" $class>";
                    echo "<td class=\"participant\" width=\"200\" valign=\"middle\">$name</th>\n";
                    echo "<td><input type=\"text\" name=\"q_{$q}_{$id}\" id=\"q_{$q}_{$index}\" size=\"6\" maxlength=\"4\" value=\"$score\" onchange=\"calcTotal({$q},{$participant_count})\" onkeyup=\"calcTotal({$q},{$participant_count})\" /></label></td>\n";
                    $index++;
                }
                echo "</tr>\n";

                // Footer row
                if ($initial_total==$group_total) {
                    $bgcolor = 'transparent';
                } else {
                    if ($initial_total>$group_total) {
                        $bgcolor = '#f99';
                    } else {
                        $bgcolor = '#ff9';
                    }
                }

                echo "<tr><th style=\"text-align: right;\">Total&nbsp;</th><th><div style=\"background-color: $bgcolor;\" id=\"total_{$q}\">$initial_total</div></th></tr>"; ?>
          </table>
        </div>
<?php
            }// /foreach(question)
        } else {
            for ($q=0; $q<$question_count; $q++) {
                $question = $this->_form->get_question($q);

                $q_num = $q + 1;
                $range = explode('-', $question['range']['_data']);
                $min_score = $range[0];
                $max_score = $range[1]; ?>
        <div class="question">
          <div class="question_text"><?php echo "{$q_num}. {$question['text']['_data']}"; ?></div>
<?php
        if (array_key_exists('desc', $question)) {
            $question_desc = (array_key_exists('desc', $question)) ? $question['desc']['_data'] : '' ;
            echo "<div>$question_desc</div>";
        } ?>
          <div class="question_scoring_labels">
<?php
        foreach ($question as $k => $v) {
            if (strpos($k, 'scorelabel')===0) {
                $num = str_replace('scorelabel', '', $k);
                echo "<div class=\"question_score_label\">Score $num : {$v['_data']}</div>";
            }
        } ?>
          </div>

          <table class="question_grid" cellpadding="3" cellspacing="1">
<?php
        // header row
        echo '<tr><td style="background-color: transparent">&nbsp;</td>';
                for ($score=$min_score; $score<=$max_score; $score++) {
                    echo "<th align=\"center\" width=\"40\">$score</th>\n";
                }
                echo '</tr>';

                // show participant rows
                foreach ($this->_participants as $id => $name) {
                    $class = ($id==$this->participant_id) ? 'class="this_participant"' : '';

                    $q_score = ((is_array($this->_results))
                        && (array_key_exists("$q", $this->_results))
                        && (array_key_exists($id, $this->_results["$q"]))) ? $this->_results["$q"]["$id"] : null;

                    echo "<tr id=\"q_{$q}_{$id}\" $class>";
                    echo "<td class=\"participant\" width=\"200\" valign=\"middle\">$name</th>\n";
                    for ($score=$min_score; $score<=$max_score; $score++) {
                        $checked = ($q_score==$score) ? 'checked="checked"' : '';
                        echo "<td width=\"40\" valign=\"middle\"><label for=\"q_{$q}_{$id}_{$score}\" style=\"display: block;\"><input type=\"radio\" name=\"q_{$q}_{$id}\" id=\"q_{$q}_{$id}_{$score}\" value=\"$score\" $checked /></label></td>\n";
                    }
                }
                echo "</tr>\n"; ?>
          </table>
        </div>
<?php
            }// /foreach(question)
        }// /if-else (likert)

    if (APP__ALLOW_TEXT_INPUT) {
        if (!empty($this->assessment_feedback)) {
            if ($this->assessment_feedback) {
                ?>
        <div class="question">
        <p><b><?php echo $this->assessment_feedback_title; ?></b></p>
          <p>This section of the assessment is for you to provide general feedback and/or justification of the <?php echo APP__MARK_TEXT; ?> you have awarded in the section above.</p>
          <table class="question_grid" >
<?php
          //now we know that the assessment feedback is allowed by the system we need to find out if the tutor has set feedback
          if ($this->assessment_feedback) {
              //out put the boxes
              foreach ($this->_participants as $id => $name) {
                  echo "<tr><td class=\"participant\" width=\"200\" valign=\"middle\">$name</th>\n";
                  echo "<td><textarea name=\"$id\" rows=\"4\" cols=\"75\">". Common::fetch_POST($id) .'</textarea></td></tr>';
              }
          } ?>
          </table>
        </div>
<?php
            }
        }
    }
    }

    // /->draw_form()

/*
* ================================================================================
* Private Methods
* ================================================================================
*/
}// /class: Form

?>
