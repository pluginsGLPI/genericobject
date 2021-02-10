<?php
/*
 -------------------------------------------------------------------------
 Genericobject plugin for GLPI
 Copyright (C) 2016 by the Genericobject Development Team.

 https://github.com/pluginsGLPI/genericobject
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Genericobject.

 {NAME} is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Genericobject is distributed in the hope that it will be useful,
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
 ----------------------------------------------------------------------
 */

function plugin_genericobject_AssignToTicket($types) {
   foreach (PluginGenericobjectType::getTypes() as $tmp => $value) {
      $itemtype = $value['itemtype'];
      if ($value['use_tickets']) {
         if (class_exists($itemtype)) {
            $types[$itemtype] = $itemtype::getTypeName();
         } else {
            $types[$itemtype] = $itemtype;
         }
      }
   }
   return $types;
}

// Define Dropdown tables to be manage in GLPI :
function plugin_genericobject_getDropdown() {

   $dropdowns = ['PluginGenericobjectTypeFamily' => PluginGenericobjectTypeFamily::getTypeName(2)];

   $plugin = new Plugin();
   if ($plugin->isActivated("genericobject")) {
      foreach (PluginGenericobjectType::getTypes() as $type) {
         //_log($idx, var_export($type, true));
         $itemtype = $type['itemtype'];
         PluginGenericobjectType::registerOneType($itemtype);
         foreach (PluginGenericobjectType::getDropdownForItemtype($itemtype) as $table) {
            $dropdown_itemtype = getItemTypeForTable($table);
            if (class_exists( $dropdown_itemtype)) {
               $dropdowns[$dropdown_itemtype] = $dropdown_itemtype::getTypeName();
            }
         }
      }
   }
   return $dropdowns;
}

// Define dropdown relations
function plugin_genericobject_getDatabaseRelations() {
   $dropdowns = [];

   //TODO : purt here relations
   /*
   $plugin = new Plugin();
   if ($plugin->isActivated("genericobject")) {
      foreach(getAllDatasFromTable(getTableForItemType('PluginGenericobjectType'),
                                   "`is_active`='1'") as $itemtype) {
         foreach(PluginGenericobjectType::getDropdownForItemtype($itemtype) as $table) {
            $dropdowns[$table][] = []
         }
      }
   }
   */
   return $dropdowns;
}

function plugin_uninstall_addUninstallTypes($uninstal_types = []) {
   foreach (PluginGenericobjectType::getTypes() as $tmp => $type) {
      if ($type["use_plugin_uninstall"]) {
         $uninstal_types[] = $type["itemtype"];
      }
   }
   return $uninstal_types;
}

//----------------------- INSTALL / UNINSTALL FUNCTION -------------------------------//

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_genericobject_install() {
   global $DB;

   include_once(GENERICOBJECT_DIR."/inc/object.class.php");
   include_once(GENERICOBJECT_DIR."/inc/type.class.php");

   $migration = new Migration(PLUGIN_GENERICOBJECT_VERSION);

   foreach ([
      'PluginGenericobjectField',
      'PluginGenericobjectCommonDropdown',
      'PluginGenericobjectCommonTreeDropdown',
      'PluginGenericobjectProfile',
      'PluginGenericobjectType',
      'PluginGenericobjectTypeFamily'
   ] as $itemtype) {
      if ($plug=isPluginItemType($itemtype)) {
         $plugname = strtolower($plug['plugin']);
         $dir      = Plugin::getPhpDir($plugname)."/inc/";
         $item     = strtolower($plug['class']);
         if (file_exists("$dir$item.class.php")) {
            include_once ("$dir$item.class.php");
            if (method_exists($itemtype, 'install')) {
               $itemtype::install($migration);
            }
         }
      }
   }

   if (!is_dir(GENERICOBJECT_CLASS_PATH)) {
      @ mkdir(GENERICOBJECT_CLASS_PATH, 0755, true)
         or die("Can't create folder " . GENERICOBJECT_CLASS_PATH);
   }

   //Init plugin & types
   plugin_init_genericobject();

   //Init profiles
   PluginGenericobjectProfile::changeProfile();
   return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_genericobject_uninstall() {
   global $DB;

   include_once(GENERICOBJECT_DIR."/inc/object.class.php");
   include_once(GENERICOBJECT_DIR."/inc/type.class.php");

   //For each type
   foreach (PluginGenericobjectType::getTypes(true) as $tmp => $value) {
      $itemtype = $value['itemtype'];
      if (class_exists($itemtype)) {
         $itemtype::uninstall();
      }
   }

   foreach ([
      'PluginGenericobjectType',
      'PluginGenericobjectProfile',
      'PluginGenericobjectField',
      'PluginGenericobjectTypeFamily'
   ] as $itemtype) {
      if ($plug=isPluginItemType($itemtype)) {
         $plugname = strtolower($plug['plugin']);
         $dir      = Plugin::getPhpDir($plugname)."/inc/";
         $item     = strtolower($plug['class']);
         if (file_exists("$dir$item.class.php")) {
            include_once ("$dir$item.class.php");
            $itemtype::uninstall();
         }
      }
   }

   // Delete all models of datainjection about genericobject
   $table_datainjection_model = 'glpi_plugin_datainjection_models';
   if ($DB->tableExists($table_datainjection_model)) {
      $DB->query("DELETE FROM $table_datainjection_model WHERE itemtype LIKE 'PluginGenericobject%'");
   }

   // Invalidate menu data in current session
   unset($_SESSION['glpimenu']);

   return true;
}

function plugin_datainjection_populate_genericobject() {
   global $INJECTABLE_TYPES;
   $type = new PluginGenericobjectType();
   foreach ($type->find(['use_plugin_datainjection' => 1, 'is_active' => 1]) as $data) {
      if (class_exists($data ['itemtype']."Injection")) {
         $INJECTABLE_TYPES[$data ['itemtype']."Injection"] = 'genericobject';
      }
   }
}

function plugin_genericobject_MassiveActions($type) {
   $types = PluginGenericobjectType::getTypes();
   if (isset($types[$type])) {
      $objecttype = PluginGenericobjectType::getInstance($type);
      if ($objecttype->isTransferable()) {
         return ['PluginGenericobjectObject'.
         MassiveAction::CLASS_ACTION_SEPARATOR.'plugin_genericobject_transfer' => __("Transfer")];
      } else {
         return [];
      }
   } else {
      return [];
   }
}
