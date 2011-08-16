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

function plugin_genericobject_getAddSearchOptions($itemtype) {
   global $LANG;
   $sopt = array ();
   
   $sopt[1]['table'] = 'glpi_plugin_genericobject_types';
   $sopt[1]['field'] = 'name';
   $sopt[1]['linkfield'] = '';
   $sopt[1]['name'] = $LANG["common"][22];
   $sopt[1]['datatype']='itemlink';
 
   $types = PluginGenericobjectType::getTypes();
   
   foreach ($types as $type => $params)
      $sopt = plugin_genericobject_objectSearchOptions($params["name"],$sopt);
   
      
   return $sopt;

}


function plugin_genericobject_getSearchOption() {
   global $LANG;
   $sopt = array ();
   
   $types = PluginGenericobjectType::getTypes();
   
   foreach ($types as $type => $params)
      $sopt = plugin_genericobject_objectSearchOptions($params["name"],$sopt);
      
   return $sopt;

}

function plugin_headings_actions_genericobject($item) {

   switch (get_class($item)) {
      case PROFILE_TYPE :
         return array (1 => "plugin_headings_genericobject");
         break;
   }
   return false;
}

function plugin_get_headings_genericobject($item, $withtemplate) {
   global $LANG;

   switch (get_class($item)) {
      case PROFILE_TYPE:
         $prof = new Profile();
            return array(1 => $LANG["genericobject"]["title"][1]);
            break;
   }
   return false;
}

function plugin_headings_genericobject($item, $withtemplate) {
   global $CFG_GLPI,$LANG;
   switch (get_class($item)) {
      case PROFILE_TYPE :
         $profile = new profile;
         $profile->getFromDB($item->getField('id'));
         PluginGenericobjectProfile::createAccess($item->getField('id'));
               
         $prof = new PluginGenericobjectProfile();
         $prof->showForm($CFG_GLPI["root_doc"] . "/plugins/genericobject/front/profile.php", 
                         $item->getField('id'));
         break;
   }   
}

function plugin_genericobject_AssignToTicket($types){
   global $LANG;
   
   foreach (PluginGenericobjectType::getTypes() as $tmp => $value)
      if (PluginGenericobjectProfile::haveRight($value["name"].'_open_ticket',"1"))
         $types['PluginGenericobject'.ucfirst($value['itemtype'])] = PluginGenericobjectObject::getLabel($value['name']);
   return $types;
}

// Define Dropdown tables to be manage in GLPI :
function plugin_genericobject_getDropdown() {
   $dropdowns = array();
   
   $plugin = new Plugin();
   if ($plugin->isActivated("genericobject"))
   {
      foreach (PluginGenericobjectType::getTypes() as $tmp => $values)
         PluginGenericobjectType::getDropdownSpecific($dropdowns,$values);
   }

   return $dropdowns;
}

// Define dropdown relations
function plugin_genericobject_getDatabaseRelations(){
   $dropdowns = array();

   $plugin = new Plugin();
   if ($plugin->isActivated("genericobject")) {
      foreach (PluginGenericobjectType::getTypes(true) as $tmp => $values) {
         PluginGenericobjectType::getDatabaseRelationsSpecificDropdown($dropdowns,$values);
         if ($values["use_entity"]) {
            $dropdowns["glpi_entities"][PluginGenericobjectType::getTableByName($values["name"])] = "entities_id";
         }
      }
         
   }

   return $dropdowns;   
}

/**
 * Integration with datainjection plugin
 */
function plugin_genericobject_datainjection_variables()
{
   global $DATA_INJECTION_MAPPING,$DATA_INJECTION_INFOS, $GENERICOBJECT_AVAILABLE_FIELDS,
          $SEARCH_OPTION;
   
   $types = PluginGenericobjectType::getTypes();
   foreach ($types as $tmp => $value) {
      $name = PluginGenericobjectType::getNameByID($value["itemtype"]);
      $fields = PluginGenericobjectField::getFieldsByType($value["itemtype"]);
      foreach ($fields as $field => $object) {
         switch ($GENERICOBJECT_AVAILABLE_FIELDS[$field]['input_type']) {
               case 'date':
               case 'text':
                  $DATA_INJECTION_MAPPING[$value["itemtype"]][$field]['table'] = 
                     PluginGenericobjectType::getTableByName($name);
                  $DATA_INJECTION_INFOS[$value["itemtype"]][$field]['table'] = 
                     PluginGenericobjectType::getTableByName($name);
                  break;
               case 'dropdown' :
                  if (PluginGenericobjectType::isDropdownTypeSpecific($field)) {
                     $DATA_INJECTION_MAPPING[$value["itemtype"]][$field]['table'] = 
                        PluginGenericobjectType::getDropdownTableName($name,$field);
                     $DATA_INJECTION_INFOS[$value["itemtype"]][$field]['table'] = 
                        PluginGenericobjectType::getDropdownTableName($name,$field);   
                  } else {
                      $DATA_INJECTION_MAPPING[$value["itemtype"]][$field]['table'] = 
                         $GENERICOBJECT_AVAILABLE_FIELDS[$field]['table'];
                      $DATA_INJECTION_INFOS[$value["itemtype"]][$field]['table'] = 
                         $GENERICOBJECT_AVAILABLE_FIELDS[$field]['table'];
                   }   
                     
                  break;
               case 'dropdown_yesno' :
                  $DATA_INJECTION_MAPPING[$value["itemtype"]][$field]['table'] = 
                     PluginGenericobjectType::getTableByName($name);
                  $DATA_INJECTION_INFOS[$value["itemtype"]][$field]['table'] = 
                     PluginGenericobjectType::getTableByName($name);
                  break;
         }
            
         $DATA_INJECTION_MAPPING[$value["itemtype"]][$field]['name'] = 
            $GENERICOBJECT_AVAILABLE_FIELDS[$field]['name'];
         $DATA_INJECTION_MAPPING[$value["itemtype"]][$field]['field'] = 
            $GENERICOBJECT_AVAILABLE_FIELDS[$field]['field'];
         $DATA_INJECTION_MAPPING[$value["itemtype"]][$field]['type'] = 
            (isset($GENERICOBJECT_AVAILABLE_FIELDS[$field]['input_type'])?
               $GENERICOBJECT_AVAILABLE_FIELDS[$field]['input_type']:'text');

         $DATA_INJECTION_INFOS[$value["itemtype"]][$field]['name'] = 
            $GENERICOBJECT_AVAILABLE_FIELDS[$field]['name'];
         $DATA_INJECTION_INFOS[$value["itemtype"]][$field]['field'] = 
            $GENERICOBJECT_AVAILABLE_FIELDS[$field]['field'];
         $DATA_INJECTION_INFOS[$value["itemtype"]][$field]['input_type'] = 
            (isset($GENERICOBJECT_AVAILABLE_FIELDS[$field]['input_type'])?
               $GENERICOBJECT_AVAILABLE_FIELDS[$field]['input_type']:'text');

      }   
   }
}

function plugin_uninstall_addUninstallTypes($uninstal_types)
{
   /*
   $types = PluginGenericobjectType::getTypes();
   
   foreach ($types as $tmp => $type)
      if ($type["use_plugin_uninstall"])
         $uninstal_types[] = $type["itemtype"];
   */
   return $uninstal_types;      
}

function plugin_genericobject_giveItem($itemtype,$ID,$data,$num,$meta=0) {
   $searchopt=&Search::getOptions($itemtype);
   
   $NAME="ITEM_";
   if ($meta) {
      $NAME="META_";
   }
   $table=$searchopt[$ID]["table"];
   $field=$searchopt[$ID]["field"];
   $linkfield=$searchopt[$ID]["linkfield"];
   
   if ($table == "glpi_plugin_genericobject_types") {
      return;
   }
   $out = "";
   //echo $field;
   switch ($field) {
      case 'name':   
         $out  = "<a id='ticket".$data[$NAME.$num]."' href=\"object.form.php?id=".$data['id'];
         $out .= "\">".$data[$NAME.$num];
         $out .= "</a>";
         break;
   }
   
   return $out;
}


/**
 * Add search options for an object type
 * @param name the internal object name
 * @return an array with all search options
 */
function plugin_genericobject_objectSearchOptions($name, $search_options = array ()) {
   global $DB, $GENERICOBJECT_AVAILABLE_FIELDS, $LANG;

   $table = PluginGenericobjectType::getTableByName($name);

   if (TableExists($table)) {
      $type = PluginGenericobjectType::getIdentifierByName($name);
      $ID = PluginGenericobjectType::getIDByName($name);
      $fields = $DB->list_fields($table);
      $i = 5000;

      $search_options[80]['table'] = 'glpi_entities';
      $search_options[80]['field'] = 'completename';
      $search_options[80]['linkfield'] = 'entities_id';
      $search_options[80]['name'] = $LANG["entity"][0];     

      $search_options[4030]['table'] = $table;
      $search_options[4030]['field'] = 'id';
      $search_options[4030]['linkfield'] = '';
      $search_options[4030]['name'] = $LANG["common"][2];

      if (!empty ($fields)) {
         $search_options['common'] = PluginGenericobjectObject::getLabel($name);
         foreach ($fields as $field_values) {
            $field_name = $field_values['Field'];
            if (isset ($GENERICOBJECT_AVAILABLE_FIELDS[$field_name])) {
               $search_options[$i]['linkfield'] = '';

               switch ($GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['input_type']) {
                  case 'date' :
                  case 'text' :
                  case 'multitext' :
                  case 'integer' :
                     $search_options[$i]['table'] = PluginGenericobjectType::getTableByName($name);
                     break;
                  case 'dropdown' :
                     if (PluginGenericobjectType::isDropdownTypeSpecific($field_name))
                        $search_options[$i]['table'] = PluginGenericobjectType::getDropdownTableName($name, $field_name);
                     else
                        $search_options[$i]['table'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['table'];

                     $search_options[$i]['linkfield'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['linkfield'];
                     break;
                  case 'dropdown_yesno' :
                  case 'dropdown_global' :
                     $search_options[$i]['table'] = PluginGenericobjectType::getTableByName($name);
                     $search_options[$i]['linkfield'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['linkfield'];
                     break;
               }
               
               $search_options[$i]['field'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['field'];
               $search_options[$i]['name'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['name'];
               if (isset ($GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['datatype']))
                  $search_options[$i]['datatype'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['datatype'];

               $i++;
            }

         }
      }

   }
   return $search_options;
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

   if (!is_dir(GENERICOBJECT_CLASS_PATH))
      @ mkdir(GENERICOBJECT_CLASS_PATH, 0777, true) 
         or die("Can't create folder " . GENERICOBJECT_CLASS_PATH);

   //Init plugin & types
   plugin_init_genericobject();

   //Init profiles
   PluginGenericobjectProfile::plugin_change_profile_genericobject();
   return true;
}

function plugin_genericobject_uninstall() {
   global $DB;

/*
   //Delete search display preferences
   $query = "DELETE FROM `glpi_displaypreferences` WHERE `itemtype`='4850';";
   $DB->query($query);
*/
   //For each type
   foreach (PluginGenericobjectType::getTypes(true) as $tmp => $value) {
      //Delete all tables and files related to the type (dropdowns)
      PluginGenericobjectType::deleteSpecificDropdownFiles($value["itemtype"]);
      PluginGenericobjectType::deleteSpecificDropdownTables($value["itemtype"]);

      //Delete loans
      PluginGenericobjectType::deleteLoans($value["itemtype"]);

      //Delete if exists datainjection models
      PluginGenericobjectType::removeDataInjectionModels($value["itemtype"]);

      PluginGenericobjectType::deleteNetworking($value["itemtype"]);

      //Delete search display preferences
      $query = "DELETE FROM `glpi_displaypreferences` WHERE `itemtype`='" . $value["itemtype"] . "';";
      $DB->query($query);

      //Delete link tables
      $link_tables = array ("glpi_infocoms", "glpi_reservationitems", "glpi_documents_items",
                            "glpi_contracts_items",  "glpi_bookmarks", "glpi_logs");
      foreach ($link_tables as $link_table) {
         $query = "DELETE FROM `" . $link_table . "` WHERE  `itemtype`='" . 
                  $value["itemtype"] . "';";
         $DB->query($query);
      }

      //Drop itemtype link table
      PluginGenericobjectType::deleteLinkTable($value["itemtype"]);
      
      //Drop type table
      $query = "DROP TABLE IF EXISTS `" .
      PluginGenericobjectType::getTableNameByName($value["name"]) . "`";
      $DB->query($query);
      
      if (file_exists(GENERICOBJECT_CLASS_PATH . "/".$value["itemtype"].".class.php"))
         unlink(GENERICOBJECT_CLASS_PATH . "/".$value["itemtype"].".class.php"); 
         
      //Remove class from the filesystem
      PluginGenericobjectType::deleteClassFile($value["itemtype"]);
      
   }

   foreach (array('PluginGenericobjectType', 'PluginGenericobjectProfile', 
                  'PluginGenericobjectField', 'PluginGenericobjectLink') as $itemtype) {
      if ($plug=isPluginItemType($itemtype)) {
         $plugname = strtolower($plug['plugin']);
         $dir      = GLPI_ROOT . "/plugins/$plugname/inc/";
         $item     = strtolower($plug['class']);
         if (file_exists("$dir$item.class.php")) {
            include_once ("$dir$item.class.php");
            call_user_func(array($itemtype,'uninstall'));
         }
      }
   }

   //plugin_init_genericobject();
   return true;
}

function check_directories() {
   global $LANG;
   
   if (!is_writable(GENERICOBJECT_DIR.'/inc/') 
         || !is_writable(GENERICOBJECT_DIR.'/front/') 
            || !is_writable(GENERICOBJECT_DIR.'/ajax/')) {
      addMessageAfterRedirect($LANG['genericobject']['install'][0]);
      return false;
   } else {
      return true;
   }
}