<?php
/**
 * Class UI - Site user interface
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\includes\functions;

class Form
{
    /**
     * Is the given email address in a valid format
     * Format: <alphanum _ - characters> @ <alphanum _ - characters> . <alphanum _ - characters>...
     *
     * @param string $email_address
     * @return bool
     */
    public static function is_email($email_address)
    {
        return filter_var($email_address, FILTER_VALIDATE_EMAIL) === false ? false : true;
    }

    // /is_email()

    /**
     * Render an associative array as <option></option> tags
     *
     * @param array $arr Associative array to render
     * @param string $selected Value that should be selected (have attribute: selected="selected")
     * @param datatype $switch_kv Use $v as the option value instead of $k
     */
    public static function render_options($arr, $selected = null, $switch_kv = false)
    {
        if (!$switch_kv) {
            if (!is_null($selected)) {
                foreach ($arr as $k => $v) {
                    echo "<option value=\"$k\" ". (($k==$selected) ? 'selected="selected"' : '') ."> $v </option>";
                }
            } else {
                foreach ($arr as $k => $v) {
                    echo "<option value=\"$k\"> $v </option>";
                }
            }
        } else {
            if ($selected) {
                foreach ($arr as $k => $v) {
                    echo "<option value=\"$v\" ". (($v==$selected) ? 'selected="selected"' : '') ."> $v </option>";
                }
            } else {
                foreach ($arr as $k => $v) {
                    echo "<option value=\"$v\"> $v </option>";
                }
            }
        }
    }

    // /render_options()

    /**
     * Render <option></option> tags for the given range of values
     * @param datatype $start
     * @param datatype $end
     * @param int $increment
     * @param null $selected
     *
     */
    public static function render_options_range($start, $end, $increment = 1, $selected = null)
    {
        for ($i=$start; $i<=$end; $i += $increment) {
            $selected_str = ($i==$selected) ? 'selected="selected"' : '';
            echo "<option value=\"$i\" $selected_str> $i </option>";
        }
    }

    // /render_options_range()

    /**
     * write to screen the check box grid
     *
     * @param array $arr
     * @param string $input_name
     * @param array $selected_arr
     * @param bool $switch_value_label
     * @param int $num_cols
    */
    public static function render_checkbox_grid($arr, $input_name, $selected_arr, $switch_value_label = false, $num_cols = 1)
    {
        if (is_null($selected_arr)) {
            $selected_arr = [];
        } else {
            $selected_arr = (array) $selected_arr;
        }

        $arr_to_use = ($switch_value_label) ? array_flip($arr) : $arr;

        $i = 0;
        $count = count($arr);

        // Calculate how many empty columns there must be
        $mod = $count % $num_cols;
        $empty_cols = ($mod == 0) ? 0 : $num_cols - ($count % $num_cols); ?>
        <table class="checkbox_grid" cellpadding="0" cellspacing="0">
        <?php
        foreach ($arr_to_use as $value => $label) {
            if (($i % $num_cols)==0) {
                if ($i!=0) {
                    echo '</tr>';
                }
                if ($i!=$count-1) {
                    echo '<tr>';
                }
            }
            $id = $input_name .'_'. str_replace(' ', '_', $value);
            $checked_str = (in_array($value, $selected_arr)) ? 'checked="checked"' : '';

            echo "<td><input type=\"checkbox\" name=\"{$input_name}[]\" id=\"$id\" value=\"$value\" $checked_str /></td>";
            echo "<th><label for=\"$id\">$label</label></th>";
            $i++;
        }
        echo str_repeat('<td>&nbsp;</td><td>&nbsp;</td>', $empty_cols);
        echo '</tr>'; ?>
        </table>
        <?php
    }

    // /render_checkbox_grid()

    /**
     * Write to screen the radio buttons
     * @param array $arr
     * @param string $input_name
     * @param string $selected_str
     * @param bool $switch_value_label
    */
    public static function render_radio_boxes($arr, $input_name, $selected_str, $switch_value_label = false)
    {
        $arr_to_use = ($switch_value_label) ? array_flip($arr) : $arr; ?>
        <table class="radio_grid" cellpadding="0" cellspacing="0">
        <?php
        /*
        if ($switch_value_label) {
            foreach($arr as $label => $value) {
                $id = $input_name .'_'. str_replace(' ','_', $value);
                $checked_str = ($value==$selected_str) ? 'selected="selected"' : '';
                echo('<tr>');
                echo("<td><input type=\"radio\" name=\"$input_name\" id=\"$id\" value=\"$value\" $checked_str /></td>");
                echo("<th><label for=\"$id\">$label</label></th>");
                echo('</tr>');
            }
        } else {
        */
            foreach ($arr_to_use as $value => $label) {
                $id = $input_name .'_'. str_replace(' ', '_', $value);
                $checked_str = ($value==$selected_str) ? 'checked="checked"' : '';
                echo '<tr>';
                echo "<td><input type=\"radio\" name=\"$input_name\" id=\"$id\" value=\"$value\" $checked_str /></td>";
                echo "<th><label for=\"$id\">$label</label></th>";
                echo '</tr>';
            }
        //	}
        ?>
        </table>
        <?php
    }

    // /render_radio_boxes()
}
