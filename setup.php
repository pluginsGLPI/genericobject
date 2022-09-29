<?php

/**
 * -------------------------------------------------------------------------
 * GenericObject plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GenericObject.
 *
 * GenericObject is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * GenericObject is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GenericObject. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2009-2022 by GenericObject plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/genericobject
 * -------------------------------------------------------------------------
 */

define ('PLUGIN_GENERICOBJECT_VERSION', '2.14.0');

// Minimal GLPI version, inclusive
define("PLUGIN_GENERICOBJECT_MIN_GLPI", "10.0.0");
// Maximum GLPI version, exclusive
define("PLUGIN_GENERICOBJECT_MAX_GLPI", "10.0.99");

if (!defined("GENERICOBJECT_DIR")) {
   define("GENERICOBJECT_DIR", Plugin::getPhpDir("genericobject"));
}

if (!defined("GENERICOBJECT_DOC_DIR")) {
   define("GENERICOBJECT_DOC_DIR", GLPI_PLUGIN_DOC_DIR . "/genericobject");
   if (!file_exists(GENERICOBJECT_DOC_DIR)) {
      mkdir(GENERICOBJECT_DOC_DIR);
   }
}
if (!defined("GENERICOBJECT_FRONT_PATH")) {
   define("GENERICOBJECT_FRONT_PATH", GENERICOBJECT_DOC_DIR."/front");
   if (!file_exists(GENERICOBJECT_FRONT_PATH)) {
      mkdir(GENERICOBJECT_FRONT_PATH);
   }
}
if (!defined("GENERICOBJECT_AJAX_PATH")) {
   define("GENERICOBJECT_AJAX_PATH", GENERICOBJECT_DOC_DIR . "/ajax");
   if (!file_exists(GENERICOBJECT_AJAX_PATH)) {
      mkdir(GENERICOBJECT_AJAX_PATH);
   }
}

if (!defined("GENERICOBJECT_CLASS_PATH")) {
   define("GENERICOBJECT_CLASS_PATH", GENERICOBJECT_DOC_DIR . "/inc");
   if (!file_exists(GENERICOBJECT_CLASS_PATH)) {
      mkdir(GENERICOBJECT_CLASS_PATH);
   }
}

if (!defined("GENERICOBJECT_LOCALES_PATH")) {
   define("GENERICOBJECT_LOCALES_PATH", GENERICOBJECT_DOC_DIR . "/locales");
   if (!file_exists(GENERICOBJECT_LOCALES_PATH)) {
      mkdir(GENERICOBJECT_LOCALES_PATH);
   }
}

if (!defined("GENERICOBJECT_FIELDS_PATH")) {
   define("GENERICOBJECT_FIELDS_PATH", GENERICOBJECT_DOC_DIR . "/fields");
   if (!file_exists(GENERICOBJECT_FIELDS_PATH)) {
      mkdir(GENERICOBJECT_FIELDS_PATH);
   }
}

if (!defined("GENERICOBJECT_PICS_PATH")) {
   define("GENERICOBJECT_PICS_PATH", GENERICOBJECT_DOC_DIR . "/pics");
   if (!file_exists(GENERICOBJECT_PICS_PATH)) {
      mkdir(GENERICOBJECT_PICS_PATH);
   }
}

// Autoload class generated in files/_plugins/genericobject/inc/
include_once( GENERICOBJECT_DIR . "/inc/autoload.php");
include_once( GENERICOBJECT_DIR . "/inc/functions.php");
if (file_exists(GENERICOBJECT_DIR . "/log_filter.settings.php")) {
   include_once(GENERICOBJECT_DIR . "/log_filter.settings.php");
}

$go_autoloader = new PluginGenericobjectAutoloader([
   GENERICOBJECT_CLASS_PATH
]);
$go_autoloader->register();

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_genericobject() {
   global $PLUGIN_HOOKS, $GO_BLACKLIST_FIELDS,
          $GENERICOBJECT_PDF_TYPES, $GO_LINKED_TYPES, $GO_READONLY_FIELDS, $CFG_GLPI;

   $GO_READONLY_FIELDS  =  ["is_helpdesk_visible", "comment"];

   $GO_BLACKLIST_FIELDS =  ["itemtype", "table", "is_deleted", "id", "entities_id",
                            "is_recursive", "is_template", "notepad", "template_name",
                            "date_mod", "name", "is_helpdesk_visible", "comment",
                            "date_creation"];

   $GO_LINKED_TYPES     =  ['Computer', 'Phone', 'Peripheral', 'Software', 'Monitor',
                            'Printer', 'NetworkEquipment'];

   $PLUGIN_HOOKS['csrf_compliant']['genericobject'] = true;
   $GENERICOBJECT_PDF_TYPES                         =  [];

   if (Plugin::isPluginActive("genericobject") && isset($_SESSION['glpiactiveprofile'])) {

      //if treeview is installed
      if (Plugin::isPluginActive("treeview") && class_exists('PluginTreeviewConfig')) {

         //foreach type in genericobject
         foreach (PluginGenericobjectType::getTypes() as $itemtype => $value) {
            //check if location_id field exist
            $fields_in_db = PluginGenericobjectSingletonObjectField::getInstance($itemtype);
            $objecttype = PluginGenericobjectType::getInstance($itemtype);
            if (isset($fields_in_db['locations_id']) && $objecttype->canUsePluginTreeview()) {

               //register class
               PluginTreeviewConfig::registerType($itemtype);
               $PLUGIN_HOOKS['treeview'][$itemtype] = Plugin::getWebDir('genericobject') . '/pics/default-icon16.png';

               //add hook for overload item show form url
               $PLUGIN_HOOKS['treeview_params']['genericobject'] = [
                  'PluginGenericobjectObject',
                  'showGenericObjectTreeview'
               ];

               //add hook for overload search form url of itemtype
               $PLUGIN_HOOKS['treeview_search_url_parent_node']['genericobject'] = [
                  'PluginGenericobjectObject',
                  'getParentNodeSearchUrl'
               ];
            }
         }
      }

      $PLUGIN_HOOKS['change_profile']['genericobject'] = [
         'PluginGenericobjectProfile',
         'changeProfile'
      ];

      plugin_genericobject_includeCommonFields();
      $PLUGIN_HOOKS['use_massive_action']['genericobject'] = 1;

      // add css styles
      $PLUGIN_HOOKS['add_css']['genericobject'] = [
         "css/styles.css"
      ];

      // Display a menu entry ?
      $PLUGIN_HOOKS['menu_toadd']['genericobject'] = [
         'config' => 'PluginGenericobjectType',
         'assets' => 'PluginGenericobjectObject'
      ];

      // Config page
      if (Session::haveRight('config', READ)) {
         $PLUGIN_HOOKS['config_page']['genericobject'] = 'front/type.php';
      }

      $PLUGIN_HOOKS['assign_to_ticket']['genericobject'] = true;
      $PLUGIN_HOOKS['use_massive_action']['genericobject'] = 1;

      $PLUGIN_HOOKS['post_init']['genericobject'] = 'plugin_post_init_genericobject';
      $PLUGIN_HOOKS['plugin_datainjection_populate']['genericobject'] = "plugin_datainjection_populate_genericobject";

      $PLUGIN_HOOKS['formcreator_get_glpi_object_types']['genericobject'] = [
         PluginGenericobjectType::getType(),
         'getTypesForFormcreator'
      ];

      // Add every genericobject item's to the list of itemtypes for which the
      // impact analysis can be enabled
      foreach ((new PluginGenericobjectType())->find([]) as $row) {
         if (empty($row['impact_icon'])) {
            $icon = ""; // Will fallback to default impact icon
         } else {
            $icon = PluginGenericobjectType::getImpactIconFileStoragePath(
               $row['impact_icon'],
               $row['itemtype'],
               true
            ) ?? "";
         }

         $CFG_GLPI['impact_asset_types'][$row['itemtype']] = $icon;
      }
   }
}

function plugin_post_init_genericobject() {
   Plugin::registerClass(
      'PluginGenericobjectProfile',
      ['addtabon' => ['Profile', 'PluginGenericobjectType']]
   );

   foreach (PluginGenericobjectType::getTypes() as $id => $objecttype) {
      $itemtype = $objecttype['itemtype'];
      if (class_exists($itemtype)) {
         $itemtype::registerType();
      }

   }
}

/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_genericobject() {
   return [
      'name'           => __("Objects management", "genericobject"),
      'version'        => PLUGIN_GENERICOBJECT_VERSION,
      'author'         => "<a href=\"mailto:contact@teclib.com\">Teclib'</a> & siprossii",
      'homepage'       => 'https://github.com/pluginsGLPI/genericobject',
      'license'        => 'GPLv2+',
      'requirements'   => [
         'glpi' => [
            'min' => PLUGIN_GENERICOBJECT_MIN_GLPI,
            'max' => PLUGIN_GENERICOBJECT_MAX_GLPI,
            'dev' => true, //Required to allow 9.2-dev
          ]
       ]
   ];
}


function plugin_genericobject_haveTypeRight($itemtype, $right) {
   switch ($itemtype) {
      case 'PluginGenericobjectType' :
         return Session::haveRight("config", $right);
      default :
         return Session::haveRight($itemtype, $right);
   }

}

function plugin_genericobject_includeCommonFields($force = false) {
   global $GO_FIELDS, $LANG; // Permit missing global declaration in user files

   $includes = [
       sprintf('%s/fields/field.constant.php', GENERICOBJECT_DIR), // Default common fields constants
   ];

   // User locales for common fields
   if (
      isset($_SESSION['glpilanguage'])
      && file_exists($locale_file = sprintf('%s/fields.%s.php', GENERICOBJECT_LOCALES_PATH, $_SESSION['glpilanguage']))
   ) {
      $includes[] = $locale_file;
   } elseif (file_exists($locale_file = sprintf('%s/fields.%s.php', GENERICOBJECT_LOCALES_PATH, 'en_GB'))) {
      $includes[] = $locale_file;
   }

   // User common fields constants
   if (file_exists($fields_file = sprintf('%s/field.constant.php', GENERICOBJECT_FIELDS_PATH))) {
      $includes[] = $fields_file;
   }

   foreach ($includes as $include) {
      if (!$force) {
         include_once($include);
      } else {
         include($include);
      }
   }
}

function plugin_genericobject_haveRight($class, $right) {

   $right_name = PluginGenericobjectProfile::getProfileNameForItemtype($class);
   return Session::haveRight($right_name, $right);

}
