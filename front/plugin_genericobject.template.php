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
define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

if (!isset($_SESSION["glpi_plugin_genericobject_device_type"]))
	$type = $_SESSION["glpi_plugin_genericobject_device_type"] = $_REQUEST["device_type"];
elseif (isset($_REQUEST["device_type"]))
	$type = $_SESSION["glpi_plugin_genericobject_device_type"] = $_REQUEST["device_type"];
else
	$type = $_SESSION["glpi_plugin_genericobject_device_type"];
	
$type = $_SESSION["glpi_plugin_genericobject_device_type"];

$name = plugin_genericobject_getNameByID($type);

commonHeader(plugin_genericobject_getObjectLabel($name), $_SERVER['PHP_SELF'], "plugins", "genericobject", $name);
plugin_genericobject_showTemplateByDeviceType($CFG_GLPI["root_doc"]."/".$INFOFORM_PAGES[$_GET["device_type"]],$_GET["device_type"],$_SESSION["glpiactive_entity"],$_GET["add"]);
commonFooter();
?>
