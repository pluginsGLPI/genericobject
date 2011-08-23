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

function plugin_headings_actions_genericobject($item) {

   switch (get_class($item)) {
      case 'Profile' :
         return array (1 => "plugin_headings_genericobject");
         break;
   }
   return false;
}

function plugin_get_headings_genericobject($item, $withtemplate) {
   global $LANG;

   switch (get_class($item)) {
      case 'Profile':
         $prof = new Profile();
            return array(1 => $LANG["genericobject"]["title"][1]);
            break;
   }
   return false;
}

function plugin_headings_genericobject($item, $withtemplate) {
   switch (get_class($item)) {
      case 'Profile' :
         PluginGenericobjectProfile::createAccess($item->getID());
         $prof = new PluginGenericobjectProfile();
         $prof->showForm($item->getID());
         break;
   }
}

function plugin_genericobject_AssignToTicket($types) {
   foreach (PluginGenericobjectType::getTypes() as $tmp => $value) {
      if ($value['use_tickets'] && haveRight($value["itemtype"].'_open_ticket',"1")) {
         $types[$value['itemtype']] = call_user_func(array($value['itemtype'], 'getTypeName'));
      }
   }
   return $types;
}

// Define Dropdown tables to be manage in GLPI :
function plugin_genericobject_getDropdown() {
   $dropdowns = array();
   
   $plugin = new Plugin();
   if ($plugin->isActivated("genericobject")) {
      foreach (PluginGenericobjectType::getTypes() as $tmp => $values)
         PluginGenericobjectType::getDropdownSpecific($dropdowns,$values);
   }

   return $dropdowns;
}

// Define dropdown relations
function plugin_genericobject_getDatabaseRelations() {
   $dropdowns = array();
/*
   $plugin = new Plugin();
   if ($plugin->isActivated("genericobject")) {
      foreach (PluginGenericobjectType::getTypes(true) as $tmp => $values) {
         PluginGenericobjectType::getDatabaseRelationsSpecificDropdown($dropdowns,$values);
         if ($values["use_entity"]) {
            $dropdowns["glpi_entities"][PluginGenericobjectType::getTableByName($values["name"])] = "entities_id";
         }
      }
   }
*/
   return $dropdowns;
}

function plugin_uninstall_addUninstallTypes($uninstal_types = array()) {
   foreach (PluginGenericobjectType::getTypes() as $tmp => $type)
      if ($type["use_plugin_uninstall"]) {
         $uninstal_types[] = $type["itemtype"];
      }
   return $uninstal_types;
}

//----------------------- INSTALL / UNINSTALL FUNCTION -------------------------------//

function plugin_genericobject_install() {
   global $DB;
   
   //check directories rights
   if (!check_directories()) {
      return false;
   } 

   $migration = new Migration('0.80.0');
   
   foreach (array('PluginGenericobjectType', 'PluginGenericobjectProfile', 
                  'PluginGenericobjectField', 'PluginGenericobjectLink') as $itemtype) {
      if ($plug=isPluginItemType($itemtype)) {
         $plugname = strtolower($plug['plugin']);
         $dir      = GLPI_ROOT . "/plugins/$plugname/inc/";
         $item     = strtolower($plug['class']);
         if (file_exists("$dir$item.class.php")) {
            include_once ("$dir$item.class.php");
            call_user_func(array($itemtype,'install'), $migration);
         }
      }
   }

   if (!is_dir(GENERICOBJECT_CLASS_PATH)) {
      @ mkdir(GENERICOBJECT_CLASS_PATH, 0777, true) 
         or die("Can't create folder " . GENERICOBJECT_CLASS_PATH);
   }
   /*
   foreach (array(GENERICOBJECT_AJAX_PATH, GENERICOBJECT_CLASS_PATH, GENERICOBJECT_FRONT_PATH)
            as $path) {
      chown($path, 'www-data');
      chmod($path, 744);
   }*/

   //Init plugin & types
   plugin_init_genericobject();

   //Init profiles
   PluginGenericobjectProfile::changeProfile();
   return true;
}

function plugin_genericobject_uninstall() {
   global $DB;
   
   include_once(GLPI_ROOT."/plugins/genericobject/inc/type.class.php");

   //For each type
   foreach (PluginGenericobjectType::getTypes(true) as $tmp => $value) {
      call_user_func(array($value['itemtype'], 'uninstall'));
   }

   foreach (array('PluginGenericobjectType', 'PluginGenericobjectProfile', 
                  'PluginGenericobjectField', 'PluginGenericobjectLink') as $itemtype) {
      if ($plug=isPluginItemType($itemtype)) {
         $plugname = strtolower($plug['plugin']);
         $dir      = GLPI_ROOT . "/plugins/$plugname/inc/";
         $item     = strtolower($plug['class']);
         if (file_exists("$dir$item.class.php")) {
            include_once ("$dir$item.class.php");
            call_user_func(array($itemtype, 'uninstall'));
         }
      }
   }

   //plugin_init_genericobject();
   return true;
}

function check_directories() {
   global $LANG;
   
   foreach (array(GENERICOBJECT_AJAX_PATH, GENERICOBJECT_CLASS_PATH, GENERICOBJECT_FRONT_PATH, 
                  GENERICOBJECT_LOCALES_PATH) as $path) {
      if (!is_dir($path) || !is_writable($path)) {
         addMessageAfterRedirect($LANG['genericobject']['install'][0]);
         return false;
      }
   }
   return true;
}