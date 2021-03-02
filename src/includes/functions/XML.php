<?php
/**
 * XML validation functions
 *
 * @copyright Loughborough University
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL version 3
 *
 * @link https://github.com/webpa/webpa
 */

namespace WebPA\includes\functions;

use DOMDocument;

class XML
{
    public static function validate($xml, $xsd)
    {
        libxml_use_internal_errors(true);

        $objDom = new DOMDocument('1.0', 'utf-8');

        $objDom->loadXML($xml);

        if (!$objDom->schemaValidate($xsd)) {
            libxml_get_errors();
            return false;
        }
        return true;
    }
}
