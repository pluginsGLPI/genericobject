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
class PluginGenericobjectProfile extends Profile {

   /* if profile deleted */
   function cleanProfiles($id) {
      $this->deleteByCriteria(array('id' => $id));
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      if ($item->fields['interface'] == 'central') {
         return self::createTabEntry(__('Generic Object'));
      }
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      $profile = new self();
      $profile->showForm($item->getID());
      return TRUE;
   }

   static function showForItemtype($type) {
      global $DB;

      if (!Session::haveRight("profile", READ)) {
         return false;
      }
      $canedit = Session::haveRight("profile", UPDATE);

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

   static function getProfileNameForItemtype($itemtype) {
      return preg_replace("/^glpi_/","",getTableForItemType($itemtype));
   }


   /* profiles modification */
   function showForm($profiles_id, $options = array()) {
      if (!Session::haveRight("profile", READ)) {
         return false;
      }
      $canedit = Session::haveRight("profile", UPDATE);
      //if ($id) {
      //   $this->getProfilesFromDB($id);
      //}

      //Ensure rights are defined in database
      self::installRights();

      $profile = new Profile();
      $profile->getFromDB($profiles_id);


      echo "<form action='" . Profile::getFormUrl() . "' method='post'>";
      echo "<table class='tab_cadre_fixe'>";

      $general_rights = self::getGeneralRights();

      $profile->displayRightsChoiceMatrix(
         $general_rights,
         array(
            'canedit'       => $canedit,
            'default_class' => 'tab_bg_2',
            'title'         => __('General', 'genericobject')
         )
      );

      $types_rights = self::getTypesRights();

      $title = __('Objects', 'genericobject');
      if (count($types_rights) == 0) {
         $title .= __(" (No types defined yet)", "genericobject");
       }

      $profile->displayRightsChoiceMatrix(
         $types_rights,
         array(
            'canedit'       => $canedit,
            'default_class' => 'tab_bg_2',
            'title'         => $title
         )
      );
      $profile->showLegend();
      if ($canedit) {
         echo "<div class='center'>";
         echo Html::hidden('id', array('value' => $profiles_id));
         echo Html::submit(_sx('button', 'Save'), array('name' => 'update'));
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";

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
   }

   public static function getGeneralRights() {
      $rights = array();
      $rights[] = array(
         'itemtype' => 'PluginGenericobjectType',
         'label'    => PluginGenericobjectType::getTypeName(),
         'field'    => self::getProfileNameForItemtype('PluginGenericobjectType'),
      );
      return $rights;
   }

   public static function getTypesRights() {
      $rights = array();

      $types = PluginGenericobjectType::getTypes(true);
      if ( count( $types) > 0 ) {
         foreach ($types as $_ => $type) {
            $itemtype   = $type['itemtype'];
            $field = self::getProfileNameForItemtype($itemtype);
            $objecttype = new PluginGenericobjectType($itemtype);
            $rights[] = array(
               'itemtype' => $itemtype,
               'label'    => $itemtype::getTypeName(),
               'field'    => self::getProfileNameForItemtype($itemtype)
            );
         }
      }

      return $rights;
   }

   public static function installRights($first=false) {
      $missing_rights = array();
      $installed_rights = ProfileRight::getAllPossibleRights();
      $right_names = array();

      // Add common plugin's rights
      $right_names[] = self::getProfileNameForItemtype('PluginGenericobjectType');

      // Add types' rights
      $types = PluginGenericobjectType::getTypes(true);
      foreach($types as $_ => $type) {
         $itemtype = $type['itemtype'];
         $right_names[] = self::getProfileNameForItemtype($itemtype);
      }

      // Check for already defined rights
      foreach($right_names as $right_name) {
         if ( !isset($installed_rights[$right_name]) ) {
            $missing_rights[] = $right_name;
         }
      }

      //Install missing rights in profile and update the object
      if ( count($missing_rights) > 0) {
         ProfileRight::addProfileRights($missing_rights);
      }

   }

   /**
    * Delete type from the rights
    * @param name the name of the type
    * @return nothing
    */
   public static function deleteTypeFromProfile($itemtype) {
      $rights = array();
      $rights[] = self::getProfileNameForItemtype($itemtype);
      ProfileRight::deleteProfileRights($rights);
   }

   public static function changeProfile() {
      $profile = new self();
      if($profile->getProfilesFromDB($_SESSION['glpiactiveprofile']['id'])) {
         foreach ($profile->fields as $key => $value) {
            if (preg_match("/^PluginGenericobject/",$key)) {
               $_SESSION["glpi_plugin_genericobject_profile"][$key] = $value;
            }
         }
      } else {
         unset($_SESSION["glpi_plugin_genericobject_profile"]);
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
