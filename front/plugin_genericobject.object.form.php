<?php
/*
 ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2008 by the INDEPNET Development Team.
 
 http://indepnet.net/   http://glpi-project.org/
 ----------------------------------------------------------------------

 LICENSE

	This file is part of GLPI.

    GLPI is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    GLPI is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with GLPI; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 ------------------------------------------------------------------------
*/

// Original Author of file: BALPE Dévi
// Purpose of file:
// ----------------------------------------------------------------------
define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

if (isset($_REQUEST['device_type']))
	$_SESSION["plugin_genericobject_device_type"] = $_REQUEST['device_type'];

if (!isset($_REQUEST["ID"]))
	$_REQUEST["ID"] = '';

$name = plugin_genericobject_getNameByID($_SESSION["plugin_genericobject_device_type"]);
$object = new CommonItem;
$object->setType($_SESSION["plugin_genericobject_device_type"],true);

if (isset($_POST["add"]))
{
	$object->obj->add($_POST);
	glpi_header($_SERVER["HTTP_REFERER"]);	
}	
elseif (isset($_POST["update"]))
{
	$object->obj->update($_POST);
	glpi_header($_SERVER["HTTP_REFERER"]);	
}
elseif (isset($_POST["restore"]))
{
	$object->obj->restore($_POST);
	glpi_header($_SERVER["HTTP_REFERER"]);	
}
elseif (isset($_POST["delete"]))
{
	$object->obj->delete($_POST);
	glpi_header($CFG_GLPI["root_doc"] . '/'.$SEARCH_PAGES[$_SESSION["plugin_genericobject_device_type"]]."?device_type=".$_SESSION["plugin_genericobject_device_type"]);	
}
	


commonHeader($LANG["genericobject"][$name][1],$_SERVER['PHP_SELF'],"plugins","genericobject",$name);
$object->obj->showForm($_SERVER["PHP_SELF"],$_REQUEST["ID"]);

commonFooter();
?>
