<?php
/**
 * 
 * String Functions
 *
 * 			
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 1.0.0.0
 * 
 */
DEFINE('STR_ALPHA_CHARS','ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
DEFINE('STR_ALPHANUM_CHARS','0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
DEFINE('STR_UUID_CHARS','0123456789ABCDEF-');

/**
 * Create a query string style key=value string from the given array
 * 
 * @param array $kv_array
 * @param string $separator
 * 
 * @return datatype $kvs
*/
function query_str ($kv_array, $separator = '&') {
	$kvs = '';
	foreach ($kv_array as $key => $value) {
		if (strlen($kvs)>1) { $kvs .= $separator; }
		$kvs .= urlencode($key) . '=' . urlencode($value);
	}
	return $kvs;
}

/**
 * Tokenize a quoted string
 * 
 * @param string $str
 * @param int $min_token_length
 * 
 * @return array
 */
function tokenize_quoted_string ($str, $min_token_length = 0) {
	if (empty($str)) { return false; }
	
	$str = trim($str);
	$str_len = strlen($str);
	
	$tokens = array();
	$token_length = 0;
	$str_dummy = '';
	$c_end = ' ';
	$in_quote = false;

	for ($i=0;$i<$str_len;++$i) {
		if ( ($str[$i]=='"') && (!$in_quote) ) {	
			$c_end = '"';
			$in_quote = true; 
		} else {
			if ($str[$i]==$c_end) {
				if ($token_length>=$min_token_length) {
					$arr_tokens[] = trim($str_dummy);
					$str_dummy = '';
				}
				$token_length = 0;
				$in_quote = false;
				$c_end = ' ';
				$str_dummy = '';
			} else {
				$str_dummy .= $str[$i];
				++$token_length;
			}
  	}
  }

  if (!empty($str_dummy)) { $arr_tokens[] = $str_dummy; }
	return $arr_tokens;
}


/**
 * Return a string of the given length, randomly generated from the given valid chars
 * 
 * @param string $length
 * @param null $valid_chars
 * 
 * @return string
*/
function str_random ($length = 8, $valid_chars = null) { 
	if (is_null($valid_chars)) {
		$valid_chars = STR_ALPHANUM_CHARS; 
	}

	$str = '';
  while(strlen($str) < $length) { 
		$str .= substr($valid_chars, mt_rand(0, strlen($valid_chars) -1), 1); 
	}
	return $str; 
}


/**
 * Return $subject cut to the given number of characters-ish.  Cut happens at the next space character (' ')
 * 
 * @param string $subject
 * @param int $length
 * @param string $replace_str
 * 
 * @return string
*/
function str_wordcut ($subject, $length, $replace_str = '') {
	if ( strlen($subject)>$length ) {
		$pos = strpos($subject, ' ', $length);
		if ($pos) {	$subject = substr($subject, 0, $pos+1) . $replace_str;	}
	}
	return $subject;
}


/**
 * 	Sets any non-valid chars in a string to spaces ' '
 * 
 * @param string $str_var
 * @param null $valid_chars
 * 
 * @return string
*/
function limit_chars ($str_var, $valid_chars = null) {
	if (!$valid_chars) {
		$valid_chars = STR_ALPHANUM_CHARS . '`~!@#$%^&*()_+=-,.<>?/|;:\' "'.chr(92).chr(10);
	}
	$str_length = strlen($str_var);
	$str_length--;
	for($i=0; $i<=$str_length; ++$i) {
		if (strstr($valid_chars, $str_var[$i])==FALSE) {
			$str_var[$i] = ' ';
		}
	}
	return $str_var;
}

?>