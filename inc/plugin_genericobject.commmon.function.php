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
 * Get the object class name, by giving the name
 * @param name the object's internal name
 * @return the classname associated with the object
 */
function plugin_genericobject_getObjectTypeByName($name) {
	return $classname = 'PluginGenericObject' . ucfirst($name);
}

/**
 * Get the object table name, by giving the identifier
 * @param name the object's internal identifier
 * @return the classname associated with the object
 */
function plugin_genericobject_getObjectTableNameByName($name) {
	return 'glpi_plugin_genericobject_' . $name;
}

/**
 * Get the object ID, by giving the name
 * @param name the object's internal identifier
 * @return the ID associated with the object
 */
function plugin_genericobject_getObjectIdentifierByName($name) {
	return 'PLUGIN_GENERICOBJECT_' . strtoupper($name) . '_TYPE';
}

/**
 * Get the object class, by giving the name
 * @param name the object's internal identifier
 * @return the class associated with the object
 */
function plugin_genericobject_getObjectClassByName($name) {
	return 'PluginGenericObject' . ucfirst($name);
}

/**
 * Get all types of active&published objects
 */
function plugin_genericobject_getAllTypes() {
	if (TableExists("glpi_plugin_genericobject_types"))
	{
		$where = "state=" . GENERICOBJECT_OBJECTTYPE_STATE_PUBLISHED .
		" AND status=" . GENERICOBJECT_OBJECTTYPE_STATUS_ACTIVE;
		return getAllDatasFromTable("glpi_plugin_genericobject_types", $where);
	}
	else
		return array();
}

/**
 * Get an internal ID by the object name
 * @param name the object's name
 * @return the object's ID
 */
function plugin_genericobject_getIDByName($name)
{
	global $DB;
	$query = "SELECT device_type FROM `glpi_plugin_genericobject_types` WHERE name='$name'";
	$result = $DB->query($query);
	if ($DB->numrows($result))
		return $DB->result($result,0,"device_type");
	else
		return 0;	
}

/**
 * Get object name by ID
 * @param ID the internal ID
 * @return the name associated with the ID
 */
function plugin_genericobject_getNameByID($device_type)
{
	global $DB;
	$query = "SELECT name FROM `glpi_plugin_genericobject_types` WHERE device_type='$device_type'";
	$result = $DB->query($query);
	if ($DB->numrows($result))
		return $DB->result($result,0,"name");
	else
		return "";	
}

/**
 * Get table name by ID
 * @param ID the object's ID
 * @return the table
 */
function plugin_genericobject_getTableNameByID($ID)
{
	global $LINK_ID_TABLE;
	if (isset($LINK_ID_TABLE[$ID]))
		return $LINK_ID_TABLE[$ID];
	else
		return false;	
}

/**
 * Get table name by name
 * @param ID the object's ID
 * @return the table
 */
function plugin_genericobject_getTableNameByName($name)
{
	global $LINK_ID_TABLE;
	return 'glpi_plugin_genericobject_'.$name;	
}

/**
 * Register all object's types and values
 * @return nothing
 */
function plugin_genericobject_registerNewTypes() {
	//Only look for published and active types

	foreach (plugin_genericobject_getAllTypes() as $ID => $type)
		plugin_genericobject_registerOneType($type);
}

/**
 * Register all variables for a type
 * @param type the type's attributes
 * @return nothing
 */
function plugin_genericobject_registerOneType($type) {
	global $LANG, $DB,$PLUGIN_HOOKS;
	$name = $type["name"];
	$typeID = $type["device_type"];

	$tablename = plugin_genericobject_getObjectTableNameByName($name);
	//If table doesn't exists, do not try to register !
	if (TableExists($tablename)) {
		$object_identifier = plugin_genericobject_getObjectIdentifierByName($name);

		$db_fields = $DB->list_fields($tablename);

		registerPluginType('genericobject', $object_identifier, $typeID, array (
			'classname' => plugin_genericobject_getObjectClassByName($name),
			'tablename' => $tablename,
			'formpage' => 'front/plugin_genericobject.object.form.php',
			'searchpage' => 'front/plugin_genericobject.search.php',
			'typename' => $LANG["genericobject"][$name][1],
			'deleted_tables' => ($type["use_deleted"] ? true : false),
			'template_tables' => ($type["use_template"] ? true : false),
			'specif_entities_tables' => ($type["use_entity"] ? true : false),
			'recursive_type' => ($type["use_recursivity"] ? true : false),
			'infocom_types' => ($type["use_infocoms"] ? true : false),
			'linkuser_types' => (($type["use_tickets"] && isset($db_fields["FK_users"]))? true : false),
			'linkgroup_types' => (($type["use_tickets"] && isset($db_fields["FK_groups"]))? true : false),
		));
	
        include_once (GLPI_ROOT . '/plugins/genericobject/objects/plugin_genericobject.'.$name.'.class.php');

		$PLUGIN_HOOKS['submenu_entry']['genericobject']['add'][$name] = 'front/plugin_genericobject.object.form.php?device_type='.$typeID;
		$PLUGIN_HOOKS['submenu_entry']['genericobject']['search'][$name] = 'front/plugin_genericobject.search.php?device_type='.$typeID;
	}
}

/**
 * Add search options for an object type
 * @param name the internal object name
 * @return an array with all search options
 */
function plugin_genericobject_objectSearchOptions($name, $search_options = array ()) {
	global $DB, $GENERICOBJECT_AVAILABLE_FIELDS, $LANG;
	
	$table = plugin_genericobject_getObjectTableNameByName($name);

	if (TableExists($table)) {
		$type = plugin_genericobject_getObjectIdentifierByName($name);
		$ID = plugin_genericobject_getIDByName($name);
		$fields = $DB->list_fields($table);
		$i = 1;
	
		if (!empty ($fields)) {
			$search_options[$ID]['common'] = $LANG['genericobject'][$name][1];
			foreach ($fields as $field_values) {
				$field_name = $field_values['Field'];
				if (isset ($GENERICOBJECT_AVAILABLE_FIELDS[$field_name])) {
					$search_options[$ID][$i]['linkfield'] = '';
					
					switch ($GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['input_type']) {
						case 'text':
							$search_options[$ID][$i]['table'] = plugin_genericobject_getObjectTableNameByName($name);
							break;
						case 'dropdown' :
							$search_options[$ID][$i]['table'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['table'];
							$search_options[$ID][$i]['linkfield'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['linkfield'];
							break;
						case 'dropdown_yesno' :
							$search_options[$ID][$i]['table'] = plugin_genericobject_getObjectTableNameByName($name);
							$search_options[$ID][$i]['linkfield'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['linkfield'];
							break;
					}
					$search_options[$ID][$i]['field'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['field'];
					$search_options[$ID][$i]['name'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['name'];
					if (isset($GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['datatype']))
						$search_options[$ID][$i]['datatype'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['datatype'];

					$i++;
				}
				
			}
		}

	}
	return $search_options;
}

function plugin_genericobject_getObjectTypeConfiguration($device_type)
{
	$objecttype = new PluginGenericObjectType;
	$objecttype->getFromDBByType($device_type);
	return $objecttype->fields;
}
?>
