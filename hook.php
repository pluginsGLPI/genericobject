<?php
/*
 This file is part of the genericobject plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Genericobject. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   genericobject
 @author    the genericobject plugin team
 @copyright Copyright (c) 2010-2011 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/genericobject
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

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
      foreach(getAllDatasFromTable(getTableForItemType('PluginGenericobjectType'), 
                                   "`is_active`='1'") as $itemtype) {
         foreach(PluginGenericobjectType::getDropdownForItemtype($itemtype['itemtype']) as $table) {
            $dropdown_itemtype = getItemTypeForTable($table);
            $dropdowns[$dropdown_itemtype] = call_user_func(array($dropdown_itemtype, 
                                                                  'getTypeName')); 
         }
      }
   }
   return $dropdowns;
}

// Define dropdown relations
function plugin_genericobject_getDatabaseRelations() {
   $dropdowns = array();

   //TODO : purt here relations
/*
   $plugin = new Plugin();
   if ($plugin->isActivated("genericobject")) {
      foreach(getAllDatasFromTable(getTableForItemType('PluginGenericobjectType'), 
                                   "`is_active`='1'") as $itemtype) {
         foreach(PluginGenericobjectType::getDropdownForItemtype($itemtype) as $table) {
            $dropdowns[$table][] = array() 
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
                  'PluginGenericobjectField') as $itemtype) {
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
                  'PluginGenericobjectField') as $itemtype) {
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

function plugin_datainjection_populate_genericobject() {
   global $INJECTABLE_TYPES;
   $type = new PluginGenericobjectType();
   foreach($type->find("`use_plugin_datainjection`='1' AND `is_active`='1'") as $data) {
      if (class_exists($data ['itemtype']."Injection")) {
         $INJECTABLE_TYPES[$data ['itemtype']."Injection"] = 'genericobject';
      }
   }
}

function plugin_genericobject_MassiveActions($type) {
   global $LANG;
   $types = PluginGenericobjectType::getTypes();
   if (isset($types[$type])) {
      $objecttype = PluginGenericobjectType::getInstance($type);
      if ($objecttype->isTransferable()) {
         return array('plugin_genericobject_transfer' => $LANG['buttons'][48]);
      } else {
         return array();
      }
   } else {
      return array();
   }
}

function plugin_genericobject_MassiveActionsDisplay($options=array()) {
   global $LANG;

   $objecttype = PluginGenericobjectType::getInstance($options['itemtype']);
   switch ($options['action']) {
      case 'plugin_genericobject_transfer':
         if ($objecttype->isTransferable()) {
            Dropdown::show('Entity', array('name' => 'new_entity'));
            echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" . 
               $LANG['buttons'][2] . "\" >";
         }
         break;
   }
   return "";
}

function plugin_genericobject_MassiveActionsProcess($data) {
   global $LANG, $DB;

   switch ($data['action']) {
      case 'plugin_genericobject_transfer':
         $item = new $data['itemtype']();
         foreach ($data["item"] as $key => $val) {
            if ($val == 1) {
               $item->getFromDB($key);
               $item->transfer($_POST['new_entity']);
            }
         }
         break;
   }
}
/*
function plugin_genericobject_giveItem($type, $ID, $data, $num) {
   global $CFG_GLPI, $LANG;

   $searchopt = &Search::getOptions($type);
   $table = $searchopt[$ID]["table"];
   $field = $searchopt[$ID]["field"];

   $genericobjecttype = new PluginGenericobjectType();
   $genericobjecttype->getFromDB($data['id']);

   switch ($table . '.' . $field) {
      // display associated items with order 
      case "glpi_plugin_genericobject_types.use_deleted" :
         Drodpdown::getYesNo($genericobjecttype->canBeDeleted());
         break;
      case "glpi_plugin_genericobject_types.use_entity" :
         Drodpdown::getYesNo($genericobjecttype->canBeEntityAssigned());
         break;
      case "glpi_plugin_genericobject_types.use_recursivity" :
         Drodpdown::getYesNo($genericobjecttype->canBeRecursive());
         break;
      case "glpi_plugin_genericobject_types.use_template" :
         Drodpdown::getYesNo($genericobjecttype->canUseTemplate());
         break;
      case "glpi_plugin_genericobject_types.use_notes" :
         Drodpdown::getYesNo($genericobjecttype->canUseNotepad());
         break;
   }
   return "";
}

function plugin_genericobject_addSelect($type, $ID, $num) {

   $searchopt = &Search::getOptions($type);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];
   
   $out = "`$table`.`$field` AS `ITEM_$num`, ";
   switch ($table.".".$field) {
      case "glpi_plugin_genericobject_types.use_deleted" :
      case "glpi_plugin_genericobject_types.use_entity" :
      case "glpi_plugin_genericobject_types.use_recursivity" :
      case "glpi_plugin_genericobject_types.use_template" :
      case "glpi_plugin_genericobject_types.use_notes" :
         $out = '';
         break;
   }
   logDebug($field, $out);
   return $out;
}*/