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

   protected $objecttype;
   
   //Internal field counter
   private $cpt = 0;
   
   static function registerType() {
      global $DB, $LANG, $PLUGIN_HOOKS;
      $class  = get_called_class();
      $item   = new $class();
      $fields = $DB->list_fields(getTableForItemType($class));
      
      $options = array("document_types"         => $item->objecttype->canUseDocuments(),
                       "helpdesk_visible_types" => $item->objecttype->canUseTickets() 
                                                    && isset($fields['is_helpdesk_visible']),
                       "linkgroup_types"        => $item->objecttype->canUseTickets() 
                                                    && isset ($fields["groups_id"]),
                       "linkuser_types"         => $item->objecttype->canUseTickets() 
                                                   && isset ($fields["users_id"]),
                       "ticket_types"           => $item->objecttype->canUseTickets(),
                       "infocom_types"          => $item->objecttype->canUseInfocoms(),
                       "networkport_types"      => $item->objecttype->canUseNetworkPorts(),
                       "reservation_types"      => $item->objecttype->canBeReserved(),
                       "contract_types"         => $item->objecttype->canUseContract());
         Plugin::registerClass($class, $options);
         if (haveRight($class, "r")) {
           $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['title']
                                                      = call_user_func(array($class, 'getTypeName'));
           $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['page']
                                                      = getItemTypeSearchURL($class, false);
           $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['links']['search']
                                                      = getItemTypeSearchURL($class, false);
           $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['links']['add']
                                                      = getItemTypeFormURL($class, false);
           if ($item->objecttype->canUseTemplate()) {
              $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['links']['template']
                                                         = "/front/setup.templates.php?itemtype=$class&amp;add=0";
           }

           if (haveRight('config', 'w')) {
              $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['links']['config'] 
                                          = getItemTypeSearchURL('PluginGenericobjectType',false);
           }
      }
   }
   
   static function getTypeName() {
      $class = get_called_class();
      $item  = new $class();
      PluginGenericobjectType::includeLocales($item->objecttype->fields['name']);
      if(isset($LANG['genericobject'][$class][0])) {
         return $LANG['genericobject'][$class][0];
      } else {
         return $item->objecttype->fields['name'];
      }
   }
   
   public function __construct() {
      $this->table = getTableForItemType(get_called_class());
      if (get_called_class() && class_exists(get_called_class())) {
         $this->objecttype = new PluginGenericobjectType(get_called_class());
      }
      $this->dohistory = $this->canUseHistory();
   }
   
   function canCreate() {
      return haveRight(get_called_class(), 'w');
   }

   function canView() {
      return haveRight(get_called_class(), 'r');
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
/*
         $linked_types = PluginGenericobjectLink::getLinksByType($this->type);
         if (!empty ($linked_types)) {
            $ong[7] = $LANG['setup'][620];
         }
*/
         if ($this->canUseNotepad() && haveRight("notes", "r")) {
            $ong[10] = $LANG['title'][37];
         }

         if ($this->canBeReserved()) {
            $ong[11] = $LANG['Menu'][17];
         }

         if ($this->canUseHistory())
            $ong[12] = $LANG['title'][38];

      }
      return $ong;
   }

   function canUseInfocoms() {
      return ($this->objecttype->canUseInfocoms() 
               && (haveRight("contract", "r") || haveRight("infocom", "r")));
   }

   function canUseDocuments() {
      return ($this->objecttype->canUseDocuments() && haveRight("document", "r"));

   }

   function canUseTickets() {
      return ($this->objecttype->canUseTickets() && haveRight("show_all_ticket", "1"));
   }

   function canUseNotepad() {
      return ($this->objecttype->canUseNotepad() && haveRight("notes", "r"));
   }

   function canBeReserved() {
      return ($this->objecttype->canBeReserved() && haveRight("reservation_central", "r"));
   }

   function canUseHistory() {
      return ($this->objecttype->canUseHistory());
   }

   function canUsePluginDataInjection() {
      return ($this->objecttype->canUsePluginDataInjection());
   }

   function canUsePluginPDF() {
      return ($this->objecttype->canUsePluginPDF());
   }

   function canUsePluginOrder() {
      return ($this->objecttype->canUsePluginOrder());
   }

   function canUseNetworkPorts() {
      return ($this->objecttype->canUseNetworkPorts());
   }

   function canUseDirectConnections() {
      return ($this->objecttype->canUseDirectConnections());
   }

   function title() {
      $name = call_user_func(array(get_called_class(), 'getTypeName'));
      displayTitle('', $name, $name);
   }

   function showForm($ID, $options=array(), $previsualisation = false) {
      global $LANG, $DB;

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
      foreach ($DB->list_fields(getTableForItemType(get_called_class())) as $field => $description) {
         $value = $this->fields[$field];
         $this->displayField($canedit, $field, $value, $description);
      }
      $this->closeColumn();

      if (!$previsualisation) {
         $this->showFormButtons($options);
         echo "<div id='tabcontent'></div>";
         echo "<script type='text/javascript'>loadDefaultTab();</script>";
      } else {
         echo "</table></div></form>";
      }
   }

   function displayField($canedit, $name, $value, $description = array()) {
      global $GO_FIELDS, $GO_BLACKLIST_FIELDS;

      if (isset ($GO_FIELDS[$name]) 
         && !in_array($name, $GO_BLACKLIST_FIELDS)) {

         $this->startColumn();
         echo $GO_FIELDS[$name]['name'];
         $this->endColumn();
         $this->startColumn();
         
         switch ($description['Type']) {
            case "int(11)":
               $fk_table = getTableNameForForeignKeyField($name);
               if ($fk_table != '') {
                  $itemtype = getItemTypeForTable($fk_table); 
                  $dropdown = new $itemtype();
                  $parameters = array('name' => $name, 'value' => $value, 'comments' => true);
                  if ($dropdown->isEntityAssign()) {
                     $parameters["entity"] = $this->fields['entities_id'];
                  }
                  if ($dropdown->maybeRecursive()) {
                     $parameters['entity_sons'] = true;
                  }
                  Dropdown::show($itemtype, $parameters);
               }
               break;
            case "tinyint(1)":
               Dropdown::showYesNo($name, $value);
               break;
            case "varchar(255)":
                  autocompletionTextField($this, $name);
               break;
            case "longtext":
            case "text":
               echo "<textarea cols='40' rows='4' name='" . $name . "'>" . $value . 
                     "</textarea>";
               break;
            case "date":
                  showDateFormItem($name, $value, false, true);
                  break;
            case "datetime":
                  showDateTimeFormItem($name, $value, false, true);
                  break;
            default:
            case "float":
                  echo "<input type='text' name='$name' value='$value'>";
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

      if (isset ($input["id"]) && $input["id"] > 0) {
         $input["_oldID"] = $input["id"];
      }
      unset ($input['id']);
      unset ($input['withtemplate']);

      return $input;
   }

   function post_addItem() {
      global $DB;
      // Manage add from template
      if (isset ($this->input["_oldID"])) {
         // ADD Infocoms
         $ic = new Infocom();
         if ($item->canUseInfocoms() 
            && $ic->getFromDBforDevice($this->type, $this->input["_oldID"])) {
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

         foreach (array('Document_Item' => 'documents_id', 
                        'Contract_Item' => 'contracts_id') as $type => $fk) {
            $item = new $type();
            foreach ($item->find("items_id='" . $this->input["_oldID"] . "' 
                                 AND itemtype='" . $this->type . "'") as $tmpid => $data) {
               $tmp = array();
               $tmp['items_id'] = $this->input["_oldID"];
               $tmp['itemtype'] = $type;
               $tmp[$fk]        = $data[$fk];
               $item->add($tmp);
            }
         }
         
         if ($item->canUseNetworkPorts()) {
            // ADD Ports
            $query = "SELECT `id`
                      FROM `glpi_networkports`
                      WHERE `items_id` = '".$this->input["_oldID"]."'
                            AND `itemtype` = '".get_called_class()."';";
            $result=$DB->query($query);
            if ($DB->numrows($result)>0) {
               while ($data=$DB->fetch_array($result)) {
                  $np  = new NetworkPort();
                  $npv = new NetworkPort_Vlan();
                  $np->getFromDB($data["id"]);
                  unset($np->fields["id"]);
                  unset($np->fields["ip"]);
                  unset($np->fields["mac"]);
                  unset($np->fields["netpoints_id"]);
                  $np->fields["items_id"] = $this->fields['id'];
                  $portid = $np->addToDB();
                  foreach ($DB->request('glpi_networkports_vlans',
                                        array('networkports_id' => $data["id"])) as $vlan) {
                     $npv->assignVlan($portid, $vlan['vlans_id']);
                  }
               }
            }
         }

      }
   }

   function cleanDBonPurge() {
      $parameters = array('items_id' => $this->getID(), 'itemtype' => get_called_class());
      
      $types = array('Ticket', 'NetworkPort', 'NetworkPort_NetworkPort', 'Computer_Item', 
                     'Reservation_Item', 'Document_Item', 'Infocom', 'Contract_Item');
      foreach ($types as $type) {
         $item = new $type();
         $item->deleteByCriteria($parameters);
      }
   }
   
   /**
    * Display object preview form
    * @param type the object type
    */
   public static function showPrevisualisationForm($itemtype) {
      global $LANG;

      if (haveRight($itemtype,'r')) {
         $name = PluginGenericobjectType::getNameByID($itemtype);
         echo "<br><strong>" . $LANG['genericobject']['config'][8] . "</strong><br>";
         $object = new PluginGenericobjectObject($itemtype);
         $object->showForm('', null, true);
      } else {
         echo "<br><strong>" . $LANG['genericobject']['fields'][9] . "</strong><br>";
      }
   }
   
   function getSearchOptions() {
      global $DB, $GO_FIELDS;
      
      $index = 0;
      $options = array();
      $table = getTableForItemType(get_called_class());
      foreach ($DB->list_fields($table) as $field => $values) {
         if ($field == 'is_deleted') {
            continue;
         }
         //Table definition
         $tmp = getTableNameForForeignKeyField($field);
         if ($tmp != '') {
            $itemtype = getItemTypeForTable($tmp);
            $item     = new $itemtype();
            //Set table
            $options[$index]['table'] = $tmp;
            
            //Set field
            if ($item instanceof CommonTreeDropdown) {
               $options[$index]['field'] = 'completename';
            } else {
               $options[$index]['field'] = 'name';
            }
         } else {
            $options[$index]['table'] = $table;
            $options[$index]['field'] = $field;
         }

         $options[$index]['name']  = $GO_FIELDS[$field]['name'];
         
         //Massive action or not
         if (isset($GO_FIELDS[$field]['massiveaction'])) {
            $options[$index]['massiveaction'] 
               = $GO_FIELDS[$field]['massiveaction'];
         }
         
         //Field type
         switch ($values['Type']) {
            default:
            case "varchar(255)":
               if ($field == 'name') {
                  $options[$index]['datatype']      = 'itemlink';
                  $options[$index]['itemlink_type'] = get_called_class();
                  $options[$index]['massiveaction'] = false;
               } else {
                  $options[$index]['datatype'] = 'string';
               }
               break;
            case "tinyint(1)":
               $options[$index]['datatype'] = 'bool';
               break;
            case "text":
            case "longtext":
               $options[$index]['datatype'] = 'text';
               break;
            case "int(11)":
               $options[$index]['datatype'] = 'text';
               break;
            case "float":
                $options[$index]['datatype'] = 'decimal';
               break;
            case "date":
               $options[$index]['datatype'] = 'date';
               break;
            case "datetime":
               $options[$index]['datatype'] = 'datetime';
               break;
         }
         $index++;
      }
      return $options;
   }
   
   /**
    * Reorder all fields for a type
    * @param itemtype the object type
    */
   public static function reorderFields($itemtype) {
      global $DB;
      logDebug($DB->list_fields(getTableForItemType($itemtype)));
      /*
      $query = "SELECT id FROM `glpi_plugin_genericobject_type_fields` " .
               "WHERE itemtype='$itemtype' ORDER BY rank ASC";
      $result = $DB->query($query);
      $i = 0;
      while ($datas = $DB->fetch_array($result))
      {
         $query = "UPDATE `glpi_plugin_genericobject_type_fields` SET rank=$i " .
                  "WHERE itemtype='$itemtype' AND id=".$datas["id"];
         $DB->query($query);
         $i++; 
      }*/
   }
   
   static function getLabel($name) {
      global $LANG;
      if (isset ($LANG['genericobject'][$name][1])) {
         return $LANG['genericobject'][$name][1];
      } else {
         return $name;
      }
   }

}