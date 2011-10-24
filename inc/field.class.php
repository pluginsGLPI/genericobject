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
 
class PluginGenericobjectField extends CommonDBTM {

   public static function showObjectFieldsForm($id) {
      global $LANG, $DB, $GO_BLACKLIST_FIELDS, $GO_FIELDS, $CFG_GLPI;

      $url          = Toolbox::getItemTypeFormURL(__CLASS__);
      $object_type  = new PluginGenericobjectType();
      $object_type->getFromDB($id);
      $itemtype     = $object_type->fields['itemtype'];
      $fields_in_db = $DB->list_fields(getTableForItemType($itemtype));
      $used_fields  = array();

      foreach ($GO_BLACKLIST_FIELDS as $autofield) {
         if (!in_array($autofield, $used_fields)) {
            $used_fields[$autofield] = $autofield;
         }
      }

      echo "<div class='center'>";
      echo "<form name='fields_definition' method='post' action='$url'>";
      echo "<table class='tab_cadre_fixe' >";
      echo "<input type='hidden' name='id' value='$id'>";
      echo "<tr class='tab_bg_1'><th colspan='7'>";
      echo $LANG['genericobject']['fields'][1] . " : ";
      call_user_func(array($itemtype, 'getTypeName'));
      echo "</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th width='10'></th>";
      echo "<th>" . $LANG['genericobject']['fields'][3] . "</th>";
      echo "<th>" . $LANG['genericobject']['fields'][2] . "</th>";
      echo "<th width='10'></th>";
      echo "<th width='10'></th>";
      echo "</tr>";

      $total        = count($fields_in_db);
      $global_index = $index = 1;
      
      foreach ($fields_in_db as $field => $value) {
         self::displayFieldDefinition($url, $itemtype, $field, $index, ($global_index==$total));
         //All backlisted fields cannot be moved, and are listed first
         if (!in_array($field, $GO_BLACKLIST_FIELDS)) {
            $index++;
         }
         $used_fields[$field] = $field; 
         $global_index++;
      }
      echo "</table>";
      Html::openArrowMassives('fields_definition', true);
      Html::closeArrowMassives('delete', $LANG['buttons'][6]);

      echo "<table class='tab_cadre'>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . $LANG['genericobject']['fields'][4] . "</td>";
      echo "<td align='left'>";
      self::dropdownFields("new_field", $itemtype, $used_fields);
      echo "</td>";
      echo "<td>";
      echo "<input type='submit' name='add_field' value=\"" . $LANG['buttons'][8] . "\" class='submit'>";
      echo "</tr>";
      echo "</table></div></form>";
   }

   /**
    * Get the name of the field, as defined in a constant file
    * The name may be the same, or not depending if it's a global dropdown or not
    */
   static function getFieldGlobalName($field, $itemtype, $options, $remove_prefix = false) {
      if (isset($options['dropdown_type']) 
            && $options['dropdown_type'] == 'global') {

         $fk   = getForeignKeyFieldForTable(getTableForItemType($itemtype));
         if (!$remove_prefix) {
            $field = preg_replace("/s_id$/",$field, $fk);
         } else {
            $fk    = preg_replace("/s_id$/","", $fk);
            $field = preg_replace("/".$fk."/","", $field);
         }
      }
      return $field;
   }
   static function dropdownFields($name,$itemtype, $used = array()) {
      global $GO_FIELDS, $LANG;
      
      $dropdown_types = array();
      foreach ($GO_FIELDS as $field => $values) {
         $message = "";
         if(!in_array($field, $used)) {
            $field = self::getFieldGlobalName($field, $itemtype, $values, false);
            if (!isset($dropdown_types[$field])) {
               //Global management : 
               //meaning that a dropdown can be useful in all types (for example type, model, etc.)
               if (isset($values['input_type']) && $values['input_type'] == 'dropdown') {
                  if (isset($values['entities_id'])) {
                    $message = " ".$LANG['entity'][0]." : ".Dropdown::getYesNo($values['entities_id']);
                     if ($values['entities_id']) {
                        if (isset($values['is_recursive'])) {
                           $message.= " ".$LANG['entity'][9]." : ".Dropdown::getYesNo($values['is_recursive']);
                        }
                     }
                  } else {
                    $message = " ".$LANG['entity'][0]." : ".Dropdown::getYesNo(0);
                  }
                  if (isset($values['is_tree'])) {
                     $message.= " ".$LANG['entity'][7]." : ".Dropdown::getYesNo($values['is_tree']);
                  } else {
                     $message.= " ".$LANG['entity'][7]." : ".Dropdown::getYesNo(0);
                  }
                  
               }
               if ($message != '') {
                  $message = "(".trim($message).")";
               }
            }
            $dropdown_types[$field] = $values['name']." ".$message;
         }
      }
      ksort($dropdown_types);
      Dropdown::showFromArray($name,$dropdown_types);
   }

   static function getOptionsWithGlobal($field, $itemtype) {
      global $GO_FIELDS;
      
      if (!isset($GO_FIELDS[$field])) {
         $tmpfield = self::getFieldGlobalName($field, $itemtype, 
                                           array('dropdown_type' => 'global'), true);
         $options = $GO_FIELDS[$tmpfield];
      } else {
         $options = $GO_FIELDS[$field];
      }
      return $options;
   }
   
   public static function displayFieldDefinition($target, $itemtype, $field, $index, $last = false) {
      global $GO_FIELDS, $CFG_GLPI, $GO_BLACKLIST_FIELDS;

      $readonly = in_array($field, $GO_BLACKLIST_FIELDS);
      $options  = self::getOptionsWithGlobal($field, $itemtype);

      echo "<tr class='tab_bg_".(($index%2)+1)."' align='center'>";
      if (isset ($_GET["select"]) && $_GET["select"] == "all") {
         $sel = "checked";
      }
      $sel ="";

      echo "<td width='10'>";
      if (!$readonly) {
         echo "<input type='checkbox' name='fields[" .$field. "]' value='1' $sel>";
      }
      echo "</td>";
      echo "<td>" . $options['name'] . "</td>";
      echo "<td>" . $field . "</td>";

      echo "<td width='10'>";
      if (!$readonly && $index > 1) {
         echo "<a href=\"" . $target . "?field=" . $field . "&amp;action=up&amp;itemtype=$itemtype\">"; 
         echo "<img src=\"" . $CFG_GLPI["root_doc"] . "/pics/deplier_up.png\" alt=''></a>";
      }
      echo "</td>";

      echo "<td width='10'>";
      if (!$readonly && !$last) {
         echo "<a href=\"" . $target . "?field=" . $field . "&amp;action=down&amp;itemtype=$itemtype\">"; 
         echo "<img src=\"" . $CFG_GLPI["root_doc"] . "/pics/deplier_down.png\" alt=''></a>";
      }
      echo "</td>";

      echo "</tr>";
   }

   /**
    * Add a new field in DB
    * @param table the table
    * @param field the field to delete
    * @return nothing
    */
   public static function addNewField($table, $field, $after=false) {
      global $DB, $GO_FIELDS;

      $options  = self::getOptionsWithGlobal($field, getItemTypeForTable($table));
      
      if (!FieldExists($table, $field)) {
         $query = "ALTER TABLE `$table` ADD `$field` ";
         switch ($options['input_type']) {
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
               break;
            case 'integer' :
               $query .= "INT ( 11 ) NOT NULL DEFAULT '0'";
               break;
            case 'date':
               $query.="DATE DEFAULT NULL";
               break;
            case 'datetime':
               $query.="DATETIME DEFAULT NULL";
               break;
         }
         if ($after) {
            $query.=" AFTER `$after`";
         }
         $DB->query($query);
         
         $recursive = $entity_assign = $tree = false;
         $table     = getTableNameForForeignKeyField($field);
         if ($table != '' && !TableExists($table)) {
            //Cannot use standard methods because class doesn't exists yet !
            $name = str_replace("glpi_plugin_genericobject_","", $table);
            $name = getSingular($name);
            //Build itemtype
            $itemtype = 'PluginGenericobject'.ucfirst($name);
            
            $entity_assign = isset($options['entities_id'])  && $options['entities_id']; 
            if ($entity_assign) {
               $recursive = isset($options['is_recursive'])  && $options['is_recursive'];
            }
            $tree = isset($options['is_tree']) && $options['is_tree']; 

            //Add files on the disk
            PluginGenericobjectType::addDropdownClassFile($name, $itemtype, $tree);
            PluginGenericobjectType::addDropdownTable($table, $entity_assign, $recursive, $tree);
            PluginGenericobjectType::addDropdownFrontFile($name);
            PluginGenericobjectType::addDropdownAjaxFile($name, $field);
            PluginGenericobjectType::addDropdownFrontformFile($name, $field);
         }
      }
   }

   /**
    * Delete a field in DB
    * @param table the table
    * @param field the field to delete
    * @return nothing
    */
   static function deleteField($table, $field) {
     global $DB;
     //If field exists, drop it !
     if (FieldExists($table, $field)) {
        $DB->query("ALTER TABLE `$table` DROP `$field`");
     }

     $table = getTableNameForForeignKeyField($field);
     //If dropdown is managed by the plugin
     if ($table != '' && preg_match('/plugin_genericobject/', $table)) {
        //Delete dropdown table
        $query = "DROP TABLE `$table`";
        $DB->query($query);
        //
     }
   }
   
   /**
    * Change field order in DB
    * @params an array which contains the itemtype, the field to move and the action (up/down)
    * @return nothing
    */
   static function changeFieldOrder($params = array()) {
      global $DB;
      $itemtype = $params['itemtype'];
      $field    = $params['field'];
      $table    = getTableForItemType($itemtype);
      $fields   = $DB->list_fields(getTableForItemType($params['itemtype']));
      
      //If action is down, reverse array first
      if ($params['action'] == 'down') {
         $fields = array_reverse($fields);
      }

      //Get array keys
      $keys  = array_keys($fields);
      //Index represents current position of $field
      $index = 0;
      foreach ($keys as $id => $key) {
         if ($key == $field) {
            $index = $id;
         }
      }
      //Get 2 positions before and move field
      $previous = $index -2;
      if (isset($keys[$previous])) {
         $parent = $fields[$keys[$previous]];
         $query  = "ALTER TABLE `$table` MODIFY `$field` ".$fields[$field]['Type'];
         $query .= " AFTER `".$fields[$keys[$previous]]['Field']."`";
         $DB->query($query) or die ($DB->error());
      }
   }
   
   public static function checkNecessaryFieldsDelete($itemtype,$field) {
      $type = new PluginGenericobjectType;
      $type->getFromDBByType($itemtype);
      
      if ($type->canUseNetworkPorts() && 'locations_id' == $field) {
         return false;
      }
      /*
      if ($type->fields['use_direct_connections']) {
         foreach(array('users_id','groups_id',' states_id','locations_id') as $tmp_field) {
            if ($tmp_field == $field) {
               return false;
            }
         } 
      }*/
      return true;
   }
   
   static function install(Migration $migration) {
   }
   
   static function uninstall() {
   }

}