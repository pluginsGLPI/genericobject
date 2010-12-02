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

/**
 * Get all fields for an object type
 * @itemtype the object type
 * @return an array with all the fields for this type
 */
function plugin_genericobject_getFieldsByType($itemtype)
{
	global $DB;
	
	$itemtype = strtolower(str_replace("PluginGenericobject", "", $itemtype));
	
	$query = "SELECT * FROM `glpi_plugin_genericobject_type_fields` WHERE itemtype='$itemtype' ORDER BY rank ASC";
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

/**
 * Get next available field display ranking for a type
 * @type the itemtype
 * @return the next available ranking
 */
function plugin_genericobject_getNextRanking($itemtype)
{
	global $DB;
	$query = "SELECT MAX(rank) as cpt FROM `glpi_plugin_genericobject_type_fields` WHERE itemtype='$itemtype'";
	$result = $DB->query($query);
	if ($DB->result($result,0,"cpt") != null)
		return $DB->result($result,0,"cpt") + 1;
	else
		return 0;	
}

/**
 * Add a new field for an object (into object's device table)
 * @itemtype the object type
 */
function plugin_genericobject_addNewField($itemtype,$name)
{
	if (!plugin_genericobject_fieldExists($itemtype,$name)) {
      $type_field = new PluginGenericObjectField;
         $input["name"] = $name;
         $input["itemtype"] = $itemtype;
         $input["rank"] = plugin_genericobject_getNextRanking($itemtype);
         $input["mandatory"] = 0;
         $input["unique"] = 0;
         $input["entity_restrict"] = 0;
         $type_field->add($input);
	}
	else exit("plugin_genericobject_addNewField".$itemtype);
}

function plugin_genericobject_fieldExists($itemtype,$name) {
	global $DB;
   $query = "SELECT id FROM `glpi_plugin_genericobject_type_fields` " .
         "WHERE `itemtype`='$itemtype' AND `name`='$name'";
   $result = $DB->query($query);
   if (!$DB->numrows($result)) {
   	return false;
   }
   else {
   	return true;
   }
}
function plugin_genericobject_deleteAllFieldsByType($itemtype)
{
	global $DB;
	$query = "DELETE FROM `glpi_plugin_genericobject_type_fields` WHERE itemtype='$itemtype'";
	$DB->query($query);
}

function plugin_genericobject_setMandatoryField($itemtype,$field)
{
	
}

function plugin_genericobject_checkNecessaryFieldsDelete($itemtype,$field) {
   $type = new PluginGenericObjectType;
   $type->getFromDBByType($itemtype);
   
   if ($type->fields['use_network_ports'] && 'locations_id'==$field) {
      return false;
   }
   
   if ($type->fields['use_direct_connections']) {
      foreach(array('users_id','groups_id','	states_id','locations_id') as $tmp_field) {
         if ($tmp_field==$field) {
            return false;
         }
      } 
   }
   return true;
}

?>
