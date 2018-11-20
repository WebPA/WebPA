<?php
/**
 * Class UI - Site user interface
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

/**
 * Get a list of files/folders in the given directory
 *
 * @param dir $dir
 *
 * @return array
 */
function dir_list($dir) {
  $dir_list = array();
 	if ($handle = opendir($dir)) {
    while ($filename = readdir($handle)) { if (preg_match('#^\.#',$filename)==0) $dir_list[] = $filename; }
 	  closedir($handle);
  }
 	asort($dir_list);
	return $dir_list;
}// /dir_list()

?>
