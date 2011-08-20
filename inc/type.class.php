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

   const INACTIVE = 0;
   const ACTIVE   = 1;

   const DRAFT     = 0;
   const PUBLISHED = 1;

   const CLASS_TEMPLATE              = "../objects/generic.class.tpl";
   const FORM_TEMPLATE               = "../objects/generic.form.tpl";
   const CLASS_DROPDOWN_TEMPLATE     = "../objects/generic.dropdown.class.tpl";
   const FRONTFORM_DROPDOWN_TEMPLATE = "../objects/front.form.tpl";
   const SEARCH_TEMPLATE             = "../objects/front.tpl";
   const AJAX_DROPDOWN_TEMPLATE      = "../objects/dropdown.tabs.tpl";
   const AJAX_TEMPLATE               = "../objects/ajax.tabs.tpl";

   function __construct($itemtype = false) {
      if ($itemtype) {
         $this->getFromDBByType($itemtype);
      }
   }
   
   function canCreate() {
      return haveRight("config", "w");
   }

   function canView() {
      return haveRight("config", "r");
   }

   function getFromDBByType($itemtype) {
      global $DB;
      $query  = "SELECT * FROM `" . getTableForItemType(__CLASS__) . "` " .
                "WHERE `itemtype`='$itemtype'";
      $result = $DB->query($query);
      if ($DB->numrows($result) > 0) {
         $this->fields = $DB->fetch_array($result);
      } else {
         $this->getEmpty();
      }
   }

   function defineTabs($options=array()) {
      global $LANG;
      $ong        = array ();
      $ong[1]     = $LANG['title'][26];
      if (isset($this->fields['id']) && $this->fields['id'] > 0) {
         $ong[3]  = $LANG['rulesengine'][12];
         //$ong[4] = $LANG['genericobject']['config'][4];
         $ong[5]  = $LANG['genericobject']['config'][7];
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

      self::includeLocales($this->fields["name"]);
      
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
      if ($ID) {
         echo PluginGenericobjectObject::getLabel($this->fields["name"]);
      }
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . $LANG['common'][60] . "</td>";
      echo "<td>";
      if (!$ID) {
         echo $LANG['choice'][0];
      }
      else {
         Dropdown::showYesNo("status", $this->fields["status"]);
      }
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options);

      echo "<div id='tabcontent'></div>";
      echo "<script type='text/javascript'>loadDefaultTab();</script>";

   }

   function showBehaviourForm($id) {
      global $LANG;
      if ($id > 0) {
         $this->check($id, 'r');
      } else {
         // Create item 
         $this->check(-1, 'w');
         $use_cache = false;
         $this->getEmpty();
      }

      $canedit = $this->can($id, 'w');
      echo "<form name='behaviour' method='post' action=\"".$this->getFormURL()."\">";
      echo "<div class='center'>";
      echo "<table class='tab_cadre_fixe' >";
      echo "<tr class='tab_bg_1'><th colspan='2'>";
      echo $LANG['genericobject']['config'][3];
      echo "</th></tr>";

      $use = array ("use_entity"               => $LANG['Menu'][37],
                    "use_recursivity"          => $LANG['entity'][9],
                    "use_tickets"              => $LANG['Menu'][31],
                    "use_deleted"              => $LANG['ocsconfig'][49],
                    "use_notes"                => $LANG['title'][37],
                    "use_history"              => $LANG['title'][38],
                    "use_template"             => $LANG['common'][14],
                    "use_infocoms"             => $LANG['financial'][3],
                    "use_contracts"            => $LANG['Menu'][25],
                    "use_documents"            => $LANG['Menu'][27],
                    "use_loans"                => $LANG['Menu'][17],
                    "use_loans"                => $LANG['Menu'][17],
                    "use_network_ports"        => $LANG['genericobject']['config'][14],
                    "use_plugin_datainjection" => $LANG['genericobject']['config'][10],
                    "use_plugin_pdf"         => $LANG['genericobject']['config'][11],
                    "use_plugin_order"         => $LANG['genericobject']['config'][12],
                    "use_plugin_uninstall"     => $LANG['genericobject']['config'][13]
                     //"use_plugin_geninventorynumber"=>$LANG['genericobject']['config'][15]
                     );
/*
      if (GLPI_VERSION >= '0.72.3') {
         $use["use_direct_connections"] = $LANG['connect'][0];

      }
*/
      $plugin = new Plugin();
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
                  Dropdown::showYesNo($right, $this->fields[$right]);
               break;

            case 'use_plugin_datainjection' :
               if ($plugin->isInstalled("datainjection") && $plugin->isActivated("datainjection")) {
                  Plugin::load("datainjection");
                  $infos = plugin_version_datainjection();
                  if ($infos['version'] >= '1.7.0') {
                  Dropdown::showYesNo($right, $this->fields[$right]);
                  }
               } else {
                  echo "<input type='hidden' name='use_plugin_datainjection' value='0'>\n";
               }
               break;
            case 'use_plugin_pdf' :
               if ($plugin->isInstalled("pdf") && $plugin->isActivated("pdf")) {
                  Dropdown::showYesNo($right, $this->fields[$right]);
               }
                  
               else
                  echo "<input type='hidden' name='use_plugin_pdf' value='0'>\n";
               break;
            case 'use_plugin_order' :
               if ($plugin->isInstalled("order") && $plugin->isActivated("order")) {
                  Dropdown::showYesNo($right, $this->fields[$right]);
               }
               else
                  echo "<input type='hidden' name='use_plugin_order' value='0'>\n";
               break;
               /*
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
               break;*/
            case 'use_plugin_uninstall' :
               if ($plugin->isInstalled("uninstall") && $plugin->isActivated("uninstall")) {
                  //usePlugin("uninstall");
                  Plugin::load("uninstall");
                  $infos = plugin_version_uninstall();
                  if ($infos['version'] >= '1.2.1') {
                     Dropdown::showYesNo($right, $this->fields[$right]);
                  }
               } else {
                  echo "<input type='hidden' name='use_plugin_uninstall' value='0'>\n";
               }

               break;
            default :
                  Dropdown::showYesNo($right, $this->fields[$right]);
               break;
         }
         echo "</td>";
         echo "</tr>";
      }

      if ($canedit) {
         echo "<tr>";
         echo "<td class='tab_bg_2' colspan='2' align='center'>";

         echo "<input type='hidden' name='id' value='$id'>\n";
         echo "<input type='submit' name='update' value=\"" . $LANG['buttons'][7] . 
                  "\" class='submit'>";
         echo "</td>";
         echo "</tr>";
      }
   }

   function prepareInputForAdd($input) {
      $input['name']     = str_replace(array(' ', '_', '-'), '', strtolower($input["name"]));
      $input['itemtype'] = self::getClassByName($input['name']);
      return $input;
   }

   function post_addItem() {
      //Add new type table
      
      self::addTable($this->input["itemtype"]);

      //Write object class on the filesystem
      self::addClassFile($this->input["name"], $this->input["itemtype"]);

     //Write the form on the filesystem
      self::addFormFile($this->input["name"],$this->input["itemtype"]);
      self::addSearchFile($this->input["name"],$this->input["itemtype"]);
      self::addAjaxFile($this->input["name"],$this->input["itemtype"]);
      
      //Create rights for this new object
      PluginGenericobjectProfile::createAccess($_SESSION["glpiactiveprofile"]["id"], true);

      //Reload profiles
      PluginGenericobjectProfile::changeProfile();
      return true;
   }

   function prepareInputForUpdate($input) {
      $type = new self();
      $this->getFromDB($input["id"]);
      if (isset ($input["status"]) && $input["status"]) {
         $type->fields = $this->fields;
         self::registerOneType($type);
      }

      return $input;
   }

   function post_updateItem($history = 1) {
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
      //self::deleteLinkTable($this->fields["itemtype"]);

      //Delete all tables and files related to the type (dropdowns)
      self::deleteSpecificDropdownTables($this->fields["itemtype"]);
      
      //Delete loans associated with this type
      self::deleteLoans($this->fields["itemtype"]);


      //Remove class from the filesystem
      self::deleteClassFile($this->fields["name"]);

      //Remove form from the filesystem
      self::deleteFormFile($this->fields["name"]);

      //Remove form from the filesystem
      self::deleteSearchFile($this->fields["name"]);

      self::deleteAjaxFile($this->fields["name"]);

      //Delete profile informations associated with this type
      PluginGenericobjectProfile::deleteTypeFromProfile($this->fields["itemtype"]);

      //Table type table in DB
      self::deleteTable($this->fields["itemtype"]);

      self::removeDataInjectionModels($this->fields["itemtype"]);
      return true;
   }

   function checkNecessaryFieldsUpdate() {
      $itemtype = $this->fields["itemtype"];
      $item     = new $itemtype();
      $item->getEmpty();
      $table    = getTableForItemType($itemtype);
      //Entity
      if ($this->canBeEntityAssigned()) {
         PluginGenericobjectField::addNewField($table, 'entities_id');
      } else {
         PluginGenericobjectField::deleteField($table, 'entities_id');
      }

      //Recursivity
      if ($this->canBeRecursive()) {
         PluginGenericobjectField::addNewField($table, 'is_recursive');
      } else {
         PluginGenericobjectField::deleteField($table, 'is_recursive');
      }
      
      //Template
      if ($this->canUseTemplate()) {
         PluginGenericobjectField::addNewField($table, 'is_template');
         PluginGenericobjectField::addNewField($table, 'template_name');
      } else {
         PluginGenericobjectField::deleteField($table, 'is_template');
         PluginGenericobjectField::deleteField($table, 'template_name');
      }

      //Trash
      if ($this->canBeDeleted()) {
         PluginGenericobjectField::addNewField($table, 'is_deleted');
      } else {
         PluginGenericobjectField::deleteField($table, 'is_deleted');
      }
      
      //Helpdesk post-only
      if ($this->canUseTickets()) {
         PluginGenericobjectField::addNewField($table, 'is_helpdesk_visible');
      } else {
         PluginGenericobjectField::deleteField($table, 'is_helpdesk_visible');
      }
      
      //Helpdesk post-only
      if ($this->canUseNotepad()) {
         PluginGenericobjectField::addNewField($table, 'notepad');
      } else {
         PluginGenericobjectField::deleteField($table, 'notepad');
      }
      
      //Networkport
      if ($this->canUseNetworkPorts()) {
         PluginGenericobjectField::addNewField($table, 'locations_id');
      }

/*
     if ($this->canUsePluginGeninventoryNumber() 
            && !$commonitem->getField('otherserial')) {
         PluginGenericobjectField::addNewField($this->fields["itemtype"], 'otherserial');
         
      }
*/

/*
     if ($this->fields['use_direct_connections']) { 
        foreach (array ('users_id', 'groups_id', 'states_id', 'locations_id') as $field) {
           if (!$commonitem->getField($field)) {
              PluginGenericobjectField::addNewField($this->fields["itemtype"], $field);
           }
        }
     }
*/
   }
   
   function getSearchOptions() {
      global $LANG;
      $sopt['common'] = $LANG["genericobject"]["title"][1];
   
      $sopt[1]['table']       = $this->getTable();
      $sopt[1]['field']       = 'name';
      $sopt[1]['name']        = $LANG["common"][22];
      $sopt[1]['datatype']    = 'itemlink';

      $sopt[5]['table']       = $this->getTable();
      $sopt[5]['field']       = 'status';
      $sopt[5]['name']        = $LANG['common'][60];
      $sopt[5]['datatype']    = 'bool';
   
      $sopt[6]['table']       = $this->getTable();
      $sopt[6]['field']       = 'use_tickets';
      $sopt[6]['name']        = $LANG['genericobject']['config'][1]." ".$LANG['Menu'][31];
      $sopt[6]['datatype']    = 'bool';
   
      $sopt[7]['table']       = $this->getTable();
      $sopt[7]['field']       = 'use_deleted';
      $sopt[7]['name']        = $LANG['genericobject']['config'][1]." ".$LANG['ocsconfig'][49];
      $sopt[7]['datatype']    = 'bool';
   
      $sopt[8]['table']       = $this->getTable();
      $sopt[8]['field']       = 'use_notes';
      $sopt[8]['name']        = $LANG['genericobject']['config'][1]." ".$LANG['title'][37];
      $sopt[8]['datatype']    = 'bool';
   
      $sopt[9]['table']       = $this->getTable();
      $sopt[9]['field']       = 'use_history';
      $sopt[9]['name']        = $LANG['genericobject']['config'][1]." ".$LANG['title'][38];
      $sopt[9]['datatype']    = 'bool';
   
      $sopt[10]['table']      = $this->getTable();
      $sopt[10]['field']      = 'use_entity';
      $sopt[10]['name']       = $LANG['genericobject']['config'][1]." ". $LANG['Menu'][37];
      $sopt[10]['datatype']   = 'bool';
   
      $sopt[11]['table']      = $this->getTable();
      $sopt[11]['field']      = 'use_recursivity';
      $sopt[11]['name']       = $LANG['genericobject']['config'][1]." ".$LANG['entity'][9];
      $sopt[11]['datatype']   = 'bool';
   
      $sopt[12]['table']      = $this->getTable();
      $sopt[12]['field']      = 'use_template';
      $sopt[12]['name']       = $LANG['genericobject']['config'][1]." ".$LANG['common'][14];
      $sopt[12]['datatype']   = 'bool';
   
      $sopt[13]['table']      = $this->getTable();
      $sopt[13]['field']      = 'use_infocoms';
      $sopt[13]['name']       = $LANG['genericobject']['config'][1]." ".$LANG['financial'][3];
      $sopt[13]['datatype']   = 'bool';
   
      $sopt[14]['table']      = $this->getTable();
      $sopt[14]['field']      = 'use_documents';
      $sopt[14]['name']       = $LANG['genericobject']['config'][1]." ".$LANG['Menu'][27];
      $sopt[14]['datatype']   = 'bool';
   
      $sopt[15]['table']      = $this->getTable();
      $sopt[15]['field']      = 'use_loans';
      $sopt[15]['name']       = $LANG['genericobject']['config'][1]." ".$LANG['Menu'][17];
      $sopt[15]['datatype']   = 'bool';
      return $sopt;
   }
   
   
   /**
    * Add object type table + entries in glpi_display
    * @name object type's name
    * @return nothing
    */
   public static function addTable($itemtype) {
      global $DB;
      $query = "CREATE TABLE `".getTableForItemType($itemtype)."` (
                  `id` INT( 11 ) NOT NULL AUTO_INCREMENT,
                  `name` VARCHAR( 255 ) collate utf8_unicode_ci NOT NULL DEFAULT '',
                  `comment` TEXT NULL  ,
                  `notepad` TEXT NULL  ,
                  PRIMARY KEY ( `id` ) 
                  ) ENGINE = MYISAM COMMENT = '$itemtype' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
      $DB->query($query);

      $query = "INSERT INTO `glpi_displaypreferences` (`id`, `itemtype`, `num`, `rank`, `users_id`) " .
               "VALUES (NULL, '$itemtype', '2', '1', '0');";
      $DB->query($query);

   }
   

   public static function addLinkTable($itemtype) {
      global $DB;
      $name = $itemtype;
      //$name = self::getNameByID($itemtype);
      $query = "CREATE TABLE IF NOT EXISTS `".self::getLinkDeviceTableName($name)."` (
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
   
   
   public static function getLinkDeviceTableName($name) {
      return "glpi_plugin_genericobject_".getPlural($name)."_device";
   }
   
   
   public static function getSpecificDropdownsTablesByType($type) {
      $dropdowns   = array ();
      $object_type = new PluginGenericobjectType;
      $object_type->getFromDBByType($type);
      self::getDropdownSpecific($dropdowns, $object_type->fields, true);
      return $dropdowns;
   }
   
   
   public static function getDropdownSpecific(& $dropdowns, $type, $check_entity = false) {
      global $GENERICOBJECT_AVAILABLE_FIELDS;
      
      $specific_types = self::getDropdownSpecificFields();
      foreach ($specific_types as $ID => $field) {
         if (FieldExists(getTableForItemType(__CLASS__), $field)) {
            if (!$check_entity 
               || ($check_entity 
                  && self::isDropdownEntityRestrict($field)))
               $dropdowns["PluginGenericobject".ucfirst($type["name"]).$field] = 
                  PluginGenericobjectObject::getLabel($type["name"]) . ' : ' . 
                                                      $GENERICOBJECT_AVAILABLE_FIELDS[$field]['name'];
         }
      }
   }
   
   public static function getDropdownSpecificFields() {
      global $GENERICOBJECT_AVAILABLE_FIELDS;
      $specific_fields = array ();

      foreach ($GENERICOBJECT_AVAILABLE_FIELDS as $field => $values) {
         if (isset ($values["dropdown_type"]) && $values["dropdown_type"] == 'type_specific') {
            $specific_fields[$field] = $field;
         }
      }

      return $specific_fields;
   }
   
   public static function isDropdownTypeSpecific($field) {
      global $GENERICOBJECT_AVAILABLE_FIELDS;
      return (isset ($GENERICOBJECT_AVAILABLE_FIELDS[$field]['dropdown_type']) 
                 && $GENERICOBJECT_AVAILABLE_FIELDS[$field]['dropdown_type'] == 'type_specific');
   }
   
   
   //-------------------------------- FILE CREATION / DELETION ----------------------------//
   public static function deleteFile($filename) {
      if (file_exists($filename)) {
         unlink($filename);
      }
   }


   //Form pages
   
   static function getCompleteClassFilename($name) {
      return GENERICOBJECT_CLASS_PATH . "/".$name.".class.php";
   }

   static function getCompleteFormFilename($name) {
      return GENERICOBJECT_FRONT_PATH . "/".$name.".form.php";
   }

   static function getCompleteSearchFilename($name) {
      return GENERICOBJECT_FRONT_PATH . "/".$name.".php";
   }

   static function getCompleteAjaxTabFilename($name) {
      return GENERICOBJECT_AJAX_PATH . "/".$name.".tabs.php";
   }
   
   /**
    * Delete an used form file
    * @param name the name of the object type
    * @return nothing
    */
   public static function deleteFormFile($name) {
      self::deleteFile(self::getCompleteFormFilename($name));
   }

   public static function deleteDropdownFrontformFile($name, $field) {
      self::deleteFile(self::getCompleteFormFilename($name));
   }

   public static function deleteDropdownFrontFile($name, $field) {
      self::deleteFile(self::getCompleteSearchFilename($name));
   }

   public static function deleteSearchFile($name) {
      self::deleteFile(self::getCompleteSearchFilename($name));
   }

   public static function deleteAjaxFile($name) {
      self::deleteFile(self::getCompleteAjaxTabFilename($name));
   }

   public static function deleteDropdownAjaxFile($name, $field) {
      self::deleteFile(GENERICOBJECT_AJAX_PATH . "/".$name.$field.".tabs.php");
   }

   //Classes
   public static function deleteDropdownClassFile($name, $field) {
      self::deleteFile(GENERICOBJECT_CLASS_PATH . "/".$name.$field.".class.php");
   }

   /**
    * Delete an used class file
    * @param name the name of the object type
    * @return nothing
    */
   public static function deleteClassFile($name) {
      self::deleteFile(self::getCompleteClassFilename($name));
   }

   public static function deleteSpecificDropdownFiles($itemtype) {
      global $DB;
      $name = self::getNameByID($itemtype);
      $types = self::getDropdownSpecificFields();

      foreach($types as $type => $tmp) {
         self::deleteDropdownAjaxFile($name, $type);
         self::deleteDropdownFrontFile($name, $type);
         self::deleteDropdownFrontformFile($name, $type);
         self::deleteDropdownClassFile($name, $type);
      }
   }
   
   public static function addFileFromTemplate($name, $template, $directory, $filename) {
      $DBf_handle = fopen($template, "rt");
      $template_file = fread($DBf_handle, filesize($template));
      fclose($DBf_handle);
      $template_file = str_replace("%%CLASSNAME%%", self::getClassByName($name), $template_file);
      $DBf_handle = fopen($directory . "/".$filename.".php", "w");
      fwrite($DBf_handle, $template_file);
      fclose($DBf_handle);
      
   }
   
   public static function addDropdownFrontFile($name) {
      self::addFileFromTemplate($name, self::FRONT_DROPDOWN_TEMPLATE, GENERICOBJECT_FRONT_PATH, $name);
   }

   public static function addDropdownAjaxFile($name, $field) {
      self::addFileFromTemplate($name, self::AJAX_DROPDOWN_TEMPLATE, GENERICOBJECT_AJAX_PATH, $name.".tabs");
   }

   public static function addAjaxFile($name, $field) {
      self::addFileFromTemplate($name, self::AJAX_TEMPLATE, GENERICOBJECT_AJAX_PATH, $name.".tabs");
   }
   
   public static function addDropdownFrontformFile($name, $field) {
      self::addFileFromTemplate($name, self::FRONTFORM_DROPDOWN_TEMPLATE, GENERICOBJECT_FRONT_PATH, $name.".form");
   }

   public static function addDropdownClassFile($name, $field) {
      self::addFileFromTemplate($name, self::DROPDOWN_TEMPLATE, GENERICOBJECT_CLASS_PATH, $name.".class");
   } 

   /**
    * Write on the the class file for the new object type
    * @param name the name of the object type
    * @param classname the name of the new object
    * @param itemtype the object device type
    * @return nothing
    */
   public static function addClassFile($name, $classname) {
      self::addFileFromTemplate($name, self::CLASS_TEMPLATE, GENERICOBJECT_CLASS_PATH, $name.".class");
   }
   
   /**
    * Write on the the form file for the new object type
    * @param name the name of the object type
    * @param classname the name of the new object
    * @param itemtype the object device type
    * @return nothing
    */
   public static function addFormFile($name, $classname) {
      self::addFileFromTemplate($name, self::FORM_TEMPLATE, GENERICOBJECT_FRONT_PATH, $name.".form");
   }

   /**
    * Write on the the form file for the new object type
    * @param name the name of the object type
    * @param classname the name of the new object
    * @param itemtype the object device type
    * @return nothing
    */
   public static function addSearchFile($name, $classname) {
      self::addFileFromTemplate($name, self::SEARCH_TEMPLATE, GENERICOBJECT_FRONT_PATH, $name);
   }



   public static function deleteDropdownTable($name, $field) {
      global $DB;
      if (TableExists(self::getDropdownTableName($name, $field)))
         $DB->query("DROP TABLE IF EXISTS`" . self::getDropdownTableName($name, $field) . "`");
   }
   
   public static function deleteLinkTable($itemtype) {
      global $DB;
      $name = self::getNameByID($itemtype);
      $query = "DROP TABLE IF EXISTS `".self::getLinkDeviceTableName($name)."`";
      $DB->query($query);
   }
   
   
   
   public static function addDropdownTable($name, $field) {
      global $DB;
      if (!TableExists(self::getDropdownTableName($name, $field))) {
         if (!self::isDropdownEntityRestrict($field)) {
            $query = "CREATE TABLE `" . self::getDropdownTableName($name, $field) . "` (
                          `id` int(11) NOT NULL auto_increment,
                          `name` varchar(255) collate utf8_unicode_ci default NULL,
                          `comment` text collate utf8_unicode_ci,
                          PRIMARY KEY  (`id`),
                          KEY `name` (`name`)
                        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         } else {
            $query = "CREATE TABLE IF NOT EXISTS `" . 
                        self::getDropdownTableName($name, $field) . "` (
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
   

    /**
    * Get next available device typ
    * @return the next available device type 
    */
   public static function getNextDeviceType() {
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
   public static function deleteTable($itemype) {
      global $DB;
      $DB->query("DELETE FROM `glpi_displaypreferences` WHERE `itemtype`='$itemype'");
      $DB->query("DROP TABLE IF EXISTS `".getTableForItemType($itemype)."`");
   }

   public static function getDropdownTableName($name, $field) {
      return getPlural("glpi_plugin_genericobject_" . $name . $field);
   }



   public static function isDropdownEntityRestrict($field) {
      global $GENERICOBJECT_AVAILABLE_FIELDS;
      return (isset ($GENERICOBJECT_AVAILABLE_FIELDS[$field]['entity']) 
                  && $GENERICOBJECT_AVAILABLE_FIELDS[$field]['entity'] == 'entity_restrict');
   }

   public static function enableTemplateManagement($name) {
      global $DB;
      $table = self::getTableNameByName($name);
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

   public static function disableTemplateManagement($name) {
      global $DB;

      $table = self::getTableNameByName($name);

      if (FieldExists($table, "is_template")) {
         $table = self::getTableNameByName($name);
         $query = "ALTER TABLE `$table` DROP `is_template`";
         $DB->query($query);
      }

      if (FieldExists($table, "template_name")) {
         $query = "ALTER TABLE `$table` DROP `template_name`";
         $DB->query($query);
      }
   }

   public static function getDatabaseRelationsSpecificDropdown(& $dropdowns, $type) {
      global $GENERICOBJECT_AVAILABLE_FIELDS;
      $specific_types = self::getDropdownSpecificFields();
      $table = self::getTableNameByName($type["name"]);

      foreach ($specific_types as $ID => $field) {
         if (TableExists($table) && FieldExists($table, $field)) {
            $dropdowns[$table] = array (
               self::getDropdownTableName($type["name"], $field) => 
                  $GENERICOBJECT_AVAILABLE_FIELDS[$field]['linkfield']);
         }
      }
   }

   public static function deleteSpecificDropdownTables($itemtype) {
      global $DB;
      foreach(self::getDropdownSpecificFields() as $dropdown_itemtype => $tmp) {
         $DB->query("DROP TABLE IF EXISTS `" . getTableForItemType($dropdown_itemtype)."`");
      }
   }

   /**
    * Get an internal ID by the object name
    * @param name the object's name
    * @return the object's ID
    */
   static function getIDByName($name) {
      global $DB;
      $query = "SELECT `itemtype` FROM `".getTableForItemType(__CLASS__)."` " .
               "WHERE `name`='$name'";
      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         return $DB->result($result, 0, "itemtype");
      } else {
         return 0;
      }
   }
   
   /**
    * Get object name by ID
    * @param ID the internal ID
    * @return the name associated with the ID
    */
   static function getNameByID($itemtype) {
      global $DB;
      $query = "SELECT `name` FROM `".getTableForItemType(__CLASS__)."` " .
               "WHERE `itemtype`='$itemtype'";
      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         return $DB->result($result, 0, "name");
      } else {
         return "";
      }
   }
   
   public static function removeDataInjectionModels($itemtype) {
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
   public static function deleteLoans($itemtype) {
      global $DB;
      
      $query = "DELETE FROM  `glpi_reservationitems`, `glpi_reservations` " .
               "USING `glpi_reservationitems`, `glpi_reservations` " .
                  "WHERE `glpi_reservationitems`.`itemtype`='$itemtype' " .
                     "AND `glpi_reservationitems`.`id`=`glpi_reservations`.`reservationitems_id`";
      $DB->query($query); 
   }


   static function deleteNetworking($itemtype) {
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

   
   /**
    * Get the object table name, by giving the identifier
    * @param name the object's internal identifier
    * @return the classname associated with the object
    */
   static function getTableByName($name) {
      return 'glpi_plugin_genericobject_' . getPlural($name);
   }
   
   /**
    * Get the object ID, by giving the name
    * @param name the object's internal identifier
    * @return the ID associated with the object
    */
   static function getIdentifierByName($name) {
      return 'PLUGIN_GENERICOBJECT_' . strtoupper($name) . '_TYPE';
   }
   
   /**
    * Get the object class, by giving the name
    * @param name the object's internal identifier
    * @return the class associated with the object
    */
   static function getClassByName($name) {
      return 'PluginGenericobject' . ucfirst($name);
   }
   
   /**
    * Get all types of active&published objects
    */
   static function getTypes($all = false) {
      $table = getTableForItemType(__CLASS__);
      if (TableExists($table)) {
         return getAllDatasFromTable($table, ($all?" status=" . self::ACTIVE:""));
      } else {
         return array ();
      }
   }
   
   /**
    * Register all object's types and values
    * @return nothing
    */
   static function registerNewTypes() {
      //Only look for published and active types
   
      foreach (self::getTypes() as $id => $objecttype) {
         call_user_func(array($objecttype['itemtype'], 'registerType'));
      }
   }
   
   /**
    * Register all variables for a type
    * @param type the type's attributes
    * @return nothing
    */
   static function registerOneType(PluginGenericobjectType $objecttype) {
      global $LANG, $DB, $PLUGIN_HOOKS, $CFG_GLPI, 
            $IMPORT_PRIMARY_TYPES, $IMPORT_TYPES, $ORDER_AVAILABLE_TYPES,
            $ORDER_TYPE_TABLES,$ORDER_MODEL_TABLES, $ORDER_TEMPLATE_TABLES,
            $UNINSTALL_TYPES,$GENERICOBJECT_PDF_TYPES,$GENINVENTORYNUMBER_INVENTORY_TYPES;

      $itemtype  = get_class($objecttype);
      $table     = getTableForItemType($itemtype);
      $fields    = $DB->list_fields($table);

      //If table doesn't exists, do not try to register !
      if (class_exists($objecttype->fields['itemtype'])) {
         call_user_func(array($objecttype->fields['itemtype'], 'registerType'));

/*
         $PLUGIN_HOOKS['submenu_entry']['genericobject']['search'][$name] = 'front/search.php?itemtype=' . $itemtype;
           if ($type['use_plugin_uninstall'] && $plugin->isActivated('uninstall')) {
              Plugin::load("uninstall");
              $UNINSTALL_TYPES[] = $typeID;
           }
*/
/*
         // Later, when per entity and tree dropdowns will be managed !
         foreach (self::getSpecificDropdownsTablesByType($i) as $table => $name) {
            array_push($CFG_GLPI["specif_entities_tables"], $table);
            //array_push($CFG_GLPI["dropdowntree_tables"], $table);
            
            //$PLUGIN_HOOKS['submenu_entry']['genericobject']['add'][$name.$field] = "front/$name.$field.php";
         }
*/ 
      }
   }
   
   /**
    * Include locales for a specific type
    * @name object type's name
    * @return nothing
    */
   static function includeLocales($name) {
      global $CFG_GLPI, $LANG;
   
      $prefix = GENERICOBJECT_DIR . "/objects/" . $name . "/" . $name;
      if (isset ($_SESSION["glpilanguage"]) 
             && file_exists($prefix . "." . $CFG_GLPI["languages"][$_SESSION["glpilanguage"]][1])) {
         include_once ($prefix . "." . $CFG_GLPI["languages"][$_SESSION["glpilanguage"]][1]);
   
      } else
         if (file_exists($prefix . ".en_GB.php")) {
            include_once ($prefix . ".en_GB.php");
   
         } else
            if (file_exists($prefix . ".fr_FR.php")) {
               include_once ($prefix . ".fr_FR.php");
   
            } else {
               return false;
            }
      return true;
   }

   /**
    * Get table name by ID
    * @param ID the object's ID
    * @return the table
    */
   static function getTableNameByID($ID) {
      return self::getTableNameByName(self::getNameByID($ID));
   }
   
   /**
    * Get table name by name
    * @param ID the object's ID
    * @return the table
    */
   static function getTableNameByName($name) {
      return 'glpi_plugin_genericobject_' . getPlural($name);
   }

   //------------------------------- GETTERS -------------------------//

   function canBeLinked() {
      return $this->fields['use_links'];
   }

   function canUseTemplate() {
      return $this->fields['use_template'];
   }
   
   function canBeDeleted() {
      return $this->fields['use_deleted'];
   }
   
   function canBeEntityAssigned() {
      return $this->fields['use_entity'];
   }
   
   function canBeRecursive() {
      return $this->fields['use_recursivity'];
   }
   
   function canBeReserved() {
      return $this->fields['use_loans'];
   }
   
   function canUseNotepad() {
      return $this->fields['use_notes'];
   }
   
   function canUseHistory() {
      return $this->fields['use_history'];
   } 
   
   function canUseDocuments() {
      return $this->fields['use_documents'];
   }
   
   function canUseInfocoms() {
      return $this->fields['use_infocoms'];
   }

   function canUseContract() {
      return $this->fields['use_contracts'];
   }
   
   function canUseTickets() {
      return $this->fields['use_tickets'];
   }
   
   function canUseNetworkPorts() {
      return $this->fields['use_network_ports'];
   }
   
   function canUseDirectConnections() {
      return $this->fields['use_direct_connections'];
   }

   function canUsePluginDataInjection() {
      return $this->fields['use_plugin_datainjection'];
   }

   function canUsePluginOrder() {
      return $this->fields['use_plugin_order'];
   }

   function canUsePluginPDF() {
      return $this->fields['use_plugin_pdf'];
   }

   function canUsePluginUninstall() {
      return $this->fields['use_plugin_uninstall'];
   }

   function canUsePluginGeninventoryNumber() {
      return $this->fields['use_plugin_geninventorynumber'];
   }

   //------------------------------- INSTALL / UNINSTALL METHODS -------------------------//
   static function install(Migration $migration) {
      global $DB;
      
      if (!TableExists(getTableForItemType(__CLASS__))) {
         $query = "CREATE TABLE `glpi_plugin_genericobject_types` (
                           `id` INT( 11 ) NOT NULL AUTO_INCREMENT,
                           `entities_id` INT( 11 ) NOT NULL DEFAULT 0,
                           `itemtype` varchar(255) collate utf8_unicode_ci default NULL,
                           `state` INT( 2 ) NOT NULL DEFAULT 0 ,
                           `status` INT ( 1 )NOT NULL DEFAULT 0 ,
                           `name` varchar(255) collate utf8_unicode_ci default NULL,
                           `use_deleted` tinyint(1) NOT NULL default '0',
                           `use_notes` tinyint(1) NOT NULL default '0',
                           `use_history` tinyint(1) NOT NULL default '0',
                           `use_entity` tinyint(1) NOT NULL default '0',
                           `use_recursivity` tinyint(1) NOT NULL default '0',
                           `use_template` tinyint(1) NOT NULL default '0',
                           `use_infocoms` tinyint(1) NOT NULL default '0',
                           `use_contracts` tinyint(1) NOT NULL default '0',
                           `use_documents` tinyint(1) NOT NULL default '0',
                           `use_tickets` tinyint(1) NOT NULL default '0',
                           `use_links` tinyint(1) NOT NULL default '0',
                           `use_loans` tinyint(1) NOT NULL default '0',
                           `use_network_ports` tinyint(1) NOT NULL default '0',
                           `use_direct_connections` tinyint(1) NOT NULL default '0',
                           `use_plugin_datainjection` tinyint(1) NOT NULL default '0',
                           `use_plugin_pdf` tinyint(1) NOT NULL default '0',
                           `use_plugin_order` tinyint(1) NOT NULL default '0',
                           `use_plugin_uninstall` tinyint(1) NOT NULL default '0',
                           `use_plugin_geninventorynumber` tinyint(1) NOT NULL default '0',
                           PRIMARY KEY ( `id` ) 
                           ) ENGINE = MYISAM COMMENT = 'Object types definition table' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die($DB->error());
      }
      
      $migration->addField(getTableForItemType(__CLASS__), "use_network_ports", 
                           "tinyint(1) NOT NULL default '0'");
      $migration->addField(getTableForItemType(__CLASS__), "use_direct_connections", 
                           "tinyint(1) NOT NULL default '0'");
      $migration->addField(getTableForItemType(__CLASS__), "use_plugin_geninventorynumber", 
                           "tinyint(1) NOT NULL default '0'");
      $migration->addField(getTableForItemType(__CLASS__), "use_contracts", 
                           "tinyint(1) NOT NULL default '0'");
      $migration->migrationOneTable(getTableForItemType(__CLASS__));
      

      //Displayprefs
      $prefs = array(10 => 6, 9 => 5, 8 => 4, 7 => 3, 6 => 2, 2 => 1, 4 => 1, 11 => 7,  12 => 8, 
                     14 => 10, 15 => 11);
      foreach ($prefs as $num => $rank) {
         if (!countElementsInTable("glpi_displaypreferences", 
                                    "`itemtype`='".__CLASS__."' AND `num`='$num' 
                                       AND `rank`='$rank' AND `users_id`='0'")) {
            $DB->query("INSERT INTO glpi_displaypreferences 
                        VALUES (NULL,'".__CLASS__."','$num','$rank','0');") 
               or die($DB->error());
         }
      }
   }
   
   static function uninstall() {
      global $DB;
      
      $query = "DROP TABLE IF EXISTS `".getTableForItemType(__CLASS__)."`";
      $DB->query($query) or die($DB->error());
   
      $tables = array ("glpi_displaypreferences", "glpi_documents_items", "glpi_bookmarks",
                       "glpi_logs");
      foreach ($tables as $table) {
         $query = "DELETE FROM `$table` WHERE `itemtype`='".__CLASS__."'";
         $DB->query($query);
      }
   }
}