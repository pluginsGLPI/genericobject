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
function plugin_genericobject_getFieldsByType($type)
{
	global $DB;
	$query = "SELECT * FROM `glpi_plugin_genericobject_type_fields` WHERE device_type=$type ORDER BY rank ASC";
	$result = $DB->query($query);
	$fields = array();
	
	while ($datas = $DB->fetch_array($result))
	{
		$tmp = new PluginGenericObjectField;
		$tmp->fields = $datas;
		$fields[$datas["name"]] = $tmp;
	}
	return $fields;
}

function plugin_genericobject_getNextRanking($type)
{
	global $DB;
	$query = "SELECT MAX(rank) as cpt FROM `glpi_plugin_genericobject_type_fields` WHERE device_type='$type'";
	$result = $DB->query($query);
	if ($DB->result($result,0,"cpt") != null)
		return $DB->result($result,0,"cpt") + 1;
	else
		return 0;	
}

function plugin_genericobject_addNewField($device_type,$name)
{
	$type_field = new PluginGenericObjectField;
		$input["name"] = $name;
		$input["device_type"] = $device_type;
		$input["rank"] = plugin_genericobject_getNextRanking($device_type);
		$input["mandatory"] = 0;
		$input["unique"] = 0;
		$input["entity_restrict"] = 0;
		$type_field->add($input);
}

function plugin_genericobject_deleteAllFieldsByType($device_type)
{
	global $DB;
	$query = "DELETE FROM `glpi_plugin_genericobject_type_fields` WHERE device_type=$device_type";
	$DB->query($query);
}
?>