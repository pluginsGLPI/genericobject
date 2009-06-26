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

if (!isset($_REQUEST["ID"]))
	$_REQUEST["ID"] = '';

$type = new PluginGenericObjectType;

if (isset($_POST["add"]))
{
	$type->add($_POST);
	glpi_header($_SERVER["HTTP_REFERER"]);	
}	
elseif (isset($_POST["update"]))
{
	$type->update($_POST);
	glpi_header($_SERVER["HTTP_REFERER"]);	
}
elseif (isset($_POST["delete"]))
{
	$type->delete($_POST);
	glpi_header($CFG_GLPI["root_doc"] . '/'.$SEARCH_PAGES[PLUGIN_GENERICOBJECT_TYPE]);	
}
elseif (isset($_POST["delete_field"]))
{
	$type->getFromDB($_POST["ID"]);
	plugin_genericobject_registerOneType($type->fields);
	plugin_genericobject_includeLocales($type->fields["name"]);

	foreach($_POST["fields"] as $field => $value)
		if ($value == 1)
		{
			$table = plugin_genericobject_getTableNameByID($type->fields["device_type"]);
			plugin_genericobject_deleteFieldFromDB($table,$field,$type->fields["name"]);
			addMessageAfterRedirect($LANG['genericobject']['fields'][5],true);
		}
		glpi_header($_SERVER['HTTP_REFERER']);
}
elseif (isset($_POST["add_field"]))
{
	if ($_POST["new_field"])
	{
		$type->getFromDB($_POST["ID"]);
		plugin_genericobject_registerOneType($type->fields);
		plugin_genericobject_includeLocales($type->fields["name"]);

		$table = plugin_genericobject_getTableNameByID($type->fields["device_type"]);
		plugin_genericobject_addFieldInDB($table,$_POST["new_field"],$type->fields["name"]);
		addMessageAfterRedirect($LANG['genericobject']['fields'][6]);
	}
	glpi_header($_SERVER['HTTP_REFERER']);
}

commonHeader($LANG['genericobject']['title'][1],$_SERVER['PHP_SELF'],"plugins","genericobject","type");
$type->showForm($_SERVER["PHP_SELF"],$_REQUEST["ID"]);

commonFooter();
?>
