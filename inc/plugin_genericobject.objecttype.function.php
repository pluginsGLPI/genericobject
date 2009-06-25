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
function plugin_genericobject_getNextDeviceType()
{
	global $DB;
	$query ="SELECT MAX(device_type) as cpt FROM `glpi_plugin_genericobject_types`";
	$result= $DB->query($query);
	if (!$DB->result($result,0,"cpt"))
		$cpt = 4090;
	else
		$cpt= $DB->result($result,0,"cpt") + 1;
	return $cpt;	
}

function plugin_genericobject_addClassFile($name,$classname,$device_type)
{
	$DBf_handle = fopen(GENERICOBJECT_CLASS_TEMPLATE, "rt");
	$template_file = fread($DBf_handle, filesize(GENERICOBJECT_CLASS_TEMPLATE));
	fclose($DBf_handle);
	$template_file=str_replace("%%CLASSNAME%%",$classname,$template_file);
	$template_file=str_replace("%%DEVICETYPE%%",$device_type,$template_file);
	$DBf_handle = fopen(GENERICOBJECT_CLASS_PATH."/plugin_genericobject.$name.class.php", "w");
	fwrite($DBf_handle,$template_file);
	fclose($DBf_handle);
}

function plugin_genericobject_deleteClassFile($name)
{
	if (file_exists(GENERICOBJECT_CLASS_PATH."/plugin_genericobject.$name.class.php"))
		unlink(GENERICOBJECT_CLASS_PATH."/plugin_genericobject.$name.class.php");
}
?>
