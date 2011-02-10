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

// ----------------------------------------------------------------------
// Original Author of file: Balpe Dévi
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

include_once (GLPI_ROOT . "/inc/includes.php");
include_once ("objecttype.function.php");

function plugin_genericobject_haveRight($module, $right) {
	$matches = array (
		"" => array (
			"",
			"r",
			"w"
		), // ne doit pas arriver normalement
	"r" => array (
			"r",
			"w"
		),
		"w" => array (
			"w"
		),
		"1" => array (
			"1"
		),
		"0" => array (
			"0",
			"1"
		), // ne doit pas arriver non plus

	
	);

	if (isset ($_SESSION["glpi_plugin_genericobject_profile"][$module]) && in_array($_SESSION["glpi_plugin_genericobject_profile"][$module], $matches[$right]))
		return true;
	else
		return false;
}

function check_directories() {
	global $LANG;
	
	$go_dir = "../plugins/genericobject/";
	
	if (!is_writable($go_dir.'inc/') || !is_writable($go_dir.'front/') || !is_writable($go_dir.'ajax/')) {
		AddMessageAfterRedirect($LANG['genericobject']['install'][0]);
		return false;
	}	
	return true;
}


function plugin_genericobject_install() {
	global $DB;
	
	//check directories rights
	if (!check_directories()) return false;

	$plugin = new Plugin;

	//Plugin is not installed
	if (!TableExists('glpi_plugin_genericobject_types')) {
		$query = "CREATE TABLE `glpi_plugin_genericobject_types` (
		                  `id` INT( 11 ) NOT NULL AUTO_INCREMENT,
		                  `entities_id` INT( 11 ) NOT NULL DEFAULT 0,
		                  `itemtype` VARCHAR( 255 ) NOT NULL DEFAULT 0 ,
		                  `state` INT( 2 ) NOT NULL DEFAULT 0 ,
		                  `status` INT ( 1 )NOT NULL DEFAULT 0 ,
		                  `name` VARCHAR( 255 )  collate utf8_unicode_ci NOT NULL ,
		                  `use_deleted` INT ( 1 ) NOT NULL DEFAULT 0 ,
		                  `use_notes` INT ( 1 ) NOT NULL DEFAULT 0 ,
		                  `use_history` INT ( 1 ) NOT NULL DEFAULT 0 ,
		                  `use_entity` INT ( 1 ) NOT NULL DEFAULT 0 ,
		                  `use_recursivity` INT ( 1 ) NOT NULL DEFAULT 0 ,
		                  `use_template` INT ( 1 ) NOT NULL DEFAULT 0 ,
		                  `use_infocoms` INT ( 1 ) NOT NULL DEFAULT 0 ,
		                  `use_documents` INT ( 1 ) NOT NULL DEFAULT 0 ,
		                  `use_tickets` INT ( 1 ) NOT NULL DEFAULT 0 ,
		                  `use_links` INT ( 1 ) NOT NULL DEFAULT 0 ,
		                  `use_loans` INT ( 1 ) NOT NULL DEFAULT 0 ,
		                  `use_network_ports` INT ( 1 ) NOT NULL DEFAULT 0 ,
		                  `use_direct_connections` INT ( 1 ) NOT NULL DEFAULT 0 ,
		                  `use_plugin_datainjection` INT ( 1 ) NOT NULL DEFAULT 0 ,
		                  `use_plugin_pdf` INT ( 1 ) NOT NULL DEFAULT 0 ,
		                  `use_plugin_order` INT ( 1 ) NOT NULL DEFAULT 0 ,
		                  `use_plugin_uninstall` INT ( 1 ) NOT NULL DEFAULT 0 ,
                        `use_plugin_geninventorynumber` INT ( 1 ) NOT NULL DEFAULT 0 ,
		                  PRIMARY KEY ( `id` ) 
		                  ) ENGINE = MYISAM COMMENT = 'Object types definition table' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$DB->query($query);

		$query = "INSERT INTO `glpi_display` (`id`, `type`, `num`, `rank`, `users_id`) VALUES
		                  (NULL, 4850, 10, 6, 0),
		                  (NULL, 4850, 9, 5, 0),
		                  (NULL, 4850, 8, 4, 0),
		                  (NULL, 4850, 7, 3, 0),
		                  (NULL, 4850, 6, 2, 0),
		                  (NULL, 4850, 2, 1, 0),
		                  (NULL, 4090, 4, 1, 0),
		                  (NULL, 4850, 11, 7, 0),
		                  (NULL, 4850, 12, 8, 0),
		                  (NULL, 4850, 13, 9, 0),
		                  (NULL, 4850, 14, 10, 0),
		                  (NULL, 4850, 15, 11, 0);";
		$DB->query($query);

	}

	if (!TableExists("glpi_plugin_genericobject_profiles")) {
		$query = "CREATE TABLE `glpi_plugin_genericobject_profiles` (
		                  `id` int(11) NOT NULL auto_increment,
		                  `name` varchar(255) collate utf8_unicode_ci default NULL,
		                  `device_name` VARCHAR( 255 ) default NULL,
		                   `right` char(1) default NULL,
		                   `open_ticket` char(1) NOT NULL DEFAULT 0,
		                   PRIMARY KEY  (`id`),
		                  KEY `name` (`name`)
		                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$DB->query($query);
	}

	if (!TableExists("glpi_plugin_genericobject_type_fields")) {
		$query = "CREATE TABLE `glpi_plugin_genericobject_type_fields` (
		            `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		            `itemtype` VARCHAR( 255 ) NOT NULL DEFAULT 0 ,
		            `name` VARCHAR( 255 )   collate utf8_unicode_ci NOT NULL DEFAULT '' ,
		            `rank` INT( 11 ) NOT NULL DEFAULT 0 ,
		            `mandatory` INT( 1 ) NOT NULL ,
		            `entity_restrict` INT( 1 ) NOT NULL ,
		            `unique` INT( 1 ) NOT NULL
		            ) ENGINE = MYISAM  COMMENT = 'Field type description' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$DB->query($query);
	}

	if (!TableExists("glpi_plugin_genericobject_type_links")) {
		$query = "CREATE TABLE `glpi_plugin_genericobject_type_links` (
		                  `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
		                  `itemtype` VARCHAR( 255 ) NOT NULL ,
		                  `destination_type` INT( 11 ) NOT NULL ,
		                  PRIMARY KEY ( `id` )
		                  ) ENGINE = MYISAM COMMENT = 'Device type links definitions'  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$DB->query($query);
	}

	plugin_genericobject_createFirstAccess();

	if (!FieldExists('glpi_plugin_genericobject_types', 'use_network_ports')) {
		$DB->query("ALTER TABLE `glpi_plugin_genericobject_types` ADD `use_network_ports` INT( 1 ) NOT NULL DEFAULT '0'");
	}

	if (!FieldExists('glpi_plugin_genericobject_types', 'use_direct_connections')) {
		$DB->query("ALTER TABLE `glpi_plugin_genericobject_types` ADD `use_direct_connections` INT( 1 ) NOT NULL DEFAULT '0'");
	}

   if (!FieldExists('glpi_plugin_genericobject_types', 'use_plugin_geninventorynumber')) {
      $DB->query("ALTER TABLE `glpi_plugin_genericobject_types` ADD `use_plugin_geninventorynumber` INT( 1 ) NOT NULL DEFAULT '0'");
   }

	if (!is_dir(GENERICOBJECT_CLASS_PATH))
		@ mkdir(GENERICOBJECT_CLASS_PATH, 0777, true) or die("Can't create folder " . GENERICOBJECT_CLASS_PATH);

	//Init plugin & types
	plugin_init_genericobject();

	//Init profiles
	plugin_change_profile_genericobject();
	return true;
}

function plugin_genericobject_uninstall() {
	global $DB;

	//include_once (GLPI_ROOT . "/inc/computer.class.php");
	//include_once (GLPI_ROOT . "/inc/computer.function.php");
	//Delete search display preferences
	$query = "DELETE FROM glpi_display WHERE type='4850';";
	$DB->query($query);

	//For each type
	foreach (plugin_genericobject_getAllTypes() as $tmp => $value) {
		
		//Delete loans
		plugin_genericobject_deleteLoans($value["itemtype"]);

		//Delete if exists datainjection models
		plugin_genericobject_removeDataInjectionModels($value["itemtype"]);

		plugin_genericobject_deleteNetworking($value["itemtype"]);

		//Delete search display preferences
		$query = "DELETE FROM glpi_display WHERE type='" . $value["itemtype"] . "';";
		$DB->query($query);

		//Delete link tables
		$link_tables = array (
			"glpi_infocoms",
			"glpi_reservation_item",
			"glpi_doc_device",
			"glpi_contract_device",
			"glpi_bookmark",
			"glpi_history"
		);
		foreach ($link_tables as $link_table) {
			$query = "DELETE FROM `" . $link_table . "` WHERE itemtype='" . $value["itemtype"] . "';";
			$DB->query($query);
		}

		//Drop itemtype link table
		plugin_genericobject_deleteLinkTable($value["itemtype"]);
		
		plugin_genericobject_deleteSpecificDropdownFiles($value["itemtype"]);
		
		plugin_genericobject_deleteSpecificDropdownTables($value["itemtype"]);

		//Drop type table
		$DB->query("DROP TABLE IF EXISTS `" .
		plugin_genericobject_getTableNameByName($value["name"]) . "`");
		
		if (file_exists(GENERICOBJECT_CLASS_PATH . "/".$value["itemtype"].".class.php"))
			unlink(GENERICOBJECT_CLASS_PATH . "/".$value["itemtype"].".class.php");	
	}

	//Delete plugin's table
	$tables = array (
		"glpi_plugin_genericobject_types",
		"glpi_plugin_genericobject_profiles",
		"glpi_plugin_genericobject_type_fields",
		"glpi_plugin_genericobject_type_links"
	);
	foreach ($tables as $table)
		$DB->query("DROP TABLE IF EXISTS `$table`");

	/*if (is_dir(GENERICOBJECT_CLASS_PATH)) {
		deleteDir(GENERICOBJECT_CLASS_PATH);
	}*/

	plugin_init_genericobject();
	//cleanCache("GLPI_HEADER_" . $_SESSION["glpiID"]);

	return true;
}
?>
