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
class PluginGenericobjectType extends CommonDBTM {
   function __construct() {
      $this->table = "glpi_plugin_genericobject_types";
      $this->type = PLUGIN_GENERICOBJECT_TYPE;
      $this->dohistory = true;
   }
   
   function canCreate() {
      //return plugin_genericobject_haveRight('objecttype', 'w');
      return true;
   }

   function canView() {
      //return plugin_genericobject_haveRight('objecttype', 'r');
      return true;
   }

   function getFromDBByType($itemtype) {
      global $DB;
      $query = "SELECT * FROM `" . $this->table . "` WHERE itemtype='$itemtype'";
      $result = $DB->query($query);
      if ($DB->numrows($result) > 0)
         $this->fields = $DB->fetch_array($result);
   }

   function defineTabs($options=array()) {
      global $LANG;
      $ong = array ();
      $ong[1] = $LANG['title'][26];
      if (isset($this->fields['id']) && $this->fields['id'] > 0) {
         $ong[3] = $LANG['rulesengine'][12];
         //$ong[4] = $LANG['genericobject']['config'][4];
         $ong[5] = $LANG['genericobject']['config'][7];
         $ong[12] = $LANG['title'][38];
      }

      return $ong;
   }

   function showForm($ID, $options=array()) {
      global $LANG;
      if ($ID > 0) {
         $this->check($ID, 'r');
      } else {
         // Create item 
         $this->check(-1, 'w');
         $use_cache = false;
         $this->getEmpty();
      }
      
      $this->fields['id'] = $ID;
      
      $canedit = $this->can($ID, 'w');

      plugin_genericobject_includeLocales($this->fields["name"]);
      
      $options['colspan'] = 1;
      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . $LANG['genericobject']['common'][1] . "</td>";
      echo "<td>";
      if (!$ID) {
         autocompletionTextField($this, 'name', array('value' => $this->fields["name"]));
      }
      else {
         echo "<input type='hidden' name='name' value='" . $this->fields["name"] . "'>";
         echo $this->fields["name"];
      }

      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . $LANG['genericobject']['config'][9] . "</td>";
      echo "<td>";
      if ($ID)
         echo plugin_genericobject_getObjectLabel($this->fields["name"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . $LANG['common'][60] . "</td>";
      echo "<td>";
      if (!$ID) {
         echo $LANG['choice'][0];
      }
      else {
         Alert::dropdownYesNo(array('name'=>"status", 'value'=> $this->fields["status"]));
      }
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options);

      echo "<div id='tabcontent'></div>";
      echo "<script type='text/javascript'>loadDefaultTab();</script>";

   }

   function showBehaviourForm($target, $ID) {
      global $LANG;
      if ($ID > 0) {
         $this->check($ID, 'r');
      } else {
         // Create item 
         $this->check(-1, 'w');
         $use_cache = false;
         $this->getEmpty();
      }

      $canedit = $this->can($ID, 'w');
      echo "<form name='behaviour' method='post' action=\"$target\">";
      echo "<div class='center'>";
      echo "<table class='tab_cadre_fixe' >";
      echo "<tr class='tab_bg_1'><th colspan='2'>";
      echo $LANG['genericobject']['config'][3];
      echo "</th></tr>";

      $use = array (
         "use_entity" => $LANG['Menu'][37],
         "use_recursivity" => $LANG['entity'][9],
         "use_tickets" => $LANG['Menu'][31],
         "use_deleted" => $LANG['ocsconfig'][49],
         "use_notes" => $LANG['title'][37],
         "use_history" => $LANG['title'][38],
         "use_template" => $LANG['common'][14],
         "use_infocoms" => $LANG['financial'][3],
         "use_documents" => $LANG['Menu'][27],
         "use_loans" => $LANG['Menu'][17],
         "use_loans" => $LANG['Menu'][17],
         "use_network_ports" => $LANG['genericobject']['config'][14],
         "use_plugin_datainjection" => $LANG['genericobject']['config'][10],
         //"use_plugin_pdf"=>$LANG['genericobject']['config'][11],
         "use_plugin_order" => $LANG['genericobject']['config'][12],
         "use_plugin_uninstall" => $LANG['genericobject']['config'][13],
         "use_plugin_geninventorynumber"=>$LANG['genericobject']['config'][15]
      );

      if (GLPI_VERSION >= '0.72.3') {
         $use["use_direct_connections"] = $LANG['connect'][0];

      }

     $plugin = new Plugin;

      foreach ($use as $right => $label) {
         echo "<tr class='tab_bg_1'>";
         echo "<td>" . $LANG['genericobject']['config'][1] . " " . $label . "</td>";
         echo "<td>";

         switch ($right) {
            case 'use_recursivity' :
               if (!$this->fields['use_entity']) {
                  echo "<input type='hidden' name='use_recursivity' value='0'>\n";
                  echo $LANG['choice'][0];
               } else
                  //dropdownYesNo($right, $this->fields[$right]);
                  Alert::dropdownYesNo(array('name'=>$right,
                                    'value'=> $this->fields[$right]));
                  /*Alert::dropdownYesNo(array('name'=>"status",
                                    'value'=> $this->fields["status"]));*/
               break;
            case 'use_plugin_datainjection' :
               if ($plugin->isInstalled("datainjection") && $plugin->isActivated("datainjection")) {
                  //usePlugin("datainjection");
                  Plugin::load("datainjection");
                  $infos = plugin_version_datainjection();
                  if ($infos['version'] >= '1.7.0') {
                     Alert::dropdownYesNo(array('name'=>$right,
                                    'value'=> $this->fields[$right]));
                  }
               } else {
                  echo "<input type='hidden' name='use_plugin_datainjection' value='0'>\n";
               }
               break;
            case 'use_plugin_pdf' :
               if ($plugin->isInstalled("pdf") && $plugin->isActivated("pdf")) {
                  Alert::dropdownYesNo(array('name'=>$right,
                                    'value'=> $this->fields[$right]));
               }
                  
               else
                  echo "<input type='hidden' name='use_plugin_pdf' value='0'>\n";
               break;
            case 'use_plugin_order' :
               if ($plugin->isInstalled("order") && $plugin->isActivated("order")) {
                  Alert::dropdownYesNo(array('name'=>$right,
                                    'value'=> $this->fields[$right]));
               }
               else
                  echo "<input type='hidden' name='use_plugin_order' value='0'>\n";
               break;
            case 'use_plugin_geninventorynumber' :
               if ($plugin->isInstalled("geninventorynumber") && $plugin->isActivated("geninventorynumber")) {
                  $infos = plugin_version_geninventorynumber();
                  if ($infos['version'] >= '1.3.0') {
                     Alert::dropdownYesNo(array('name'=>$right,
                                    'value'=> $this->fields[$right]));
                  }
               }
               else
                  echo "<input type='hidden' name='use_plugin_geninventorynumber' value='0'>\n";
               break;
            case 'use_plugin_uninstall' :
               if ($plugin->isInstalled("uninstall") && $plugin->isActivated("uninstall")) {
                  //usePlugin("uninstall");
                  Plugin::load("uninstall");
                  $infos = plugin_version_uninstall();
                  if ($infos['version'] >= '1.2.1') {
                     Alert::dropdownYesNo(array('name'=>$right,
                                    'value'=> $this->fields[$right]));
                  }
               } else {
                  echo "<input type='hidden' name='use_plugin_uninstall' value='0'>\n";
               }

               break;
            default :
               //dropdownYesNo($right, $this->fields[$right]);
               Alert::dropdownYesNo(array('name'=>$right,
                                    'value'=> $this->fields[$right]));
               break;
         }
         echo "</td>";
         echo "</tr>";
      }

      if ($canedit) {
         echo "<tr>";
         echo "<td class='tab_bg_2' colspan='2' align='center'>";

         echo "<input type='hidden' name='id' value=\"$ID\">\n";
         echo "<input type='submit' name='update' value=\"" . $LANG['buttons'][7] . "\" class='submit'>";
         echo "</td>";
         echo "</tr>";
      }
   }

   function prepareInputForAdd($input) {
      $input["name"] = strtolower($input["name"]);
      $input['name'] = str_replace(' ', '', $input['name']);
      return $input;
   }

   function post_addItem() {
      //Add new type table
      plugin_genericobject_addTable(getPlural($this->input["name"]));

      //Write object class on the filesystem
      plugin_genericobject_addClassFile($this->input["name"], 
                                        plugin_genericobject_getObjectClassByName($this->input["name"]), 
                                        $this->input["name"]);

      //Create rights for this new object
      plugin_genericobject_createAccess($_SESSION["glpiactiveprofile"]["id"], true);

      //Add default field 'name' for the object
      plugin_genericobject_addNewField($this->input["name"], "name");

      //Add new link device table
      plugin_genericobject_addLinkTable(getPlural($this->input["name"]));

      plugin_change_profile_genericobject();
      return true;
   }

   function prepareInputForUpdate($input) {
      $this->getFromDB($input["id"]);
      if (isset ($input["status"]) && $input["status"]) {
         plugin_genericobject_registerOneType($this->fields);
      }

      return $input;
   }

   function post_updateItem($history = 1) {
      global $GENINVENTORYNUMBER_INVENTORY_TYPES;
      $this->checkNecessaryFieldsUpdate();
      if (in_array('use_plugin_geninventorynumber',$this->updates)) {
         if ($input['use_plugin_geninventorynumber']) {
            plugin_geninventorynumber_registerType($this->fields["itemtype"],'otherserial');
            array_push($GENINVENTORYNUMBER_INVENTORY_TYPES,$this->fields["itemtype"]);
         }
         else {
            plugin_geninventorynumber_unRegisterType($this->fields["itemtype"],'otherserial');
            unset($GENINVENTORYNUMBER_INVENTORY_TYPES[$this->fields["itemtype"]]);
         }
      }
   }

   function pre_deleteItem() {
      
      $this->getFromDB($this->fields["id"]);

      //Delete loans associated with this type
      plugin_genericobject_deleteLoans($this->fields["itemtype"]);

      //Delete all tables related to the type (dropdowns)
      plugin_genericobject_deleteSpecificDropdownTables($this->fields["itemtype"]);

      //Delete relation table
      plugin_genericobject_deleteLinkTable(getPlural($this->fields["itemtype"]));

      //Remove class from the filesystem
      plugin_genericobject_deleteClassFile($this->fields["name"]);

      //Delete profile informations associated with this type
      plugin_genericobject_deleteTypeFromProfile($this->fields["name"]);

      //Table type table in DB
      plugin_genericobject_deleteTable(getPlural($this->fields["name"]));

      //Remove fields from the type_fields table
      plugin_genericobject_deleteAllFieldsByType($this->fields["itemtype"]);

      plugin_genericobject_removeDataInjectionModels($this->fields["itemtype"]);
      return true;
   }

   function checkNecessaryFieldsUpdate() {
      $commonitem = new PluginGenericobjectObject($this->fields["itemtype"]);
      //$commonitem->setType($this->fields["itemtype"], true);

      if ($this->fields['use_network_ports'] && !$commonitem->getField('locations_id')) {
         
         plugin_genericobject_addNewField($this->fields["itemtype"], 'locations_id');
      }

      if ($this->fields['use_loans'] && !$commonitem->getField('locations_id')) {
         plugin_genericobject_addNewField($this->fields["itemtype"], 'locations_id');
         
      }

     if ($this->fields['use_plugin_geninventorynumber'] && !$commonitem->getField('otherserial')) {/***/
         plugin_genericobject_addNewField($this->fields["itemtype"], 'otherserial');
         
      }

      if ($this->fields['use_direct_connections']) { /***/
         foreach (array (
               'users_id',
               'groups_id',
               'states_id',
               'locations_id'
            ) as $field) {
            if (!$commonitem->getField($field)) {
               plugin_genericobject_addNewField($this->fields["itemtype"], $field);
            }
         }
      }
   }
   
   function getSearchOptions() {
      global $LANG;
      $sopt['common'] = $LANG["genericobject"]["title"][1];
   
      $sopt[1]['table']       = $this->getTable();
      $sopt[1]['field']       = 'name';
      $sopt[1]['linkfield']   = '';
      $sopt[1]['name']        = $LANG["common"][22];
      $sopt[1]['datatype']    = 'itemlink';

      $sopt[5]['table']       = $this->getTable();
      $sopt[5]['field']       = 'status';
      $sopt[5]['linkfield']   = 'status';
      $sopt[5]['name']        = $LANG['joblist'][0];
   
      $sopt[6]['table']       = $this->getTable();
      $sopt[6]['field']       = 'use_tickets';
      $sopt[6]['linkfield']   = '';
      $sopt[6]['name']        = $LANG['genericobject']['config'][1]." ".
                                                              $LANG['Menu'][31];
      $sopt[6]['datatype']    = 'bool';
   
      $sopt[7]['table']       = $this->getTable();
      $sopt[7]['field']       = 'use_deleted';
      $sopt[7]['linkfield']   = '';
      $sopt[7]['name']        = $LANG['genericobject']['config'][1]." ".
                                                              $LANG['ocsconfig'][49];
      $sopt[7]['datatype']    = 'bool';
   
      $sopt[8]['table']       = $this->getTable();
      $sopt[8]['field']       = 'use_notes';
      $sopt[8]['linkfield']   = '';
      $sopt[8]['name']        = $LANG['genericobject']['config'][1]." ".
                                                              $LANG['title'][37];
      $sopt[8]['datatype']    = 'bool';
   
      $sopt[9]['table']       = $this->getTable();
      $sopt[9]['field']       = 'use_history';
      $sopt[9]['linkfield']   = '';
      $sopt[9]['name']        = $LANG['genericobject']['config'][1]." ".
                                                              $LANG['title'][38];
      $sopt[9]['datatype']    = 'bool';
   
      $sopt[10]['table']      = $this->getTable();
      $sopt[10]['field']      = 'use_entity';
      $sopt[10]['linkfield']  = '';
      $sopt[10]['name']       = $LANG['genericobject']['config'][1]." ".
                                                              $LANG['Menu'][37];
      $sopt[10]['datatype']   = 'bool';
   
      $sopt[11]['table']      = $this->getTable();
      $sopt[11]['field']      = 'use_recursivity';
      $sopt[11]['linkfield']  = '';
      $sopt[11]['name']       = $LANG['genericobject']['config'][1]." ".
                                                              $LANG['entity'][9];
      $sopt[11]['datatype']   = 'bool';
   
      $sopt[12]['table']      = $this->getTable();
      $sopt[12]['field']      = 'use_template';
      $sopt[12]['linkfield']  = '';
      $sopt[12]['name']       = $LANG['genericobject']['config'][1]." ".
                                                               $LANG['common'][14];
      $sopt[12]['datatype']   = 'bool';
   
      $sopt[13]['table']      = $this->getTable();
      $sopt[13]['field']      = 'use_infocoms';
      $sopt[13]['linkfield']  = '';
      $sopt[13]['name']       = $LANG['genericobject']['config'][1]." ".
                                                              $LANG['financial'][3];
      $sopt[13]['datatype']   = 'bool';
   
      $sopt[14]['table']      = $this->getTable();
      $sopt[14]['field']      = 'use_documents';
      $sopt[14]['linkfield']  = '';
      $sopt[14]['name']       = $LANG['genericobject']['config'][1]." ".
                                                              $LANG['Menu'][27];
      $sopt[14]['datatype']   = 'bool';
   
      $sopt[15]['table']      = $this->getTable();
      $sopt[15]['field']      = 'use_loans';
      $sopt[15]['linkfield']  = '';
      $sopt[15]['name']       = $LANG['genericobject']['config'][1]." ".
                                                              $LANG['Menu'][17];
      $sopt[15]['datatype']   = 'bool';
      return $sopt;
   }
}
?>
