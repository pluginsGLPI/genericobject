<?php


/*----------------------------------------------------------------------
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
   ----------------------------------------------------------------------*/
/*----------------------------------------------------------------------
    Original Author of file: 
    Purpose of file:
    ----------------------------------------------------------------------*/
/**
 * Create rights for the profile if it doesn't exists
 * @param profileID the profile ID
 * @return nothing
 */
function plugin_genericobject_createAccess($profileID,$first=false)
{
	$types = plugin_genericobject_getAllTypes(true);
	$plugin_profile = new PluginGenericObjectProfile;
	$profile = new Profile;
	$profile->getFromDB($profileID);
	foreach ($types as $tmp => $value)
	{
		if (!plugin_genericobject_profileForTypeExists($profileID,$value["name"]))
		{
			$input["device_name"] = $value["name"];
			$input["right"] = ($first?'w':'');
			$input["open_ticket"] = ($first?1:0);
			$input["name"] = $profile->fields["name"];
			$plugin_profile->add($input);
		}
	}
}

/**
 * Check if rights for a profile still exists
 * @param profileID the profile ID
 * @return true if exists, no if not
 */
function plugin_genericobject_profileExists($profileID)
{
	global $DB;
	$profile = new Profile;
	$profile->getFromDB($profileID);
	$query = "SELECT COUNT(*) as cpt FROM `glpi_plugin_genericobject_profiles` WHERE name='".$profile->fields["name"]."'";
	$result = $DB->query($query);
	if ($DB->result($result,0,"cpt") > 0)
		return true;
	else
		return false;	
}

/**
 * Check if rights for a profile and type still exists
 * @param profileID the profile ID
 * @param device_name name of the type 
 * @return true if exists, no if not
 */
function plugin_genericobject_profileForTypeExists($profileID,$device_name)
{
	global $DB;
	$profile = new Profile;
	$profile->getFromDB($profileID);
	$query = "SELECT COUNT(*) as cpt FROM `glpi_plugin_genericobject_profiles` WHERE name='".$profile->fields["name"]."' " .
			"AND device_name='$device_name'";
	$result = $DB->query($query);
	if ($DB->result($result,0,"cpt") > 0)
		return true;
	else
		return false;	
}

/**
 * Create rights for the current profile
 * @param profileID the profile ID
 * @return nothing
 */
function plugin_genericobject_createFirstAccess()
{
	if (!plugin_genericobject_profileExists($_SESSION["glpiactiveprofile"]["ID"]))
		plugin_genericobject_createAccess($_SESSION["glpiactiveprofile"]["ID"],true);
}

/**
 * Delete type from the rights
 * @param name the name of the type
 * @return nothing
 */
function plugin_genericobject_deleteTypeFromProfile($name)
{
	global $DB;
	$query = "DELETE FROM `glpi_plugin_genericobject_profiles` WHERE device_name='$name'";
	$DB->query($query);
}

function plugin_change_profile_genericobject()
{
	$prof=new PluginGenericObjectProfile();
	if($prof->getProfilesFromDB($_SESSION['glpiactiveprofile']['ID']))
		$_SESSION["glpi_plugin_genericobject_profile"]=$prof->fields;
	else
		unset($_SESSION["glpi_plugin_genericobject_profile"]);

}
?>