<?php
/*
 * @version $Id: HEADER 7762 2009-01-06 18:30:32Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2009 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

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
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------
include_once(GLPI_ROOT."/inc/profile.class.php");
foreach (glob(GLPI_ROOT . '/plugins/genericobject/inc/*.php') as $file)
        include_once ($file);

define ("GENERICOBJECT_OBJECTTYPE_STATE_DRAFT",0);
define ("GENERICOBJECT_OBJECTTYPE_STATE_PUBLISHED",1);

define ("GENERICOBJECT_OBJECTTYPE_STATUS_INACTIVE",0);
define ("GENERICOBJECT_OBJECTTYPE_STATUS_ACTIVE",1);

// Init the hooks of the plugins -Needed
function plugin_init_genericobject() {
	global $PLUGIN_HOOKS,$LANG,$CFG_GLPI;

	// Params : plugin name - string type - ID - Array of attributes
	registerPluginType('genericobject', 'PLUGIN_GENERICOBJECT_TYPE', 4850, array(
		'classname'  => 'PluginGenericObjectType',
		'tablename'  => 'glpi_plugin_genericobject_types',
		'formpage'   => 'front/plugin_genericobject.object.form.php',
		'searchpage' => 'front/plugin_genericobject.search.php',
		'typename'   => $LANG["genericobject"]["common"][1],
		));

	/* load changeprofile function */
	$PLUGIN_HOOKS['change_profile']['genericobject'] = 'plugin_genericobject_changeprofile';
	
	// Display a menu entry ?
		$PLUGIN_HOOKS['menu_entry']['genericobject'] = true;
		$PLUGIN_HOOKS['submenu_entry']['genericobject']['config'] = 'front/config.php';

	// Config page
	if (haveRight('config','w')) {
		$PLUGIN_HOOKS['config_page']['genericobject'] = 'config.php';
	}

	$PLUGIN_HOOKS['change_profile']['genericobject'] = 'plugin_change_profile_genericobject';
	$PLUGIN_HOOKS['assign_to_ticket']['genericobject'] = true;
	
	// Onglets management
	$PLUGIN_HOOKS['headings']['genericobject'] = 'plugin_get_headings_genericobject';
	$PLUGIN_HOOKS['headings_action']['genericobject'] = 'plugin_headings_actions_genericobject';

	plugin_genericobject_registerNewTypes();
}


// Get the name and the version of the plugin - Needed
function plugin_version_genericobject(){
	global $LANG;
	return array( 
		'name'    => $LANG["genericobject"]["title"][1],
		'version' => '1.0.0',
		'author' => 'Walid Nouh',
		'homepage'=> 'http://glpi-project.org',
		'minGlpiVersion' => '0.72',// For compatibility / no install in version < 0.72
	);
}


// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_genericobject_check_prerequisites(){
	if (GLPI_VERSION>=0.72){
		return true;
	} else {
		echo "GLPI >= 0.72 is needed";
	}
}


// Check configuration process for plugin : need to return true if succeeded
// Can display a message only if failure and $verbose is true
function plugin_genericobject_check_config($verbose=false){
	global $LANG;

	if (true) { // Your configuration check
		return true;
	}
	if ($verbose) {
		echo $LANG['plugins'][2];
	}
	return false;
}

function plugin_genericobject_haveTypeRight($type, $right) {
	return plugin_genericobject_checkRight(plugin_genericobject_getNameByID($type),$right);
}

function plugin_genericobject_checkRight($module, $right) {
	global $CFG_GLPI;

	if (!plugin_genericobject_haveRight($module, $right)) {
		// Gestion timeout session
		if (!isset ($_SESSION["glpiID"])) {
			glpi_header($CFG_GLPI["root_doc"] . "/index.php");
			exit ();
		}

		displayRightError();
	}
	return true;
}

?>
