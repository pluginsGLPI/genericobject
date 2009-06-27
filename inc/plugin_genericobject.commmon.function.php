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
function plugin_genericobject_getAllTypes($all=false) {
	if (TableExists("glpi_plugin_genericobject_types"))
	{
		if (!$all)
			$where = " status=" . GENERICOBJECT_OBJECTTYPE_STATUS_ACTIVE;
		else
			$where='';
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
	global $LANG, $DB,$PLUGIN_HOOKS,$CFG_GLPI;
	$name = $type["name"];
	$typeID = $type["device_type"];

	$tablename = plugin_genericobject_getObjectTableNameByName($name);
	//If table doesn't exists, do not try to register !
	if (TableExists($tablename) && !defined($typeID)) {
		$object_identifier = plugin_genericobject_getObjectIdentifierByName($name);

		$db_fields = $DB->list_fields($tablename);
		//Include locales
        plugin_genericobject_includeLocales($name);
		plugin_genericobject_includeClass($name);

		registerPluginType('genericobject', $object_identifier, $typeID, array (
			'classname' => plugin_genericobject_getObjectClassByName($name),
			'tablename' => $tablename,
			'formpage' => 'front/plugin_genericobject.object.form.php',
			'searchpage' => 'front/plugin_genericobject.search.php',
			'typename' => (isset($LANG["genericobject"][$name][1])?$LANG["genericobject"][$name][1]:$name),
			'deleted_tables' => ($type["use_deleted"] ? true : false),
			'template_tables' => ($type["use_template"] ? true : false),
			'specif_entities_tables' => ($type["use_entity"] ? true : false),
			'recursive_type' => ($type["use_recursivity"] ? true : false),
			'infocom_types' => ($type["use_infocoms"] ? true : false),
			'linkuser_types' => (($type["use_tickets"] && isset($db_fields["FK_users"]))? true : false),
			'linkgroup_types' => (($type["use_tickets"] && isset($db_fields["FK_groups"]))? true : false),
		));
	
		if ($type["use_template"])
			$PLUGIN_HOOKS['submenu_entry']['genericobject']['template'][$name]='front/plugin_genericobject.object.form.php?device_type='.$typeID.'&amp;add=0';

		$PLUGIN_HOOKS['submenu_entry']['genericobject']['add'][$name] = 'front/plugin_genericobject.object.form.php?device_type='.$typeID;
		$PLUGIN_HOOKS['submenu_entry']['genericobject']['search'][$name] = 'front/plugin_genericobject.search.php?device_type='.$typeID;
	
		/* Later, when per entity and tree dropdowns will be managed !
		foreach(plugin_genericobject_getSpecificDropdownsTablesByType($typeID) as $table => $name)
		{
			array_push($CFG_GLPI["specif_entities_tables"], $table);
			array_push($CFG_GLPI["dropdowntree_tables"], $table);
		}
		*/
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
			$search_options[$ID]['common'] = plugin_genericobject_getObjectName($name);
			foreach ($fields as $field_values) {
				$field_name = $field_values['Field'];
				if (isset ($GENERICOBJECT_AVAILABLE_FIELDS[$field_name])) {
					$search_options[$ID][$i]['linkfield'] = '';
					
					switch ($GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['input_type']) {
						case 'text':
							$search_options[$ID][$i]['table'] = plugin_genericobject_getObjectTableNameByName($name);
							break;
						case 'dropdown' :
							if (plugin_genericobject_isDropdownTypeSpecific($field_name))
				 				$search_options[$ID][$i]['table'] = plugin_genericobject_getDropdownTableName($name,$field_name);
				 			else	
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

/**
 * Get an object type configuration by device_type
 * @param device_type the object device type
 * @return an array which contains all the type's configuration
 */
function plugin_genericobject_getObjectTypeConfiguration($device_type)
{
	$objecttype = new PluginGenericObjectType;
	$objecttype->getFromDBByType($device_type);
	return $objecttype->fields;
}

function plugin_genericobject_addObjectTypeDirectory($name)
{
	
}
/**
 * Include locales for a specific type
 * @name object type's name
 * @return nothing
 */
function plugin_genericobject_includeLocales($name) {
	global $CFG_GLPI, $LANG;
	
	$prefix = GLPI_ROOT . "/plugins/genericobject/objects/". $name ."/" . $name;
	syslog(LOG_ERR,$prefix); 
	if (isset ($_SESSION["glpilanguage"]) 
		&& file_exists($prefix . "." . $CFG_GLPI["languages"][$_SESSION["glpilanguage"]][1])) {
		include_once  ($prefix . "." . $CFG_GLPI["languages"][$_SESSION["glpilanguage"]][1]);

	} else if (file_exists($prefix . ".en_GB.php")) {
			include_once  ($prefix . ".en_GB.php");

	} else if (file_exists($prefix . ".fr_FR.php")) {
			include_once  ($prefix . ".fr_FR.php");

	} else {
		logInFile('php-errors', "includeLocales($name) => not found\n");
		return false;
	}
	return true;
}

/**
 * Include object type class
 * @name object type's name
 * @return nothing
 */
function plugin_genericobject_includeClass($name) {
	//If class comes directly with the plugin
	if (file_exists(GLPI_ROOT."/plugins/genericobject/objects/$name/plugin_genericobject.$name.class.php"))
	{
		include_once(GLPI_ROOT."/plugins/genericobject/objects/$name/plugin_genericobject.$name.class.php");
	}
		
	else	
	{
		include_once (GENERICOBJECT_CLASS_PATH.'/plugin_genericobject.'.$name.'.class.php');
	}
		
		
}

function plugin_genericobject_getObjectName($name)
{
	global $LANG;
	if (isset($LANG['genericobject'][$name][1]))
		return $LANG['genericobject'][$name][1];
	else
		return $name;	
}
?>
