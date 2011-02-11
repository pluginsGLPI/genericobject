<?php


/*----------------------------------------------------------------------
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
   ----------------------------------------------------------------------*/
/*----------------------------------------------------------------------
    Original Author of file: 
    Purpose of file:
    ----------------------------------------------------------------------*/
class PluginGenericobjectProfile extends CommonDBTM {
   function __construct() {
      $this->table = "glpi_plugin_genericobject_profiles";
      $this->type = -1;
   }
   /* if profile deleted */
   function cleanProfiles($ID) {
      global $DB;

      $query = "DELETE FROM `".$this->table."` 
                    WHERE ID='$ID' ";
      $DB->query($query);
   }

   /* profiles modification */
   function showForm($target, $ID) {
      global $LANG;


      if (!haveRight("profile", "r"))
         return false;
      $canedit = haveRight("profile", "w");
      if ($ID) {
         $this->getProfilesFromDB($ID);
      }

      echo "<form action='" . $target . "' method='post'>";
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr><th colspan='2' align='center'><strong>" . $LANG["genericobject"]['profile'][0] . " " . $this->fields["name"] . "</strong></th></tr>";

      $types = plugin_genericobject_getAllTypes(true);
      
      if (!empty ($types)) {
         foreach ($types as $tmp => $profile) {
            $name = plugin_genericobject_getNameByID($profile['itemtype']);

            plugin_genericobject_includeLocales($name);
            echo "<tr><th align='center' colspan='2' class='tab_bg_2'>".plugin_genericobject_getObjectLabel($profile['name'])."</th></tr>";
            echo "<tr class='tab_bg_2'>";
            echo "<td>" . plugin_genericobject_getObjectLabel($profile['name']) . ":</td><td>";
            Profile::dropdownNoneReadWrite($name, $this->fields[$profile['name']], 1, 1, 1);
            echo "</td>";
            echo "</tr>";
            if ($profile["use_tickets"])
            {
               echo "<tr class='tab_bg_2'>";
               echo "<td>" . $LANG["genericobject"]['profile'][1] . ":</td><td>";
               //dropdownYesNo($name."_open_ticket", $this->fields[$name.'_open_ticket']);
               Dropdown::showYesNo($name."_open_ticket", $this->fields[$name.'_open_ticket']);
               echo "</td>";
               echo "</tr>";
            }

         }
      }

      if ($canedit) {
         echo "<tr class='tab_bg_1'>";
         echo "<td align='center' colspan='2'>";
         echo "<input type='hidden' name='profile_name' value='".$this->fields["name"]."'>";
         echo "<input type='hidden' name='ID' value=$ID>";
         echo "<input type='submit' name='update_user_profile' value=\"" . $LANG['buttons'][7] . "\" class='submit'>";
         echo "</td></tr>";
      }
      echo "</table></form>";
   }


   function getProfilesFromDB($ID) {
      global $DB;
      $profile = new Profile;
      $profile->getFromDB($ID);

      $prof_datas = array ();
      $query = "SELECT `device_name`, `right`, `open_ticket` FROM `" . $this->table . "` WHERE name='" . $profile->fields["name"] . "'";
      $result = $DB->query($query);
      while ($prof = $DB->fetch_array($result))
      {
         $prof_datas[$prof['device_name']] = $prof['right'];
         $prof_datas[$prof['device_name'].'_open_ticket'] = $prof['open_ticket'];
      }
      
      $prof_datas['ID'] = $ID;
      $prof_datas['name'] = $profile->fields["name"];
      $this->fields = $prof_datas;
   
      return true;
   }

   function saveProfileToDB($params)
   {
      global $DB;
      $this->getProfilesFromDB($params["ID"]);
      
      $types = plugin_genericobject_getAllTypes();
      if (!empty ($types)) {
         foreach ($types as $tmp => $profile) {
            $query = "UPDATE `".$this->table."` " .
                  "SET `right`='".$params[$profile['name']]."' ";
                  
                  if (isset($params[$profile['name'].'_open_ticket']))
                     $query.=", `open_ticket`='".$params[$profile['name'].'_open_ticket']."' ";
                  
                  $query.="WHERE `name`='".$this->fields['name']."' AND `device_name`='".$profile['name']."'";
            $DB->query($query);      
         }
      }      
   }
   
   
   /**
    * Create rights for the current profile
    * @param profileID the profile ID
    * @return nothing
    */
   public static function plugin_genericobject_createFirstAccess() {
      if (!self::plugin_genericobject_profileExists($_SESSION["glpiactiveprofile"]["id"]))
         self::plugin_genericobject_createAccess($_SESSION["glpiactiveprofile"]["id"],true);
   }
   
   
   /**
    * Check if rights for a profile still exists
    * @param profileID the profile ID
    * @return true if exists, no if not
    */
   public static function plugin_genericobject_profileExists($profileID) {
      global $DB;
      $profile = new Profile;
      $profile->getFromDB($profileID);
      $query = "SELECT COUNT(*) as cpt FROM `glpi_plugin_genericobject_profiles` WHERE name='".$profile->fields["name"]."'";
      $result = $DB->query($query);
      if ($DB->result($result,0,"cpt") > 0)
         return true;
      else
         return false;   
   }
   
   /**
    * Create rights for the profile if it doesn't exists
    * @param profileID the profile ID
    * @return nothing
    */
   public static function plugin_genericobject_createAccess($profileID,$first=false) {
      $types = plugin_genericobject_getAllTypes(true);
      $plugin_profile = new PluginGenericobjectProfile;
      $profile = new Profile;
      $profile->getFromDB($profileID);
      foreach ($types as $tmp => $value) {
         if (!self::plugin_genericobject_profileForTypeExists($profileID,$value["name"])) {
            $input["device_name"] = $value["name"];
            $input["right"] = ($first?'w':'');
            $input["open_ticket"] = ($first?1:0);
            $input["name"] = $profile->fields["name"];
            $plugin_profile->add($input);
         }
      }
   }
   
   /**
    * Check if rights for a profile and type still exists
    * @param profileID the profile ID
    * @param device_name name of the type 
    * @return true if exists, no if not
    */
   public static function plugin_genericobject_profileForTypeExists($profileID,$device_name)
   {
      global $DB;
      $profile = new Profile;
      $profile->getFromDB($profileID);
      $query = "SELECT COUNT(*) as cpt FROM `glpi_plugin_genericobject_profiles` WHERE name='".$profile->fields["name"]."' " .
            "AND device_name='$device_name'";
      $result = $DB->query($query);
      if ($DB->result($result,0,"cpt") > 0)
         return true;
      else
         return false;   
   }

   /**
    * Delete type from the rights
    * @param name the name of the type
    * @return nothing
    */
   public static function plugin_genericobject_deleteTypeFromProfile($name)
   {
      global $DB;
      $query = "DELETE FROM `glpi_plugin_genericobject_profiles` WHERE device_name='$name'";
      $DB->query($query);
   }
   
}
?>
