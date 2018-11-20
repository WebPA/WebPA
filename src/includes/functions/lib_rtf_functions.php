<?php
/**
 *  RTF Functions
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */
// define constants for common RTF markup codes

define('RTF_LINE_TOP','{\pard \ql \li0\ri0\nowidctlpar\brdrt\brdrs\brdrw15\brsp20 \faauto\rin0\lin0\itap0 \par}');
define('RTF_LINE_BOTTOM','{\pard \ql \li0\ri0\nowidctlpar\brdrb\brdrs\brdrw15\brsp20 \faauto\rin0\lin0\itap0 \par}');


/*

RTF codes to use...

New Line		: 	\par

Bold				:		{\b  zzzzz zzzzz  }

Font Size		:		{\fs32  zzzzz zzzzz  }

*/


/**
 * Write headers for RTF MIME type and RTF download
 *
 * @param string $filename	File to download this page as
 */
function RTF_headers($filename = null) {
	if ($filename) {
		header("Content-Disposition: attachment; filename=\"$filename\"");
	}
	header('Content-Type: application/rtf');
}// /RTF_headers()


/**
 * The begining of the rtf page
*/
function RTF_start_page() {
	echo('{\rtf\ansi\deff0{\fonttbl{\f0\froman Arial;}}{\colortbl;\red0\green0\blue0;\red0\green0\blue255;\red0\green255\blue255;\red0\green255\blue0;\red255\green0\blue255;\red255\green0\blue0;\red255\green255\blue0;\red255\green255\blue255;}{\stylesheet{\fs20\snext0Normal;}}');
	echo('{\info{\version1}{\edmins0}{\vern8351}}\widoctrl\ftnbj \sectd\linex0\endnhere \pard\plain \fs20');
}// /RTF_start_page


/**
 * The end of the RTF page
*/
function RTF_end_page() {
	echo('}');
}// /RTF_end_page()

?>
