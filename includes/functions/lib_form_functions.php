<?php
/**
 * 
 * Class UI - Site user interface
 *
 * 			
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 * 
 */
require_once('lib_array_functions.php');

$form_months = array( 1 => gettext('January'),gettext('February'),gettext('March'),gettext('April'),gettext('May'),gettext('June'),gettext('July'),gettext('August'),gettext('September'),gettext('October'),gettext('November'),gettext('December'));

/**
 * Is the given email address in a valid format
 * Format: <alphanum _ - characters> @ <alphanum _ - characters> . <alphanum _ - characters>...
 * 
 * @param string $email_address
 * @return bool 
 */
function is_email($email_address) {
	return (!preg_match('/^(a-zA-Z0-9_-])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)+/',$email_address));
}// /is_email()

/**
 * Render an associative array as <option></option> tags
 * 
 * @param array $arr Associative array to render
 * @param string $selected Value that should be selected (have attribute: selected="selected")
 * @param datatype $switch_kv Use $v as the option value instead of $k
 */
function render_options($arr, $selected = null, $switch_kv = false) {
	if (!$switch_kv) {
		if (!is_null($selected)) {
			foreach($arr as $k => $v) {
  			echo("<option value=\"$k\" ". ( ($k==$selected) ? 'selected="selected"' : '' ) ."> $v </option>");
		  }
		} else {
			foreach($arr as $k => $v) {
  			echo("<option value=\"$k\"> $v </option>");
		  }
		}
	} else {
		if ($selected) {
			foreach($arr as $k => $v) {
  			echo("<option value=\"$v\" ". ( ($v==$selected) ? 'selected="selected"' : '' ) ."> $v </option>");
		  }
		} else {
			foreach($arr as $k => $v) {
  			echo("<option value=\"$v\"> $v </option>");
		  }
		}
	}
}// /render_options()

/**
 * Render <option></option> tags for the given range of values
 * @param datatype $start
 * @param datatype $end
 * @param int $increment
 * @param null $selected
 *  
 */

function render_options_range($start, $end, $increment = 1, $selected = null) {
	for ($i=$start; $i<=$end; $i += $increment) {
		$selected_str = ($i==$selected) ? 'selected="selected"' : ''; 
		echo("<option value=\"$i\" $selected_str> $i </option>");
	}
}// /render_options_range()


/**
 * Draw the input grid
 * @param datatype $recs
 * @param datatype $input_type
 * @param datatype $input_name
 * @param datatype $num_cols
 * @param datatype $width
 * @param string $checked_attr
 */

function draw_input_grid(&$recs, $input_type, $input_name, $num_cols, $width, $checked_attr = '') {
		$recs_count = count($recs);
		echo('<table cellpadding="0" cellspacing="0" style="font-size: 92%" width="'.$width.'">');
		for ($i=0; $i<$recs_count; ++$i) {
			if (($i % $num_cols)==0) {
				if ($i!=0) { echo('</tr>'); }
				if ($i!=$recs_count-1) { echo('<tr>'); }
			}
			if (!empty($recs[$i]['input_checked'])) {
				$checked_str = 'checked="checked"';
				$label_str = $checked_attr;
			} else {
				$checked_str = '';
				$label_str = '';
			}
			
			echo("<td align=\"center\" width=\"22\"><input class=\"no_border\" type=\"$input_type\" name=\"$input_name\" id=\"{$input_name}_$i\" value=\"{$recs[$i]['input_value']}\" $checked_str /></td>");
			echo("<td><label for=\"{$input_name}_$i\" class=\"small\" $label_str>{$recs[$i]['input_label']}</label></td>");
		}
		echo('</table>');
}

/**
 * draws option tags using the given $recs array
 * 
 * @param datatype $recs
 * @param int $value_field
 * @param int $name_field
 * @param int $selected_index
 */
function draw_options(&$recs, $value_field=0, $name_field=1, $selected_index = null) {
	$recs_count = count($recs);
	if (is_int($selected_index)) {
		for ($i=0; $i<$recs_count; ++$i) {
			echo("<option value=\"{$recs[$i][$value_field]}\" ". (($selected_index==$i) ? 'selected="selected"': '') ."> {$recs[$i][$name_field]} </option>\r");
		}
	} else {
		for ($i=0; $i<$recs_count; ++$i) {
			echo("<option value=\"{$recs[$i][$value_field]}\" ". (($selected_index==$recs[$i][$value_field]) ? 'selected="selected"': '') ."> {$recs[$i][$name_field]} </option>\r");
		}
	}
}

/**
 * render all the check boxes
 * 
 * @param array $arr
 * @param string $input_name
 * @param array $selected_arr
 * @param bool $switch_value_label
 */
function render_checkboxes(&$arr, $input_name, $selected_arr, $switch_value_label = false) {
	if (is_null($selected_arr)) { $selected_arr = array(); }
	else { $selected_arr = (array) $selected_arr; }

	$arr_to_use = ($switch_value_label) ? array_flip($arr) : $arr;

	?>
	<table class="checkbox_grid" cellpadding="0" cellspacing="0">
	<?
		foreach($arr_to_use as $value => $label) {
			$id = $input_name .'_'. str_replace(' ','_', $value);
			$checked_str = (in_array($value, $selected_arr)) ? 'checked="checked"' : '';
			echo('<tr>');
			echo("<td><input type=\"checkbox\" name=\"$input_name\" id=\"$id\" value=\"$value\" $checked_str /></td>");
			echo("<th><label for=\"$id\">$label</label></th>");
			echo('</tr>');
		}
	?>
	</table>
	<?php
}// /render_checkboxes()


/**
 * write to screen the check box grid
 * 
 * @param array $arr
 * @param string $input_name
 * @param array $selected_arr
 * @param bool $switch_value_label
 * @param int $num_cols
*/
function render_checkbox_grid($arr, $input_name, $selected_arr, $switch_value_label = false, $num_cols = 1) {
	if (is_null($selected_arr)) { $selected_arr = array(); }
	else { $selected_arr = (array) $selected_arr; }

	$arr_to_use = ($switch_value_label) ? array_flip($arr) : $arr;
	
	$i = 0;
	$count = count($arr);

	// Calculate how many empty columns there must be
	$mod = $count % $num_cols;
	$empty_cols = ($mod == 0) ? 0 : $num_cols - ($count % $num_cols);
	?>
	<table class="checkbox_grid" cellpadding="0" cellspacing="0">
	<?php
	foreach($arr_to_use as $value => $label) {
		if (($i % $num_cols)==0) {
			if ($i!=0) { echo('</tr>'); }
			if ($i!=$count-1) { echo('<tr>'); }
		}
		$id = $input_name .'_'. str_replace(' ','_', $value);
		$checked_str = (in_array($value,$selected_arr)) ? 'checked="checked"' : '';
		
		echo("<td><input type=\"checkbox\" name=\"{$input_name}[]\" id=\"$id\" value=\"$value\" $checked_str /></td>");
		echo("<th><label for=\"$id\">$label</label></th>");
		$i++;
	}	
	echo( str_repeat('<td>&nbsp;</td><td>&nbsp;</td>',$empty_cols) );
	echo('</tr>');
	?>
	</table>
	<?php
}// /render_checkbox_grid()


/**
 * Write to screen the radio buttons
 * @param array $arr
 * @param string $input_name
 * @param string $selected_str
 * @param bool $switch_value_label
*/
function render_radio_boxes($arr, $input_name, $selected_str, $switch_value_label = false) {
	$arr_to_use = ($switch_value_label) ? array_flip($arr) : $arr;
	
	?>
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
		foreach($arr_to_use as $value => $label) {
			$id = $input_name .'_'. str_replace(' ','_', $value);
			$checked_str = ($value==$selected_str) ? 'checked="checked"' : '';
			echo('<tr>');
			echo("<td><input type=\"radio\" name=\"$input_name\" id=\"$id\" value=\"$value\" $checked_str /></td>");
			echo("<th><label for=\"$id\">$label</label></th>");
			echo('</tr>');
		}
//	}
	?>
	</table>
	<?php
}// /render_radio_boxes()

?>