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

   /**
    *
    * Displat all fields present in DB for an itemtype
    * @param $id the itemtype's id
    */
   public static function showObjectFieldsForm($id) {
      global $LANG, $DB, $GO_BLACKLIST_FIELDS, $GO_FIELDS, $CFG_GLPI;

      $url          = Toolbox::getItemTypeFormURL(__CLASS__);
      $object_type  = new PluginGenericobjectType();
      $object_type->getFromDB($id);
      $itemtype     = $object_type->fields['itemtype'];
      $fields_in_db = PluginGenericobjectSingletonObjectField::getInstance($itemtype);
      $used_fields  = array();

      //Reset fields definition only to keep the itemtype ones
      $GO_FIELDS = array();
      plugin_genericobject_includeCommonFields(true);
      $file = GLPI_ROOT."/plugins/genericobject/fields/constants/".
               $object_type->fields['name'].".constant.php";
      if (file_exists($file)) {
         include $file;
      }
      PluginGenericobjectType::includeConstants($itemtype, true);
      
      foreach ($GO_BLACKLIST_FIELDS as $autofield) {
         if (!in_array($autofield, $used_fields)) {
            $used_fields[$autofield] = $autofield;
         }
      }

      echo "<div class='center'>";
      echo "<form name='fieldslist' method='POST' action='$url'>";
      echo "<table class='tab_cadre_fixe' >";
      echo "<input type='hidden' name='id' value='$id'>";
      echo "<tr class='tab_bg_1'><th colspan='7'>";
      echo $LANG['genericobject']['fields'][1] . " : ";
      echo $itemtype::getTypeName();
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
         //If it's a plugin dropdowns, get it's real name
         //(it may not be the one from the DB, in case it's a global field)
         $table = getTableNameForForeignKeyField($field);
         if ($table != '' && isPluginItemType(getItemTypeForTable($table))) {
            $classname = getItemTypeForTable($table);
            $class     = new $classname();
            $used_fields[$class->getFieldName()] = $class->getFieldName();
         } else {
            $used_fields[$field] = $field;
         }
         $global_index++;
      }
      echo "</table>";
      Html::openArrowMassives('fieldslist', true);
      Html::closeArrowMassives(array('delete' => __("Delete permanently")));

      echo "<table class='tab_cadre'>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . $LANG['genericobject']['fields'][4] . "</td>";
      echo "<td align='left'>";
      self::dropdownFields("new_field", $itemtype, $used_fields);
      echo "</td>";
      echo "<td>";
      echo "<input type='submit' name='add_field' value=\"" . _sx('button','Add') . "\" class='submit'>";
      echo "</tr>";
      echo "</table>";
      Html::closeForm();
      echo "</div>";
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

   /**
    *
    * Display a dropdown with all available fields for an itemtype
    * @since
    * @param $name the dropdown name
    * @param $itemtype the itemtype
    * @param $used an array which contains all fields already added
    *
    * @return the dropdown random ID
    */
   static function dropdownFields($name,$itemtype, $used = array()) {
      global $GO_FIELDS;
      
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
                    $message = " ".__("Entity")." : ".Dropdown::getYesNo($values['entities_id']);
                     if ($values['entities_id']) {
                        if (isset($values['is_recursive'])) {
                           $message.= " ".__("Child entities")." : ".Dropdown::getYesNo($values['is_recursive']);
                        }
                     }
                  } else {
                    $message = " ".__("Entity")." : ".Dropdown::getYesNo(0);
                  }
                  if (isset($values['is_tree'])) {
                     $message.= " ".__("tree structure")." : ".Dropdown::getYesNo($values['is_tree']);
                  } else {
                     $message.= " ".__("tree structure")." : ".Dropdown::getYesNo(0);
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
      return Dropdown::showFromArray($name, $dropdown_types);
   }

   /**
    *
    * Get field definition for a field
    *
    * @param $field the current field
    * @param $itemtype the itemtype
    * @return an array which contains the full field definition
    */
   static function getOptionsWithGlobal($field, $itemtype) {
      global $GO_FIELDS;
      
      if (!isset($GO_FIELDS[$field])) {
         $tmpfield = self::getFieldGlobalName($field, $itemtype,
                                           array('dropdown_type' => 'global'), true);
         $options             = $GO_FIELDS[$tmpfield];
         $options['realname'] = $tmpfield;
      } else {
         $options             = $GO_FIELDS[$field];
         $options['realname'] = $field;
      }
      return $options;
   }
   
   public static function displayFieldDefinition($target, $itemtype, $field, $index, $last = false) {
      global $GO_FIELDS, $CFG_GLPI, $GO_BLACKLIST_FIELDS;

      $readonly = in_array($field, $GO_BLACKLIST_FIELDS);
      $options  = self::getOptionsWithGlobal($field, $itemtype);

      echo "<tr class='tab_bg_".(($index%2)+1)."' align='center'>";
      //if (isset ($_POST["select"]) && $_POST["select"] == "all") {
      //   $sel = "checked";
      //}
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
         Html::showSimpleForm($target, $CFG_GLPI["root_doc"] . "/pics/deplier_up.png", 'up',
                               array('field' => $field, 'action' => 'up', 'itemtype' => $itemtype),
                               $CFG_GLPI["root_doc"] . "/pics/deplier_up.png");
      }
      echo "</td>";

      echo "<td width='10'>";
      if (!$readonly && !$last) {
         Html::showSimpleForm($target, $CFG_GLPI["root_doc"] . "/pics/deplier_down.png", 'down',
                               array('field' => $field, 'action' => 'down', 'itemtype' => $itemtype),
                               $CFG_GLPI["root_doc"] . "/pics/deplier_down.png");
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
      global $DB;

      $itemtype = getItemTypeForTable($table);
      if (!FieldExists($table, $field, false)) {
         $options  = self::getOptionsWithGlobal($field, $itemtype);
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
            case 'integer' :
               $query .= "INT ( 11 ) NOT NULL DEFAULT '0'";
               break;
            case 'date':
               $query.="DATE DEFAULT NULL";
               break;
            case 'datetime':
               $query.="DATETIME DEFAULT NULL";
               break;
            case 'float' :
               $query .= "FLOAT NOT NULL DEFAULT '0'";
               break;
            case 'decimal' :
               $query .= "DECIMAL(20,4) NOT NULL DEFAULT '0.0000'";
               break;
         }
         if ($after) {
            $query.=" AFTER `$after`";
         }
         $DB->query($query);
         
         //Reload list of fields for this itemtype in the singleton
         
         $recursive = $entity_assign = $tree = false;
         $table     = getTableNameForForeignKeyField($field);
         if ($table != '' && !TableExists($table)) {
            //Cannot use standard methods because class doesn't exists yet !
            $name                       = str_replace("glpi_plugin_genericobject_","", $table);
            $name                       = getSingular($name);
            $options['linked_itemtype'] = $itemtype;
            PluginGenericobjectType::addNewDropdown($name, 'PluginGenericobject'.ucfirst($name),
                                                    $options);
         }
         PluginGenericobjectSingletonObjectField::getInstance($itemtype, true);
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
     if ($table != '' && preg_match('/plugin_genericobject_(.*)/', $table, $results)) {
        //Delete dropdown table
        $query = "DROP TABLE `$table`";
        $DB->query($query);
        //Delete dropdown files & class
        $name = getSingular($results[1]);
        PluginGenericobjectType::deleteClassFile($name);
        PluginGenericobjectType::deleteFormFile($name);
        PluginGenericobjectType::deletesearchFile($name);
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
      $fields   = PluginGenericobjectSingletonObjectField::getInstance($params['itemtype']);
      
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
      if ($params['action'] == 'down') {
         $previous = $index - 1;
      } else {
         $previous = $index - 2;
      }
      
      if (isset($keys[$previous])) {
         $parent = $fields[$keys[$previous]];
         $query  = "ALTER TABLE `$table` MODIFY `$field` ".$fields[$field]['Type'];
         $query .= " AFTER `".$fields[$keys[$previous]]['Field']."`";
         $DB->query($query) or die ($DB->error());
      }
   }
   
   public static function checkNecessaryFieldsDelete($itemtype,$field) {
      $type = new PluginGenericobjectType();
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