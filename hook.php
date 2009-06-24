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

// Original Author of file: BALPE Dévi & Walid Nouh
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
	$sopt[PLUGIN_GENERICOBJECT_TYPE][2]['field'] = 'type';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][2]['linkfield'] = '';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][2]['name'] = $LANG["genericobject"]["common"][2];

	$sopt[PLUGIN_GENERICOBJECT_TYPE][4]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][4]['field'] = 'state';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][4]['linkfield'] = 'state';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][4]['name'] = $LANG["genericobject"]["common"][3];
	$sopt[PLUGIN_GENERICOBJECT_TYPE][4]['datatype']='integer';

	$sopt[PLUGIN_GENERICOBJECT_TYPE][5]['table'] = 'glpi_plugin_genericobject_types';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][5]['field'] = 'status';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][5]['linkfield'] = 'status';
	$sopt[PLUGIN_GENERICOBJECT_TYPE][5]['name'] = $LANG['joblist'][0];
	$sopt[PLUGIN_GENERICOBJECT_TYPE][5]['datatype']='integer';
	
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
				if (!plugin_genericobject_profileExists($ID))
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

function plugin_change_profile_genericobject()
{
	$prof=new PluginGenericObjectProfile();
	if($prof->getProfilesFromDB($_SESSION['glpiactiveprofile']['ID']))
		$_SESSION["glpi_plugin_genericobject_profile"]=$prof->fields;
	else
		unset($_SESSION["glpi_plugin_genericobject_profile"]);

}

function plugin_genericobject_AssignToTicket($types){
	global $LANG;
	
	foreach (plugin_genericobject_getAllTypes() as $tmp => $value)
	{
		if (plugin_genericobject_haveRight($value["name"].'_open_ticket',"1"))
			$types[$value['device_type']]=$LANG['genericobject'][$value['name']][1];
	}
		
	return $types;
}

?>
