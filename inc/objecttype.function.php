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
 * Get next available device typ
 * @return the next available device type 
 */
function plugin_genericobject_getNextDeviceType() {
	global $DB;
	$query = "SELECT MAX(itemtype) as cpt FROM `glpi_plugin_genericobject_types`";
	$result = $DB->query($query);
	if (!$DB->result($result, 0, "cpt"))
		$cpt = 4090;
	else
		$cpt = $DB->result($result, 0, "cpt") + 1;
	return $cpt;
}

/**
 * Write on the the class file for the new object type
 * @param name the name of the object type
 * @param classname the name of the new object
 * @param itemtype the object device type
 * @return nothing
 */
function plugin_genericobject_addClassFile($name, $classname, $itemtype) {
	$DBf_handle = fopen(GENERICOBJECT_CLASS_TEMPLATE, "rt");
	$template_file = fread($DBf_handle, filesize(GENERICOBJECT_CLASS_TEMPLATE));
	fclose($DBf_handle);
	$template_file = str_replace("%%CLASSNAME%%", $classname, $template_file);
	$template_file = str_replace("%%DEVICETYPE%%", $itemtype, $template_file);
	$DBf_handle = fopen(GENERICOBJECT_CLASS_PATH . "/$name.class.php", "w");
	fwrite($DBf_handle, $template_file);
	fclose($DBf_handle);
}

/**
 * Delete an used class file
 * @param name the name of the object type
 * @return nothing
 */
function plugin_genericobject_deleteClassFile($name) {
	if (file_exists(GENERICOBJECT_CLASS_PATH . "/$name.class.php"))
		unlink(GENERICOBJECT_CLASS_PATH .
		"/$name.class.php");
}

function plugin_genericobject_showObjectFieldsForm($target, $ID) {
	global $LANG, $DB, $GENERICOBJECT_BLACKLISTED_FIELDS, $GENERICOBJECT_AVAILABLE_FIELDS, $CFG_GLPI, $GENERICOBJECT_AUTOMATICALLY_MANAGED_FIELDS;

	$object_type = new PluginGenericObjectType;
	$object_type->getFromDB($ID);
	
	$object_table = plugin_genericobject_getTableNameByID($object_type->fields["itemtype"]);
	$fields_in_db = plugin_genericobject_getFieldsByType($object_type->fields["itemtype"]);

	foreach ($GENERICOBJECT_AUTOMATICALLY_MANAGED_FIELDS as $autofield)
		$used_fields[$autofield] = $autofield;

	foreach ($GENERICOBJECT_BLACKLISTED_FIELDS as $autofield)
		if (!in_array($autofield,$used_fields))
			$used_fields[$autofield] = $autofield;


	echo "<form name='form_fields' method='post' action=\"$target\">";
	echo "<div class='center'>";
	echo "<table class='tab_cadre_fixe' >";
	echo "<input type='hidden' name='id' value='$ID'>";
	echo "<tr class='tab_bg_1'><th colspan='7'>";
	echo $LANG['genericobject']['fields'][1] . " : " . plugin_genericobject_getObjectLabel($object_type->fields["name"]);
	echo "</th></tr>";

	echo "<tr class='tab_bg_1'>";
	echo "<th width='10'></th>";
	echo "<th>" . $LANG['genericobject']['fields'][2] . "</th>";
	echo "<th>" . $LANG['genericobject']['fields'][3] . "</th>";
	echo "<th width='10'>" . $LANG['genericobject']['fields'][7] . "</th>";
	echo "<th width='10'>" . $LANG['genericobject']['fields'][8] . "</th>";
	echo "<th width='10'></th>";
	echo "<th width='10'></th>";
	echo "</tr>";

	$index = 1;
	$total = count($fields_in_db);

	foreach ($fields_in_db as $type => $value) {
		plugin_genericobject_displayFieldDefinition($target, $ID, $value->getName(), $index, $total);
		$used_fields[$value->getName()] = $value->getName();
		$index++;
	}
	echo "<tr><td><img src=\"" . $CFG_GLPI["root_doc"] . "/pics/arrow-left.png\" alt=''></td><td class='center'><a onclick= \"if ( markCheckboxes('form_fields') ) return false;\" href='" . $target . "?id=$ID&amp;select=all'>" . $LANG['buttons'][18] . "</a>";
	echo "&nbsp;/&nbsp;<a onclick= \"if ( unMarkCheckboxes('form_fields') ) return false;\" href='" . $target . "?id=$ID&amp;select=none'>" . $LANG['buttons'][19] . "</a>";
	echo "</td><td colspan='5' align='left' width='75%'>";

	echo "<select name=\"massiveaction\" id='massiveaction'>";
	echo "<option value=\"-1\" selected>-----</option>";
	echo "<option value=\"delete\">" . $LANG['buttons'][6] . "</option>";
	//echo "<option value=\"move_field\">" . $LANG['buttons'][20] . "</option>";
	echo "</select>";

	$params = array (
		'action' => '__VALUE__',
		'itemtype' => $object_type->fields["itemtype"],		
	);

	ajaxUpdateItemOnSelectEvent("massiveaction", "show_massiveaction", $CFG_GLPI["root_doc"] . "/plugins/genericobject/ajax/plugin_genericobject_dropdownObjectTypeFields.php", $params);

	echo "<span id='show_massiveaction'>&nbsp;</span>\n";

	echo "</td></tr>";

	echo "</table>";
	echo "<br>";

	echo "<table class='tab_cadre'>";
	echo "<tr class='tab_bg_1'>";
	echo "<td>" . $LANG['genericobject']['fields'][4] . "</td>";
	echo "<td align='left'>";
	plugin_genericobject_dropdownFields("new_field", $used_fields);
	echo "</td>";
	echo "<td>";
	echo "<input type='submit' name='add_field' value=\"" . $LANG['buttons'][8] . "\" class='submit'>";
	echo "</tr>";
	echo "</table></div></form>";
}

function plugin_genericobject_deleteFieldFromDB($table, $field, $name) {
	global $DB;
	if (FieldExists($table, $field)) {
		$DB->query("ALTER TABLE `$table` DROP `$field`;");
		if (plugin_genericobject_isDropdownTypeSpecific($field))
			plugin_genericobject_deleteDropdownTable($name, $field);
			plugin_genericobject_deleteDropdownClassFile($name, $field);
			plugin_genericobject_deleteDropdownFrontFile($name, $field);
			plugin_genericobject_deleteDropdownFrontformFile($name, $field);
			plugin_genericobject_deleteDropdownAjaxFile($name, $field);
	}

}

function plugin_genericobject_addFieldInDB($table, $field, $name) {
	global $DB, $GENERICOBJECT_AVAILABLE_FIELDS;
	$query = "ALTER TABLE `$table` ADD `$field` ";
	if (!FieldExists($table, $field)) {
		
		switch ($GENERICOBJECT_AVAILABLE_FIELDS[$field]['input_type']) {
			case 'dropdown_yesno' :
         case 'dropdown_global' :
         case 'bool' :
				$query .= "INT ( 1 ) NOT NULL DEFAULT 0";
				break;
			case 'text' :
				$query .= "VARCHAR ( 255 )  collate utf8_unicode_ci NOT NULL DEFAULT ''";
				break;
			case 'multitext' :
				$query .= "TEXT NULL";
				break;
			case 'dropdown' :
				$query .= "INT ( 11 ) NOT NULL DEFAULT 0";
				if (plugin_genericobject_isDropdownTypeSpecific($field)) {
					plugin_genericobject_addDropdownTable($name, $field);
					plugin_genericobject_addDropdownClassFile($name, $field);
					plugin_genericobject_addDropdownFrontFile($name, $field);
					plugin_genericobject_addDropdownFrontformFile($name, $field);
					plugin_genericobject_addDropdownAjaxFile($name, $field);
				}
				break;
			case 'integer' :
				$query .= "INT ( 11 ) NOT NULL DEFAULT 0";
				break;
			case 'date':
				$query.="date default NULL";	
		}
		$DB->query($query);
	}
}

function plugin_genericobject_addDropdownClassFile($name, $field) {
	$tablename = plugin_genericobject_getDropdownTableName($name, $field);
	$classname = "PluginGenericobject".ucfirst($name).ucfirst($field);
	
	if (TableExists($tablename)) {
		
		$DBf_handle = fopen(GENERICOBJECT_CLASS_DROPDOWN_TEMPLATE, "rt");
		$template_file = fread($DBf_handle, filesize(GENERICOBJECT_CLASS_DROPDOWN_TEMPLATE));
		fclose($DBf_handle);
		$template_file = str_replace("%%CLASSNAME%%", $classname, $template_file);
		//$template_file = str_replace("%%DEVICETYPE%%", $itemtype, $template_file);
		$DBf_handle = fopen(GENERICOBJECT_CLASS_PATH . "/".$name.$field.".class.php", "w");
		fwrite($DBf_handle, $template_file);
		fclose($DBf_handle);
	}
} 

function plugin_genericobject_addDropdownFrontformFile($name, $field) {
	$classname = "PluginGenericobject".ucfirst($name).ucfirst($field);
	
	$DBf_handle = fopen(GENERICOBJECT_FRONTFORM_DROPDOWN_TEMPLATE, "rt");
	$template_file = fread($DBf_handle, filesize(GENERICOBJECT_FRONTFORM_DROPDOWN_TEMPLATE));
	fclose($DBf_handle);
	$template_file = str_replace("%%OBJECT%%", $classname, $template_file);
	$DBf_handle = fopen(GENERICOBJECT_FRONT_PATH . "/".$name.$field.".form.php", "w");
	fwrite($DBf_handle, $template_file);
	fclose($DBf_handle);
}

function plugin_genericobject_addDropdownFrontFile($name, $field) {
	$classname = "PluginGenericobject".ucfirst($name).ucfirst($field);
	
	$DBf_handle = fopen(GENERICOBJECT_FRONT_DROPDOWN_TEMPLATE, "rt");
	$template_file = fread($DBf_handle, filesize(GENERICOBJECT_FRONT_DROPDOWN_TEMPLATE));
	fclose($DBf_handle);
	$template_file = str_replace("%%OBJECT%%", $classname, $template_file);
	$DBf_handle = fopen(GENERICOBJECT_FRONT_PATH . "/".$name.$field.".php", "w");
	fwrite($DBf_handle, $template_file);
	fclose($DBf_handle);
}

function plugin_genericobject_addDropdownAjaxFile($name, $field) {
	$classname = "PluginGenericobject".ucfirst($name).ucfirst($field);
	
	$DBf_handle = fopen(GENERICOBJECT_AJAX_DROPDOWN_TEMPLATE, "rt");
	$template_file = fread($DBf_handle, filesize(GENERICOBJECT_AJAX_DROPDOWN_TEMPLATE));
	fclose($DBf_handle);
	$template_file = str_replace("%%OBJECT%%", $classname, $template_file);
	$DBf_handle = fopen(GENERICOBJECT_AJAX_PATH . "/".$name.$field.".tabs.php", "w");
	fwrite($DBf_handle, $template_file);
	fclose($DBf_handle);
}

function plugin_genericobject_addDropdownTable($name, $field) {
	global $DB;
	if (!TableExists(plugin_genericobject_getDropdownTableName($name, $field))) {
		if (!plugin_genericobject_isDropdownEntityRestrict($field)) {
			$query = "CREATE TABLE `" . plugin_genericobject_getDropdownTableName($name, $field) . "` (
							  `id` int(11) NOT NULL auto_increment,
							  `name` varchar(255) collate utf8_unicode_ci default NULL,
							  `comment` text collate utf8_unicode_ci,
							  PRIMARY KEY  (`id`),
							  KEY `name` (`name`)
							) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		} else {
			$query = "CREATE TABLE IF NOT EXISTS `" . plugin_genericobject_getDropdownTableName($name, $field) . "` (
						  `id` int(11) NOT NULL auto_increment,
						  `entities_id` int(11) NOT NULL default '0',
						  `name` varchar(255) collate utf8_unicode_ci default NULL,
						  `parentID` int(11) NOT NULL default '0',
						  `completename` text collate utf8_unicode_ci,
						  `comment` text collate utf8_unicode_ci,
						  `level` int(11) NOT NULL default '0',
						  PRIMARY KEY  (`id`),
						  UNIQUE KEY `name` (`name`,`parentID`,`entities_id`),
						  KEY `parentID` (`parentID`),
						  KEY `entities_id` (`entities_id`)
						) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		}
		$DB->query($query);
	}
}

function plugin_genericobject_deleteDropdownClassFile($name, $field) {
	if (file_exists(GENERICOBJECT_CLASS_PATH . "/".$name.$field.".class.php"))
		unlink(GENERICOBJECT_CLASS_PATH .
		"/".$name.$field.".class.php");
}

function plugin_genericobject_deleteDropdownFrontformFile($name, $field) {
	if (file_exists(GENERICOBJECT_FRONT_PATH . "/".$name.$field.".form.php"))
		unlink(GENERICOBJECT_FRONT_PATH .
		"/".$name.$field.".form.php");
}

function plugin_genericobject_deleteDropdownFrontFile($name, $field) {
	if (file_exists(GENERICOBJECT_FRONT_PATH . "/".$name.$field.".form.php"))
		unlink(GENERICOBJECT_FRONT_PATH .
		"/".$name.$field.".php");
}

function plugin_genericobject_deleteDropdownAjaxFile($name, $field) {
	if (file_exists(GENERICOBJECT_AJAX_PATH . "/".$name.$field.".tabs.php"))
		unlink(GENERICOBJECT_AJAX_PATH .
		"/".$name.$field.".tabs.php");
}

function plugin_genericobject_deleteSpecificDropdownFiles($itemtype)
{
	global $DB;
	$name = plugin_genericobject_getNameByID($itemtype);
	$types = plugin_genericobject_getDropdownSpecificFields();

	foreach($types as $type => $tmp)	{
		plugin_genericobject_deleteDropdownAjaxFile($name, $type);
		plugin_genericobject_deleteDropdownFrontFile($name, $type);
		plugin_genericobject_deleteDropdownFrontformFile($name, $type);
		plugin_genericobject_deleteDropdownClassFile($name, $type);
	}
		
}

function plugin_genericobject_deleteDropdownTable($name, $field) {
	global $DB;
	if (TableExists(plugin_genericobject_getDropdownTableName($name, $field)))
		$DB->query("DROP TABLE `" .
		plugin_genericobject_getDropdownTableName($name, $field) . "`");
}

/**
 * Add object type table + entries in glpi_display
 * @name object type's name
 * @return nothing
 */
function plugin_genericobject_addTable($name) {
	global $DB;
	$query = "CREATE TABLE `glpi_plugin_genericobject_$name` (
					`id` INT( 11 ) NOT NULL AUTO_INCREMENT,
			 		`name` VARCHAR( 255 ) NOT NULL ,
					`entities_id` INT( 11 ) NOT NULL DEFAULT 0,
					`object_type` INT( 11 ) NOT NULL DEFAULT 0,
					`deleted` INT( 1 ) NOT NULL DEFAULT 0,
			 		`recursive` INT ( 1 ) NOT NULL DEFAULT 0,
               `is_template` INT ( 1 ) NOT NULL DEFAULT 0,
			 		`comments` TEXT NULL  ,
			 		`notepad` TEXT NULL  ,
			 		PRIMARY KEY ( `id` ) 
					) ENGINE = MYISAM COMMENT = '$name table';";
	$DB->query($query);

	$query = "INSERT INTO `glpi_display` (`id`, `itemtype`, `num`, `rank`, `users_id`) VALUES
					(NULL, " . plugin_genericobject_getIDByName($name) . ", 2, 1, 0);";
	$DB->query($query);

}

/**
 * Delete object type table + entries in glpi_display
 * @name object type's name
 * @return nothing
 */
function plugin_genericobject_deleteTable($name) {
	global $DB;
	$type = plugin_genericobject_getIDByName($name);
	$DB->query("DELETE FROM `glpi_display` WHERE itemtype='$type'");
	$DB->query("DROP TABLE IF EXISTS `glpi_plugin_genericobject_$name`");
}

function plugin_genericobject_getDropdownTableName($name, $field) {
	$table_name = "glpi_plugin_genericobject_" . $name . $field;
	return getPlural($table_name);
}

function plugin_genericobject_getLinkDeviceTableName($name)
{
	return "glpi_plugin_genericobject_".$name."_device";
}

function plugin_genericobject_isDropdownTypeSpecific($field) {
	global $GENERICOBJECT_AVAILABLE_FIELDS;
	return (isset ($GENERICOBJECT_AVAILABLE_FIELDS[$field]['dropdown_type']) && $GENERICOBJECT_AVAILABLE_FIELDS[$field]['dropdown_type'] == 'type_specific');
}

function plugin_genericobject_isDropdownEntityRestrict($field) {
	global $GENERICOBJECT_AVAILABLE_FIELDS;
	return (isset ($GENERICOBJECT_AVAILABLE_FIELDS[$field]['entity']) && $GENERICOBJECT_AVAILABLE_FIELDS[$field]['entity'] == 'entity_restrict');
}

function plugin_genericobject_enableTemplateManagement($name) {
	global $DB;
	$table = plugin_genericobject_getTableNameByName($name);
	if (!FieldExists($table, "is_template")) {
		$query = "ALTER TABLE `$table` ADD `is_template` INT ( 1 ) NOT NULL DEFAULT 0";
		$DB->query($query);
	}

	if (!FieldExists($table, "tplname")) {
		$query = "ALTER TABLE `$table` ADD `tplname` VARCHAR ( 255 )  collate utf8_unicode_ci NOT NULL DEFAULT ''";
		$DB->query($query);
	}
}

function plugin_genericobject_disableTemplateManagement($name) {
	global $DB;

	$table = plugin_genericobject_getTableNameByName($name);

	if (FieldExists($table, "is_template")) {
		$table = plugin_genericobject_getTableNameByName($name);
		$query = "ALTER TABLE `$table` DROP `is_template`";
		$DB->query($query);
	}

	if (FieldExists($table, "tplname")) {
		$query = "ALTER TABLE `$table` DROP `tplname`";
		$DB->query($query);
	}
}

function plugin_genericobject_getDropdownSpecificFields() {
	global $GENERICOBJECT_AVAILABLE_FIELDS;
	$specific_fields = array ();

	foreach ($GENERICOBJECT_AVAILABLE_FIELDS as $field => $values)
		if (isset ($values["dropdown_type"]) && $values["dropdown_type"] == 'type_specific')
			$specific_fields[$field] = $field;

	return $specific_fields;
}

function plugin_genericobject_getDropdownSpecific(& $dropdowns, $type, $check_entity = false) {
	global $GENERICOBJECT_AVAILABLE_FIELDS;
	
	$specific_types = plugin_genericobject_getDropdownSpecificFields();
	$table = plugin_genericobject_getTableNameByName($type["name"]);

	foreach ($specific_types as $ID => $field)
	{
		if (FieldExists($table, $field)) {
			if (!$check_entity || ($check_entity && plugin_genericobject_isDropdownEntityRestrict($field)))
				//$dropdowns[plugin_genericobject_getDropdownTableName($type["name"], $field)] = plugin_genericobject_getObjectLabel($type["name"]) . ' : ' . $GENERICOBJECT_AVAILABLE_FIELDS[$field]['name'];
				$dropdowns["PluginGenericobject".ucfirst($type["name"]).$field] = plugin_genericobject_getObjectLabel($type["name"]) . ' : ' . $GENERICOBJECT_AVAILABLE_FIELDS[$field]['name'];
		}
	}
}

function plugin_genericobject_getDatabaseRelationsSpecificDropdown(& $dropdowns, $type) {
	global $GENERICOBJECT_AVAILABLE_FIELDS;
	$specific_types = plugin_genericobject_getDropdownSpecificFields();
	$table = plugin_genericobject_getTableNameByName($type["name"]);

	foreach ($specific_types as $ID => $field)
		if (TableExists($table) && FieldExists($table, $field))
			$dropdowns[$table] = array (
				plugin_genericobject_getDropdownTableName($type["name"], $field) => $GENERICOBJECT_AVAILABLE_FIELDS[$field]['linkfield']
			);
}

function plugin_genericobject_getSpecificDropdownsTablesByType($type) {
	$dropdowns = array ();
	$object_type = new PluginGenericObjectType;
	$object_type->getFromDBByType($type);
	plugin_genericobject_getDropdownSpecific($dropdowns, $object_type->fields, true);
	return $dropdowns;
}

function plugin_genericobject_displayFieldDefinition($target, $ID, $field, $index, $total) {
	global $GENERICOBJECT_AVAILABLE_FIELDS, $CFG_GLPI;
	$readonly = ($field == "name");

	echo "<tr class='tab_bg_1' align='center'>";
	$sel = "";
	if (isset ($_POST["selected"]))
		$sel = "checked";

	echo "<td width='10'>";
	if (!$readonly)
		echo "<input type='checkbox' name='fields[" .$field. "]' value='1' $sel>";
	echo "</td>";
	echo "<td>" . $field . "</td>";
	echo "<td>" . $GENERICOBJECT_AVAILABLE_FIELDS[$field]['name'] . "</td>";
	echo "<td width='10'>";
	echo "<input type='checkbox' name='mandatory[" . $field . "]' value='1'>";
	echo "</td>";
	echo "<td width='10'>";
	echo "<input type='checkbox' name='unique[" . $field . "]' value='1'>";
	echo "</td>";

	echo "<td width='10'>";
	if (!$readonly && $index > 2)
		echo "<a href=\"" . $target . "?field=" . $field . "&amp;action=up&amp;id=" . $ID . "\"><img src=\"" . $CFG_GLPI["root_doc"] . "/pics/deplier_up.png\" alt=''></a>";
	echo "</td>";

	echo "<td width='10'>";
	if (!$readonly && $index > 1 && $index < $total)
		echo "<a href=\"" . $target . "?field=" . $field . "&amp;action=down&amp;id=" . $ID . "\"><img src=\"" . $CFG_GLPI["root_doc"] . "/pics/deplier_down.png\" alt=''></a>";
	echo "</td>";

	echo "</tr>";
}

function plugin_genericobject_deleteSpecificDropdownTables($itemtype)
{
	global $DB;
	$name = plugin_genericobject_getNameByID($itemtype);
	$types = plugin_genericobject_getDropdownSpecificFields();

	foreach($types as $type => $tmp)	{
		$DB->query("DROP TABLE IF EXISTS `" . plugin_genericobject_getDropdownTableName($name,$type)."`");
	}
		
}

function plugin_genericobject_addLinkTable($itemtype)
{
	global $DB;
	$name = $itemtype;
	//$name = plugin_genericobject_getNameByID($itemtype);
	$query = "CREATE TABLE IF NOT EXISTS `".plugin_genericobject_getLinkDeviceTableName($name)."` (
	  `id` int(11) NOT NULL auto_increment,
	  `source_id` int(11) NOT NULL default '0',
	  `items_id` int(11) NOT NULL default '0',
	  `itemtype` VARCHAR( 255 ) NOT NULL,
	  PRIMARY KEY  (`id`),
	  UNIQUE KEY `source_id` (`source_id`,`items_id`,`itemtype`),
	  KEY `source_id_2` (`source_id`),
	  KEY `items_id` (`items_id`,`itemtype`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	$DB->query($query);
}

function plugin_genericobject_deleteLinkTable($itemtype)
{
	global $DB;
	$name = plugin_genericobject_getNameByID($itemtype);
	$DB->query("DROP TABLE IF EXISTS `".plugin_genericobject_getLinkDeviceTableName($name)."`");
}
	
function plugin_genericobject_removeDataInjectionModels($itemtype)
{
	global $DB;
		$plugin = new Plugin;
			//Delete if exists datainjection models
		if ($plugin->isInstalled("datainjection"))
		{
			$query = "DELETE FROM `glpi_plugin_datainjection_models`, `glpi_plugin_datainjection_mappings`, `glpi_plugin_datainjection_infos` " .
               "USING `glpi_plugin_datainjection_models`, `glpi_plugin_datainjection_mappings`, `glpi_plugin_datainjection_infos` " .
                  "WHERE glpi_plugin_datainjection_models.itemtype=".$itemtype." " .
                        "AND glpi_plugin_datainjection_mappings.model_id=glpi_plugin_datainjection_models.id " .
                           "AND glpi_plugin_datainjection_infos.model_id=glpi_plugin_datainjection_models.id";
         
         $DB->query ($query);
		}
	
}

/**
 * Delete all loans associated with a itemtype
 */
function plugin_genericobject_deleteLoans($itemtype)
{
   global $DB;
   
   $query = "DELETE FROM  `glpi_reservation_item`, `glpi_reservation_resa` " .
            "USING `glpi_reservation_item`, `glpi_reservation_resa` " .
               "WHERE `glpi_reservation_item`.`itemtype`='$itemtype' " .
                  "AND `glpi_reservation_item`.`id`=`glpi_reservation_resa`.`id_item`";
   $DB->query($query); 
}


function plugin_genericobject_deleteNetworking($itemtype) {
	    global $DB;
        $query = "SELECT id 
               FROM glpi_networkports 
               WHERE itemtype = '" . $itemtype . "'";
      $result = $DB->query($query);
      while ($data = $DB->fetch_array($result)) {
         $q = "DELETE FROM glpi_networkports_networkports WHERE networkports_id_1 = '" . $data["id"] . "' OR networkports_id_2 = '" . $data["id"] . "'";
         $result2 = $DB->query($q);
      }

      $query2 = "DELETE FROM glpi_networkports WHERE itemtype = '" . $itemtype . "'";
      $result2 = $DB->query($query2);

      $query = "SELECT id FROM glpi_computers_items WHERE itemtype='" . $itemtype."'";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) > 0) {
            while ($data = $DB->fetch_array($result)) {
               // Disconnect without auto actions
               Disconnect($data["id"], 1, false);
            }
         }
      }
   
}

?>
