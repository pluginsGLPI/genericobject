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
function plugin_genericobject_getSearchOption() {
	global $LANG;
	$sopt = array ();
	$sopt[PLUGIN_GENERICOBJECT_TYPE]['common'] = $LANG["genericobject"]["title"][1];

	$sopt[PLUGIN_GENERICOBJECT_TYPE][1]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][1]['field'] = 'name';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][1]['linkfield'] = '';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][1]['name'] = $LANG["common"][22];
	$sopt[PLUGIN_GENERICOBJECT_TYPE][1]['datatype']='itemlink';

	$sopt[PLUGIN_GENERICOBJECT_TYPE][2]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][2]['field'] = 'device_type';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][2]['linkfield'] = '';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][2]['name'] = $LANG["genericobject"]["common"][2];
	$sopt[PLUGIN_GENERICOBJECT_TYPE][2]['datatype']='itemlink';

	$sopt[PLUGIN_GENERICOBJECT_TYPE][5]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][5]['field'] = 'status';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][5]['linkfield'] = 'status';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][5]['name'] = $LANG['joblist'][0];

	$sopt[PLUGIN_GENERICOBJECT_TYPE][6]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][6]['field'] = 'use_tickets';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][6]['linkfield'] = '';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][6]['name'] = $LANG['genericobject']['config'][1]." ".$LANG['Menu'][31];
	$sopt[PLUGIN_GENERICOBJECT_TYPE][6]['datatype'] = 'bool';

	$sopt[PLUGIN_GENERICOBJECT_TYPE][7]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][7]['field'] = 'use_deleted';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][7]['linkfield'] = '';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][7]['name'] = $LANG['genericobject']['config'][1]." ".$LANG['ocsconfig'][49];
	$sopt[PLUGIN_GENERICOBJECT_TYPE][7]['datatype'] = 'bool';

	$sopt[PLUGIN_GENERICOBJECT_TYPE][8]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][8]['field'] = 'use_notes';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][8]['linkfield'] = '';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][8]['name'] = $LANG['genericobject']['config'][1]." ".$LANG['title'][37];
	$sopt[PLUGIN_GENERICOBJECT_TYPE][8]['datatype'] = 'bool';

	$sopt[PLUGIN_GENERICOBJECT_TYPE][9]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][9]['field'] = 'use_history';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][9]['linkfield'] = '';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][9]['name'] = $LANG['genericobject']['config'][1]." ".$LANG['title'][38];
	$sopt[PLUGIN_GENERICOBJECT_TYPE][9]['datatype'] = 'bool';

	$sopt[PLUGIN_GENERICOBJECT_TYPE][10]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][10]['field'] = 'use_entity';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][10]['linkfield'] = '';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][10]['name'] = $LANG['genericobject']['config'][1]." ".$LANG['Menu'][37];
	$sopt[PLUGIN_GENERICOBJECT_TYPE][10]['datatype'] = 'bool';

	$sopt[PLUGIN_GENERICOBJECT_TYPE][11]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][11]['field'] = 'use_recursivity';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][11]['linkfield'] = '';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][11]['name'] = $LANG['genericobject']['config'][1]." ".$LANG['entity'][9];
	$sopt[PLUGIN_GENERICOBJECT_TYPE][11]['datatype'] = 'bool';

	$sopt[PLUGIN_GENERICOBJECT_TYPE][12]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][12]['field'] = 'use_template';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][12]['linkfield'] = '';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][12]['name'] = $LANG['genericobject']['config'][1]." ".$LANG['common'][14];
	$sopt[PLUGIN_GENERICOBJECT_TYPE][12]['datatype'] = 'bool';

	$sopt[PLUGIN_GENERICOBJECT_TYPE][13]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][13]['field'] = 'use_infocoms';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][13]['linkfield'] = '';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][13]['name'] = $LANG['genericobject']['config'][1]." ".$LANG['financial'][3];
	$sopt[PLUGIN_GENERICOBJECT_TYPE][13]['datatype'] = 'bool';

	$sopt[PLUGIN_GENERICOBJECT_TYPE][14]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][14]['field'] = 'use_documents';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][14]['linkfield'] = '';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][14]['name'] = $LANG['genericobject']['config'][1]." ".$LANG['Menu'][27];
	$sopt[PLUGIN_GENERICOBJECT_TYPE][14]['datatype'] = 'bool';

	$sopt[PLUGIN_GENERICOBJECT_TYPE][15]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][15]['field'] = 'use_loans';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][15]['linkfield'] = '';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][15]['name'] = $LANG['genericobject']['config'][1]." ".$LANG['Menu'][17];
	$sopt[PLUGIN_GENERICOBJECT_TYPE][15]['datatype'] = 'bool';
	
	$types = plugin_genericobject_getAllTypes();
	
	foreach ($types as $type => $params)
		$sopt = plugin_genericobject_objectSearchOptions($params["name"],$sopt);
		
	return $sopt;

}

function plugin_headings_actions_genericobject($type) {

	switch ($type) {
		case PROFILE_TYPE :
			return array (
				1 => "plugin_headings_genericobject",
			);
			break;
	}
	return false;
}

function plugin_get_headings_genericobject($type, $ID, $withtemplate) {
	global $LANG;

	switch ($type) {
		case PROFILE_TYPE:
			$prof = new Profile();
			if ($ID>0 && $prof->getFromDB($ID) && $prof->fields['interface']=='central') {
				return array(
					1 => $LANG["genericobject"]["title"][1]
				    );				
			} else {
				return array();
			}
			break;
	}
	return false;
}

function plugin_headings_genericobject($type, $ID, $withtemplate = 0)
{
	global $CFG_GLPI,$LANG;
	switch ($type) {
		case PROFILE_TYPE :
			$profile = new profile;
			$profile->getFromDB($ID);
			if ($profile->fields["interface"] != "helpdesk") {
					plugin_genericobject_createAccess($ID);
					
				$prof = new PluginGenericObjectProfile();
				$prof->showForm($CFG_GLPI["root_doc"] . "/plugins/genericobject/front/plugin_genericobject.profile.php", $ID);
			} else {
				echo "<table class='tab_cadre_fixe'><tr class='tab_bg_2'><td align='center'>";
				echo $LANG['genericobject']['setup'][1];
				echo "</td></tr></table>";
			}
			break;
	}	
}

function plugin_genericobject_AssignToTicket($types){
	global $LANG;
	
	foreach (plugin_genericobject_getAllTypes() as $tmp => $value)
		if (plugin_genericobject_haveRight($value["name"].'_open_ticket',"1"))
			$types[$value['device_type']]=plugin_genericobject_getObjectLabel($value['name']);
		
	return $types;
}

// Define Dropdown tables to be manage in GLPI :
function plugin_genericobject_getDropdown() {
	$dropdowns = array();
	
	$plugin = new Plugin();
	if ($plugin->isActivated("genericobject"))
	{
		foreach (plugin_genericobject_getAllTypes() as $tmp => $values)
			plugin_genericobject_getDropdownSpecific($dropdowns,$values);
	}

	return $dropdowns;	
}

// Define dropdown relations
function plugin_genericobject_getDatabaseRelations(){
	$dropdowns = array();

	$plugin = new Plugin();
	if ($plugin->isActivated("genericobject"))
	{
		foreach (plugin_genericobject_getAllTypes(true) as $tmp => $values)
		{
			plugin_genericobject_getDatabaseRelationsSpecificDropdown($dropdowns,$values);
			if ($values["use_entity"])
				$dropdowns["glpi_entities"][plugin_genericobject_getObjectTableNameByName($values["name"])] = "FK_entities";
		}
			
	}

	return $dropdowns;	
}

/**
 * Integration with datainjection plugin
 */
function plugin_genericobject_datainjection_variables()
{
	global $DATA_INJECTION_MAPPING,$DATA_INJECTION_INFOS, $GENERICOBJECT_AVAILABLE_FIELDS,$SEARCH_OPTION;
	
	$types = plugin_genericobject_getAllTypes();
	foreach ($types as $tmp => $value)
	{
		$name = plugin_genericobject_getNameByID($value["device_type"]);
		$fields = plugin_genericobject_getFieldsByType($value["device_type"]);
		foreach ($fields as $field => $object)
		{
			switch ($GENERICOBJECT_AVAILABLE_FIELDS[$field]['input_type']) {
					case 'date':
					case 'text':
						$DATA_INJECTION_MAPPING[$value["device_type"]][$field]['table'] = plugin_genericobject_getObjectTableNameByName($name);
						$DATA_INJECTION_INFOS[$value["device_type"]][$field]['table'] = plugin_genericobject_getObjectTableNameByName($name);
						break;
					case 'dropdown' :
						if (plugin_genericobject_isDropdownTypeSpecific($field))
						{
							$DATA_INJECTION_MAPPING[$value["device_type"]][$field]['table'] = plugin_genericobject_getDropdownTableName($name,$field);
							$DATA_INJECTION_INFOS[$value["device_type"]][$field]['table'] = plugin_genericobject_getDropdownTableName($name,$field);	
						}
			 			else
			 			{
			 				$DATA_INJECTION_MAPPING[$value["device_type"]][$field]['table'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field]['table'];
			 				$DATA_INJECTION_INFOS[$value["device_type"]][$field]['table'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field]['table'];
			 			}	
							
						break;
					case 'dropdown_yesno' :
						$DATA_INJECTION_MAPPING[$value["device_type"]][$field]['table'] = plugin_genericobject_getObjectTableNameByName($name);
						$DATA_INJECTION_INFOS[$value["device_type"]][$field]['table'] = plugin_genericobject_getObjectTableNameByName($name);
						break;
			}
				
			$DATA_INJECTION_MAPPING[$value["device_type"]][$field]['name'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field]['name'];
			$DATA_INJECTION_MAPPING[$value["device_type"]][$field]['field'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field]['field'];
			$DATA_INJECTION_MAPPING[$value["device_type"]][$field]['type'] = (isset($GENERICOBJECT_AVAILABLE_FIELDS[$field]['input_type'])?$GENERICOBJECT_AVAILABLE_FIELDS[$field]['input_type']:'text');

			$DATA_INJECTION_INFOS[$value["device_type"]][$field]['name'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field]['name'];
			$DATA_INJECTION_INFOS[$value["device_type"]][$field]['field'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field]['field'];
			$DATA_INJECTION_INFOS[$value["device_type"]][$field]['input_type'] = (isset($GENERICOBJECT_AVAILABLE_FIELDS[$field]['input_type'])?$GENERICOBJECT_AVAILABLE_FIELDS[$field]['input_type']:'text');

		}	
	}
}

function plugin_uninstall_addUninstallTypes($uninstal_types)
{
	/*
	$types = plugin_genericobject_getAllTypes();
	
	foreach ($types as $tmp => $type)
		if ($type["use_plugin_uninstall"])
			$uninstal_types[] = $type["device_type"];
	*/
	return $uninstal_types;		
}

?>