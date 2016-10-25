<?php
/*
 This file is part of the genericobject plugin.

 Genericobject plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Genericobject plugin is distributed in the hope that it will be useful,
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

define ('PLUGIN_GENERICOBJECT_VERSION', '2.4.0');

if (!defined("GENERICOBJECT_DIR")) {
   define("GENERICOBJECT_DIR",GLPI_ROOT . "/plugins/genericobject");
}

if (!defined("GENERICOBJECT_DOC_DIR") ) {
   define("GENERICOBJECT_DOC_DIR", GLPI_PLUGIN_DOC_DIR . "/genericobject");
   if(!file_exists(GENERICOBJECT_DOC_DIR)) {
      mkdir(GENERICOBJECT_DOC_DIR);
   }
}
if (!defined("GENERICOBJECT_FRONT_PATH")) {
   define("GENERICOBJECT_FRONT_PATH", GENERICOBJECT_DOC_DIR."/front");
   if(!file_exists(GENERICOBJECT_FRONT_PATH)) {
      mkdir(GENERICOBJECT_FRONT_PATH);
   }
}
if (!defined("GENERICOBJECT_AJAX_PATH")) {
   define("GENERICOBJECT_AJAX_PATH", GENERICOBJECT_DOC_DIR . "/ajax");
   if(!file_exists(GENERICOBJECT_AJAX_PATH)) {
      mkdir(GENERICOBJECT_AJAX_PATH);
   }
}

if (!defined("GENERICOBJECT_CLASS_PATH")) {
   define("GENERICOBJECT_CLASS_PATH", GENERICOBJECT_DOC_DIR . "/inc");
   if(!file_exists(GENERICOBJECT_CLASS_PATH)) {
      mkdir(GENERICOBJECT_CLASS_PATH);
   }
}

if (!defined("GENERICOBJECT_LOCALES_PATH")) {
   define("GENERICOBJECT_LOCALES_PATH", GENERICOBJECT_DOC_DIR . "/locales");
   if(!file_exists(GENERICOBJECT_LOCALES_PATH)) {
      mkdir(GENERICOBJECT_LOCALES_PATH);
   }
}

if (!defined("GENERICOBJECT_FIELDS_PATH")) {
   define("GENERICOBJECT_FIELDS_PATH", GENERICOBJECT_DOC_DIR . "/fields");
   if(!file_exists(GENERICOBJECT_FIELDS_PATH)) {
      mkdir(GENERICOBJECT_FIELDS_PATH);
   }
}

if (!defined("GENERICOBJECT_PICS_PATH")) {
   define("GENERICOBJECT_PICS_PATH", GENERICOBJECT_DOC_DIR . "/pics");
   if(!file_exists(GENERICOBJECT_PICS_PATH)) {
      mkdir(GENERICOBJECT_PICS_PATH);
   }
}

// Autoload class generated in files/_plugins/genericobject/inc/
include_once( GENERICOBJECT_DIR . "/inc/autoload.php");
include_once( GENERICOBJECT_DIR . "/inc/functions.php");
if (file_exists(GENERICOBJECT_DIR . "/log_filter.settings.php") ){
   include_once(GENERICOBJECT_DIR . "/log_filter.settings.php");
}

$options = array(
   GENERICOBJECT_CLASS_PATH
);
$go_autoloader = new PluginGenericobjectAutoloader($options);
$go_autoloader->register();

// Init the hooks of the plugins -Needed
function plugin_init_genericobject() {
   global $PLUGIN_HOOKS, $CFG_GLPI, $GO_BLACKLIST_FIELDS, $GO_FIELDS,
          $GENERICOBJECT_PDF_TYPES, $GO_LINKED_TYPES, $GO_READONLY_FIELDS, $LOADED_PLUGINS;

   $GO_READONLY_FIELDS  = array ("is_helpdesk_visible", "comment");

   $GO_BLACKLIST_FIELDS = array ("itemtype", "table", "is_deleted", "id", "entities_id",
                                 "is_recursive", "is_template", "notepad", "template_name",
                                 "date_mod", "name", "is_helpdesk_visible", "comment",
                                 "date_creation");

   $GO_LINKED_TYPES     = array ('Computer', 'Phone', 'Peripheral', 'Software', 'Monitor',
                                  'Printer', 'NetworkEquipment');

   $PLUGIN_HOOKS['csrf_compliant']['genericobject'] = true;
   $GENERICOBJECT_PDF_TYPES                         = array ();
   $plugin                                          = new Plugin();

   if ($plugin->isInstalled("genericobject")
      && $plugin->isActivated("genericobject")
         && isset($_SESSION['glpiactiveprofile'])) {

      $PLUGIN_HOOKS['change_profile']['genericobject'] = array(
            'PluginGenericobjectProfile',
            'changeProfile'
      );

      plugin_genericobject_includeCommonFields();
      $PLUGIN_HOOKS['use_massive_action']['genericobject'] = 1;

      // add css styles
      $PLUGIN_HOOKS['add_css']['genericobject'] = array(
         "css/styles.css"
      );

      // Display a menu entry ?
      $PLUGIN_HOOKS['menu_toadd']['genericobject'] = array(
         'config' => 'PluginGenericobjectType',
         'assets' => 'PluginGenericobjectObject'
      );

      // Config page
      if (Session::haveRight('config', READ)) {
         $PLUGIN_HOOKS['config_page']['genericobject'] = 'front/type.php';
      }

      $PLUGIN_HOOKS['assign_to_ticket']['genericobject'] = true;
      $PLUGIN_HOOKS['use_massive_action']['genericobject'] = 1;

      $PLUGIN_HOOKS['post_init']['genericobject'] = 'plugin_post_init_genericobject';
      $PLUGIN_HOOKS['plugin_datainjection_populate']['genericobject'] = "plugin_datainjection_populate_genericobject";

   }
}

function plugin_post_init_genericobject() {
   global $GO_FIELDS;
   Plugin::registerClass(
      'PluginGenericobjectProfile',
      array('addtabon' => array(
         'Profile', 'PluginGenericobjectType'
      ))
   );

   foreach (PluginGenericobjectType::getTypes() as $id => $objecttype) {
      $itemtype = $objecttype['itemtype'];
      if (class_exists($itemtype)) {
         $itemtype::registerType();
      }

   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_genericobject() {
   return array ('name'           => __("Objects management", "genericobject"),
                 'version'        => PLUGIN_GENERICOBJECT_VERSION,
                 'author'         => "<a href=\"mailto:contact@teclib.com\">Teclib'</a> & <a href='http://www.siprossii.com/'>siprossii</a>",
                 'homepage'       => 'https://github.com/teclib/genericobject',
                 'license'        => 'GPLv2+',
                 'minGlpiVersion' => '0.85.3');
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_genericobject_check_prerequisites() {
   if (version_compare(GLPI_VERSION,'0.85.3','lt')) {
      echo "This plugin requires GLPI 0.85.3 or higher";
      return false;
   }
   return true;
}

// Check configuration process for plugin : need to return true if succeeded
// Can display a message only if failure and $verbose is true
function plugin_genericobject_check_config($verbose = false) {
   if (true) { // Your configuration check
      return true;
   }
   if ($verbose) {
      echo __('Installed / not configured');
   }
   return false;
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
   //Load genericobject default constants
   if (!$force) {
      include_once (GLPI_ROOT . "/plugins/genericobject/fields/field.constant.php");
   } else {
      include (GLPI_ROOT . "/plugins/genericobject/fields/field.constant.php");
   }

   //Include user constants, that must be accessible for all itemtypes
   if (file_exists(GENERICOBJECT_FIELDS_PATH . "/field.constant.php")) {
      if (!$force) {
         include_once ( GENERICOBJECT_FIELDS_PATH . "/field.constant.php");
      } else {
         include ( GENERICOBJECT_FIELDS_PATH . "/field.constant.php");
      }
   }
}

function plugin_genericobject_haveRight($class,$right) {

   $right_name = PluginGenericobjectProfile::getProfileNameForItemtype($class);
   return Session::haveRight($right_name, $right);

}
