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
class PluginGenericobjectProfile extends CommonDBTM {

   /* if profile deleted */
   function cleanProfiles($id) {
      $this->deleteByCriteria(array('id' => $id));
   }

   static function showForItemtype($type) {
      global $DB;

      if (!Session::haveRight("profile", "r")) {
         return false;
      }
      $canedit = Session::haveRight("profile", "w");
   
      echo "<form action='" . Toolbox::getItemTypeSearchURL(__CLASS__) . "' method='post'>";
      echo "<table class='tab_cadre_fixe'>";
      $itemtype = $type->fields['itemtype'];
      echo "<tr><th colspan='2' align='center'><strong>";
      echo __("Rights assignment").":&nbsp;";
      echo $itemtype::getTypeName();
      echo "</strong></th></tr>";

      foreach (getAllDatasFromTable('glpi_profiles') as $profile) {
         echo "<tr><th colspan='2' align='center'><strong>";
         echo __("Profile")." ".$profile['name']."</strong></th></tr>";

         $pgf_find = self::getProfileforItemtype($profile['id'], $itemtype);

         if (!count($pgf_find) > 0) {
            self::createAccess($profile['id']);
            $pgf_find = self::getProfileforItemtype($profile['id'], $itemtype);
         }

         $PluginGenericobjectProfile = new self();
         $PluginGenericobjectProfile->getFromDB($pgf_find['id']);

         $prefix = "profiles[".$pgf_find['id']."]";
         if ($profile['interface'] == 'central') {
            echo "<tr class='tab_bg_2'>";
            echo "<td>" . __("Access object", "genericobject") . ":</td><td>";
            Profile::dropdownNoneReadWrite($prefix."[right]",
                              $PluginGenericobjectProfile->fields['right'], 1, 1, 1);
            echo "</td></tr>";
         }
         if ($type->canUseTickets()) {
            echo "<tr class='tab_bg_2'>";
            echo "<td>" . __("Associate tickets to this object", "genericobject") . ":</td><td>";
            Dropdown::showYesNo($prefix."[open_ticket]",
                              $PluginGenericobjectProfile->fields['open_ticket']);
            echo "</td></tr>";
         }
         
      }

      if ($canedit) {
         echo "<tr class='tab_bg_1'>";
         echo "<td align='center' colspan='2'>";
         echo "<input type='submit' name='update_all_rights' value=\"" .
            _sx('button', 'Post') . "\" class='submit'>";
         echo "</td></tr>";
      }
      echo "</table>";
      Html::closeForm();
   }
   
   /* profiles modification */
   function showForm($id) {
      if (!Session::haveRight("profile", "r")) {
         return false;
      }
      $canedit = Session::haveRight("profile", "w");
      if ($id) {
         $this->getProfilesFromDB($id);
      }

      $general_profile = new Profile();
      $general_profile->getFromDB($id);
      
      echo "<form action='" . $this->getSearchURL() . "' method='post'>";
      echo "<table class='tab_cadre_fixe'>";


      $types = PluginGenericobjectType::getTypes(true);
      
      if (!empty ($types)) {

         echo "<tr><th colspan='2' align='center'><strong>";
         echo __("Rights assignment")."</strong></th></tr>";
         
         foreach ($types as $tmp => $type) {
            $itemtype   = $type['itemtype'];
            $objecttype = new PluginGenericobjectType($itemtype);
            $profile    = self::getProfileforItemtype($id, $itemtype);
            echo "<tr><th align='center' colspan='2' class='tab_bg_2'>".
               $itemtype::getTypeName()."</th></tr>";
            if ($general_profile->fields['interface'] == 'central') {
               echo "<tr class='tab_bg_2'>";
               $right = $type['itemtype'];
               echo "<td>" . __("Access object", "genericobject") . ":</td><td>";
               Profile::dropdownNoneReadWrite($right,  $profile['right'], 1, 1, 1);
               echo "</td></tr>";
            }
            if ($objecttype->canUseTickets()) {
               echo "<tr class='tab_bg_2'>";
               echo "<td>" . __("Associate tickets to this object", "genericobject") . ":</td><td>";
               $right_openticket = $type['itemtype']."_open_ticket";
               Dropdown::showYesNo($right_openticket,  $profile['open_ticket']);
               echo "</td></tr>";
            }

         }
         if ($canedit) {
            echo "<tr class='tab_bg_1'>";
            echo "<td align='center' colspan='2'>";
            echo "<input type='hidden' name='profiles_id' value='".$id."'>";
            echo "<input type='hidden' name='id' value=$id>";
            echo "<input type='submit' name='update_user_profile' value=\"" .
               _sx('button', 'Post') . "\" class='submit'>";
            echo "</td></tr>";
         
         }

      } else {
         echo "<tr><td class='center'><strong>";
         echo __("No type defined", "genericobject")."</strong></td></tr>";
      }

      echo "</table>";
      Html::closeForm();

   }

   static function getProfileforItemtype($profiles_id, $itemtype) {
      $results = getAllDatasFromTable(getTableForItemType(__CLASS__),
                                      "`itemtype`='$itemtype' AND `profiles_id`='$profiles_id'");
      if (!empty($results)) {
         return array_pop($results);
      } else {
         return array();
      }
   }
   
   function getProfilesFromDB($id, $config = true) {
      global $DB;
      $prof_datas = array ();
      foreach (getAllDatasFromTable(getTableForItemType(__CLASS__),
                                    "`profiles_id`='" . $id . "'") as $prof) {
         if ($prof['right'] != "" || $config) {
            $prof_datas[$prof['itemtype']]                = $prof['right'];
            $prof_datas[$prof['itemtype'].'_open_ticket'] = $prof['open_ticket'];
            $prof_datas['id']                             = $prof['id'];
         }
      }

      if (empty($prof_datas) && !$config) return false;
      
      $prof_datas['profiles_id']   = $id;
      $this->fields       = $prof_datas;
      
      return true;
   }

   function saveProfileToDB($params) {
      global $DB;

      $types = PluginGenericobjectType::getTypes(true);
      if (!empty ($types)) {
         foreach ($types as $tmp => $profile) {
            $query = "UPDATE `".getTableForItemType(__CLASS__)."` " .
                     "SET ";

            if (isset($params[$profile['itemtype']]) && $params[$profile['itemtype']] == 'NULL') {
               $query.="`right`='' ";
            } else {
               if (isset($params[$profile['itemtype']])) {
                  $query.="`right`='".$params[$profile['itemtype']]."'";
               } else {
                  $query.="`right`=''";
               }
            }

            if (isset($params[$profile['itemtype'].'_open_ticket'])) {
               $query.=", `open_ticket`='".$params[$profile['itemtype'].'_open_ticket']."' ";
            }

      
            $query.="WHERE `profiles_id`='".$params['profiles_id']."' " .
                    "AND `itemtype`='".$profile['itemtype']."'";
            $DB->query($query);
         }
      }
   }
   
   
   /**
    * Create rights for the current profile
    * @param profileID the profile ID
    * @return nothing
    */
   public static function createFirstAccess() {
      if (!self::profileExists($_SESSION["glpiactiveprofile"]["id"])) {
         self::createAccess($_SESSION["glpiactiveprofile"]["id"],true);
      }
   }
   
   /**
    * Check if rights for a profile still exists
    * @param profiles_id the profile ID
    * @param itemtype name of the type
    * @return true if exists, no if not
    */
   public static function profileExists($profiles_id, $itemtype = false) {
      $condition = "`profiles_id`='$profiles_id'";
      if($itemtype) {
         $condition.= "AND `itemtype`='$itemtype'";
      }
      return (countElementsInTable(getTableForItemType(__CLASS__),$condition) >0?true:false);
   }
   
   /**
    * Create rights for the profile if it doesn't exists
    * @param profileID the profile ID
    * @return nothing
    */
   public static function createAccess($profiles_id, $first=false) {
      $profile = new self();
      foreach ( PluginGenericobjectType::getTypes(true) as $tmp => $value) {
         if (!self::profileExists($profiles_id, $value["itemtype"])) {
            $input["itemtype"]    = $value["itemtype"];
            $input["right"]       = ($first?'w':'');
            $input["open_ticket"] = ($first?1:0);
            $input["profiles_id"] = $profiles_id;
            $profile->add($input);
         }
      }
   }


   /**
    * Delete type from the rights
    * @param name the name of the type
    * @return nothing
    */
   public static function deleteTypeFromProfile($itemtype) {
      $profile = new self();
      $profile->deleteByCriteria(array("itemtype" => $itemtype));
   }
   
   public static function changeProfile() {
      $profile = new self();
      if($profile->getProfilesFromDB($_SESSION['glpiactiveprofile']['id'], false)) {
         foreach ($profile->fields as $key => $value) {
            if ($key != 'id') {
               $_SESSION["glpiactiveprofile"][$key] = $value;
            }
         }
      } else {
         foreach ($_SESSION["glpiactiveprofile"] as $key => $value) {
            if (preg_match("/^PluginGenericobject/",$key)) {
               unset($_SESSION["glpiactiveprofile"][$key]);
            }
         }
      }
   }

   static function install(Migration $migration) {
      global $DB;
      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $query = "CREATE TABLE `$table` (
                           `id` int(11) NOT NULL auto_increment,
                           `profiles_id` int(11) NOT NULL  DEFAULT '0',
                           `itemtype` VARCHAR( 255 ) default NULL,
                           `right` char(1) default NULL,
                           `open_ticket` char(1) NOT NULL DEFAULT 0,
                           PRIMARY KEY  (`id`),
                           KEY `name` (`profiles_id`)
                           ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die($DB->error());
      }
      self::createFirstAccess();
   }
   
   static function uninstall() {
      global $DB;
      $query = "DROP TABLE IF EXISTS `".getTableForItemType(__CLASS__)."`";
      $DB->query($query) or die($DB->error());
   }
}