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
function plugin_genericobject_showPrevisualisationForm($type) {
	global $LANG;
	$name = plugin_genericobject_getNameByID($type);
	echo "<br>" . $LANG['genericobject']['config'][8] . "<br>";

	$object = new CommonItem;
	$object->setType($type, true);
	$object->obj->showForm('', '', '', true);
}

function plugin_genericobject_changeFieldOrder($field,$type,$action){
		global $DB;

		$sql ="SELECT ID, rank FROM glpi_plugin_genericobject_type_fields WHERE device_type='$type' AND name='$field'";

		if ($result = $DB->query($sql)){
			if ($DB->numrows($result)==1){
				
				$current_rank=$DB->result($result,0,"rank");
				$ID = $DB->result($result,0,"ID");
				// Search rules to switch
				$sql2="";
				switch ($action){
					case "up":
						$sql2 ="SELECT ID, rank FROM `glpi_plugin_genericobject_type_fields` WHERE device_type='$type' AND rank < '$current_rank' ORDER BY rank DESC LIMIT 1";
					break;
					case "down":
						$sql2 ="SELECT ID, rank FROM `glpi_plugin_genericobject_type_fields` WHERE device_type='$type' AND rank > '$current_rank' ORDER BY rank ASC LIMIT 1";
					break;
					default :
						return false;
					break;
				}
				
				if ($result2 = $DB->query($sql2)){
					if ($DB->numrows($result2)==1){
						list($other_ID,$new_rank)=$DB->fetch_array($result2);
						$query="UPDATE `glpi_plugin_genericobject_type_fields` SET rank='$new_rank' WHERE ID ='$ID'";
						$query2="UPDATE `glpi_plugin_genericobject_type_fields` SET rank='$current_rank' WHERE ID ='$other_ID'";
						return ($DB->query($query)&&$DB->query($query2));
					}
				}
			}
			return false;
		}
	}

function plugin_genericobject_reorderFields($device_type)
{
	global $DB;
	$query = "SELECT ID FROM `glpi_plugin_genericobject_type_fields` WHERE device_type='$device_type' ORDER BY rank ASC";
	$result = $DB->query($query);
	$i = 0;
	while ($datas = $DB->fetch_array($result))
	{
		$query = "UPDATE `glpi_plugin_genericobject_type_fields` SET rank=$i WHERE device_type='$device_type' AND ID=".$datas["ID"];
		$DB->query($query);
		$i++;	
	}	
}
?>