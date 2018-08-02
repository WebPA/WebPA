<?php
/**
 * Array Functions
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */



/*
 * Is the array associative (almost works perfectly?)
 *
 * @param array $arr  The array to be checked
 *
 * @returns Status
*/
function is_assoc($arr) {
   return is_array($arr) && array_keys($arr)!==range(0,sizeof($arr)-1);
}


/*
 * Unset an entire array (kill all contents)
 *
 * @param array $arr The array to be handled
 *
*/
function unset_array(&$arr) {
  while (array_pop($arr) != NULL);
}


/*
 * Implode an associative array..
 * First it implodes the key-values =>  key + $inner_glue + value
 * Then it implodes the array =>  key+$inner_glue+value + $outer_glue + key+$inner_glue+value ...
 *
 * @param ??    $inner_glue
 * @param ??    $outer_glue
 * @param Array   $array
 *
 * @returns Implode
*/
function implode_assoc($inner_glue, $outer_glue, $array) {
  $output = array();
  foreach( $array as $key => $item ) {
    $output[] = $key . $inner_glue . $item;
  }
  return implode($outer_glue, $output);
}

/**
 *
 * @param Array $arr
 * @returns Array $new_array
 */
function array_nonblank($arr) {

  if (!is_array($arr)) { return array(); }
  else {
    $arr_count = count($arr);
    $new_index = 0;
    $new_array = array();
    for($i=0; $i<$arr_count; ++$i) {
      if (!empty($arr[$i])) {
        $new_array[$new_index] = $arr[$i];
        ++$new_index;
      }
    }
    return $new_array;
  }
}

/**
 * Search for a value in the array
 *
 * @param string $needle description
 * @param string $haystack description
 * @param mixed $search_index description
 * @param mixed $return_index description
 *
 * @return ?? description
 */
function array_searchvalue($needle, $haystack, $search_index, $return_index = 0) {
  $arr_count = count($haystack);
  for ($i=0; $i<$arr_count; ++$i) {
    if ($haystack[$i][$search_index]==$needle) {
      return $haystack[$i][$return_index];
      break;
    }
  }
}


/**
 * Sort a 2D array
 *
 * @param Array $array
 * @param ?? $key
 *
 * @return Array Sorted array
 */

function array_sort_2D($array, $key) {
  for ($i = 0; $i < sizeof($array); $i++) {
       $sort_values[$i] = $array[$i][$key];
  }
  asort ($sort_values);
  reset ($sort_values);
  foreach ($sort_values as $arr_key => $arr_val) {
         $sorted_arr[] = $array[$arr_key];
  }
  return $sorted_arr;
}

/**
 * Extracts a column of values from a 2D array, and returns them a 1D array
 * WARNING : Does not check if the column exists!
 *
 * @param Array $array  Array to process
 * @param int | string  $column column to extract
 *
 * @return array 1D array of items matching
 */

function array_extract_column(&$array, $column) {
  $extracted_columns = null;
  if (is_array($array)) {
    foreach($array as $i => $row) {
      if (isset($row["$column"])) {
        $extracted_columns[] = $row["$column"];
      } else {
        $extracted_columns[] = $i;
      }
    }
    return $extracted_columns;
  } else {
    return null;
  }
}// /array_extract_column()

/**
 *  Get an associative array, keyed using the given index
 * WARNING : Does not check if the column exists!
 *
 * @param array $array Array to convert
 * @param mixed $key_index Index to use when creating Key
 *
 * @return array assoc array: array (key=>org_row, key=>org)
 * _row, ...
 */
function array_get_assoc($array, $key_index) {
  $assoc_array = null;

  if ( (is_array($array)) ) {
    foreach($array as $i => $array_row) {
      $assoc_array["{$array_row[$key_index]}"] = $array_row;
    }
  }
  return $assoc_array;
}// /array_get_assoc()

/**
 * Check to see if any of the elements in the array contain no data
 *
 * @param array $arr
 *
 * @return int number of element counts
 *
 */
function count_nonblank($arr) {
  if (!is_array($arr)) { return 0; }
  else {
    $arr_count = count($arr);
    $element_count = 0;
    for($i=0; $i<$arr_count; ++$i) {
      if (!empty($arr[$i])) { ++$element_count; }
    }
    return $element_count;
  }
}
?>
