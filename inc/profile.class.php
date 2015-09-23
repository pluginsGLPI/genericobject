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

      switch($item->getType()) {
         case 'Profile':
            return self::createTabEntry(__('Objects management', 'genericobject'));
            break;
         case 'PluginGenericobjectType':
            return self::createTabEntry(_n('Profile', 'Profiles', 2));
            break;
      }
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      switch($item->getType()) {
         case 'Profile':
            $profile = new self();
            $profile->showForm($item->getID());
            break;
         case 'PluginGenericobjectType':
            _log($item);
            self::showForItemtype($item);
            break;
      }
      return TRUE;
   }

   static function showForItemtype($type) {
      global $DB;

      if (!Session::haveRight("profile", READ)) {
         return false;
      }
      self::installRights();
      $canedit = Session::haveRight("profile", UPDATE);

      echo "<form action='" . self::getFormUrl() . "' method='post'>";
      echo "<table class='tab_cadre_fixe'>";
      $itemtype = $type->fields['itemtype'];
      echo "<tr><th colspan='2' align='center'><strong>";
      echo __("Rights assignment").":&nbsp;";
      echo $itemtype::getTypeName();
      echo "</strong></th></tr>";

      echo "<tr><td class='genericobject_type_profiles'>";
      $rights = array();
      foreach (getAllDatasFromTable(getTableForItemtype("Profile")) as $profile) {
         $prof = new Profile();
         $prof->getFromDB($profile['id']);
         $right = self::getProfileforItemtype($profile['id'], $itemtype);
         $label = $profile['name'];
         $rights = array(
            array(
               'label' => $label,
               'itemtype' => $itemtype,
               'field' =>  self::getProfileNameForItemtype($itemtype),
               'html_field' => "profile_" . $profile['id'],
            )
         );
         $prof->displayRightsChoiceMatrix(
            $rights
         );
      }
      echo "</td></tr>";
      echo "<input type='hidden' name='itemtype' value='".$itemtype."'>";

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
      $rights             = ProfileRight::getProfileRights($profiles_id);
      $itemtype_rightname = self::getProfileNameForItemtype($itemtype);
      return isset($rights[$itemtype_rightname]) ? $rights[$itemtype_rightname] : 0;
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
      if (!self::profileExists($_SESSION["glpiactiveprofile"]["id"], 'PluginGenericobjectType')) {
         self::createAccess($_SESSION["glpiactiveprofile"]["id"],"PluginGenericobjectType",true);
      }
   }

   /**
    * Check if rights for a profile still exists
    * @param profiles_id the profile ID
    * @param itemtype name of the type
    * @return true if exists, no if not
    */
   public static function profileExists($profiles_id, $itemtype = false) {
      $profile = new Profile();
      $profile->getFromDB($profiles_id);
      $rights = ProfileRight::getProfileRights($profiles_id);
      $itemtype_rightname = self::getProfileNameForItemtype($itemtype);
      if($itemtype) {
         _log(
            "get rights on itemtype ".$itemtype." for profile ".$profile->fields['name'], ':',
            isset($rights[$itemtype_rightname]) ? $rights[$itemtype_rightname] : "NONE"
         );
         return (isset($rights[self::getProfileNameForItemtype($itemtype)]));
      }
      return true;
   }

   /**
    * Create rights for the profile if it doesn't exists
    * @param profileID the profile ID
    * @return nothing
    */
   public static function createAccess($profiles_id, $itemtype, $first=false) {

      $rights             = getAllDatasFromTable('glpi_profiles');
      $profile_right      = new ProfileRight();
      $itemtype_rightname = self::getProfileNameForItemtype($itemtype);
      
      foreach ($rights as $right) {
         if ($right['id'] == $profiles_id) {
            $r = ALLSTANDARDRIGHT | READNOTE | UPDATENOTE;   
         } else {
            $r = 0;
         }
         $profile_right->updateProfileRights($right['id'], 
                                             array($itemtype_rightname => $r));
      }
   }

   public static function getGeneralRights() {
      $rights = array();
      $rights[] = array(
         'itemtype' => 'PluginGenericobjectType',
         'label'    => __("Type of objects", "genericobject"),
         'field'    => self::getProfileNameForItemtype('PluginGenericobjectType'),
      );
      return $rights;
   }

   public static function getTypesRights() {
      $rights = array();

      include_once(GLPI_ROOT."/plugins/genericobject/inc/type.class.php");

      $types = PluginGenericobjectType::getTypes(true);
      if ( count( $types) > 0 ) {
         foreach ($types as $_ => $type) {
            $itemtype   = $type['itemtype'];
            $field      = self::getProfileNameForItemtype($itemtype);
            $objecttype = new PluginGenericobjectType($itemtype);
            $rights[]   = array(
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
         _log($right_name, isset($installed_rights[$right_name]));
         if ( !isset($installed_rights[$right_name]) ) {
            $missing_rights[] = $right_name;
         }
      }

      //Install missing rights in profile and update the object
      if ( count($missing_rights) > 0) {
         ProfileRight::addProfileRights($missing_rights);
         self::changeProfile();
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
      $general_rights = self::getGeneralRights();
      $type_rights    = self::getTypesRights();
      $db_rights      = ProfileRight::getProfileRights($_SESSION['glpiactiveprofile']['id']);
      $rights         = array_merge($general_rights, $type_rights);

      foreach ($rights as $right) {
         $str_right = $right['field'];
         if (preg_match("/plugin_genericobject_/", $str_right)) {
            unset($_SESSION['glpiactiveprofile'][$str_right]);
            if (!empty($db_rights) && isset($db_rights[$str_right])) {
               $_SESSION['glpiactiveprofile'][$str_right] = $db_rights[$str_right]; 
            }
         }
      }
   }

   static function install(Migration $migration) {
      global $DB;

      $profileRight = new ProfileRight();
      $profile      = new Profile();

      //Update needed
      if (TableExists('glpi_plugin_genericobject_profiles')) {
         foreach (getAllDatasFromTable('glpi_plugin_genericobject_profiles') as $right) {
            if (preg_match("/PluginGenericobject(.*)/", $right['itemtype'], $results)) {
               $newrightname = 'plugin_genericobject_'.strtolower($results[1]).'s';
               if (!countElementsInTable('glpi_profilerights', 
                                         "`profiles_id`='".$right['profiles_id']."' 
                                           AND `name`='$newrightname'")) {
                  switch ($right['right']) {
                     case NULL:
                     case '':
                        $rightvalue = 0;
                        break;
                     case 'r':
                        $rightvalue = READ;
                        break;
                     case 'w':
                        $rightvalue = ALLSTANDARDRIGHT;
                        break;
                  }

                  $profileRight->add(array('profiles_id' => $right['profiles_id'], 
                                           'name' => $newrightname, 
                                           'rights' => $rightvalue));

                  if (!countElementsInTable('glpi_profilerights', 
                                            "`profiles_id`='".$right['profiles_id']."' 
                                              AND `name`='plugin_genericobject_types'")) {
                     $profileRight->add(array('profiles_id' => $right['profiles_id'], 
                                              'name' => 'plugin_genericobject_types', 
                                              'rights' => 23));
                  }
               }

               if ($right['open_ticket']) {
                  $profile->getFromDB($right['profiles_id']);
                  $helpdesk_item_types = json_decode($profile->fields['helpdesk_item_type'], true);
                  if (is_array($helpdesk_item_types)) {
                     if (!in_array($right['itemtype'], $helpdesk_item_types)) {
                        $helpdesk_item_types[] = $right['itemtype'];
                     }
                  } else {
                     $helpdesk_item_types = array($right['itemtype']);
                  }

                  $tmp['id'] = $profile->getID();
                  $tmp['helpdesk_item_type'] = json_encode($helpdesk_item_types);
                  $profile->update($tmp);
               }
            }
         }
         //$migration->dropTable('glpi_plugin_genericobject_profiles');
      }
      if (!countElementsInTable('glpi_profilerights', 
                                "`name` LIKE '%genericobject%'")) {
         self::createFirstAccess();
      }
   }

   static function uninstall() {
      global $DB;
      $query = "DELETE FROM `glpi_profilerights` 
                WHERE `name` LIKE '%plugin_genericobject%'";
      $DB->query($query) or die($DB->error());
   }
}
