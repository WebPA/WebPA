<?php
/**
 * Class :  Wizard
 *
 * Includes the form-functions library in case a page needs it
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\includes\classes;

use WebPA\includes\functions\Common;

class Wizard
{
    // Public Vars
    public $back_button = '&lt; Back';

    public $next_button = 'Next &gt;';

    public $cancel_button = 'Cancel';

    public $cancel_url = '';

    public $name = '';

    // Private Vars
    private $_page_url;

    private $_head_content;

    private $_form_content;

    private $_current_step = 1;

    private $_total_steps = 1;

    private $_override_num_steps;

    private $_last_wizstep;

    private $_current_wizstep;

    private $_step_includes = [];

    private $_fields = [];

    private $_vars = [];

    private $_errors;

    /**
    * CONSTRUCTOR for the wizard class
    * @param string $name
    */
    public function __construct($name)
    {
        $this->name = $name;
        $this->_page_url = $_SERVER['PHP_SELF'];

        $this->_fields = unserialize(base64_decode(Common::fetch_POST('wiz_stored_fields', null)));
        if (!is_array($this->_fields)) {
            $this->_fields = [];
        }
    }

    // /->Wizard()

    /*
    * --------------------------------------------------------------------------------
    * Public Methods
    * --------------------------------------------------------------------------------
    */

    /**
     * Function to add a step to the wizard
     * @param string $step_num
     * @param string $step_include_file
     */
    public function add_step($step_num, $step_include_file)
    {
        $step_num = (int) $step_num;
        $this->_step_includes["$step_num"] = $step_include_file;
    }

    // /add_step()

    /**
     * Function to write errors to screen
     */
    public function draw_errors()
    {
        if ($this->_errors) {
            ?>
      <div class="error_box" style="">
        <p style="font-weight: bold;">The following errors were found:</p>
        <ul class="spaced">
        <?php
        foreach ($this->_errors as $error_msg) {
            echo "<li>$error_msg</li>";
        } ?>
        </ul>
        <p>Please check the information in the form and try again.</p>
      </div>
      <?php
        }
    }

    // ->draw_errors()

    /**
     * function to write the wizard information to the screen
     */
    public function draw_wizard()
    {
        $wiz_fields = base64_encode(serialize($this->_fields));

        // build the wizard HTML code
        $html = <<<HTMLEnd
                <form action="{$this->_page_url}" method="post" name="wizard_form" onsubmit="return wizard_form_onsubmit();">
                <input type="hidden" name="wiz_stored_fields" value="$wiz_fields" />
                <input type="hidden" name="wiz_command" id="wiz_command" value="none" />

                <div style="min-height: 170px;">
            HTMLEnd;
        echo $html;

        if ($this->_current_wizstep) {
            $this->_current_wizstep->form();
        }

        // set the text for the wizard buttons
        $temp_back = (empty($this->back_button)) ? '&nbsp;': "<input type=\"button\" name=\"wiz_command_back\" id=\"wiz_command_back\" onclick=\"do_command('back')\" value=\"$this->back_button\" />";
        $temp_next = (empty($this->next_button)) ? '&nbsp;': "<input type=\"button\" name=\"wiz_command_next\" id=\"wiz_command_next\" onclick=\"do_command('next')\" value=\"$this->next_button\" />";
        $temp_cancel = (empty($this->cancel_button)) ? '&nbsp;': "<input type=\"button\" name=\"wiz_command_cancel\" id=\"wiz_command_cancel\" onclick=\"do_command('cancel')\" value=\"$this->cancel_button\" />";

        $html = <<<HTMLEnd
                </div>

                <table align="center" style="margin-top: 20px;" cellpadding="0" cellspacing="0" width="70%">
                <tr>
                  <td align="left" width="33%">$temp_back</td>
                  <td align="center" width="33%">$temp_cancel</td>
                  <td align="right" width="33%">$temp_next</td>
                </tr>
                </table>

                </form>
            HTMLEnd;

        echo $html;
    }

    // /->draw_wizard()

    /**
     * function prepare
     */
    public function prepare()
    {
        $this->_current_step = (array_key_exists('current_step', $this->_fields)) ? $this->_fields['current_step'] : 1 ;
        $this->_total_steps = count($this->_step_includes);

        $do_last = false;
        $do_next = true;

        $wiz_command = Common::fetch_POST('wiz_command');

        switch ($wiz_command) {
      case 'back':
            $this->_current_step = ($this->_current_step>1) ? ($this->_current_step-1) : 1;
            $do_last = false;
            break;
      // ----------
      case 'next':
            $this->_current_step = ($this->_current_step<$this->_total_steps) ? ($this->_current_step + 1) : $this->_total_steps;
            $do_last = true;
            break;
      // ----------
      case 'cancel':
            header("Location: {$this->cancel_url}");
            exit;
            break;
      // ----------
      default:
            $do_last = false;
            break;
    }// /switch


        // check if we should even try to do the wizard
        if ($this->_current_step<=$this->_total_steps) {

      // get the last page we loaded
            if (($do_last) && ($this->_current_step>1)) {
                $last_wiz_num = $this->_current_step - 1;
                include_once $this->_step_includes["$last_wiz_num"];
                eval("\$this->_last_wizstep = new WizardStep{$last_wiz_num}(\$this);");

                if ($this->_last_wizstep) {
                    $this->_errors = $this->_last_wizstep->process_form();

                    // if there are errors, we won't be showing the next page, we'll go back to the last one!
                    if (is_array($this->_errors)) {
                        $this->_current_step = $this->_last_wizstep->step;
                        $this->_current_wizstep =& $this->_last_wizstep;
                    }
                }
            }// /if checking last page

            // If the last page of the wizard was OK (or didn't exist), get the next page
            if ($do_next) {
                // get the current step
                include_once $this->_step_includes["{$this->_current_step}"];
                eval("\$this->_current_wizstep = new WizardStep{$this->_current_step}(\$this);");
            }
        }

        $this->_current_step = $this->_current_wizstep->step;
        $this->_fields['current_step'] = $this->_current_step;
    }

    // /->prepare()

    /**
     * function to write the title of the wizard step to the screen
     */
    public function title()
    {
        if (!is_null($this->_override_num_steps)) {
            if ($this->_current_step<=$this->_override_num_steps) {
                echo "<p>You are on <strong>step {$this->_current_step}</strong> of <strong>{$this->_override_num_steps}</strong> in the {$this->name}.</p>";
            }
        } else {
            echo "<p>You are on <strong>step {$this->_current_step}</strong> of <strong>{$this->_total_steps}</strong> in the {$this->name}.</p>";
        }
    }

    // /->title()

    /**
     * function to get the field names for the input boxes
     * @param string $field_name
     * @return array
     */
    public function get_field($field_name, $default = null)
    {
        return (array_key_exists($field_name, $this->_fields)) ? $this->_fields["$field_name"] :  $default ;
    }

    // /->get_field()

    /**
     * function to set the fields used in the wizard
     * @param string $field_name
     * @param string $field_value
     */
    public function set_field($field_name, $field_value)
    {
        $this->_fields["$field_name"] = $field_value;
    }

    // /->set_field()

    /**
     * function to set variables
     * @param string $var_name
     * @param string $var_value
     */
    public function set_var($var_name, &$var_value)
    {
        $this->_vars["$var_name"] =& $var_value;
    }

    // /->set_var()

    /**
     * function to get the variable by name
     * @param string $var_name
     *
     * @return mixed|null
     */
    public function get_var($var_name)
    {
        return (array_key_exists($var_name, $this->_vars)) ? $this->_vars["$var_name"] :  null ;
    }

    /**
     * Function to get the step
     * @return string
     */
    public function get_step()
    {
        return $this->_current_step;
    }

    // /->get_step()

    /**
     * function to set the url for the wizard
     * @param string $url
     */
    public function set_wizard_url($url)
    {
        $this->_page_url = $url;
    }

    // /->set_page_url()

    /**
     * function to show the steps
     * @param string|integer $num_steps
     */
    public function show_steps($num_steps)
    {
        $this->_override_num_steps = $num_steps;
    }

    // /->show_steps

    /**
     * function to wite the head for the wizard page
     */
    public function head()
    {
        $this->_current_wizstep->head();

        $html = <<<HTMLEnd
            <script language="JavaScript" type="text/javascript">
            <!--

              function do_command(str_comm) {
                document.getElementById('wiz_command').value = str_comm;
                document.wizard_form.submit();
              }// /do_command

              function wizard_form_onsubmit() {
                return (document.wizard_form.wiz_command.value !='none');
              }

            //-->
            </script>
            HTMLEnd;

        echo $html;
    }

    // /->head()

/*
* --------------------------------------------------------------------------------
* Private Methods
* --------------------------------------------------------------------------------
*/
}// /class: Wizard

?>
