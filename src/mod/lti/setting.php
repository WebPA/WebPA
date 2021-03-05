<?php
/*
 *  webpa-lti - WebPA module to add LTI support
 *  Copyright (C) 2020  Stephen P Vickers
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along
 *  with this program; if not, write to the Free Software Foundation, Inc.,
 *  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 *  Contact: stephen@spvsoftwareproducts.com
 */

use ceLTIc\LTI\Util;
use ceLTIc\LTI\ApiHook\ApiHook;
use ceLTIc\LTI\Tool;
use ceLTIc\LTI\ResourceLink;

require_once('tool.php');

###
###  Define constants for options
###

define('LTI_MODULE_NAME', 'lti');
define('ALLOW_SHARING', true);
define('SHARE_KEY_LENGTH', 10);
define('DEFAULT_EMAIL', '');

###
###  Set LTI 1.3 parameters
###

define('LTI_SIGNATURE_METHOD', 'RS256');
define('LTI_KID', '');  // A random string to identify the public key
define('LTI_PRIVATE_KEY', <<< EOD
-----BEGIN RSA PRIVATE KEY-----
-----END RSA PRIVATE KEY-----
EOD
);

###
###  Registration settings
###
define('AUTO_ENABLE', false);
define('ENABLE_FOR_DAYS', 0);

###
###  Set the default tool
###

Tool::$defaultTool = new WebPA_Tool(null);

###
###  Set API handlers for services
###

Util::$logLevel = Util::LOGLEVEL_DEBUG;

Tool::registerApiHook(ApiHook::$USER_ID_HOOK, 'canvas', 'ceLTIc\LTI\ApiHook\canvas\CanvasApiTool');
ResourceLink::registerApiHook(ApiHook::$MEMBERSHIPS_SERVICE_HOOK, 'canvas', 'ceLTIc\LTI\ApiHook\canvas\CanvasApiResourceLink');
ResourceLink::registerApiHook(ApiHook::$MEMBERSHIPS_SERVICE_HOOK, 'moodle', 'ceLTIc\LTI\ApiHook\moodle\MoodleApiResourceLink');
?>