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
class PluginGenericobjectField extends CommonDBTM {
   
    function deleteByFieldByDeviceTypeAndName($itemtype, $name) {
      global $DB;
      $query = "DELETE FROM `".$this->table."` " .
               "WHERE `itemtype`='$itemtype' AND name='$name'";
      $DB->query($query);
    }
 
   function getRank() {
      return $this->fields["rank"];
   }
   
   function getMandatory() {
      return $this->fields["mandatory"];
   }   

   function post_addItem() {
      $name  = PluginGenericobjectType::getNameByID($this->input["itemtype"]);
      $table = PluginGenericobjectType::getTableNameByName($name);
      self::addFieldInDB($table, $this->fields["name"], $name);
   }
   
   /**
    * Add a new field for an object (into object's device table)
    * @itemtype the object type
    */
   public static function addNewField($itemtype, $name) {
      if (!self::plugin_genericobject_fieldExists($itemtype, $name)) {
         $type_field                  = new PluginGenericobjectField;
            $input["name"]            = $name;
            $input["itemtype"]        = $itemtype;
            $input["rank"]            = self::plugin_genericobject_getNextRanking($itemtype);
            $input["mandatory"]       = 0;
            $input["unique"]          = 0;
            $input["entity_restrict"] = 0;
            $type_field->add($input);
      } else {
         exit("addNewField" .$itemtype." already exists");
      }
   }
   
   public static function plugin_genericobject_fieldExists($itemtype,$name) {
      global $DB;
      $query = "SELECT `id` FROM `".getTableForItemType(__CLASS__)."` " .
               "WHERE `itemtype`='$itemtype' AND `name`='$name'";
      $result = $DB->query($query);
      if (!$DB->numrows($result)) {
         return false;
      }
      else {
         return true;
      }
   }
   
   /**
    * Get next available field display ranking for a type
    * @type the itemtype
    * @return the next available ranking
    */
   public static function plugin_genericobject_getNextRanking($itemtype) {
      global $DB;
      $query  = "SELECT MAX(rank) as cpt FROM `".getTableForItemType(__CLASS__)."`" .
                "WHERE itemtype='$itemtype'";
      $result = $DB->query($query);
      if ($DB->result($result,0,"cpt") != null)
         return $DB->result($result,0,"cpt") + 1;
      else
         return 0;
   }
   
   
   public static function addFieldInDB($table, $field, $name) {
      global $DB, $GENERICOBJECT_AVAILABLE_FIELDS;
      $query = "ALTER TABLE ".getTableForItemType(__CLASS__)." ADD `$field` ";
      if (!FieldExists($table, $field)) {
         
         switch ($GENERICOBJECT_AVAILABLE_FIELDS[$field]['input_type']) {
            case 'dropdown_yesno' :
            case 'dropdown_global' :
            case 'bool' :
               $query .= "TINYINT (1) NOT NULL DEFAULT '0'";
               break;
            case 'text' :
               $query .= "VARCHAR ( 255 ) collate utf8_unicode_ci NOT NULL DEFAULT ''";
               break;
            case 'multitext' :
               $query .= "TEXT NULL";
               break;
            case 'dropdown' :
               $query .= "INT ( 11 ) NOT NULL DEFAULT '0'";
               if (PluginGenericobjectType::plugin_genericobject_isDropdownTypeSpecific($field)) {
                  PluginGenericobjectType::plugin_genericobject_addDropdownTable($name, $field);
                  PluginGenericobjectType::plugin_genericobject_addDropdownClassFile($name, $field);
                  PluginGenericobjectType::plugin_genericobject_addDropdownFrontFile($name, $field);
                  PluginGenericobjectType::plugin_genericobject_addDropdownFrontformFile($name, $field);
                  PluginGenericobjectType::plugin_genericobject_addDropdownAjaxFile($name, $field);
               }
               break;
            case 'integer' :
               $query .= "INT ( 11 ) NOT NULL DEFAULT '0'";
               break;
            case 'date':
               $query.="DATE DEFAULT NULL";
               break;
         }
         $DB->query($query);
      }
   }
   
   /**
    * Get all fields for an object type
    * @itemtype the object type
    * @return an array with all the fields for this type
    */
   public static function plugin_genericobject_getFieldsByType($itemtype) {
      global $DB;
      
      $itemtype = strtolower(str_replace("PluginGenericobject", "", $itemtype));
      $query    = "SELECT * FROM `".getTableForItemType(__CLASS__)."` " .
                  "WHERE itemtype='$itemtype' ORDER BY rank ASC";
      $result   = $DB->query($query);
      $fields   = array();
      
      while ($datas = $DB->fetch_array($result)) {
         $tmp                    = new PluginGenericobjectField;
         $tmp->fields            = $datas;
         $fields[$datas["name"]] = $tmp;
      }
      return $fields;
   }
   
   public static function plugin_genericobject_checkNecessaryFieldsDelete($itemtype,$field) {
      $type = new PluginGenericobjectType;
      $type->getFromDBByType($itemtype);
      
      if ($type->fields['use_network_ports'] && 'locations_id' == $field) {
         return false;
      }
      
      if ($type->fields['use_direct_connections']) {
         foreach(array('users_id','groups_id',' states_id','locations_id') as $tmp_field) {
            if ($tmp_field == $field) {
               return false;
            }
         } 
      }
      return true;
   }
   
   public static function deleteAllFieldsByType($itemtype) {
      global $DB;
      $query = "DELETE FROM `".getTableForItemType(__CLASS__)."` WHERE itemtype='$itemtype'";
      $DB->query($query);
   }

   public static function plugin_genericobject_setMandatoryField($itemtype,$field) {
      
   }
   
   static function install(Migration $migration) {
      global $DB;
      
      if (!TableExists(getTableForItemType(__CLASS__))) {
         $query = "CREATE TABLE `".getTableForItemType(__CLASS__)."` (
                     `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                     `itemtype` varchar(255) collate utf8_unicode_ci default NULL,
                     `name` VARCHAR( 255 ) collate utf8_unicode_ci NOT NULL DEFAULT '' ,
                     `rank` INT( 11 ) NOT NULL DEFAULT '0' ,
                     `mandatory` tinyint(1) NOT NULL default '0',
                     `entity_restrict` tinyint(1) NOT NULL default '0',
                     `unique` tinyint(1) NOT NULL default '0'
                     ) ENGINE = MYISAM  COMMENT = 'Field type description' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die ($DB->error());
      }
   }
   
   static function uninstall() {
      global $DB;
      $query = "DROP TABLE IF EXISTS `".getTableForItemType(__CLASS__)."`";
      $DB->query($query) or die ($DB->error());
   }
}