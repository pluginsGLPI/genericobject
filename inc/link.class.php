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
class PluginGenericobjectLink extends CommonDBTM{
   
   public static function showDeviceTypeLinks($target,$ID) {
      global $LANG, $CFG_GLPI, $GENERICOBJECT_LINK_TYPES;
      $object_type = new PluginGenericobjectType();
      $object_type->getFromDB($ID);
         
      $links = self::getLinksByType($object_type->fields["itemtype"]);
      
      echo "<form name='form_links' method='post' action=\"$target\">";
      echo "<div class='center'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<input type='hidden' name='id' value='$ID'>";
      echo "<tr class='tab_bg_1'><th>";
      echo $LANG['genericobject']['links'][1]."</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td align='center'>";
      echo "<select name='link_itemtype[]' multiple size='10' width='40'>";
      
      foreach($GENERICOBJECT_LINK_TYPES as $key => $link) {
         echo "<option value='$key' ".(in_array($key,$links)?"selected":"").">" . 
            call_user_func(array($link, 'getTypeName')). "</option>\n";
      }
      echo "</select>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td align='center'>";
      echo "<input type='submit' name='update_links_types' value=\"" . $LANG['buttons'][7] .
             "\" class='submit'>";
      echo "</td></tr>";
      
      echo "</table></div></form>";
   }

   public static function getLinksByType($itemtype) {
      global $DB;
      $query  = "SELECT destination_type FROM `".getTableForItemType(__CLASS__)."` " .
                "WHERE itemtype='$itemtype'";
      $result = $DB->query($query);
      $types  = array();
      while ($datas = $DB->fetch_array($result))
         $types[] = $datas["destination_type"];
      return $types; 
   }

   public static function getLinksByTypeAndID($name, $device_id) {
      global $DB;
      $query  = "SELECT * FROM `".PluginGenericobjectType::getLinkDeviceTableName($name)."` " .
                 "WHERE `source_id`='$device_id'";
      $result = $DB->query($query);
      $types = array();
      while ($datas = $DB->fetch_array($result))
         $types[] = $datas["destination_type"];
      return $types; 
   }

   public static function linkedDeviceTypeExists($itemtype, $destination_type) {
      global $DB;
      $query = "SELECT COUNT(*) FROM `".getTableForItemType(__CLASS__)."` " .
               "WHERE itemtype='$itemtype' AND destination_type='$destination_type'";
      $result = $DB->query($query);
      if ($DB->result($result,0,0))
         return true;
      else
         return false;  
   }

   public static function addNewLinkedDeviceType($itemtype, $destination_type) {
      if (!self::linkedDeviceTypeExists($itemtype,$destination_type)) {
         $link_type = new PluginGenericobjectLink;
         $input["itemtype"] = $itemtype;
         echo $input["destination_type"] = $destination_type;
         $link_type->add($input);
      }
   }

   public static function deleteAllLinkedDeviceByType($itemtype) {
      global $DB;
      $DB->query("DELETE FROM `".getTableForItemType(__CLASS__)."` WHERE itemtype='$itemtype'");
   }

   public static function addDeviceLink($source_type, $source_id, $itemtype,  $items_id) {
      global $DB;
      $name = PluginGenericobjectType::getNameByID($source_type);
      $table = PluginGenericobjectType::getLinkDeviceTableName($name);
      $query = "INSERT INTO `$table` (`id`, `source_id`, `itemtype`, `items_id`) " .
               "VALUES (NULL, $source_id, $itemtype,$items_id)";
      $DB->query($query);
   }

   public static function deleteDeviceLink($source_type,$link_id) {
      global $DB;
      $name = PluginGenericobjectType::getNameByID($source_type);
      $table = PluginGenericobjectType::getLinkDeviceTableName($name);
      $DB->query("DELETE FROM `$table` WHERE id=$link_id");
   }
   
   static function install(Migration $migration) {
      global $DB;

      if (!TableExists(getTableForItemType(__CLASS__))) {
         $query = "CREATE TABLE `".getTableForItemType(__CLASS__)."` (
                           `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
                           `itemtype` varchar(255) collate utf8_unicode_ci default NULL,
                           `destination_type` INT( 11 ) NOT NULL DEFAULT '0',
                           PRIMARY KEY ( `id` )
                           ) ENGINE = MYISAM COMMENT = 'Device type links definitions' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query);
      }
   }
   
   static function uninstall() {
      global $DB;
      $query = "DROP TABLE IF EXISTS `".getTableForItemType(__CLASS__)."`";
      $DB->query($query) or die ($DB->error());
   }
}
?>
