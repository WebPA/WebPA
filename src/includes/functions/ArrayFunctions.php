<?php
/**
 * Array Functions
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\includes\functions;

class ArrayFunctions
{
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
    public static function array_searchvalue($needle, $haystack, $search_index, $return_index = 0)
    {
        $arr_count = count($haystack);
        for ($i=0; $i<$arr_count; ++$i) {
            if ($haystack[$i][$search_index]==$needle) {
                return $haystack[$i][$return_index];
                break;
            }
        }
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
    public static function array_extract_column(&$array, $column)
    {
        $extracted_columns = null;
        if (is_array($array)) {
            foreach ($array as $i => $row) {
                if (isset($row["$column"])) {
                    $extracted_columns[] = $row["$column"];
                } else {
                    $extracted_columns[] = $i;
                }
            }
            return $extracted_columns;
        }
        return null;
    }

    // /array_extract_column()

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
    public static function array_get_assoc($array, $key_index)
    {
        $assoc_array = null;

        if ((is_array($array))) {
            foreach ($array as $i => $array_row) {
                $assoc_array["{$array_row[$key_index]}"] = $array_row;
            }
        }
        return $assoc_array;
    }

    // /array_get_assoc()
}
