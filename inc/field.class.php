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
      global $DB, $GO_BLACKLIST_FIELDS, $GO_READONLY_FIELDS, $GO_FIELDS, $CFG_GLPI;

      $url          = Toolbox::getItemTypeFormURL(__CLASS__);
      $object_type  = new PluginGenericobjectType();
      $object_type->getFromDB($id);
      $itemtype     = $object_type->fields['itemtype'];
      $fields_in_db = PluginGenericobjectSingletonObjectField::getInstance($itemtype);
      $used_fields  = array();

      //Reset fields definition only to keep the itemtype ones
      $GO_FIELDS = array();
      plugin_genericobject_includeCommonFields(true);

      PluginGenericobjectType::includeConstants($object_type->fields['name'], true);

      self::addReadOnlyFields($object_type);

      foreach ($GO_BLACKLIST_FIELDS as $autofield) {
         if (!in_array($autofield, $used_fields)) {
            $used_fields[$autofield] = $autofield;
         }
      }

      echo "<div class='center'>";
      echo "<form id='fieldslist' method='POST' action='$url'>";
      echo "<table class='tab_cadre_fixe' >";
      echo "<input type='hidden' name='id' value='$id'>";
      echo "<tr class='tab_bg_1'><th colspan='7'>";
      echo __("Fields associated with the object", "genericobject") . " : ";
      echo $itemtype::getTypeName();
      echo "</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th width='10'></th>";
      echo "<th>" . __("Label", "genericobject") . "</th>";
      echo "<th>" . __("Name in DB", "genericobject") . "</th>";
      echo "<th width='10'></th>";
      echo "<th width='10'></th>";
      echo "</tr>";

      $total        = count($fields_in_db);
      $global_index = $index = 1;
      $haveCheckbox = false;

      foreach ($fields_in_db as $field => $value) {
         $readonly  = in_array($field, $GO_READONLY_FIELDS);
         $blacklist = in_array($field, $GO_BLACKLIST_FIELDS);

         self::displayFieldDefinition($url, $itemtype, $field, $index, ($global_index==$total));

         //All backlisted fields cannot be moved, and are listed first
         if (!$readonly) {
            $index++;
         }

         if (!$blacklist && !$readonly) {
            $haveCheckbox = true;
         }

         //$table = getTableNameForForeignKeyField($field);
         $used_fields[$field] = $field;
         $global_index++;
      }
      echo "</table>";
      if ($haveCheckbox) {
         Html::openArrowMassives('fieldslist', true);
         Html::closeArrowMassives(array('delete' => __("Delete permanently")));
      }

      $dropdownFields = self::dropdownFields("new_field", $itemtype, $used_fields);

      if ($dropdownFields) {
         echo "<table class='tab_cadre genericobject_fields add_new'>";
         echo "<tr class='tab_bg_1'>";
         echo "<td class='label'>" . __("Add new field", "genericobject") . "</td>";
         echo "<td align='left' class='dropdown'>";
         echo $dropdownFields;
         echo "</td>";
         echo "<td>";
         echo "<input type='submit' name='add_field' value=\"" . _sx('button','Add') . "\" class='submit'>";
         echo "</tr>";
         echo "</table>";
      }

      Html::closeForm();
      echo "</div>";
   }

   /**
   * Method to set fields as read only, when the depend on some features 
   * that are enabled
   * @since 0.85+2.4.0
   */
   static function addReadOnlyFields(PluginGenericobjectType $type) {
      global $GO_READONLY_FIELDS;

      if ($type->canBeReserved()) {
        $GO_READONLY_FIELDS[] = 'users_id';
        $GO_READONLY_FIELDS[] = 'locations_id';
      }

      if ($type->canUseGlobalSearch()) {
        $GO_READONLY_FIELDS[] = 'serial';
        $GO_READONLY_FIELDS[] = 'otherserial';
        $GO_READONLY_FIELDS[] = 'locations_id';
        $GO_READONLY_FIELDS[] = 'states_id';
        $GO_READONLY_FIELDS[] = 'users_id';
        $GO_READONLY_FIELDS[] = 'groups_id';
        $GO_READONLY_FIELDS[] = 'manufacturers_id';
        $GO_READONLY_FIELDS[] = 'users_id_tech';
      }

   }
   /**
    * Get the name of the field, as defined in a constant file
    * The name may be the same, or not depending if it's an isolated dropdown or not
    */
   static function getFieldName($field, $itemtype, $options, $remove_prefix = false) {
      $field_orig = $field;
      $field_table = null;
      $input_type = isset($options['input_type'])
         ? $options['input_type']
         : null;
      switch($input_type) {

         case 'dropdown':
            $dropdown_type = isset($options['dropdown_type'])
               ? $options['dropdown_type']
               : null;
            $fk = getForeignKeyFieldForTable(getTableForItemType($itemtype));

            if ( $dropdown_type == 'isolated' ) {
               if (!$remove_prefix) {
                  $field = preg_replace("/s_id$/",$field, $fk);
               } else {
                  $fk    = preg_replace("/s_id$/","", $fk);
                  $field = preg_replace("/".$fk."/","", $field);
               }
            }
            $field_table = getTableNameForForeignKeyField($field);

            //Prepend plugin's table prefix if this dropdown is not already handled by GLPI natively
            if (
               substr($field, 0, strlen('plugin_genericobject')) !== 'plugin_genericobject'
               and (
                  substr($field_table, strlen('glpi_'))
                  === substr($field,  0, strlen($field) -strlen('_id'))
               )
               and !TableExists($field_table)
            ) {
               if (!$remove_prefix) { $field = 'plugin_genericobject_' . $field;}
            }
            break;

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
         $field_options = array();
         $field = self::getFieldName($field, $itemtype, $values, false);
         if(!in_array($field, $used)) {
            if (!isset($dropdown_types[$field])) {
               //Global management :
               //meaning that a dropdown can be useful in all types (for example type, model, etc.)
               if (isset($values['input_type']) && $values['input_type'] == 'dropdown') {
                  if (isset($values['entities_id'])) {
                    $field_options[] = __("Entity")." : ".Dropdown::getYesNo($values['entities_id']);
                     if ($values['entities_id']) {
                        if (isset($values['is_recursive'])) {
                           $field_options[] = __("Child entities")." : ".Dropdown::getYesNo($values['is_recursive']);
                        }
                     }
                  } else {
                    $field_options[] = __("Entity")." : ".Dropdown::getYesNo(0);
                  }
                  if (isset($values['is_tree'])) {
                     $field_options[] = __("tree structure")." : ".Dropdown::getYesNo($values['is_tree']);
                  } else {
                     $field_options[] = __("tree structure")." : ".Dropdown::getYesNo(0);
                  }
                  //if (isset($values['isolated']) and $values['isolated']) {
                  //   $field_options[] = __("Isolated") . " : ". Dropdown::getYesNo($values['isolated']);
                  //} else {
                  //   $field_options[] = __("Isolated") . " : ". Dropdown::getYesNo(0);
                  //}
               }
               if (!empty($field_options)) {
                  $message = "(".trim( implode(", ",$field_options)).")";
               }
            }
            $dropdown_types[$field] = $values['name']." ".$message;
         }
      }

      // Don't show dropdown empty
      if (empty($dropdown_types)) {
         return '';
      }

      ksort($dropdown_types);
      return Dropdown::showFromArray($name, $dropdown_types, array('display' => false));
   }

   /**
    *
    * Get field's options defined in constant files.
    * If this field has not been defined, it means that this field has been defined globally and
    * must be dynamically created.
    *
    * @param $field the current field
    * @param $itemtype the itemtype
    * @return an array which contains the full field definition
    */
   static function getFieldOptions($field, $itemtype="") {
      global $GO_FIELDS;

      $cleaned_field = preg_replace("/^plugin_genericobject_/",'', $field);
      if (!isset($GO_FIELDS[$cleaned_field]) && !empty($itemtype)) {
         // This field has been dynamically defined because it's an isolated dropdown
         $tmpfield = self::getFieldName(
            $field, $itemtype,
            array(
               'dropdown_type' => 'isolated',
               'input_type' => 'dropdown'
            ),
            true
         );
         $options             = $GO_FIELDS[$tmpfield];
         $options['realname'] = $tmpfield;
      } else {
         $options             = $GO_FIELDS[$cleaned_field];
         $options['realname'] = $cleaned_field;
      }
      return $options;
   }

   public static function displayFieldDefinition($target, $itemtype, $field, $index, $last = false) {
      global $GO_FIELDS, $CFG_GLPI, $GO_BLACKLIST_FIELDS, $GO_READONLY_FIELDS;

      $readonly  = in_array($field, $GO_READONLY_FIELDS);
      $blacklist = in_array($field, $GO_BLACKLIST_FIELDS);
      $options  = self::getFieldOptions($field, $itemtype);

      echo "<tr class='tab_bg_".(($index%2)+1)."' align='center'>";
      $sel ="";

      echo "<td width='10'>";
      if (!$blacklist && !$readonly) {
         echo "<input type='checkbox' name='fields[" .$field. "]' value='1' $sel>";
      }
      echo "</td>";
      echo "<td>" . __($options['name'], 'genericobject') . "</td>";
      echo "<td>" . $field . "</td>";

      echo "<td width='10'>";
      if ((!$blacklist || $readonly) && $index > 1) {
         Html::showSimpleForm($target, $CFG_GLPI["root_doc"] . "/pics/deplier_up.png", 'up',
                               array('field' => $field, 'action' => 'up', 'itemtype' => $itemtype),
                               $CFG_GLPI["root_doc"] . "/pics/deplier_up.png");
      }
      echo "</td>";

      echo "<td width='10'>";
      if ((!$blacklist || $readonly) && !$last) {
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

      _log("add", $field, "from", $table);
      $itemtype = getItemTypeForTable($table);
      if (!FieldExists($table, $field, false)) {
         $options  = self::getFieldOptions($field, $itemtype);
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

         $table = getTableNameForForeignKeyField($field);

         if ($table != '' && !TableExists($table)) {
            //Cannot use standard methods because class doesn't exists yet !
            $name = str_replace("glpi_plugin_genericobject_","", $table);
            $name = getSingular($name);

            $options['linked_itemtype'] = $itemtype;

            PluginGenericobjectType::addNewDropdown(
               $name, 'PluginGenericobject'.ucfirst($name), $options
            );
         }
         // Invalidate menu data in current session
         unset($_SESSION['glpimenu']);
         
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

      //Remove field from displaypreferences
      self::deleteDisplayPreferences($table, $field);

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

   static function deleteDisplayPreferences($table, $field) {

      $pref      = new DisplayPreference();
      $itemtype  = getItemTypeForTable($table);
      $searchopt = Search::getCleanedOptions($itemtype);
      foreach ($searchopt as $num => $option) {
         if ( (isset($option['field'])  && ($option['field'] == $field)) 
            || (isset($option['field']) && $option['linkfield'] == $field)) {
            $criteria = array('itemtype' => $itemtype, 'num' => $num);
            $pref->deleteByCriteria($criteria);
            break;  
         }
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

