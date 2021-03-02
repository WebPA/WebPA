<?php
/**
 * String Functions
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\includes\functions;

define('STR_ALPHA_CHARS', 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
define('STR_ALPHANUM_CHARS', '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
define('STR_UUID_CHARS', '0123456789ABCDEF-');

class StringFunctions
{
    /**
     * Return a string of the given length, randomly generated from the given valid chars
     *
     * @param string $length
     * @param null $valid_chars
     *
     * @return string
    */
    public static function str_random($length = 8, $valid_chars = null)
    {
        if (is_null($valid_chars)) {
            $valid_chars = STR_ALPHANUM_CHARS;
        }

        $str = '';
        while (strlen($str) < $length) {
            $str .= substr($valid_chars, mt_rand(0, strlen($valid_chars) -1), 1);
        }
        return $str;
    }
}
