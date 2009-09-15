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

// Original Author of file: Walid Nouh
// Purpose of file:
// ----------------------------------------------------------------------
$NEEDED_ITEMS = array("computer","ocsng","tracking","infocom");
define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

if (!isset ($_REQUEST["ID"])) {
	$_REQUEST["ID"] = '';
}
if (!isset ($_GET["withtemplate"])) {
	$_GET["withtemplate"] = '';
}

if (isset ($_REQUEST["device_type"])) {
	$type = $_SESSION["glpi_plugin_genericobject_device_type"] = $_REQUEST["device_type"];
}
elseif (!isset ($_SESSION["glpi_plugin_genericobject_device_type"])) {
   $_SESSION["glpi_plugin_genericobject_device_type"] = $_REQUEST["device_type"];
   $type = $_SESSION["glpi_plugin_genericobject_device_type"];	
}
else {
	$type = $_SESSION["glpi_plugin_genericobject_device_type"];
}

$name = plugin_genericobject_getNameByID($type);
$object = new CommonItem;
$object->setType($type, true);

//Manage direct connections
if (isset($_GET["disconnect"]) && isset($_GET["dID"]) && isset($_REQUEST["ID"])) {
   $object->obj->check($_GET["dID"],"w");
   Disconnect($_REQUEST["ID"]);
   glpi_header($_SERVER['HTTP_REFERER']);
}
else if(isset($_POST["connect"])&&isset($_POST["item"])&&$_POST["item"]>0) {
   $object->obj->check($_REQUEST["ID"],"w");
   Connect($_POST["sID"],$_POST["item"],$type);
   glpi_header($CFG_GLPI["root_doc"]."/plugins/genericobject/front/plugin_genericobject.object.form.php?ID=".$_POST["sID"]);
}	
//End manage direct connections

//Manage standard events
if (isset ($_POST["add"])) {
	$object->obj->add($_POST);
	glpi_header($_SERVER["HTTP_REFERER"]);
}
elseif (isset ($_POST["update"])) {
	$object->obj->update($_POST);
	glpi_header($_SERVER["HTTP_REFERER"]);
}
elseif (isset ($_POST["restore"])) {
	$object->obj->restore($_POST);
	glpi_header($_SERVER["HTTP_REFERER"]);
}
else if (isset($_REQUEST["purge"]))
{
      $input["ID"]=$_REQUEST["ID"];

   $object->obj->check($input['ID'],'w');

   $object->obj->delete($input,1);
   glpi_header($CFG_GLPI["root_doc"] . '/' . $SEARCH_PAGES[$type] . "?device_type=" . $type);
}
elseif (isset ($_POST["delete"])) {
	$object->obj->delete($_POST);
	glpi_header($CFG_GLPI["root_doc"] . '/' . $SEARCH_PAGES[$type] . "?device_type=" . $type);
}
//End manage standard events
elseif (isset ($_POST["add_type_link"])) {
	plugin_genericobject_addDeviceLink($type, $_POST["source_id"], $_POST["type"], $_POST["FK_device"]);
	glpi_header($_SERVER["HTTP_REFERER"]);
}
elseif (isset ($_POST["delete_type_link"])) {
	if (isset ($_POST["item"]))
		foreach ($_POST["item"] as $item => $value)
			if ($value == 1)
				plugin_genericobject_deleteDeviceLink($type, $item);
	glpi_header($_SERVER["HTTP_REFERER"]);
}

commonHeader(plugin_genericobject_getObjectLabel($name), $_SERVER['PHP_SELF'], "plugins", "genericobject", $name);
$object->obj->title($name);
$object->obj->showForm($_SERVER["PHP_SELF"], $_REQUEST["ID"], $_GET["withtemplate"]);

commonFooter();
?>
