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
class PluginGenericobjectObject extends CommonDBTM {

   //Object type configuration
   private $type_infos = array ();

   //Internal field counter
   private $cpt = 0;


   function canCreate() {
      //return true;
      $type = strtolower(str_replace("PluginGenericobject", "", $this->type));
      return plugin_genericobject_haveRight($type, 'w');
   }

   function canView() {
      //return true;
      $type = strtolower(str_replace("PluginGenericobject", "", $this->type));
      return plugin_genericobject_haveRight($type, 'r');
   }
   
   function __construct($itemtype = 0) {
      if ($itemtype) {
         $this->setType($itemtype);
         $_SESSION["glpi_plugin_genericobject_itemtype"] = $itemtype;
      }
      else
         $this->setType($_SESSION["glpi_plugin_genericobject_itemtype"]);
   }

   function setType($itemtype) {
      $this->type = $itemtype;
      $this->type = plugin_genericobject_getObjectTypeByName($itemtype);
      $this->table = plugin_genericobject_getTableNameByID($itemtype);
      $this->type_infos = plugin_genericobject_getObjectTypeConfiguration($itemtype);
      $this->entity_assign = $this->type_infos['use_entity'];
      $this->may_be_recursive = $this->type_infos['use_recursivity'];
      $this->dohistory = $this->type_infos['use_history'];
   }

   function defineTabs($options=array()) {
      global $LANG;
      $ong = array ();

      $ong[1] = $LANG['title'][26];

      if ($this->fields['id'] > 0) {

         if ($this->canUseDirectConnections() || $this->canUseNetworkPorts())
            $ong[3] = $LANG['title'][27];

         if ($this->canUseInfocoms()) {
            $ong[4] = $LANG['Menu'][26];
         }

         if ($this->canUseDocuments()) {
            $ong[5] = $LANG['Menu'][27];
         }

         if ($this->canUseTickets()) {
            $ong[6] = $LANG['title'][28];
         }

         $linked_types = plugin_genericobject_getLinksByType($this->type);
         if (!empty ($linked_types)) {
            $ong[7] = $LANG['setup'][620];
         }

         /*
               if ($this->type_infos["use_links"] && haveRight("link", "r")) {
                  $ong[7] = $LANG['title'][34];
               }
         */
         if ($this->type_infos["use_notes"] && haveRight("notes", "r")) {
            $ong[10] = $LANG['title'][37];
         }

         if ($this->canUseLoans()) {
            $ong[11] = $LANG['Menu'][17];
         }

         if ($this->canUseHistory())
            $ong[12] = $LANG['title'][38];

      }
      return $ong;
   }

   function canUseInfocoms() {
      return ($this->type_infos["use_infocoms"] 
               && (haveRight("contract", "r") || haveRight("infocom", "r")));
   }

   function canUseDocuments() {
      return ($this->type_infos["use_documents"] && haveRight("document", "r"));

   }

   function canUseTickets() {
      return ($this->type_infos["use_tickets"] && haveRight("show_all_ticket", "1"));
   }

   function canUseNotes() {
      return ($this->type_infos["use_notes"] && haveRight("notes", "r"));
   }

   function canUseLoans() {
      return ($this->type_infos["use_loans"] && haveRight("reservation_central", "r"));
   }

   function canUseHistory() {
      return ($this->type_infos["use_history"]);
   }

   function canUsePluginDataInjection() {
      return ($this->type_infos["use_plugin_datainjection"]);
   }

   function canUsePluginPDF() {
      return ($this->type_infos["use_plugin_pdf"]);
   }

   function canUsePluginOrder() {
      return ($this->type_infos["use_plugin_order"]);
   }

   function canUseNetworkPorts() {
      return ($this->type_infos["use_network_ports"]);
   }

   function canUseDirectConnections() {
      return ($this->type_infos["use_direct_connections"]);
   }

   function title($name) {
      displayTitle('', plugin_genericobject_getObjectLabel($name), 
                   plugin_genericobject_getObjectLabel($name));
   }

   function showForm($ID, $options=array(), $previsualisation = false) {
      global $LANG;

      if ($previsualisation) {
         $canedit = true;
         $this->getEmpty();
      } else {
         if ($ID > 0) {
            $this->check($ID, 'r');
         } else {
            // Create item 
            $this->check(-1, 'w');
            $use_cache = false;
            $this->getEmpty();
         }

         $this->showTabs($options);
         
         $canedit = $this->can($ID, 'w');
      }
      
      $this->fields['id'] = $ID;
      

      $this->showFormHeader($options);
      echo "<input type='hidden' name='itemtype' value='" . 
         strtolower(str_replace("PluginGenericobject", "", $this->type)) . "'>";

      if ($this->type_infos["use_entity"])
         echo "<input type='hidden' name='entities_id' value='" . 
            $this->fields["entities_id"] . "'>";

      if (!$previsualisation)
         echo "<div class='center' id='tabsbody'>";
      else
         echo "<div class='center'>";

      echo "<table class='tab_cadre_fixe' >";


      foreach (plugin_genericobject_getFieldsByType($this->type) as $field => $tmp) {
         $value = $this->fields[$field];
         $this->displayField($canedit, $field, $value);
      }
      $this->closeColumn();

      if (!$previsualisation)
         $this->displayActionButtons($ID, $_GET['withtemplate'], $canedit);

      echo "</table></div></form>";
      if (!$previsualisation) {
         echo "<div id='tabcontent'></div>";
         echo "<script type='text/javascript'>loadDefaultTab();</script>";
      }
   }

   function displayActionButtons($ID, $withtemplate, $canedit) {
      global $LANG;
      if ($canedit) {
         echo "<tr>";
         echo "<td class='tab_bg_2' colspan='4' align='center'>";

         if (empty ($ID) || $ID < 0 || $withtemplate == 2) {
            echo "<input type='submit' name='add' value=\"" . $LANG['buttons'][8] . 
                     "\" class='submit'>";
         } else {
            echo "<input type='hidden' name='id' value=\"$ID\">\n";
            echo "<input type='submit' name='update' value=\"" . $LANG['buttons'][7] . 
                     "\" class='submit'>";

            if (!$this->fields["deleted"]) {
               echo "&nbsp<input type='submit' name='delete' value=\"" . $LANG['buttons'][6] . 
                  "\" class='submit'>";
            } else {
               if ($this->type_infos["use_deleted"]) {
                  echo "&nbsp<input type='submit' name='restore' value=\"" . $LANG['buttons'][21] . 
                     "\" class='submit'>";
                  echo "&nbsp<input type='submit' name='purge' value=\"" . $LANG['buttons'][22] . 
                     "\" class='submit'>";
               }
            }
         }
         echo "</td>";
         echo "</tr>";
      }
   }

   function getAllTabs() {
      global $LANG;
      foreach (getAllDatasFromTable($this->table) as $ID => $value)
         $tabs[$value["itemtype"]] = $LANG["genericobject"][$value["name"]][1];

      return $tabs;
   }

   function displayField($canedit, $name, $value) {
      global $GENERICOBJECT_AVAILABLE_FIELDS, $GENERICOBJECT_BLACKLISTED_FIELDS;

      if (isset ($GENERICOBJECT_AVAILABLE_FIELDS[$name]) 
         && !in_array($name, $GENERICOBJECT_BLACKLISTED_FIELDS)) {

         $this->startColumn();
         echo $GENERICOBJECT_AVAILABLE_FIELDS[$name]['name'];
         $this->endColumn();
         $this->startColumn();
         switch ($GENERICOBJECT_AVAILABLE_FIELDS[$name]['input_type']) {
            case 'multitext' :
               if ($canedit)
                  echo "<textarea cols='40' rows='4' name='" . $name . "'>" . $value . 
                     "</textarea>";
               else
                  echo $value;
               break;
            case 'text' :
               if ($canedit) {
                  $table = plugin_genericobject_getObjectTableNameByName($name);
                  autocompletionTextField($this, $name);
               } else
                  echo $value;
               break;
            case 'date' :
               if ($canedit)
                  showDateFormItem($name, $value, false, true);
               else
                  echo convDate($value);
               break;
            case 'dropdown_global' :
               Dropdown::showGlobalSwitch($_SERVER['PHP_SELF'],'',$this->fields['id'],
                                          $this->fields['is_global'],2);
               
               break;
            case 'dropdown' :
               if (plugin_genericobject_isDropdownTypeSpecific($name)) {
                  $type = strtolower(str_replace("PluginGenericobject", "", $this->type));
                  $device_name = plugin_genericobject_getNameByID($type);
                  $table = plugin_genericobject_getDropdownTableName($device_name, $name);
               } else
                  $table = $GENERICOBJECT_AVAILABLE_FIELDS[$name]['table'];

               if ($canedit) {
                  $entity_restrict = $this->fields["entities_id"];
                  switch ($table) {
                     default :
                        if (isset($device_name)) {
                           $object_name = "PluginGenericobject".ucfirst($device_name).ucfirst($name);
                        }
                        else $object_name = ucfirst($name);
                  
                        //dropdownValue($table, $name, $value, 1, $entity_restrict);
                        Dropdown::show($object_name, array(
                                 'value' => $value,
                                 'name' => $name,
                                 'entity' => $entity_restrict)
                                 );                      
                        break;
                     case 'glpi_users' :
                        //dropdownUsers($name,$value,'all',0,1,$entity_restrict);
                        User::dropdown(array('name'   => $name,
                           'value'  => $value,
                           'right'  => 'all',
                           'entity' => $entity_restrict));
                        break;   
                  }
                  
               } else
                  echo getDropdownName($table, $value);
               break;
            case 'dropdown_yesno' :
               if ($canedit)
                  //dropdownYesNo($name, $value);
                  Alert::dropdownYesNo(array("name" => $name, 
                                                "value" => $value));
               else
                  echo getYesNo($value);
               break;
         }
         $this->endColumn();
      }
   }

   /**
   * Add a new column
   **/
   function startColumn() {
      if ($this->cpt == 0) {
         echo "<tr class='tab_bg_1'>";
      }

      echo "<td>";
      $this->cpt++;
   }

   /**
   * End a column
   **/
   function endColumn() {
      echo "</td>";

      if ($this->cpt == 4) {
         echo "</tr>";
         $this->cpt = 0;
      }

   }

   /**
   * Close a column
   **/
   function closeColumn() {
      if ($this->cpt > 0) {
         while ($this->cpt < 4) {
            echo "<td></td>";
            $this->cpt++;
         }
         echo "</tr>";
      }
   }

   function prepareInputForAdd($input) {

      if (isset ($input["ID"]) && $input["ID"] > 0) {
         $input["_oldID"] = $input["ID"];
      }
      unset ($input['ID']);
      unset ($input['withtemplate']);

      return $input;
   }

   function post_addItem() {
      global $DB;
      // Manage add from template
      if (isset ($this->input["_oldID"])) {
         // ADD Infocoms
         $ic = new Infocom();
         if ($ic->getFromDBforDevice($this->type, $this->input["_oldID"])) {
            $ic->fields["items_id"] = $this->fields['id'];
            unset ($ic->fields["id"]);
            if (isset ($ic->fields["immo_number"])) {
               $ic->fields["immo_number"] = autoName($ic->fields["immo_number"], "immo_number", 1, 
                                                     'Infocom', $this->input['entities_id']);
            }
            if (empty ($ic->fields['use_date'])) {
               unset ($ic->fields['use_date']);
            }
            if (empty ($ic->fields['buy_date'])) {
               unset ($ic->fields['buy_date']);
            }
            $ic->addToDB();
         }

         // ADD Contract
         $query = "SELECT contracts_id 
                     FROM glpi_contracts_items 
                     WHERE items_id='" . $input["_oldID"] . "' AND itemtype='" . $this->type . "';";
         $result = $DB->query($query);
         if ($DB->numrows($result) > 0) {
            while ($data = $DB->fetch_array($result))
               addDeviceContract($data["contracts_id"], $this->type, $newID);
         }

         // ADD Documents
         $query = "SELECT documents_id 
                     FROM glpi_documents_items 
                     WHERE items_id='" . $input["_oldID"] . "' AND itemtype='" . $this->type . "';";
         $result = $DB->query($query);
         if ($DB->numrows($result) > 0) {
            while ($data = $DB->fetch_array($result))
               addDeviceDocument($data["documents_id"], $this->type, $newID);
         }
      }
   }

   function cleanDBonPurge() {
      global $DB, $CFG_GLPI;
      
      $ID = $this->fields['id'];

      //$job = new Job();
      $query = "SELECT * 
               FROM glpi_tickets 
               WHERE items_id = '".$this->fields['id']."'  AND itemtype='" . $this->type . "'";
      $result = $DB->query($query);

      if ($DB->numrows($result))
         while ($data = $DB->fetch_array($result)) {
            if ($CFG_GLPI["keep_tracking_on_delete"] == 1) {
               $query = "UPDATE glpi_tickets SET items_id = '0', itemtype='0' " .
                        "WHERE id='" . $data["id"] . "';";
               $DB->query($query);
            } /*else
               $job->delete(array (
                  "id" => $data["id"]
               ));*/
         }

      $query = "SELECT id 
               FROM `glpi_networkports` 
               WHERE items_id = '".$this->fields['id']."' AND itemtype = '" . $this->type . "'";
      $result = $DB->query($query);
      while ($data = $DB->fetch_array($result)) {
         $q = "DELETE FROM `glpi_networkports_networkports` " .
              "WHERE networkports_id_1 = '" . $data["id"] . "' " .
                  "OR   networkports_id_2 = '" . $data["id"] . "'";
         $DB->query($q);
      }


      $query2 = "DELETE FROM `glpi_networkports` " .
                "WHERE items_id = '$ID' AND itemtype = '" . $this->type . "'";
      $DB->query($query2);

      $query = "SELECT * FROM `glpi_computers_items` " .
               "WHERE itemtype='" . $this->type . "' AND items_id ='$ID'";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) > 0) {
            while ($data = $DB->fetch_array($result)) {
               // Disconnect without auto actions
               Disconnect($data["id"], 1, false);
            }
         }
      }

      $query = "SELECT ID FROM `glpi_reservationsitems` " .
               "WHERE itemtype='" . $this->type . "' AND items_id='$ID'";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) > 0) {
            $rr = new ReservationItem();
            $rr->delete(array (
               "id" => $DB->result($result, 0, "id")
            ));
         }
      }

      $query = "DELETE FROM `glpi_infocoms` " .
               "WHERE items_id = '$ID' AND itemtype='" . $this->type . "'";
      $DB->query($query);

      $query = "DELETE FROM `glpi_contracts_items` " .
               "WHERE items_id = '$ID' AND itemtype='" . $this->type . "'";
      $DB->query($query);

      $query = "DELETE FROM `glpi_documents_items` " .
               "WHERE (items_id = '$ID' AND itemtype='" . $this->type . "')";
      $DB->query($query);

   }
}
?>
