<?php
/**
 * Class : WizardStep2  (edit qcriterion wizard)
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

use WebPA\includes\functions\Common;

class WizardStep2
{
    // Public
    public $wizard;

    public $step = 2;

    // CONSTRUCTOR
    public function __construct(&$wizard)
    {
        $this->wizard =& $wizard;

        $this->wizard->back_button = '&lt; Back';
        $this->wizard->next_button = 'Finish';
        $this->wizard->cancel_button = 'Cancel';
    }

    // /WizardStep2()

    public function head()
    {
        ?>
<script language="JavaScript" type="text/javascript">
<!--

  function body_onload() {
  }// /body_onload()

//-->
</script>
<?php
    }

    // /->head()

    public function form()
    {
        $form =& $this->wizard->get_var('form');

        $range_start = $this->wizard->get_field('question_range_start');
        $range_end = $this->wizard->get_field('question_range_end');

        $question = $form->get_question($this->wizard->get_var('question_id'));

        if ((is_array($question))) {
            for ($i=$range_start; $i<=$range_end; $i++) {
                if ((array_key_exists("scorelabel{$i}", $question)) && (!$this->wizard->get_field("scorelabel{$i}"))) {
                    $this->wizard->set_field("scorelabel{$i}", $question["scorelabel{$i}"]['_data']);
                }
            }
        } ?>
    <p>Your new assessment criterion allows scores from <?php echo "$range_start to $range_end"; ?>. You can use the boxes below to provide a description what those scores should mean.</p>
    <p>It's good practice to describe the meaning of at least the top and bottom scores, but you are free to provide as many, or as few, descriptions as you like. Leave a description blank and it will not be displayed on the form.</p>

    <p><strong>Score descriptions</strong></p>
    <div class="form_section">
      <p><?php echo $this->wizard->get_field('question_text'); ?></p>
      <table class="form" cellpadding="2" cellspacing="2">
      <?php
        for ($i=$range_start; $i<=$range_end; $i++) {
            echo '<tr>';
            echo "<th><label for=\"scorelabel{$i}\">Score $i</label></th>";
            echo "<td><input type=\"text\" name=\"scorelabel{$i}\" id=\"scorelabel{$i}\" maxlength=\"255\" size=\"50\" value=\"". $this->wizard->get_field("scorelabel{$i}") .'" /></td>';
            if ($i==$range_start) {
                echo '<td style="font-size: 0.9em; font-style: italic;">Lowest</td>';
            } else {
                if ($i==$range_end) {
                    echo '<td style="font-size: 0.9em; font-style: italic;">Highest</td>';
                } else {
                    echo '<td>&nbsp;</td>';
                }
            }
            echo '</tr>';
        } ?>
      </table>
    </div>
<?php
    }

    // /->form()

    public function process_form()
    {
        $errors = null;

        $range_start = $this->wizard->get_field('question_range_start');
        $range_end = $this->wizard->get_field('question_range_end');

        for ($i=$range_start; $i<=$range_end; $i++) {
            $scorelabel = trim(Common::fetch_POST("scorelabel{$i}"));
            if (!empty($scorelabel)) {
                $this->wizard->set_field("scorelabel{$i}", $scorelabel);
            }
        }

        return $errors;
    }

    // /->process_form()
}// /class: WizardStep2

?>
