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
class PluginGenericObjectField extends CommonDBTM{
	
    function __construct() {
    	$this->table="glpi_plugin_genericobject_type_fields";
    	$this->type=-1;
    }  
    
    function deleteByFieldByDeviceTypeAndName($device_type,$name)
    {
    	global $DB;
    	$query = "DELETE FROM `".$this->table."` " .
    			"WHERE device_type='$device_type' AND name='$name'";
    	$DB->query($query);		
    }
 
 	function getID()
 	{
 		return $this->fields["ID"];
 	}
 	
 	function getName()
 	{
 		return $this->fields["name"];
 	}
 	
 	function getRank()
 	{
 		return $this->fields["rank"];
 	}
 	
 	function getMandatory()
 	{
 		return $this->fields["mandatory"];
 	}   

   function post_addItem($newID, $input) {
      $name = plugin_genericobject_getNameByID($input["device_type"]);
      $table = plugin_genericobject_getTableNameByName($name);
      plugin_genericobject_addFieldInDB($table, $this->fields["name"], $name);
      
   }
}
?>
