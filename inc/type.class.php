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
      $odd=0;
      foreach ($use as $right => $label) {
         $odd++;
         echo "<tr class='tab_bg_".(($odd%2)+1)."'>";
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
               if ($plugin->isInstalled("geninventorynumber") 
                      && $plugin->isActivated("geninventorynumber")) {
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
         echo "<input type='submit' name='update' value=\"" . $LANG['buttons'][7] . 
                  "\" class='submit'>";
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
      self::plugin_genericobject_addTable(getPlural($this->input["name"]));

      //Write object class on the filesystem
      self::plugin_genericobject_addClassFile($this->input["name"], 
                                        plugin_genericobject_getObjectClassByName($this->input["name"]), 
                                        $this->input["name"]);

	  //Write the form on the filesystem
      self::plugin_genericobject_addFormFile($this->input["name"], 
                                        plugin_genericobject_getObjectClassByName($this->input["name"]), 
                                        $this->input["name"]);
	  
      //Create rights for this new object
      PluginGenericobjectProfile::plugin_genericobject_createAccess($_SESSION["glpiactiveprofile"]["id"], true);

      //Add default field 'name' for the object
      PluginGenericobjectField::plugin_genericobject_addNewField($this->input["name"], "name");

      //Add new link device table
      self::plugin_genericobject_addLinkTable(getPlural($this->input["name"]));

      PluginGenericobjectProfile::plugin_change_profile_genericobject();
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
      /*
      if (in_array('use_plugin_geninventorynumber',$this->updates)) {
         if ($input['use_plugin_geninventorynumber']) {
            plugin_geninventorynumber_registerType($this->fields["itemtype"],'otherserial');
            array_push($GENINVENTORYNUMBER_INVENTORY_TYPES,$this->fields["itemtype"]);
         }
         else {
            plugin_geninventorynumber_unRegisterType($this->fields["itemtype"],'otherserial');
            unset($GENINVENTORYNUMBER_INVENTORY_TYPES[$this->fields["itemtype"]]);
         }
      }*/
   }

   function pre_deleteItem() {
      
      $this->getFromDB($this->fields["id"]);
      //Delete relation table
      self::plugin_genericobject_deleteLinkTable($this->fields["itemtype"]);

      //Delete all tables and files related to the type (dropdowns)
      self::plugin_genericobject_deleteSpecificDropdownTables($this->fields["itemtype"]);
      PluginGenericobjectType::plugin_genericobject_deleteSpecificDropdownFiles($this->fields["itemtype"]);
      
      //Delete loans associated with this type
      self::plugin_genericobject_deleteLoans($this->fields["itemtype"]);

      //Remove class from the filesystem
      self::plugin_genericobject_deleteClassFile($this->fields["name"]);

      //Remove form from the filesystem
      self::plugin_genericobject_deleteFormFile($this->fields["name"]);

      //Delete profile informations associated with this type
      PluginGenericobjectProfile::plugin_genericobject_deleteTypeFromProfile($this->fields["name"]);

      //Table type table in DB
      self::plugin_genericobject_deleteTable(getPlural($this->fields["name"]));

      //Remove fields from the type_fields table
      PluginGenericobjectField::plugin_genericobject_deleteAllFieldsByType($this->fields["itemtype"]);

      self::plugin_genericobject_removeDataInjectionModels($this->fields["itemtype"]);
      return true;
   }

   function checkNecessaryFieldsUpdate() {
      $commonitem = new PluginGenericobjectObject($this->fields["itemtype"]);
      //$commonitem->setType($this->fields["itemtype"], true);

      if ($this->fields['use_network_ports'] && !$commonitem->getField('locations_id')) {
         
         PluginGenericobjectField::plugin_genericobject_addNewField($this->fields["itemtype"], 'locations_id');
      }

      if ($this->fields['use_loans'] && !$commonitem->getField('locations_id')) {
         PluginGenericobjectField::plugin_genericobject_addNewField($this->fields["itemtype"], 'locations_id');
         
      }

     if ($this->fields['use_plugin_geninventorynumber'] 
            && !$commonitem->getField('otherserial')) {
         PluginGenericobjectField::plugin_genericobject_addNewField($this->fields["itemtype"], 'otherserial');
         
      }

      if ($this->fields['use_direct_connections']) { /***/
         foreach (array ('users_id', 'groups_id', 'states_id', 'locations_id') as $field) {
            if (!$commonitem->getField($field)) {
               PluginGenericobjectField::plugin_genericobject_addNewField($this->fields["itemtype"], $field);
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
      $sopt[5]['linkfield']   = '';
      $sopt[5]['name']        = $LANG['common'][60];
      $sopt[5]['datatype']    = 'bool';
   
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
   
   
   /**
    * Add object type table + entries in glpi_display
    * @name object type's name
    * @return nothing
    */
   public static function plugin_genericobject_addTable($name) {
      global $DB;
      $query = "CREATE TABLE `glpi_plugin_genericobject_$name` (
                  `id` INT( 11 ) NOT NULL AUTO_INCREMENT,
                  `name` VARCHAR( 255 ) collate utf8_unicode_ci NOT NULL DEFAULT '',
                  `entities_id` INT( 11 ) NOT NULL DEFAULT 0,
                  `object_type` INT( 11 ) NOT NULL DEFAULT 0,
                  `is_deleted` INT( 1 ) NOT NULL DEFAULT 0,
                  `recursive` INT ( 1 ) NOT NULL DEFAULT 0,
                  `is_template` INT ( 1 ) NOT NULL DEFAULT 0,
                  `template_name` VARCHAR( 255 ) collate utf8_unicode_ci NOT NULL DEFAULT '',
                  `comments` TEXT NULL  ,
                  `notepad` TEXT NULL  ,
                  PRIMARY KEY ( `id` ) 
                  ) ENGINE = MYISAM COMMENT = '$name table' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
      $DB->query($query);

      $query = "INSERT INTO `glpi_displaypreferences` (`id`, `itemtype`, `num`, `rank`, `users_id`) " .
               "VALUES (NULL, " . plugin_genericobject_getIDByName($name) . ", 2, 1, 0);";
      $DB->query($query);

   }
   
   
   /**
    * Write on the the class file for the new object type
    * @param name the name of the object type
    * @param classname the name of the new object
    * @param itemtype the object device type
    * @return nothing
    */
   public static function plugin_genericobject_addClassFile($name, $classname, $itemtype) {
      $DBf_handle = fopen(GENERICOBJECT_CLASS_TEMPLATE, "rt");
      $template_file = fread($DBf_handle, filesize(GENERICOBJECT_CLASS_TEMPLATE));
      fclose($DBf_handle);
      $template_file = str_replace("%%CLASSNAME%%", $classname, $template_file);
      $template_file = str_replace("%%DEVICETYPE%%", $itemtype, $template_file);
      $DBf_handle = fopen(GENERICOBJECT_CLASS_PATH . "/$name.class.php", "w");
      fwrite($DBf_handle, $template_file);
      fclose($DBf_handle);
   }
   
   /**
    * Write on the the form file for the new object type
    * @param name the name of the object type
    * @param classname the name of the new object
    * @param itemtype the object device type
    * @return nothing
    */
   public static function plugin_genericobject_addFormFile($name, $classname, $itemtype) {
      $DBf_handle = fopen(GENERICOBJECT_FORM_TEMPLATE, "rt");
      $template_file = fread($DBf_handle, filesize(GENERICOBJECT_FORM_TEMPLATE));
      fclose($DBf_handle);
      $template_file = str_replace("%%CLASSNAME%%", $classname, $template_file);
      $template_file = str_replace("%%DEVICETYPE%%", $itemtype, $template_file);
      $DBf_handle = fopen(GENERICOBJECT_FRONT_PATH . "/$name.form.php", "w");
      fwrite($DBf_handle, $template_file);
      fclose($DBf_handle);
   }
      
   public static function plugin_genericobject_addLinkTable($itemtype) {
      global $DB;
      $name = $itemtype;
      //$name = plugin_genericobject_getNameByID($itemtype);
      $query = "CREATE TABLE IF NOT EXISTS `".self::plugin_genericobject_getLinkDeviceTableName($name)."` (
                 `id` int(11) NOT NULL auto_increment,
                 `source_id` int(11) NOT NULL default '0',
                 `items_id` int(11) NOT NULL default '0',
                 `itemtype` VARCHAR( 255 ) NOT NULL,
                 PRIMARY KEY  (`id`),
                 UNIQUE KEY `source_id` (`source_id`,`items_id`,`itemtype`),
                 KEY `source_id_2` (`source_id`),
                 KEY `items_id` (`items_id`,`itemtype`)
               ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
      $DB->query($query);
   }
   
   
   public static function plugin_genericobject_getLinkDeviceTableName($name) {
      return "glpi_plugin_genericobject_".getPlural($name)."_device";
   }
   
   
   public static function plugin_genericobject_getSpecificDropdownsTablesByType($type) {
      $dropdowns = array ();
      $object_type = new PluginGenericobjectType;
      $object_type->getFromDBByType($type);
      self::plugin_genericobject_getDropdownSpecific($dropdowns, $object_type->fields, true);
      return $dropdowns;
   }
   
   
   public static function plugin_genericobject_getDropdownSpecific(& $dropdowns, $type, $check_entity = false) {
      global $GENERICOBJECT_AVAILABLE_FIELDS;
      
      $specific_types = self::plugin_genericobject_getDropdownSpecificFields();
      $table = plugin_genericobject_getTableNameByName($type["name"]);

      foreach ($specific_types as $ID => $field) {
         if (FieldExists($table, $field)) {
            if (!$check_entity 
               || ($check_entity 
                  && self::plugin_genericobject_isDropdownEntityRestrict($field)))
               $dropdowns["PluginGenericobject".ucfirst($type["name"]).$field] = 
                  plugin_genericobject_getObjectLabel($type["name"]) . ' : ' . 
                                                      $GENERICOBJECT_AVAILABLE_FIELDS[$field]['name'];
         }
      }
   }
   
   public static function plugin_genericobject_getDropdownSpecificFields() {
      global $GENERICOBJECT_AVAILABLE_FIELDS;
      $specific_fields = array ();

      foreach ($GENERICOBJECT_AVAILABLE_FIELDS as $field => $values) {
         if (isset ($values["dropdown_type"]) && $values["dropdown_type"] == 'type_specific') {
            $specific_fields[$field] = $field;
         }
      }

      return $specific_fields;
   }
   
   public static function plugin_genericobject_showObjectFieldsForm($target, $ID) {
      global $LANG, $DB, $GENERICOBJECT_BLACKLISTED_FIELDS, $GENERICOBJECT_AVAILABLE_FIELDS, $CFG_GLPI, 
             $GENERICOBJECT_AUTOMATICALLY_MANAGED_FIELDS;

      $object_type = new PluginGenericobjectType;
      $object_type->getFromDB($ID);
      
      $object_table = plugin_genericobject_getTableNameByID($object_type->fields["itemtype"]);
      $fields_in_db = PluginGenericobjectField::plugin_genericobject_getFieldsByType($object_type->fields["itemtype"]);

      foreach ($GENERICOBJECT_AUTOMATICALLY_MANAGED_FIELDS as $autofield)
         $used_fields[$autofield] = $autofield;

      foreach ($GENERICOBJECT_BLACKLISTED_FIELDS as $autofield)
         if (!in_array($autofield,$used_fields))
            $used_fields[$autofield] = $autofield;


      echo "<form name='form_fields' method='post' action=\"$target\">";
      echo "<div class='center'>";
      echo "<table class='tab_cadre_fixe' >";
      echo "<input type='hidden' name='id' value='$ID'>";
      echo "<tr class='tab_bg_1'><th colspan='7'>";
      echo $LANG['genericobject']['fields'][1] . " : " . 
         plugin_genericobject_getObjectLabel($object_type->fields["name"]);
      echo "</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th width='10'></th>";
      echo "<th>" . $LANG['genericobject']['fields'][2] . "</th>";
      echo "<th>" . $LANG['genericobject']['fields'][3] . "</th>";
      /*echo "<th width='10'>" . $LANG['genericobject']['fields'][7] . "</th>";
      echo "<th width='10'>" . $LANG['genericobject']['fields'][8] . "</th>";*/
      echo "<th width='10'></th>";
      echo "<th width='10'></th>";
      echo "</tr>";

      $index = 1;
      $total = count($fields_in_db);

      foreach ($fields_in_db as $type => $value) {
         self::plugin_genericobject_displayFieldDefinition($target, $ID, $value->getName(), $index, $total);
         $used_fields[$value->getName()] = $value->getName();
         $index++;
      }
      echo "<tr><td><img src=\"" . $CFG_GLPI["root_doc"] . "/pics/arrow-left.png\" alt=''>"; 
      echo "</td><td class='center'>"; 
      echo "<a onclick= \"if ( markCheckboxes('form_fields') ) return false;\" href='" . $target . 
              "?id=$ID&amp;select=all'>" . $LANG['buttons'][18] . "</a>";
      echo "&nbsp;/&nbsp;<a onclick= \"if ( unMarkCheckboxes('form_fields') ) return false;\" href='" . 
               $target . "?id=$ID&amp;select=none'>" . $LANG['buttons'][19] . "</a>";
      echo "</td><td colspan='5' align='left' width='75%'>";

      echo "<select name=\"massiveaction\" id='massiveaction'>";
      echo "<option value=\"-1\" selected>-----</option>";
      echo "<option value=\"delete\">" . $LANG['buttons'][6] . "</option>";
      //echo "<option value=\"move_field\">" . $LANG['buttons'][20] . "</option>";
      echo "</select>";

      $params = array ('action' => '__VALUE__', 'itemtype' => $object_type->fields["itemtype"]);

      $url = $CFG_GLPI["root_doc"].
               "/plugins/genericobject/ajax/plugin_genericobject_dropdownObjectTypeFields.php";
      ajaxUpdateItemOnSelectEvent("massiveaction", "show_massiveaction", $url, $params);

      echo "<span id='show_massiveaction'>&nbsp;</span>\n";

      echo "</td></tr>";

      echo "</table>";
      echo "<br>";

      echo "<table class='tab_cadre'>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . $LANG['genericobject']['fields'][4] . "</td>";
      echo "<td align='left'>";
      plugin_genericobject_dropdownFields("new_field", $used_fields);
      echo "</td>";
      echo "<td>";
      echo "<input type='submit' name='add_field' value=\"" . $LANG['buttons'][8] . "\" class='submit'>";
      echo "</tr>";
      echo "</table></div></form>";
   }
   
   
   public static function plugin_genericobject_displayFieldDefinition($target, $ID, $field, $index, $total) {
      global $GENERICOBJECT_AVAILABLE_FIELDS, $CFG_GLPI;
      $readonly = ($field == "name");

      echo "<tr class='tab_bg_".(($index%2)+1)."' align='center'>";
      $sel = "";
      if (isset ($_POST["selected"])) {
         $sel = "checked";
      }

      echo "<td width='10'>";
      if (!$readonly) {
         echo "<input type='checkbox' name='fields[" .$field. "]' value='1' $sel>";
      }
      echo "</td>";
      echo "<td>" . $field . "</td>";
      echo "<td>" . $GENERICOBJECT_AVAILABLE_FIELDS[$field]['name'] . "</td>";
      /*echo "<td width='10'>";
      echo "<input type='checkbox' name='mandatory[" . $field . "]' value='1'>";
      echo "</td>";
      echo "<td width='10'>";
      echo "<input type='checkbox' name='unique[" . $field . "]' value='1'>";
      echo "</td>";*/

      echo "<td width='10'>";
      if (!$readonly && $index > 2) {
         echo "<a href=\"" . $target . "?field=" . $field . "&amp;action=up&amp;id=" . $ID . "\">"; 
         echo "<img src=\"" . $CFG_GLPI["root_doc"] . "/pics/deplier_up.png\" alt=''></a>";
      }
      echo "</td>";

      echo "<td width='10'>";
      if (!$readonly && $index > 1 && $index < $total) {
         echo "<a href=\"" . $target . "?field=" . $field . "&amp;action=down&amp;id=" . $ID . "\">"; 
         echo "<img src=\"" . $CFG_GLPI["root_doc"] . "/pics/deplier_down.png\" alt=''></a>";
      }
      echo "</td>";

      echo "</tr>";
   }
   
   
   public static function plugin_genericobject_deleteFieldFromDB($table, $field, $name) {
      global $DB;
      if (FieldExists($table, $field)) {
         $DB->query("ALTER TABLE `$table` DROP `$field`;");
         if (self::plugin_genericobject_isDropdownTypeSpecific($field))
            self::plugin_genericobject_deleteDropdownTable($name, $field);
            self::plugin_genericobject_deleteDropdownClassFile($name, $field);
            self::plugin_genericobject_deleteDropdownFrontFile($name, $field);
            self::plugin_genericobject_deleteDropdownFrontformFile($name, $field);
            self::plugin_genericobject_deleteDropdownAjaxFile($name, $field);
      }

   }
   
   public static function plugin_genericobject_isDropdownTypeSpecific($field) {
      global $GENERICOBJECT_AVAILABLE_FIELDS;
      return (isset ($GENERICOBJECT_AVAILABLE_FIELDS[$field]['dropdown_type']) 
                 && $GENERICOBJECT_AVAILABLE_FIELDS[$field]['dropdown_type'] == 'type_specific');
   }
   
   public static function plugin_genericobject_deleteDropdownClassFile($name, $field) {
      if (file_exists(GENERICOBJECT_CLASS_PATH . "/".$name.$field.".class.php"))
         unlink(GENERICOBJECT_CLASS_PATH ."/".$name.$field.".class.php");
   }

   public static function plugin_genericobject_deleteDropdownFrontformFile($name, $field) {
      if (file_exists(GENERICOBJECT_FRONT_PATH . "/".$name.$field.".form.php"))
         unlink(GENERICOBJECT_FRONT_PATH ."/".$name.$field.".form.php");
   }

   public static function plugin_genericobject_deleteDropdownFrontFile($name, $field) {
      if (file_exists(GENERICOBJECT_FRONT_PATH . "/".$name.$field.".form.php"))
         unlink(GENERICOBJECT_FRONT_PATH .
         "/".$name.$field.".php");
   }

   public static function plugin_genericobject_deleteDropdownAjaxFile($name, $field) {
      if (file_exists(GENERICOBJECT_AJAX_PATH . "/".$name.$field.".tabs.php"))
         unlink(GENERICOBJECT_AJAX_PATH ."/".$name.$field.".tabs.php");
   }

   public static function plugin_genericobject_deleteSpecificDropdownFiles($itemtype)
   {
      global $DB;
      $name = plugin_genericobject_getNameByID($itemtype);
      $types = self::plugin_genericobject_getDropdownSpecificFields();

      foreach($types as $type => $tmp) {
         self::plugin_genericobject_deleteDropdownAjaxFile($name, $type);
         self::plugin_genericobject_deleteDropdownFrontFile($name, $type);
         self::plugin_genericobject_deleteDropdownFrontformFile($name, $type);
         self::plugin_genericobject_deleteDropdownClassFile($name, $type);
      }
         
   }

   public static function plugin_genericobject_deleteDropdownTable($name, $field) {
      global $DB;
      if (TableExists(self::plugin_genericobject_getDropdownTableName($name, $field)))
         $DB->query("DROP TABLE `" .
                        self::plugin_genericobject_getDropdownTableName($name, $field) . "`");
   }
   
   
   /**
    * Delete an used class file
    * @param name the name of the object type
    * @return nothing
    */
   public static function plugin_genericobject_deleteClassFile($name) {
      if (file_exists(GENERICOBJECT_CLASS_PATH . "/$name.class.php"))
         unlink(GENERICOBJECT_CLASS_PATH .
         "/$name.class.php");
   }
   
   /**
    * Delete an used form file
    * @param name the name of the object type
    * @return nothing
    */
   public static function plugin_genericobject_deleteFormFile($name) {
      if (file_exists(GENERICOBJECT_FRONT_PATH . "/$name.form.php"))
         unlink(GENERICOBJECT_FRONT_PATH .
         "/$name.form.php");
   }
      
   public static function plugin_genericobject_deleteLinkTable($itemtype) {
      global $DB;
      $name = plugin_genericobject_getNameByID($itemtype);
      $query = "DROP TABLE IF EXISTS `".self::plugin_genericobject_getLinkDeviceTableName($name)."`";                        ;
      $DB->query($query);
   }
   
   
   public static function plugin_genericobject_addDropdownClassFile($name, $field) {
      $tablename = self::plugin_genericobject_getDropdownTableName($name, $field);
      $classname = "PluginGenericobject".ucfirst($name).ucfirst($field);
      
      if (TableExists($tablename)) {
         
         $DBf_handle = fopen(GENERICOBJECT_CLASS_DROPDOWN_TEMPLATE, "rt");
         $template_file = fread($DBf_handle, filesize(GENERICOBJECT_CLASS_DROPDOWN_TEMPLATE));
         fclose($DBf_handle);
         $template_file = str_replace("%%CLASSNAME%%", $classname, $template_file);
         //$template_file = str_replace("%%DEVICETYPE%%", $itemtype, $template_file);
         $DBf_handle = fopen(GENERICOBJECT_CLASS_PATH . "/".$name.$field.".class.php", "w");
         fwrite($DBf_handle, $template_file);
         fclose($DBf_handle);
      }
   } 
   
   public static function plugin_genericobject_addDropdownTable($name, $field) {
      global $DB;
      if (!TableExists(self::plugin_genericobject_getDropdownTableName($name, $field))) {
         if (!self::plugin_genericobject_isDropdownEntityRestrict($field)) {
            $query = "CREATE TABLE `" . self::plugin_genericobject_getDropdownTableName($name, $field) . "` (
                          `id` int(11) NOT NULL auto_increment,
                          `name` varchar(255) collate utf8_unicode_ci default NULL,
                          `comment` text collate utf8_unicode_ci,
                          PRIMARY KEY  (`id`),
                          KEY `name` (`name`)
                        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         } else {
            $query = "CREATE TABLE IF NOT EXISTS `" . 
                        self::plugin_genericobject_getDropdownTableName($name, $field) . "` (
                       `id` int(11) NOT NULL auto_increment,
                       `entities_id` int(11) NOT NULL default '0',
                       `name` varchar(255) collate utf8_unicode_ci default NULL,
                       `parentID` int(11) NOT NULL default '0',
                       `completename` text collate utf8_unicode_ci,
                       `comment` text collate utf8_unicode_ci,
                       `level` int(11) NOT NULL default '0',
                       PRIMARY KEY  (`id`),
                       UNIQUE KEY `name` (`name`,`parentID`,`entities_id`),
                       KEY `parentID` (`parentID`),
                       KEY `entities_id` (`entities_id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         }
         $DB->query($query);
      }
   }
   
   public static function plugin_genericobject_addDropdownFrontFile($name, $field) {
      $classname = "PluginGenericobject".ucfirst($name).ucfirst($field);
      
      $DBf_handle = fopen(GENERICOBJECT_FRONT_DROPDOWN_TEMPLATE, "rt");
      $template_file = fread($DBf_handle, filesize(GENERICOBJECT_FRONT_DROPDOWN_TEMPLATE));
      fclose($DBf_handle);
      $template_file = str_replace("%%OBJECT%%", $classname, $template_file);
      $DBf_handle = fopen(GENERICOBJECT_FRONT_PATH . "/".$name.$field.".php", "w");
      fwrite($DBf_handle, $template_file);
      fclose($DBf_handle);
   }

   public static function plugin_genericobject_addDropdownAjaxFile($name, $field) {
      $classname = "PluginGenericobject".ucfirst($name).ucfirst($field);
      
      $DBf_handle = fopen(GENERICOBJECT_AJAX_DROPDOWN_TEMPLATE, "rt");
      $template_file = fread($DBf_handle, filesize(GENERICOBJECT_AJAX_DROPDOWN_TEMPLATE));
      fclose($DBf_handle);
      $template_file = str_replace("%%OBJECT%%", $classname, $template_file);
      $DBf_handle = fopen(GENERICOBJECT_AJAX_PATH . "/".$name.$field.".tabs.php", "w");
      fwrite($DBf_handle, $template_file);
      fclose($DBf_handle);
   }
   
   public static function plugin_genericobject_addDropdownFrontformFile($name, $field) {
      $classname = "PluginGenericobject".ucfirst($name).ucfirst($field);
      
      $DBf_handle = fopen(GENERICOBJECT_FRONTFORM_DROPDOWN_TEMPLATE, "rt");
      $template_file = fread($DBf_handle, filesize(GENERICOBJECT_FRONTFORM_DROPDOWN_TEMPLATE));
      fclose($DBf_handle);
      $template_file = str_replace("%%OBJECT%%", $classname, $template_file);
      $DBf_handle = fopen(GENERICOBJECT_FRONT_PATH . "/".$name.$field.".form.php", "w");
      fwrite($DBf_handle, $template_file);
      fclose($DBf_handle);
   }

    /**
    * Get next available device typ
    * @return the next available device type 
    */
   public static function plugin_genericobject_getNextDeviceType() {
      global $DB;
      $query = "SELECT MAX(itemtype) as cpt FROM `glpi_plugin_genericobject_types`";
      $result = $DB->query($query);
      if (!$DB->result($result, 0, "cpt")) {
         $cpt = 4090;
      } else {
         $cpt = $DB->result($result, 0, "cpt") + 1;
      }
      return $cpt;
   }


   /**
    * Delete object type table + entries in glpi_display
    * @name object type's name
    * @return nothing
    */
   public static function plugin_genericobject_deleteTable($name) {
      global $DB;
      $type = plugin_genericobject_getIDByName($name);
      $DB->query("DELETE FROM `glpi_displaypreferences` WHERE itemtype='$type'");
      $DB->query("DROP TABLE IF EXISTS `glpi_plugin_genericobject_$name`");
   }

   public static function plugin_genericobject_getDropdownTableName($name, $field) {
      return getPlural("glpi_plugin_genericobject_" . $name . $field);
   }



   public static function plugin_genericobject_isDropdownEntityRestrict($field) {
      global $GENERICOBJECT_AVAILABLE_FIELDS;
      return (isset ($GENERICOBJECT_AVAILABLE_FIELDS[$field]['entity']) 
                  && $GENERICOBJECT_AVAILABLE_FIELDS[$field]['entity'] == 'entity_restrict');
   }

   public static function plugin_genericobject_enableTemplateManagement($name) {
      global $DB;
      $table = plugin_genericobject_getTableNameByName($name);
      if (!FieldExists($table, "is_template")) {
         $query = "ALTER TABLE `$table` ADD `is_template` INT ( 1 ) NOT NULL DEFAULT 0";
         $DB->query($query);
      }

      if (!FieldExists($table, "template_name")) {
         $query = "ALTER TABLE `$table` " .
                  "ADD `template_name` VARCHAR ( 255 )  collate utf8_unicode_ci NOT NULL DEFAULT ''";
         $DB->query($query);
      }
   }

   public static function plugin_genericobject_disableTemplateManagement($name) {
      global $DB;

      $table = plugin_genericobject_getTableNameByName($name);

      if (FieldExists($table, "is_template")) {
         $table = plugin_genericobject_getTableNameByName($name);
         $query = "ALTER TABLE `$table` DROP `is_template`";
         $DB->query($query);
      }

      if (FieldExists($table, "template_name")) {
         $query = "ALTER TABLE `$table` DROP `template_name`";
         $DB->query($query);
      }
   }

   public static function plugin_genericobject_getDatabaseRelationsSpecificDropdown(& $dropdowns, $type) {
      global $GENERICOBJECT_AVAILABLE_FIELDS;
      $specific_types = self::plugin_genericobject_getDropdownSpecificFields();
      $table = plugin_genericobject_getTableNameByName($type["name"]);

      foreach ($specific_types as $ID => $field) {
         if (TableExists($table) && FieldExists($table, $field)) {
            $dropdowns[$table] = array (
               self::plugin_genericobject_getDropdownTableName($type["name"], $field) => 
                  $GENERICOBJECT_AVAILABLE_FIELDS[$field]['linkfield']
            );
         }
      }
   }

   public static function plugin_genericobject_deleteSpecificDropdownTables($itemtype) {
      global $DB;
      $name = plugin_genericobject_getNameByID($itemtype);
      $types = self::plugin_genericobject_getDropdownSpecificFields();

      foreach($types as $type => $tmp) {
         $DB->query("DROP TABLE IF EXISTS `" . self::plugin_genericobject_getDropdownTableName($name,
                                                                                         $type)."`");
      }
         
   }

      
   public static function plugin_genericobject_removeDataInjectionModels($itemtype) {
      global $DB;
         $plugin = new Plugin;
            //Delete if exists datainjection models
         if ($plugin->isInstalled("datainjection")) {
            $query = "DELETE FROM `glpi_plugin_datainjection_models`,
                                  `glpi_plugin_datainjection_mappings`,
                                  `glpi_plugin_datainjection_infos`
                      USING `glpi_plugin_datainjection_models`, `glpi_plugin_datainjection_mappings`,
                            `glpi_plugin_datainjection_infos` 
                      WHERE glpi_plugin_datainjection_models.itemtype='".$itemtype."' 
                         AND glpi_plugin_datainjection_mappings.models_id=glpi_plugin_datainjection_models.id 
                            AND glpi_plugin_datainjection_infos.models_id=glpi_plugin_datainjection_models.id";
            
            $DB->query ($query);
         }
      
   }

   /**
    * Delete all loans associated with a itemtype
    */
   public static function plugin_genericobject_deleteLoans($itemtype) {
      global $DB;
      
      $query = "DELETE FROM  `glpi_reservationitems`, `glpi_reservations` " .
               "USING `glpi_reservationitems`, `glpi_reservations` " .
                  "WHERE `glpi_reservationitems`.`itemtype`='$itemtype' " .
                     "AND `glpi_reservationitems`.`id`=`glpi_reservations`.`reservationitems_id`";
      $DB->query($query); 
   }


   public static function plugin_genericobject_deleteNetworking($itemtype) {
          global $DB;
           $query = "SELECT `id` 
                  FROM `glpi_networkports` 
                  WHERE `itemtype` = '" . $itemtype . "'";
         $result = $DB->query($query);
         while ($data = $DB->fetch_array($result)) {
            $q = "DELETE FROM `glpi_networkports_networkports` " .
                 "WHERE `networkports_id_1` = '" . $data["id"] . "' " .
                 "OR `networkports_id_2` = '" . $data["id"] . "'";
            $result2 = $DB->query($q);
         }

         $query2 = "DELETE FROM `glpi_networkports` WHERE `itemtype` = '" . $itemtype . "'";
         $result2 = $DB->query($query2);

         $query = "SELECT `id` FROM `glpi_computers_items` WHERE `itemtype`='" . $itemtype."'";
         if ($result = $DB->query($query)) {
            if ($DB->numrows($result) > 0) {
               while ($data = $DB->fetch_array($result)) {
                  // Disconnect without auto actions
                  Disconnect($data["id"], 1, false);
               }
            }
         }
      
   }
}
?>
