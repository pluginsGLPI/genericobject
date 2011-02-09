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
function plugin_genericobject_getAddSearchOptions($itemtype) {
	global $LANG;
	$sopt = array ();
	
	$sopt[1]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[1]['field'] = 'name';
	$sopt[1]['linkfield'] = '';
	$sopt[1]['name'] = $LANG["common"][22];
	$sopt[1]['datatype']='itemlink';

	/*$sopt[2]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[2]['field'] = 'itemtype';
	$sopt[2]['linkfield'] = '';
	$sopt[2]['name'] = $LANG["genericobject"]["common"][2];
	$sopt[2]['datatype']='itemlink';*/

	$sopt[5]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[5]['field'] = 'status';
	$sopt[5]['linkfield'] = 'status';
	$sopt[5]['name'] = $LANG['joblist'][0];

	/*$sopt[6]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[6]['field'] = 'use_tickets';
	$sopt[6]['linkfield'] = '';
	$sopt[6]['name'] = $LANG['genericobject']['config'][1]." ".$LANG['Menu'][31];
	$sopt[6]['datatype'] = 'bool';

	$sopt[7]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[7]['field'] = 'use_deleted';
	$sopt[7]['linkfield'] = '';
	$sopt[7]['name'] = $LANG['genericobject']['config'][1]." ".$LANG['ocsconfig'][49];
	$sopt[7]['datatype'] = 'bool';

	$sopt[8]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[8]['field'] = 'use_notes';
	$sopt[8]['linkfield'] = '';
	$sopt[8]['name'] = $LANG['genericobject']['config'][1]." ".$LANG['title'][37];
	$sopt[8]['datatype'] = 'bool';

	$sopt[9]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[9]['field'] = 'use_history';
	$sopt[9]['linkfield'] = '';
	$sopt[9]['name'] = $LANG['genericobject']['config'][1]." ".$LANG['title'][38];
	$sopt[9]['datatype'] = 'bool';

	$sopt[10]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[10]['field'] = 'use_entity';
	$sopt[10]['linkfield'] = '';
	$sopt[10]['name'] = $LANG['genericobject']['config'][1]." ".$LANG['Menu'][37];
	$sopt[10]['datatype'] = 'bool';

	$sopt[11]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[11]['field'] = 'use_recursivity';
	$sopt[11]['linkfield'] = '';
	$sopt[11]['name'] = $LANG['genericobject']['config'][1]." ".$LANG['entity'][9];
	$sopt[11]['datatype'] = 'bool';

	$sopt[12]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[12]['field'] = 'use_template';
	$sopt[12]['linkfield'] = '';
	$sopt[12]['name'] = $LANG['genericobject']['config'][1]." ".$LANG['common'][14];
	$sopt[12]['datatype'] = 'bool';

	$sopt[13]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[13]['field'] = 'use_infocoms';
	$sopt[13]['linkfield'] = '';
	$sopt[13]['name'] = $LANG['genericobject']['config'][1]." ".$LANG['financial'][3];
	$sopt[13]['datatype'] = 'bool';

	$sopt[14]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[14]['field'] = 'use_documents';
	$sopt[14]['linkfield'] = '';
	$sopt[14]['name'] = $LANG['genericobject']['config'][1]." ".$LANG['Menu'][27];
	$sopt[14]['datatype'] = 'bool';

	$sopt[15]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[15]['field'] = 'use_loans';
	$sopt[15]['linkfield'] = '';
	$sopt[15]['name'] = $LANG['genericobject']['config'][1]." ".$LANG['Menu'][17];
	$sopt[15]['datatype'] = 'bool';*/
	
	$types = plugin_genericobject_getAllTypes();
	
	foreach ($types as $type => $params)
		$sopt = plugin_genericobject_objectSearchOptions($params["name"],$sopt);
		
	//echo "<pre>";var_dump($sopt);echo "</pre>";
		
	return $sopt;

}

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
	$sopt[PLUGIN_GENERICOBJECT_TYPE][2]['field'] = 'itemtype';
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

function plugin_headings_actions_genericobject($item) {

   switch (get_class($item)) {
		case PROFILE_TYPE :
			return array (
				1 => "plugin_headings_genericobject",
			);
			break;
	}
	return false;
}

function plugin_get_headings_genericobject($item, $withtemplate) {
	global $LANG;

	switch (get_class($item)) {
		case PROFILE_TYPE:
			$prof = new Profile();
			if ($item->getField('id')>0 && $prof->getFromDB($item->getField('id')) && $prof->fields['interface']=='central') {
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

function plugin_headings_genericobject($item, $withtemplate) {
	global $CFG_GLPI,$LANG;
	switch (get_class($item)) {
		case PROFILE_TYPE :
			$profile = new profile;
			$profile->getFromDB($item->getField('id'));
			if ($profile->fields["interface"] != "helpdesk") {
					plugin_genericobject_createAccess($item->getField('id'));
					
				$prof = new PluginGenericObjectProfile();
				$prof->showForm($CFG_GLPI["root_doc"] . "/plugins/genericobject/front/profile.php", $item->getField('id'));
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
			$types[$value['itemtype']]=plugin_genericobject_getObjectLabel($value['name']);
		
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
				$dropdowns["glpi_entities"][plugin_genericobject_getObjectTableNameByName($values["name"])] = "entities_id";
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
		$name = plugin_genericobject_getNameByID($value["itemtype"]);
		$fields = plugin_genericobject_getFieldsByType($value["itemtype"]);
		foreach ($fields as $field => $object)
		{
			switch ($GENERICOBJECT_AVAILABLE_FIELDS[$field]['input_type']) {
					case 'date':
					case 'text':
						$DATA_INJECTION_MAPPING[$value["itemtype"]][$field]['table'] = plugin_genericobject_getObjectTableNameByName($name);
						$DATA_INJECTION_INFOS[$value["itemtype"]][$field]['table'] = plugin_genericobject_getObjectTableNameByName($name);
						break;
					case 'dropdown' :
						if (plugin_genericobject_isDropdownTypeSpecific($field))
						{
							$DATA_INJECTION_MAPPING[$value["itemtype"]][$field]['table'] = plugin_genericobject_getDropdownTableName($name,$field);
							$DATA_INJECTION_INFOS[$value["itemtype"]][$field]['table'] = plugin_genericobject_getDropdownTableName($name,$field);	
						}
			 			else
			 			{
			 				$DATA_INJECTION_MAPPING[$value["itemtype"]][$field]['table'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field]['table'];
			 				$DATA_INJECTION_INFOS[$value["itemtype"]][$field]['table'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field]['table'];
			 			}	
							
						break;
					case 'dropdown_yesno' :
						$DATA_INJECTION_MAPPING[$value["itemtype"]][$field]['table'] = plugin_genericobject_getObjectTableNameByName($name);
						$DATA_INJECTION_INFOS[$value["itemtype"]][$field]['table'] = plugin_genericobject_getObjectTableNameByName($name);
						break;
			}
				
			$DATA_INJECTION_MAPPING[$value["itemtype"]][$field]['name'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field]['name'];
			$DATA_INJECTION_MAPPING[$value["itemtype"]][$field]['field'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field]['field'];
			$DATA_INJECTION_MAPPING[$value["itemtype"]][$field]['type'] = (isset($GENERICOBJECT_AVAILABLE_FIELDS[$field]['input_type'])?$GENERICOBJECT_AVAILABLE_FIELDS[$field]['input_type']:'text');

			$DATA_INJECTION_INFOS[$value["itemtype"]][$field]['name'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field]['name'];
			$DATA_INJECTION_INFOS[$value["itemtype"]][$field]['field'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field]['field'];
			$DATA_INJECTION_INFOS[$value["itemtype"]][$field]['input_type'] = (isset($GENERICOBJECT_AVAILABLE_FIELDS[$field]['input_type'])?$GENERICOBJECT_AVAILABLE_FIELDS[$field]['input_type']:'text');

		}	
	}
}

function plugin_uninstall_addUninstallTypes($uninstal_types)
{
	/*
	$types = plugin_genericobject_getAllTypes();
	
	foreach ($types as $tmp => $type)
		if ($type["use_plugin_uninstall"])
			$uninstal_types[] = $type["itemtype"];
	*/
	return $uninstal_types;		
}

function plugin_genericobject_giveItem($itemtype,$ID,$data,$num,$meta=0) {
	$searchopt=&Search::getOptions($itemtype);
	
	$NAME="ITEM_";
	if ($meta) {
		$NAME="META_";
	}
	$table=$searchopt[$ID]["table"];
	$field=$searchopt[$ID]["field"];
	$linkfield=$searchopt[$ID]["linkfield"];
	
	if ($table == "glpi_plugin_genericobject_types") return;

	//echo $field;
	switch ($field) {
		case 'name':	
			$out  = "<a id='ticket".$data[$NAME.$num]."' href=\"object.form.php?id=".$data['id'];
			$out .= "\">".$data[$NAME.$num];
			$out .= "</a>";
			break;
	}
	
	return $out;
}

?>
