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

include_once (GLPI_ROOT . "/plugins/genericobject/inc/common.function.php");
include_once (GLPI_ROOT . "/plugins/genericobject/inc/install.function.php");
include_once (GLPI_ROOT . "/plugins/genericobject/inc/dropdown.function.php");

define("PLUGIN_GENERICOBJECT_TYPE", "PluginGenericobjectType");

define("GENERICOBJECT_OBJECTTYPE_STATE_DRAFT", 0);
define("GENERICOBJECT_OBJECTTYPE_STATE_PUBLISHED", 1);

define("GENERICOBJECT_OBJECTTYPE_STATUS_INACTIVE", 0);
define("GENERICOBJECT_OBJECTTYPE_STATUS_ACTIVE", 1);

define("GENERICOBJECT_CLASS_PATH", GLPI_ROOT . "/plugins/genericobject/inc");
define("GENERICOBJECT_CLASS_TEMPLATE", 
         GLPI_ROOT . "/plugins/genericobject/objects/generic.class.tpl");

define("GENERICOBJECT_CLASS_DROPDOWN_TEMPLATE", 
         GLPI_ROOT . "/plugins/genericobject/objects/generic.dropdown.class.tpl");
define("GENERICOBJECT_FRONTFORM_DROPDOWN_TEMPLATE", 
         GLPI_ROOT . "/plugins/genericobject/objects/front.form.tpl");
define("GENERICOBJECT_FRONT_DROPDOWN_TEMPLATE", 
         GLPI_ROOT . "/plugins/genericobject/objects/front.tpl");
define("GENERICOBJECT_AJAX_DROPDOWN_TEMPLATE", 
         GLPI_ROOT . "/plugins/genericobject/objects/ajax.tabs.tpl");
define("GENERICOBJECT_FRONT_PATH", GLPI_ROOT . "/plugins/genericobject/front");
define("GENERICOBJECT_AJAX_PATH", GLPI_ROOT . "/plugins/genericobject/ajax");

// Init the hooks of the plugins -Needed
function plugin_init_genericobject() {
   global $PLUGIN_HOOKS, $LANG, $CFG_GLPI, $GENERICOBJECT_BLACKLISTED_FIELDS, 
          $GENERICOBJECT_AUTOMATICALLY_MANAGED_FIELDS, $GENERICOBJECT_LINK_TYPES, 
          $GENERICOBJECT_AVAILABLE_FIELDS, $GENERICOBJECT_PDF_TYPES;
          
   Plugin::registerClass('PluginGenericobjectProfile');
   Plugin::registerClass('PluginGenericobjectField');
   Plugin::registerClass('PluginGenericobjectType');
   Plugin::registerClass('PluginGenericobjectObject');
   Plugin::registerClass('PluginGenericobjectLink');

   $GENERICOBJECT_BLACKLISTED_FIELDS = array ("object_type", "table", "deleted", "id", "entities_id",
                                              "recursive", "is_template", "notes", "template_name");

   $GENERICOBJECT_AUTOMATICALLY_MANAGED_FIELDS = array ("id", "name", "notes", "entities_id",
                                                        "recursive", "is_template");

   $GENERICOBJECT_LINK_TYPES = array ('Computer', 'Software', 'SoftwareLicense', 'Monitor',
                                      'Printer', 'Peripheral', 'Phone', 'NetworkEquipment', 
                                      'Contract', 'Contact', 'Supplier', 'Entity');

   $GENERICOBJECT_PDF_TYPES = array ();
   
   $plugin = new Plugin;

   if ($plugin->isInstalled("genericobject") && $plugin->isActivated("genericobject")) {  
      //Include all constant's locales files
      foreach (glob(GLPI_ROOT . '/plugins/genericobject/fields/locales/*.php') as $file)
         include_once ($file);

      //Include all fields constants files
      foreach (glob(GLPI_ROOT . '/plugins/genericobject/fields/constants/*.php') as $file) 
         include_once ($file);
      
      include_once (GLPI_ROOT . "/plugins/genericobject/inc/field.constant.php");


      $PLUGIN_HOOKS['use_massive_action']['genericobject'] = 1;

      /* load changeprofile function */
      $PLUGIN_HOOKS['change_profile']['genericobject'] = array('PluginGenericobjectProfile', 'plugin_change_profile_genericobject');

      // Display a menu entry ?
      $PLUGIN_HOOKS['menu_entry']['genericobject']              = true;
      $PLUGIN_HOOKS['submenu_entry']['genericobject']['config'] = 'front/type.php';

      // Config page
      if (haveRight('config', 'w')) {
         $PLUGIN_HOOKS['config_page']['genericobject']                     = 'front/type.php';
         $PLUGIN_HOOKS['submenu_entry']['genericobject']['add']['type']    = 'front/type.form.php';
         $PLUGIN_HOOKS['submenu_entry']['genericobject']['search']['type'] = 'front/type.php';
      }

      $PLUGIN_HOOKS['assign_to_ticket']['genericobject'] = true;

      // Onglets management
      $PLUGIN_HOOKS['headings']['genericobject']         = 'plugin_get_headings_genericobject';
      $PLUGIN_HOOKS['headings_action']['genericobject']  = 'plugin_headings_actions_genericobject';

   $types = plugin_genericobject_getAllTypes();
   foreach ($types as $type => $params) {
      $sopt = plugin_genericobject_objectSearchOptions($params["name"],$sopt);
      Plugin::registerClass('PluginGenericobject'.strtoupper($params["name"]), 
                         array('classname'  => 'PluginGenericobject'.strtoupper($params["name"]),
                               'tablename'  => 'glpi_plugin_genericobject_'.$params["name"].'s',
                               'helpdesk_types'         => true,
                               'linkuser_types' => true
                               ));      
      }
      
      plugin_genericobject_registerNewTypes();
   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_genericobject() {
   global $LANG;
   return array ('name' => $LANG["genericobject"]["title"][1], 'version' => '1.2.0',
                 'author' => 'Alexandre Delaunay & Walid Nouh',
                 'homepage' => 'https://forge.indepnet.net/projects/show/genericobject',
                 'minGlpiVersion' => '0.78.0');
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_genericobject_check_prerequisites() {
   if (GLPI_VERSION >= '0.78') {
      return true;
   } else {
      echo "GLPI >= 0.78 is needed";
   }
}

// Check configuration process for plugin : need to return true if succeeded
// Can display a message only if failure and $verbose is true
function plugin_genericobject_check_config($verbose = false) {
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
   switch ($type) {
      case PLUGIN_GENERICOBJECT_TYPE :
         return haveRight("config", $right);
      default :
         return plugin_genericobject_haveRight(plugin_genericobject_getNameByID($type), $right);
   }

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
