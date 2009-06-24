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
function plugin_genericobject_createAccess($profileID)
{
	$types = plugin_genericobject_getAllTypes();
	$plugin_profile = new PluginGenericObjectProfile;
	$profile = new Profile;
	$profile->getFromDB($profileID);
	foreach ($types as $tmp => $value)
	{
		$input["device_name"] = $value["name"];
		$input["right"] = "";
		$input["open_ticket"] = "";
		$input["name"] = $profile->fields["name"];
		$plugin_profile->add($input);
	}
}

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
?>