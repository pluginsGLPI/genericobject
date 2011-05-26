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

   $sopt[5]['table'] = 'glpi_plugin_genericobject_types';
   $sopt[5]['field'] = 'status';
   $sopt[5]['linkfield'] = 'status';
   $sopt[5]['name'] = $LANG['joblist'][0];
   
   $types = plugin_genericobject_getAllTypes();
   
   foreach ($types as $type => $params)
      $sopt = plugin_genericobject_objectSearchOptions($params["name"],$sopt);
   
      
   return $sopt;

}


function plugin_genericobject_getSearchOption() {
   global $LANG;
   $sopt = array ();
   
   $types = plugin_genericobject_getAllTypes();
   
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
         PluginGenericobjectProfile::plugin_genericobject_createAccess($item->getField('id'));
               
         $prof = new PluginGenericobjectProfile();
         $prof->showForm($CFG_GLPI["root_doc"] . "/plugins/genericobject/front/profile.php", 
                         $item->getField('id'));
         break;
   }   
}

function plugin_genericobject_AssignToTicket($types){
   global $LANG;
   
   foreach (plugin_genericobject_getAllTypes() as $tmp => $value)
      if (plugin_genericobject_haveRight($value["name"].'_open_ticket',"1"))
         $types[plugin_genericobject_getObjectClassByName($value['itemtype'])] = 
            plugin_genericobject_getObjectLabel($value['name']);
      
   return $types;
}

// Define Dropdown tables to be manage in GLPI :
function plugin_genericobject_getDropdown() {
   $dropdowns = array();
   
   $plugin = new Plugin();
   if ($plugin->isActivated("genericobject"))
   {
      foreach (plugin_genericobject_getAllTypes() as $tmp => $values)
         PluginGenericobjectType::plugin_genericobject_getDropdownSpecific($dropdowns,$values);
   }

   return $dropdowns;   
}

// Define dropdown relations
function plugin_genericobject_getDatabaseRelations(){
   $dropdowns = array();

   $plugin = new Plugin();
   if ($plugin->isActivated("genericobject")) {
      foreach (plugin_genericobject_getAllTypes(true) as $tmp => $values) {
         PluginGenericobjectType::plugin_genericobject_getDatabaseRelationsSpecificDropdown($dropdowns,$values);
         if ($values["use_entity"]) {
            $dropdowns["glpi_entities"][plugin_genericobject_getObjectTableNameByName($values["name"])] = "entities_id";
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
   
   $types = plugin_genericobject_getAllTypes();
   foreach ($types as $tmp => $value) {
      $name = plugin_genericobject_getNameByID($value["itemtype"]);
      $fields = PluginGenericobjectField::plugin_genericobject_getFieldsByType($value["itemtype"]);
      foreach ($fields as $field => $object) {
         switch ($GENERICOBJECT_AVAILABLE_FIELDS[$field]['input_type']) {
               case 'date':
               case 'text':
                  $DATA_INJECTION_MAPPING[$value["itemtype"]][$field]['table'] = 
                     plugin_genericobject_getObjectTableNameByName($name);
                  $DATA_INJECTION_INFOS[$value["itemtype"]][$field]['table'] = 
                     plugin_genericobject_getObjectTableNameByName($name);
                  break;
               case 'dropdown' :
                  if (PluginGenericobjectType::plugin_genericobject_isDropdownTypeSpecific($field)) {
                     $DATA_INJECTION_MAPPING[$value["itemtype"]][$field]['table'] = 
                        PluginGenericobjectType::plugin_genericobject_getDropdownTableName($name,$field);
                     $DATA_INJECTION_INFOS[$value["itemtype"]][$field]['table'] = 
                        PluginGenericobjectType::plugin_genericobject_getDropdownTableName($name,$field);   
                  } else {
                      $DATA_INJECTION_MAPPING[$value["itemtype"]][$field]['table'] = 
                         $GENERICOBJECT_AVAILABLE_FIELDS[$field]['table'];
                      $DATA_INJECTION_INFOS[$value["itemtype"]][$field]['table'] = 
                         $GENERICOBJECT_AVAILABLE_FIELDS[$field]['table'];
                   }   
                     
                  break;
               case 'dropdown_yesno' :
                  $DATA_INJECTION_MAPPING[$value["itemtype"]][$field]['table'] = 
                     plugin_genericobject_getObjectTableNameByName($name);
                  $DATA_INJECTION_INFOS[$value["itemtype"]][$field]['table'] = 
                     plugin_genericobject_getObjectTableNameByName($name);
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
   $types = plugin_genericobject_getAllTypes();
   
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


?>
