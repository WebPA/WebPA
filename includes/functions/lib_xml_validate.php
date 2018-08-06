<?php
/**
 * XML validation functions
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

function Validate($xml, $xsd){
 libxml_use_internal_errors(true);

 $objDom = new DOMDocument('1.0', 'utf-8');

 $objDom->loadXML($xml);

 if (!$objDom->schemaValidate($xsd)) {

     $allErrors = libxml_get_errors();
  return false;
 } else {
    return true;
 }
}
?>
