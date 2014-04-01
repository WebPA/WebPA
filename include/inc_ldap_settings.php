<?php
/*
 * Created on May 24, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 define ("LDAP__HOST", "kdc.lboro.ac.uk");
 define ("LDAP__PORT", 3268);
 
 define("LDAP__USERNAME_EXT", "@lboro.ac.uk");
 
 define("LDAP__BASE_DN", "dc=lboro, dc=ac, dc=uk");
 define("LDAP__FILTER", "");
$LDAP__INFO_REQUIRED = array("displayname","mail","sn","homeDirectory","memberof");

?>
