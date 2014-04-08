<?php
/**
 * 
 * XML validation functions
 * 
 * @copyright 2007 Loughborough University
 * @license http://www.gnu.org/licenses/gpl.txt
 * @version 0.0.0.2
 * @since 8 Jan 2008
 * 
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