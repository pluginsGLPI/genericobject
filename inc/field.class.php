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

   public static function showObjectFieldsForm($ID) {
      global $LANG, $DB, $GO_BLACKLIST_FIELDS, $GO_FIELDS, $CFG_GLPI, 
             $GENERICOBJECT_AUTOMATICALLY_MANAGED_FIELDS;

      $url          = getItemTypeFormURL(__CLASS__);
      $object_type  = new PluginGenericobjectType();
      $object_type->getFromDB($ID);
      $itemtype     = $object_type->fields['itemtype'];
      $fields_in_db = $DB->list_fields(getTableForItemType($object_type->fields["itemtype"]));
      $used_fields  = array();
      
      foreach ($GENERICOBJECT_AUTOMATICALLY_MANAGED_FIELDS as $autofield) {
         $used_fields[$autofield] = $autofield;
      }

      foreach ($GO_BLACKLIST_FIELDS as $autofield) {
         if (!in_array($autofield,$used_fields)) {
            $used_fields[$autofield] = $autofield;
         }
      }

      echo "<form name='form_fields' method='post' action=\"$url\">";
      echo "<div class='center'>";
      echo "<table class='tab_cadre_fixe' >";
      echo "<input type='hidden' name='id' value='$ID'>";
      echo "<tr class='tab_bg_1'><th colspan='7'>";
      echo $LANG['genericobject']['fields'][1] . " : " . 
         PluginGenericobjectObject::getLabel($object_type->fields["name"]);
      echo "</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th width='10'></th>";
      echo "<th>" . $LANG['genericobject']['fields'][2] . "</th>";
      echo "<th>" . $LANG['genericobject']['fields'][3] . "</th>";
      echo "<th width='10'></th>";
      echo "<th width='10'></th>";
      echo "</tr>";

      $index = 1;
      $total = count($fields_in_db);

      foreach ($fields_in_db as $field => $value) {
         self::displayFieldDefinition($url, $itemtype, $field, $index, $total);
         $index++;
         $used_fields[$field] = $field; 
      }
      
      echo "<tr><td><img src=\"" . $CFG_GLPI["root_doc"] . "/pics/arrow-left.png\" alt=''>"; 
      echo "</td><td class='center'>"; 
      
      echo "<a onclick= \"if ( markCheckboxes('form_fields') ) return false;\" href='" . $url . 
              "?id=$ID&amp;select=all'>" . $LANG['buttons'][18] . "</a>";
      echo "&nbsp;/&nbsp;<a onclick= \"if ( unMarkCheckboxes('form_fields') ) return false;\" href='" . 
               $url . "?id=$ID&amp;select=none'>" . $LANG['buttons'][19] . "</a>";
      echo "</td><td colspan='5' align='left' width='75%'>";
      $rand = Dropdown::showFromArray('massiveaction', array('' => DROPDOWN_EMPTY_VALUE, 
                                                             'delete' => $LANG['buttons'][6]));
      $params = array ('action' => '__VALUE__', 'itemtype' => $object_type->fields["itemtype"]);

      $ajax_page = $CFG_GLPI["root_doc"].
               "/plugins/genericobject/ajax/plugin_genericobject_dropdownObjectTypeFields.php";
      ajaxUpdateItemOnSelectEvent("dropdown_massiveaction$rand", "show_massiveaction", $ajax_page, 
                                  $params);
      echo "<span id='show_massiveaction'>&nbsp;</span>\n";
      echo "</td></tr>";
      echo "</table>";
      echo "<br>";

      echo "<table class='tab_cadre'>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . $LANG['genericobject']['fields'][4] . "</td>";
      echo "<td align='left'>";
      self::dropdownFields("new_field", $used_fields);
      echo "</td>";
      echo "<td>";
      echo "<input type='submit' name='add_field' value=\"" . $LANG['buttons'][8] . "\" class='submit'>";
      echo "</tr>";
      echo "</table></div></form>";
   }

   static function dropdownFields($name,$used = array()) {
      global $GO_FIELDS;
      
      $dropdown_types = array();
      foreach ($GO_FIELDS as $field => $values) {
         if(!in_array($field,$used)) {
            $dropdown_types[$field] = $values['name']." (".$field.")";
         }
      }
      Dropdown::showFromArray($name,$dropdown_types);
   }

   public static function displayFieldDefinition($target, $itemtype, $field, $index, $total) {
      global $GO_FIELDS, $CFG_GLPI, $GO_BLACKLIST_FIELDS;
      $readonly = in_array($field, $GO_BLACKLIST_FIELDS);

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
      echo "<td>" . $GO_FIELDS[$field]['name'] . "</td>";

      echo "<td width='10'>";
      if (!$readonly && $index > 2) {
         echo "<a href=\"" . $target . "?field=" . $field . "&amp;action=up&amp;itemtype=$itemtype\">"; 
         echo "<img src=\"" . $CFG_GLPI["root_doc"] . "/pics/deplier_up.png\" alt=''></a>";
      }
      echo "</td>";

      echo "<td width='10'>";
      if (!$readonly && $index > 1 && $index < $total) {
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
   public static function addNewField($table, $field) {
      global $DB, $GO_FIELDS;

      if (!FieldExists($table, $field)) {
         $query = "ALTER TABLE `$table` ADD `$field` ";
         switch ($GO_FIELDS[$field]['input_type']) {
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
         $DB->query($query);
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