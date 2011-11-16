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

if (!defined("GENERICOBJECT_DIR")) {
   define("GENERICOBJECT_DIR",GLPI_ROOT . "/plugins/genericobject");
}
if (!defined("GENERICOBJECT_FRONT_PATH")) {
   define("GENERICOBJECT_FRONT_PATH", GENERICOBJECT_DIR."/front");
}
if (!defined("GENERICOBJECT_AJAX_PATH")) {
   define("GENERICOBJECT_AJAX_PATH", GENERICOBJECT_DIR . "/ajax");
}
if (!defined("GENERICOBJECT_CLASS_PATH")) {
   define("GENERICOBJECT_CLASS_PATH", GENERICOBJECT_DIR . "/inc");
}
if (!defined("GENERICOBJECT_LOCALES_PATH")) {
   define("GENERICOBJECT_LOCALES_PATH", GENERICOBJECT_DIR . "/locales");
}

// Init the hooks of the plugins -Needed
function plugin_init_genericobject() {
   global $PLUGIN_HOOKS, $LANG, $CFG_GLPI, $GO_BLACKLIST_FIELDS, $GO_FIELDS, 
          $GENERICOBJECT_PDF_TYPES;
          
   $GO_BLACKLIST_FIELDS = array ("itemtype", "table", "is_deleted", "id", "entities_id", 
                                 "is_recursive", "is_template", "notepad", "template_name", 
                                 "is_helpdesk_visible", "comment", "name", "date_mod");

   $GENERICOBJECT_PDF_TYPES = array ();
   $plugin = new Plugin();

   if ($plugin->isInstalled("genericobject") && $plugin->isActivated("genericobject")) {  

      //Load genericobject default constants
      include_once (GLPI_ROOT . "/plugins/genericobject/fields/field.constant.php");
      
      //Include user constants, that must be accessible for all itemtypes
      if (file_exists(GLPI_ROOT . "/plugins/genericobject/fields/myconstant.php")) {
         include_once (GLPI_ROOT . "/plugins/genericobject/fields/myconstant.php");
      }
      $PLUGIN_HOOKS['use_massive_action']['genericobject'] = 1;

      /* load changeprofile function */
      $PLUGIN_HOOKS['change_profile']['genericobject'] = array('PluginGenericobjectProfile', 
                                                               'changeProfile');

      // Display a menu entry ?
      $PLUGIN_HOOKS['menu_entry']['genericobject']              = true;
      $PLUGIN_HOOKS['submenu_entry']['genericobject']['config'] = 'front/type.php';

      //Do not display icon if not using the genericobject plugin
      if (isset($_GET['id']) &&  $_GET['id'] != ''
         && strpos($_SERVER['REQUEST_URI'], 
                     Toolbox::getItemTypeFormURL("PluginGenericobjectType")) !== false) {
         $url  = '/plugins/genericobject/index.php';
         $type = new PluginGenericobjectType();
         $type->getFromDB($_GET['id']);
         if ($type->fields['is_active']) {
            $url.= '?itemtypes_id='.$_GET['id'];
            $image = "<img src='".$CFG_GLPI["root_doc"]."/pics/stats_item.png' title=\"".
                      $LANG['genericobject']['common'][7].
                        "\" alt=\"".$LANG['genericobject']['common'][7]."\">";
            $PLUGIN_HOOKS['submenu_entry']['genericobject']['options']['type']['links'][$image] 
               = $url;
         }
      }
      $PLUGIN_HOOKS['submenu_entry']['genericobject']['options']['type']['links']['add']  
         = Toolbox::getItemTypeFormURL('PluginGenericobjectType', false);
      $PLUGIN_HOOKS['submenu_entry']['genericobject']['options']['type']['links']['search']  
         = Toolbox::getItemTypeSearchURL('PluginGenericobjectType', false);

      // Config page
      if (Session::haveRight('config', 'w')) {
         $PLUGIN_HOOKS['config_page']['genericobject']                     = 'front/type.php';
         $PLUGIN_HOOKS['submenu_entry']['genericobject']['add']['type']    = 'front/type.form.php';
         $PLUGIN_HOOKS['submenu_entry']['genericobject']['search']['type'] = 'front/type.php';
      }

      $PLUGIN_HOOKS['assign_to_ticket']['genericobject']   = true;
      $PLUGIN_HOOKS['use_massive_action']['genericobject'] = 1;

      // Onglets management
      $PLUGIN_HOOKS['headings']['genericobject']         = 'plugin_get_headings_genericobject';
      $PLUGIN_HOOKS['headings_action']['genericobject']  = 'plugin_headings_actions_genericobject';
      $PLUGIN_HOOKS['post_init']['genericobject']        = 'plugin_post_init_genericobject';
      $PLUGIN_HOOKS['plugin_datainjection_populate']['genericobject'] = "plugin_datainjection_populate_genericobject";
   }
}

function plugin_post_init_genericobject() {
   foreach (PluginGenericobjectType::getTypes() as $id => $objecttype) {
      call_user_func(array($objecttype['itemtype'], 'registerType'));
   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_genericobject() {
   global $LANG;
   return array ('name'           => $LANG["genericobject"]["title"][1], 
                 'version'        => '2.1.0',
                 'author'         => 'Alexandre Delaunay & Walid Nouh',
                 'homepage'       => 'https://forge.indepnet.net/projects/show/genericobject',
                 'license'        => 'GPLv2+',
                 'minGlpiVersion' => '0.83');
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_genericobject_check_prerequisites() {
   if (version_compare(GLPI_VERSION,'0.83','lt') || version_compare(GLPI_VERSION,'0.84','ge')) {
      echo "This plugin requires GLPI 0.83";
      return false;
   }
   if (version_compare(PHP_VERSION, '5.3.0', 'lt')) {
      echo "PHP 5.3.0 or higher is required";
      return false;
   }
   return true;
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

function plugin_genericobject_haveTypeRight($itemtype, $right) {
   switch ($itemtype) {
      case 'PluginGenericobjectType' :
         return Session::haveRight("config", $right);
      default :
         return Session::haveRight($itemtype, $right);
   }

}