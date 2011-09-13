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
   const FRONT_DROPDOWN_TEMPLATE     = "../objects/front.tpl";
   const SEARCH_TEMPLATE             = "../objects/front.tpl";
   const AJAX_DROPDOWN_TEMPLATE      = "../objects/dropdown.tabs.tpl";
   const AJAX_TEMPLATE               = "../objects/ajax.tabs.tpl";
   const LOCALE_TEMPLATE             = "../objects/locale.tpl";
   const OBJECTINJECTION_TEMPLATE    = "../objects/objectinjection.class.tpl";

   var $dohistory = true;
   
   static function &getInstance($itemtype, $refresh = false) {
      static $singleton = array();
      if (!isset($singleton[$itemtype]) ||$refresh) {
         $singleton[$itemtype] = new self($itemtype);
      }
      return $singleton[$itemtype];
   }
   
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
         $ong[5]  = $LANG['genericobject']['config'][7];
         $ong[6]  = $LANG['Menu'][35];
         //$ong[12] = $LANG['title'][38];
      }

      return $ong;
   }

   function showForm($ID, $options = array()) {

      if ($ID > 0) {
         $this->check($ID, 'r');
      } else {
         // Create item
         $this->check(-1, 'w');
         $this->getEmpty();
      }

      $this->showTabs($options);
      $this->addDivForTabs();

      return true;
   }

   function showBehaviorForm($ID, $options=array()) {
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
      self::includeConstants($this->fields["name"]);
      
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . $LANG['genericobject']['common'][1] . "</td>";
      echo "<td>";
      if (!$ID) {
         autocompletionTextField($this, 'name', array('value' => $this->fields["name"]));
      } else {
         echo "<input type='hidden' name='name' value='" . $this->fields["name"] . "'>";
         echo $this->fields["name"];
      }

      echo "</td>";
      echo "<td>" . $LANG['genericobject']['config'][9] . "</td>";
      echo "<td>";
      if ($ID) {
         echo call_user_func(array($this->fields["itemtype"], "getTypeName"));
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
         Dropdown::showYesNo("is_active", $this->fields["is_active"]);
      }
      echo "</td><td colspan='2'></td>";
      echo "</tr>";

      if (!$this->isNewID($ID)) {
         $canedit = $this->can($ID, 'w');
         echo "<tr class='tab_bg_1'><th colspan='4'>";
         echo $LANG['genericobject']['config'][3];
         echo "</th></tr>";
   
         $use = array ("use_recursivity"          => $LANG['entity'][9],
                       "use_tickets"              => $LANG['title'][24],
                       "use_deleted"              => $LANG['ocsconfig'][49],
                       "use_notes"                => $LANG['title'][37],
                       "use_history"              => $LANG['title'][38],
                       "use_template"             => $LANG['common'][14],
                       "use_infocoms"             => $LANG['financial'][3],
                       "use_contracts"            => $LANG['Menu'][25],
                       "use_documents"            => $LANG['Menu'][27],
                       "use_loans"                => $LANG['Menu'][17],
                       "use_unicity"              => $LANG['setup'][811],
                       "use_network_ports"        => $LANG['genericobject']['config'][14],
                       "use_plugin_datainjection" => $LANG['genericobject']['config'][10],
   //                    "use_plugin_pdf"           => $LANG['genericobject']['config'][11],
                       "use_plugin_order"         => $LANG['genericobject']['config'][12],
                       "use_plugin_uninstall"     => $LANG['genericobject']['config'][13]);
   
         $plugin = new Plugin();
         $odd=0;
         foreach ($use as $right => $label) {
            if (!$odd) {
               echo "<tr class='tab_bg_2'>";
            }
            echo "<td>" . $LANG['genericobject']['config'][1] . " " . $label . "</td>";
            echo "<td>";
   
            switch ($right) {
               case 'use_deleted':
                  Dropdown::showYesno('use_deleted', $this->canBeDeleted());
                  break;
   
               case 'use_recursivity':
                  Dropdown::showYesno('use_recursivity', $this->canBeRecursive());
                  break;
   
               case 'use_notes':
                  Dropdown::showYesno('use_notes', $this->canUseNotepad());
                  break;
   
               case 'use_template':
                  Dropdown::showYesno('use_template', $this->canUseTemplate());
                  break;
   
               case 'use_plugin_datainjection' :
                  if ($this->canUsePluginDataInjection()) {
                     Dropdown::showYesNo($right, $this->fields[$right]);
                  } else {
                     echo DROPDOWN_EMPTY_VALUE."<input type='hidden' name='use_plugin_datainjection' value='0'>\n";
                  }
                  break;
               case 'use_plugin_pdf' :
                  if ($this->canUsePluginPDF()) {
                     Dropdown::showYesNo($right, $this->fields[$right]);
                  } else {
                     echo DROPDOWN_EMPTY_VALUE."<input type='hidden' name='use_plugin_pdf' value='0'>\n";
                  }
                  break;
               case 'use_plugin_order' :
                  if ($this->canUsePluginOrder()) {
                     Dropdown::showYesNo($right, $this->fields[$right]);
                  } else {
                     echo DROPDOWN_EMPTY_VALUE."<input type='hidden' name='use_plugin_order' value='0'>\n";
                  }
                  break;
   
               case 'use_plugin_uninstall' :
                  if ($this->canUsePluginUninstall()) {
                     Dropdown::showYesNo($right, $this->fields[$right]);
                  } else {
                     echo DROPDOWN_EMPTY_VALUE."<input type='hidden' name='use_plugin_uninstall' value='0'>\n";
                  }
               
                  break;
               default :
                     Dropdown::showYesNo($right, $this->fields[$right]);
                  break;
            }
            echo "</td>";
            if ($odd == 1) {
               $odd = 0;
               echo "</tr>";
            } else {
               $odd++;
            }
         }
         if ($odd != 0) {
            echo "<td></td></tr>";
         }
      }

      $this->showFormButtons($options);
   }

   function prepareInputForAdd($input) {
      global $LANG;
      
      //Name must not be empty
      if (isset($input['name']) && $input['name'] == '') {
         addMessageAfterRedirect($LANG['genericobject']['common'][5], ERROR, true);
         return array();
      }
      
      //Name must start with a letter
      if (!preg_match("/^[a-zA-Z]+/i",$input['name'])) {
         addMessageAfterRedirect($LANG['genericobject']['common'][6], ERROR, true);
         return array();
      }
      $input['name']     = self::filterInput($input['name']);
      
      //Name must not be present in DB
      if (countElementsInTable(getTableForItemType(__CLASS__), "`name`='".$input['name']."'")) {
         addMessageAfterRedirect($LANG['genericobject']['common'][4], ERROR, true);
         return array();
      } else {
         $input['itemtype'] = self::getClassByName($input['name']);
         return $input;
      }
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

      //Write object class on the filesystem
      self::addLocales($this->input["name"], $this->input["itemtype"]);

      //Reload profiles
      PluginGenericobjectProfile::changeProfile();
      return true;
   }

   function prepareInputForUpdate($input) {
      $type = new self();
      $this->getFromDB($input["id"]);
      if (isset ($input["is_active"]) && $input["is_active"]) {
         $type->fields = $this->fields;
         self::registerOneType($type);
      }

      return $input;
   }

   function post_updateItem($history = 1) {
      $this->checkNecessaryFieldsUpdate();
   }

   function pre_deleteItem() {
      if ($this->getFromDB($this->fields["id"])) {
         $name     = $this->fields['name'];
         $itemtype = $this->fields['itemtype'];
         
         //Delete all network ports
         self::deleteNetworking($itemtype);
         
         //Drop all dropdowns associated with itemtype
         self::deleteDropdownsForItemtype($itemtype);
         
         //Delete loans associated with this type
         self::deleteLoans($itemtype);
   
         //Delete loans associated with this type
         self::deleteUnicity($itemtype);
   
         //Remove class from the filesystem
         self::deleteClassFile($name);
   
         //Remove form from the filesystem
         self::deleteFormFile($name);
   
         //Remove form from the filesystem
         self::deleteSearchFile($name);
   
         //Remove ajax file
         self::deleteAjaxFile($name);

         //Remove datainjection specific file
         self::deleteInjectionFile($name);
   
         //Delete profile informations associated with this type
         PluginGenericobjectProfile::deleteTypeFromProfile($itemtype);
         
         self::deleteTicketAssignation($itemtype);
         
         //Remove existing datainjection models
         self::removeDataInjectionModels($itemtype);
   
         //Drop itemtype table
         self::deleteTable($itemtype);
         
         //Delete specific locale directory
         self::deleteLocales($name, $itemtype);
         
         return true;
      } else {
         return false;
      }

   }

   function checkNecessaryFieldsUpdate() {
      $itemtype = $this->fields["itemtype"];
      $item     = new $itemtype();
      $item->getEmpty();
      $table    = getTableForItemType($itemtype);
      //Recursivity
      if (isset($this->input['use_recursivity']) && $this->input['use_recursivity']) {
         PluginGenericobjectField::addNewField($table, 'is_recursive', 'entities_id');
      } else {
         PluginGenericobjectField::deleteField($table, 'is_recursive');
      }

      //Template
      if (isset($this->input['use_template']) && $this->input['use_template']) {
         PluginGenericobjectField::addNewField($table, 'is_template', 'id');
         PluginGenericobjectField::addNewField($table, 'template_name', 'is_template');
      } else {
         PluginGenericobjectField::deleteField($table, 'is_template');
         PluginGenericobjectField::deleteField($table, 'template_name');
      }

      //Trash
      if (isset($this->input['use_deleted']) && $this->input['use_deleted']) {
         PluginGenericobjectField::addNewField($table, 'is_deleted', 'id');
      } else {
         PluginGenericobjectField::deleteField($table, 'is_deleted');
      }

      //Reservation needs is_deleted field !
      if ($this->canBeReserved()) {
         PluginGenericobjectField::addNewField($table, 'is_deleted', 'id');
         PluginGenericobjectField::addNewField($table, 'locations_id');
      }
      
      //Helpdesk post-only
      if ($this->canUseTickets()) {
         PluginGenericobjectField::addNewField($table, 'is_helpdesk_visible', 'comment');
      } else {
         PluginGenericobjectField::deleteField($table, 'is_helpdesk_visible');
      }
      
      //Notes
      if (isset($this->input['use_notes']) && $this->input['use_notes']) {
         PluginGenericobjectField::addNewField($table, 'notepad', 'id');
      } else {
         PluginGenericobjectField::deleteField($table, 'notepad');
      }
      
      //Networkport
      if ($this->canUseNetworkPorts()) {
         PluginGenericobjectField::addNewField($table, 'locations_id');
      }

      if ($this->canUsePluginDataInjection() && 
         !file_exists(self::getCompleteInjectionFilename($this->fields['name']))) {
         self::addDatainjectionFile($this->fields['name']);
      }

      if (!$this->canUsePluginDataInjection() && 
         file_exists(self::getCompleteInjectionFilename($this->fields['name']))) {
         self::deleteInjectionFile($this->fields['name']);
      }
   }
   
   function getSearchOptions() {
      global $LANG;
      $sopt['common'] = $LANG["genericobject"]["title"][1];
   
      $sopt[1]['table']       = $this->getTable();
      $sopt[1]['field']       = 'name';
      $sopt[1]['name']        = $LANG["common"][22];
      $sopt[1]['datatype']    = 'itemlink';

      $sopt[5]['table']       = $this->getTable();
      $sopt[5]['field']       = 'is_active';
      $sopt[5]['name']        = $LANG['common'][60];
      $sopt[5]['datatype']    = 'bool';
   
      $sopt[6]['table']       = $this->getTable();
      $sopt[6]['field']       = 'use_tickets';
      $sopt[6]['name']        = $LANG['genericobject']['config'][1]." ".$LANG['Menu'][31];
      $sopt[6]['datatype']    = 'bool';

/*   
      $sopt[7]['table']       = $this->getTable();
      $sopt[7]['field']       = 'use_deleted';
      $sopt[7]['name']        = $LANG['genericobject']['config'][1]." ".$LANG['ocsconfig'][49];
      $sopt[7]['datatype']    = 'bool';
   
      $sopt[8]['table']       = $this->getTable();
      $sopt[8]['field']       = 'use_notes';
      $sopt[8]['name']        = $LANG['genericobject']['config'][1]." ".$LANG['title'][37];
      $sopt[8]['datatype']    = 'bool';
*/
      $sopt[9]['table']       = $this->getTable();
      $sopt[9]['field']       = 'use_history';
      $sopt[9]['name']        = $LANG['genericobject']['config'][1]." ".$LANG['title'][38];
      $sopt[9]['datatype']    = 'bool';

/*
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
*/

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

      $sopt[16]['table']      = $this->getTable();
      $sopt[16]['field']      = 'use_loans';
      $sopt[16]['name']       = $LANG['genericobject']['config'][1]." ".$LANG['Menu'][25];
      $sopt[16]['datatype']   = 'bool';

      $sopt[17]['table']       = $this->getTable();
      $sopt[17]['field']       = 'use_unicity';
      $sopt[17]['name']        = $LANG['genericobject']['config'][1]." ".$LANG['setup'][811];
      $sopt[17]['datatype']    = 'bool';

      return $sopt;
   }
   
   /**
    * Add object type table + entries in glpi_display
    * @name object type's name
    * @return nothing
    */
   public static function addTable($itemtype) {
      global $DB;
      $query = "CREATE TABLE IF NOT EXISTS `".getTableForItemType($itemtype)."` (
                  `id` INT( 11 ) NOT NULL AUTO_INCREMENT,
                  `entities_id` INT( 11 ) NOT NULL DEFAULT '0',
                  `name` VARCHAR( 255 ) collate utf8_unicode_ci NOT NULL DEFAULT '',
                  `comment` text COLLATE utf8_unicode_ci,
                  `notepad` text COLLATE utf8_unicode_ci,
                  `date_mod` DATETIME NULL  ,
                  PRIMARY KEY ( `id` ) 
                  ) ENGINE = MYISAM COMMENT = '$itemtype' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
      $DB->query($query);

      $query = "INSERT INTO `glpi_displaypreferences` (`id`, `itemtype`, `num`, `rank`, `users_id`) " .
               "VALUES (NULL, '$itemtype', '2', '1', '0');";
      $DB->query($query);

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

   static function getCompleteInjectionFilename($name) {
      return GENERICOBJECT_CLASS_PATH . "/".$name."injection.class.php";
   }
   
   /**
    * Delete an used form file
    * @param name the name of the object type
    * @return nothing
    */
   public static function deleteFormFile($name) {
      self::deleteFile(self::getCompleteFormFilename($name));
   }

   public static function deleteSearchFile($name) {
      self::deleteFile(self::getCompleteSearchFilename($name));
   }

   public static function deleteAjaxFile($name) {
      self::deleteFile(self::getCompleteAjaxTabFilename($name));
   }

   /**
    * Delete an used class file
    * @param name the name of the object type
    * @return nothing
    */
   public static function deleteClassFile($name) {
      self::deleteFile(self::getCompleteClassFilename($name));
   }

   public static function deleteInjectionFile($name) {
      self::deleteFile(self::getCompleteInjectionFilename($name));
   }
   
   
   public static function addLocales($name, $itemtype) {
      global $CFG_GLPI;
      $locale_dir = GENERICOBJECT_LOCALES_PATH."/".$name;
      if (!is_dir($locale_dir)) {
         @ mkdir($locale_dir, 0777, true);
      }
      $locale_file = $name.".".$_SESSION['glpilanguage'];
      self::addFileFromTemplate(array('NAME'      => $name, 
                                      'CLASSNAME' => self::getClassByName($name)), 
                                self::LOCALE_TEMPLATE, $locale_dir, 
                                $locale_file);
      if ($CFG_GLPI['language'] != $_SESSION['glpilanguage']) {
         $locale_file = $name.".".$CFG_GLPI['language'];
         self::addFileFromTemplate(array('CLASSNAME' => $name), self::LOCALE_TEMPLATE, $locale_dir, 
                                   $locale_file);
      }
   }

   public static function deleteLocales($name, $itemtype) {
      foreach (glob(GLPI_ROOT . '/plugins/genericobject/locales/'.$name.'/*.php') as $file) {
         @unlink($file);
      }
      @rmdir(GLPI_ROOT . '/plugins/genericobject/locales/'.$name);
   }

   public static function addFileFromTemplate($mappings = array(), $template, $directory, $filename) {
      if (!empty($mappings)) {
         $file_read = @fopen($template, "rt");
         if ($file_read) {
            $template_file = fread($file_read, filesize($template));
            foreach ($mappings as $name => $value) {
               $template_file = str_replace("%%$name%%", $value, $template_file);
            }
            fclose($file_read);
            $file_write = @fopen($directory . "/".$filename.".php", "w");
            if ($file_write) {
               fwrite($file_write, $template_file);
               fclose($file_write);
            }
         }
      }
   }

   public static function addDatainjectionFile($name) {
      self::addFileFromTemplate(array('CLASSNAME' => self::getClassByName($name), 
                                      'INJECTIONCLASS' => self::getClassByName($name)."Injection"), 
                                self::OBJECTINJECTION_TEMPLATE, GENERICOBJECT_CLASS_PATH, 
                                $name."injection.class");
   }
   
   public static function addDropdownFrontFile($name) {
      self::addFileFromTemplate(array('CLASSNAME' => self::getClassByName($name)), 
                                self::FRONT_DROPDOWN_TEMPLATE, GENERICOBJECT_FRONT_PATH, $name);
   }

   public static function addDropdownAjaxFile($name, $field) {
      self::addFileFromTemplate(array('CLASSNAME' => self::getClassByName($name)), 
                                self::AJAX_DROPDOWN_TEMPLATE, GENERICOBJECT_AJAX_PATH, $name.".tabs");
   }

   public static function addAjaxFile($name, $field) {
      self::addFileFromTemplate(array('CLASSNAME' => self::getClassByName($name)), 
                                self::AJAX_TEMPLATE, GENERICOBJECT_AJAX_PATH, $name.".tabs");
   }
   
   public static function addDropdownFrontformFile($name, $field) {
      self::addFileFromTemplate(array('CLASSNAME' => self::getClassByName($name)), 
                                self::FRONTFORM_DROPDOWN_TEMPLATE, GENERICOBJECT_FRONT_PATH, 
                                $name.".form");
   }

   public static function addDropdownClassFile($name, $field, $tree) {
      self::addFileFromTemplate(array('CLASSNAME' => self::getClassByName($name), 
                                      'EXTENDS' => ($tree?"CommonTreeDropdown":"CommonDropdown")), 
                                self::CLASS_DROPDOWN_TEMPLATE, GENERICOBJECT_CLASS_PATH, 
                                $name.".class");
   } 

   /**
    * Write on the the class file for the new object type
    * @param name the name of the object type
    * @param classname the name of the new object
    * @param itemtype the object device type
    * @return nothing
    */
   public static function addClassFile($name, $classname) {
      self::addFileFromTemplate(array('CLASSNAME' => self::getClassByName($name)), 
                                self::CLASS_TEMPLATE, GENERICOBJECT_CLASS_PATH, $name.".class");
   }
   
   /**
    * Write on the the form file for the new object type
    * @param name the name of the object type
    * @param classname the name of the new object
    * @param itemtype the object device type
    * @return nothing
    */
   public static function addFormFile($name, $classname) {
      self::addFileFromTemplate(array('CLASSNAME' => self::getClassByName($name)), 
                                self::FORM_TEMPLATE, GENERICOBJECT_FRONT_PATH, $name.".form");
   }

   /**
    * Write on the the form file for the new object type
    * @param name the name of the object type
    * @param classname the name of the new object
    * @param itemtype the object device type
    * @return nothing
    */
   public static function addSearchFile($name, $classname) {
      self::addFileFromTemplate(array('CLASSNAME' => self::getClassByName($name)), 
                                self::SEARCH_TEMPLATE, GENERICOBJECT_FRONT_PATH, $name);
   }

   //-------------------- ADD / DELETE TABLES ----------------------------------//
   
   /**
    * Add a new dropdown table
    * @param table the table name
    * @param entity_assign can the dropdown be assigned to an entity
    * @param recursive can the dropdown be recursive
    * @param tree can the dropdown be a tree dropdown
    * @return nothing
    */
   public static function addDropdownTable($table, $entity_assign = false, $recursive = false, 
                                           $tree = false) {
      global $DB;
      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                       `id` int(11) NOT NULL auto_increment,
                       `name` varchar(255) collate utf8_unicode_ci default NULL,
                       `comment` text collate utf8_unicode_ci,
                       PRIMARY KEY  (`id`),
                       KEY `name` (`name`)
                     ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query);
      }
      if ($entity_assign) {
         $query = "ALTER TABLE `$table` ADD `entities_id` INT(11) NOT NULL DEFAULT '0'";
         $DB->query($query);
         if ($recursive) {
            $query = "ALTER TABLE `$table` " .
                     "ADD `is_recursive` TINYINT(1) NOT NULL DEFAULT '0' AFTER `entities_id`";
            $DB->query($query);
         }
      }
      if ($tree) {
         $query = "ALTER TABLE `$table` ADD `completename` text COLLATE utf8_unicode_ci, 
                                        ADD `level` int(11) NOT NULL DEFAULT '0',
                                        ADD `ancestors_cache` longtext COLLATE utf8_unicode_ci,
                                        ADD `sons_cache` longtext COLLATE utf8_unicode_ci";
         $DB->query($query);
      }
   }
   
   /**
    * Delete object type table + entries in glpi_display
    * @name object type's name
    * @return nothing
    */
   public static function deleteTable($itemtype) {
      global $DB;
      $preferences = new DisplayPreference();
      $preferences->deleteByCriteria(array("itemtype" => $itemtype));
      $DB->query("DROP TABLE IF EXISTS `".getTableForItemType($itemtype)."`");
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
   
   /**
    * Delete all tickets for an itemtype
    * @param the itemtype
    * @return nothing
    */
   public static function deleteTicketAssignation($itemtype) {
      global $DB;
      $ticket = new Ticket();
      foreach ($ticket->find("`itemtype`='$itemtype'") as $data) {
         $data['itemtype'] = '';
         $data['items_id'] = 0;
         $ticket->update($data);
      }
   }
   
   /**
    * Remove datainjection models for an itemtype
    * @param the itemtype
    * @return nothing
    */
   public static function removeDataInjectionModels($itemtype) {
      $plugin = new Plugin();
      //Delete if exists datainjection models
      if ($plugin->isInstalled("datainjection")) {
         $model = new PluginDatainjectionModel();
         foreach ($model->find("`itemtype`='$itemtype'") as $data) {
            $model->delete($data);
         }
      }
   }

   /**
    * Delete all loans associated with a itemtype
    * @param the itemtype
    * @return nothing
    */
   public static function deleteLoans($itemtype) {
      $reservation_item = new ReservationItem();
      foreach ($reservation_item->find("`itemtype`='$itemtype'") as $data) {
         $reservation_item->delete($data);
      }
   }

   /**
    * Delete all loans associated with a itemtype
    * @param the itemtype
    * @return nothing
    */
   public static function deleteUnicity($itemtype) {
      $unicity = new FieldUnicity();
      $unicity->deleteByCriteria(array('itemtype' => $itemtype));
   }

   /**
    * Delete network ports for an itemtype
    * @param the itemtype
    * @return nothing
    */
   static function deleteNetworking($itemtype) {
       $networkport = new NetworkPort();
       foreach ($networkport->find("`itemtype`='$itemtype'") as $port) {
         $networkport->delete($port);
       }
   }
   
   /**
    * Filter values inserted by users : remove accented chars
    * @param value the value to be filtered
    * @return the filtered value
    */
   static function filterInput($value) {
      $search = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
      $replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u");
      $value = str_replace(array(' ', '_', '-', '+', '|', '[', ']', '\'','"', '@', '&', '~', '#', '='), 
                           '', strtolower($value));
      $value = str_replace($search, $replace, $value);
      return $value;
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
         $mytypes = array();
         foreach (getAllDatasFromTable($table, (!$all?" is_active=" . self::ACTIVE:"")) as $data) {
            //If class is not present on the filesystem, do not list itemtype
            if (file_exists(GENERICOBJECT_CLASS_PATH."/".$data['name'].".class.php")) {
               $mytypes[$data['itemtype']] = $data;
            }
         }
         return $mytypes;
      } else {
         return array ();
      }
   }
   
   /**
    * Register all variables for a type
    * @param type the type's attributes
    * @return nothing
    */
   static function registerOneType(PluginGenericobjectType $objecttype) {
      //If table doesn't exists, do not try to register !
      if (class_exists($objecttype->fields['itemtype'])) {
         call_user_func(array($objecttype->fields['itemtype'], 'registerType'));
      }
   }
   
   /**
    * Include locales for a specific type
    * @name object type's name
    * @return nothing
    */
   static function includeLocales($name) {
      global $CFG_GLPI, $LANG;
   
      $prefix = GENERICOBJECT_LOCALES_PATH . "/$name/$name";
      if (isset ($_SESSION["glpilanguage"]) 
             && file_exists($prefix . "." . $CFG_GLPI["languages"][$_SESSION["glpilanguage"]][1])) {
         include_once ($prefix . "." . $CFG_GLPI["languages"][$_SESSION["glpilanguage"]][1]);
   
      } else {
         if (file_exists($prefix . ".en_GB.php")) {
            include_once ($prefix . ".en_GB.php");
         } else
           if (file_exists($prefix . ".fr_FR.php")) {
              include_once ($prefix . ".fr_FR.php");
           } else {
            return false;
         }
      }
      return true;
   }

   static function includeConstants($name) {
      $file = GLPI_ROOT . "/plugins/genericobject/fields/constants/$name.constant.php";
      if (file_exists($file)) {
         include_once($file);
      }
   }
   
   /**
    * Get all dropdown fields associated with an itemtype
    * @param itemtype the itemtype
    * @return an array or fields that represents the dropdown tables
    */
   static function getDropdownForItemtype($itemtype) {
      global $DB;
      $associated_tables = array();
      if (class_exists($itemtype)) {
         $source_table = getTableForItemType($itemtype);
         foreach ($DB->list_fields($source_table) as $field => $value) {
            $table = getTableNameForForeignKeyField($field);
            //If it's a drodpdown
            if ($table && preg_match("/".getSingular($source_table)."/",$table)) {
               $associated_tables[] = $table;
            }
         }
      }
      return $associated_tables;
   }

   static function deleteDropdownsForItemtype($itemtype) {
      global $DB;
      //Foreach dropdown : drop table & remove files !
      foreach (self::getDropdownForItemtype($itemtype) as $table) {
         $results = array();
         if (preg_match("/glpi_plugin_genericobject_(.*)/i", getSingular($table), $results) 
            && isset($results[1])) {
            $name = $results[1];
            $DB->query("DROP TABLE IF EXISTS `$table`");
            self::deleteFormFile($name);
            self::deleteSearchFile($name);
            self::deleteAjaxFile($name);
            self::deleteClassFile($name);
         }
      }
   }
   //------------------------------- GETTERS -------------------------//

   function canBeLinked() {
      return $this->fields['use_links'];
   }

   function canUseTemplate() {
      return FieldExists(getTableForItemType($this->fields['itemtype']), 'is_template');
   }

   function canUseUnicity() {
      return $this->fields['use_unicity'];
   }
   
   function canBeDeleted() {
      return FieldExists(getTableForItemType($this->fields['itemtype']), 'is_deleted');
   }
   
   function canBeEntityAssigned() {
      return FieldExists(getTableForItemType($this->fields['itemtype']), 'entities_id');
   }
   
   function canBeRecursive() {
      return FieldExists(getTableForItemType($this->fields['itemtype']), 'is_recursive');
   }
   
   function canBeReserved() {
      return $this->fields['use_loans'];
   }
   
   function canUseNotepad() {
      return FieldExists(getTableForItemType($this->fields['itemtype']), 'notepad');
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

   function canUseContracts() {
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
      $plugin = new Plugin();
      if (!$plugin->isInstalled("datainjection") || !$plugin->isActivated("datainjection")) {
         return false;
      }
      return $this->fields['use_plugin_datainjection'];
   }

   function canUsePluginOrder() {
      $plugin = new Plugin();
      if (!$plugin->isInstalled("order") || !$plugin->isActivated("order")) {
         return false;
      }
      return $this->fields['use_plugin_order'];
   }

   function canUsePluginPDF() {
      $plugin = new Plugin();
      if (!$plugin->isInstalled("pdf") || !$plugin->isActivated("pdf")) {
         return false;
      }
      return $this->fields['use_plugin_pdf'];
   }

   function canUsePluginUninstall() {
      $plugin = new Plugin();
      if (!$plugin->isInstalled("uninstall") || !$plugin->isActivated("uninstall")) {
         return false;
      }
     return $this->fields['use_plugin_uninstall'];
   }

   function canUsePluginGeninventoryNumber() {
      $plugin = new Plugin();
      if (!$plugin->isInstalled("geninventorynumber") 
         || !$plugin->isActivated("geninventorynumber")) {
         return false;
      }
      return $this->fields['use_plugin_geninventorynumber'];
   }

   function isTransferable() {
      return isMultiEntitiesMode();
      
   }
   //------------------------------- INSTALL / UNINSTALL METHODS -------------------------//
   static function install(Migration $migration) {
      global $DB;
      
      if (!TableExists(getTableForItemType(__CLASS__))) {
         $query = "CREATE TABLE `glpi_plugin_genericobject_types` (
                           `id` INT( 11 ) NOT NULL AUTO_INCREMENT,
                           `entities_id` INT( 11 ) NOT NULL DEFAULT 0,
                           `itemtype` varchar(255) collate utf8_unicode_ci default NULL,
                           `is_active` tinyint(1) NOT NULL default '0',
                           `name` varchar(255) collate utf8_unicode_ci default NULL,
                           `use_unicity` tinyint(1) NOT NULL default '0',
                           `use_history` tinyint(1) NOT NULL default '0',
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
         $itemtype = getItemTypeForTable($table);
         $item     = new $itemtype();
         $item->deleteByCriteria(array('itemtype' => __CLASS__));
      }
   }
}