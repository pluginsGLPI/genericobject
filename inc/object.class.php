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
      $class = get_called_class();
      $item = new $class();
      $fields = $DB->list_fields(getTableForItemType($class));
      
      $options = array("document_types"         => $item->objecttype->canUseDocuments(),
                       "helpdesk_visible_types" => $item->objecttype->canUseTickets() 
                                                    && isset($fields['helpdesk_visible']),
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
      $item = new $class();
      PluginGenericobjectType::includeLocales($item->objecttype->fields['name']);
      if(isset($LANG['genericobject'][$class][0])) {
         return $LANG['genericobject'][$class][0];
      } else {
         return $item->objecttype->fields['name'];
      }
   }
   
   public function __construct() {
      $this->table = getTableForItemType(get_class($this));
      if (get_called_class() && class_exists(get_called_class())) {
         $this->objecttype = new PluginGenericobjectType(get_called_class());
      }
      $this->dohistory = $this->canUseHistory();
   }
   
   function canCreate() {
      return haveRight(get_class($this), 'w');
   }

   function canView() {
      return haveRight(get_class($this), 'r');
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
      global $GENERICOBJECT_AVAILABLE_FIELDS, $GENERICOBJECT_BLACKLISTED_FIELDS;

      if (isset ($GENERICOBJECT_AVAILABLE_FIELDS[$name]) 
         && !in_array($name, $GENERICOBJECT_BLACKLISTED_FIELDS)) {

         $this->startColumn();
         echo $GENERICOBJECT_AVAILABLE_FIELDS[$name]['name'];
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
            default:
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
      global $DB, $GENERICOBJECT_AVAILABLE_FIELDS;
      
      $index = 0;
      $options = array();
      $table = getTableForItemType(get_class($this));
      foreach ($DB->list_fields($table) as $field => $values) {
         $options[$index]['field'] = $field;
         if ($tmp = getTableNameForForeignKeyField($field) != '') {
            $options[$index]['table'] = $tmp;
         } else {
            $options[$index]['table'] = $table;
         }
         $options[$index]['name']  = $GENERICOBJECT_AVAILABLE_FIELDS[$field]['name'];
         $index++;
      }
      return $options;
   }
   /*
   public static function showDevice($target,$itemtype,$item_id) {
      global $DB,$CFG_GLPI, $LANG,$INFOFORM_PAGES,$LINK_ID_TABLE;
      
      $name = PluginGenericobjectType::getNameByID($itemtype);
      
      if (!haveRight($name,"r")) return false;
      //if (!haveTypeRight($name,"r")) return false;
      
      $rand=mt_rand();
      
      $commonitem = new PluginGenericobjectObject($itemtype);
      
      
      if ($commonitem->getFromDB($item_id)){
         $obj = $commonitem;
         
         $canedit=$obj->can($item_id,'w'); 

         $query = "SELECT DISTINCT itemtype 
               FROM `".PluginGenericobjectType::getLinkDeviceTableName($name)."` 
               WHERE source_id = '$item_id' 
               ORDER BY itemtype";
         
         $result = $DB->query($query);
         $number = $DB->numrows($result);

         $i = 0;
         if (isMultiEntitiesMode()) {
            $colsup=1;
         }else {
            $colsup=0;
         }
         echo "<form method='post' name='link_type_form$rand' " .
              " id='link_type_form$rand'  action=\"$target\">";
      
         echo "<div align='center'><table class='tab_cadrehov'>";
         echo "<tr><th colspan='".($canedit?(5+$colsup):(4+$colsup))."'>".
               $LANG['genericobject']['links'][2].":</th></tr><tr>";
         if ($canedit) {
            echo "<th>&nbsp;</th>";
         }
         echo "<th>".$LANG['common'][17]."</th>";
         echo "<th>".$LANG['common'][16]."</th>";
         if (isMultiEntitiesMode())
            echo "<th>".$LANG['entity'][0]."</th>";
         echo "<th>".$LANG['common'][19]."</th>";
         echo "<th>".$LANG['common'][20]."</th>";
         echo "</tr>";
      
         $ci=new CommonItem();
         while ($i < $number) {
            $type=$DB->result($result, $i, "itemtype");
            //if (haveTypeRight($type,"r")){
            if (haveRight($type,"r")){
               $column="name";
               if ($type==TRACKING_TYPE) $column="ID";
               if ($type==KNOWBASE_TYPE) $column="question";

               $query = "SELECT ".$LINK_ID_TABLE[$type].".*, ".
                           PluginGenericobjectType::getLinkDeviceTableName($name).".id AS IDD "
                       ." FROM `".PluginGenericobjectType::getLinkDeviceTableName($name)."`, `".
                           $LINK_ID_TABLE[$type]."`, `".$obj->table."`"
                       ." WHERE ".$LINK_ID_TABLE[$type].".id = ".
                     PluginGenericobjectType::getLinkDeviceTableName($name).".items_id 
                  AND ".PluginGenericobjectType::getLinkDeviceTableName($name).".itemtype='$type' 
                  AND ".PluginGenericobjectType::getLinkDeviceTableName($name).".source_id = '$item_id' ";
                  $query.=getEntitiesRestrictRequest(" AND ",$LINK_ID_TABLE[$type],'','',
                           isset($CFG_GLPI["recursive_type"][$type])); 

                  if (in_array($LINK_ID_TABLE[$type],$CFG_GLPI["template_tables"])){
                     $query.=" AND ".$LINK_ID_TABLE[$type].".is_template='0'";
               }
               $query.=" ORDER BY ".$obj->table.".entities_id, ".$LINK_ID_TABLE[$type].".$column";

               if ($result_linked=$DB->query($query))
                  if ($DB->numrows($result_linked)){
                     $ci->setType($type);
                     initNavigateListItems($type,PluginGenericobjectObject::getLabel($name)." = ".
                        $obj->fields['name']);
                     while ($data=$DB->fetch_assoc($result_linked)){
                        addToNavigateListItems($type,$data["id"]);
                        $ID="";
                        if ($type==TRACKING_TYPE) $data["name"]=$LANG['job'][38]." ".$data["id"];
                        if ($type==KNOWBASE_TYPE) $data["name"]=$data["question"];
                        
                        if($_SESSION["glpiview_ID"]||empty($data["name"])) $ID= " (".$data["id"].")";
                        $item_name= "<a href=\"".$CFG_GLPI["root_doc"]."/".$INFOFORM_PAGES[$type].
                           "?id=".$data["id"]."&itemtype=$type\">".$data["name"]."$ID</a>";
      
                        echo "<tr class='tab_bg_1'>";

                        if ($canedit){
                           echo "<td width='10'>";
                           $sel="";
                           if (isset($_GET["select"])&&$_GET["select"]=="all") $sel="checked";
                           echo "<input type='checkbox' name='item[".$data["IDD"]."]' value='1' $sel>";
                           echo "</td>";
                        }
                        echo "<td class='center'>".$ci->getType()."</td>";
                        
                        echo "<td class='center' ".(isset($data['is_deleted'])
                                                      && $data['is_deleted']?"class='tab_bg_2_2'":"").">".
                                                      $item_name."</td>";

                        if (isMultiEntitiesMode())
                           echo "<td class='center'>".getDropdownName("glpi_entities",
                                                                      $data['entities_id'])."</td>";
                        
                        echo "<td class='center'>".(isset($data["serial"])? "".$data["serial"]."" :"-").
                           "</td>";
                        echo "<td class='center'>".(isset($data["otherserial"])? "".
                                                      $data["otherserial"]."" :"-")."</td>";
                        
                        echo "</tr>";
                     }
                  }
            }
            $i++;
         }
      
         if ($canedit)  {
            echo "<tr class='tab_bg_1'><td colspan='".(3+$colsup)."' class='center'>";
      
            echo "<input type='hidden' name='source_id' value='$itemtype'>";
            dropdownAllItems("items_id",0,0,($obj->fields['recursive']?-1:$obj->fields['entities_id']),
                             PluginGenericobjectLink::getLinksByType($itemtype));     
            echo "</td>";
            echo "<td colspan='2' class='center' class='tab_bg_2'>";
            echo "<input type='submit' name='add_type_link' value=\"".$LANG['buttons'][8]."\" class='submit'>";
            echo "</td></tr>";
            echo "</table></div>" ;
            
            echo "<div class='center'>";
            echo "<table width='80%' class='tab_glpi'>";
            echo "<tr><td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left.png\" alt=''></td>"; 
            echo "<td class='center'>"; 
            echo "<a onclick= \"if ( markCheckboxes('link_type_form$rand') ) return false;\" href='".
               $_SERVER['PHP_SELF']."?id=$item_id&amp;select=all'>".$LANG['buttons'][18]."</a></td>";
            
            echo "<td>/</td><td class='center'>"; 
            echo "<a onclick= \"if ( unMarkCheckboxes('link_type_form$rand') ) return false;\" href='".
               $_SERVER['PHP_SELF']."?id=$item_id&amp;select=none'>".$LANG['buttons'][19]."</a>";
            echo "</td>";
            echo "<td align='left' width='80%'>";
            echo "<input type='submit' name='delete_type_link' value=\"".$LANG['buttons'][6].
                    "\" class='submit'>";
            echo "</td>";
            echo "</table>";
         
            echo "</div>";

         }else{
      
            echo "</table></div>";
         }
         echo "</form>";
      }
   }
   */
   
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
   
   
   /**
    * Change object field's order
    * @param field the field to move up/down
    * @param itemtype object item type
    * @param action up/down
    */
    /*
   public static function changeFieldOrder($field,$itemtype,$action){
         global $DB;

         $table = getTableForItemType('PluginGenericobjectField');
         $sql ="SELECT `id`, `rank` " .
               "FROM `$table` " .
               "WHERE `itemtype`='$itemtype' AND `name`='$field'";

         if ($result = $DB->query($sql)){
            if ($DB->numrows($result)==1){
               
               $current_rank=$DB->result($result,0,"rank");
               $id = $DB->result($result,0,"id");
               // Search rules to switch
               $sql2="";
               switch ($action){
                  case "up":
                     $sql2 ="SELECT id, rank FROM `$table` " .
                            "WHERE itemtype='$itemtype' " .
                            "   AND rank < '$current_rank' ORDER BY `rank` DESC LIMIT 1";
                  break;
                  case "down":
                     $sql2 ="SELECT id, rank FROM `$table` " .
                            "WHERE itemtype='$itemtype' " .
                            "   AND rank > '$current_rank' ORDER BY `rank` ASC LIMIT 1";
                  break;
                  default :
                     return false;
                  break;
               }
               
               $field = new PluginGenericobjectField();
               if ($result2 = $DB->query($sql2)){
                  if ($DB->numrows($result2)==1){
                     list($others_id,$new_rank)=$DB->fetch_array($result2);
                     $tmp = array();
                     $tmp['id'] = $id;
                     $tmp['rank'] = $new_rank;
                     $field->update($tmp);
                     
                     $tmp['id'] = $others_id;
                     $tmp['rank'] = $current_rank;
                     $field->update($tmp);
                     return true;
                  }
               }
            }
            return false;
         }
      }


   public static function showTemplateByDeviceType($target, $itemtype, $entity, 
                                                                        $add=0) {
      global $LANG,$DB;
      $name = PluginGenericobjectType::getNameByID($itemtype);
      $commonitem = new PluginGenericobjectObject($itemtype);
      //$commonitem->setType($itemtype,true);
      $title = PluginGenericobjectObject::getLabel($name);
      $query = "SELECT * FROM `".$commonitem->getTable()."` " .
               "WHERE `is_template` = '1' AND `entities_id`='" . 
                  $_SESSION["glpiactive_entity"] . "' ORDER BY `template_name`";
      if ($result = $DB->query($query)) {

         echo "<div class='center'><table class='tab_cadre' width='50%'>";
         if ($add) {
            echo "<tr><th>" . $LANG['common'][7] . " - $title:</th></tr>";
         } else {
            echo "<tr><th colspan='2'>" . $LANG['common'][14] . " - $title:</th></tr>";
         }

         if ($add) {

            echo "<tr>";
            echo "<td align='center' class='tab_bg_1'>";
            echo "<a href=\"$target?itemtype=$itemtype&id=-1&amp;withtemplate=2\">&nbsp;&nbsp;&nbsp;" . 
               $LANG['common'][31] . "&nbsp;&nbsp;&nbsp;</a></td>";
            echo "</tr>";
         }
      
         while ($data = $DB->fetch_array($result)) {

            $templname = $data["template_name"];
            if ($_SESSION["glpiview_ID"]||empty($data["template_name"])){
                        $templname.= "(".$data["id"].")";
            }
            echo "<tr>";
            echo "<td align='center' class='tab_bg_1'>";
            
            if (haveTypeRight($itemtype, "w") && !$add) {
               echo "<a href=\"$target?itemtype=$itemtype&id=" . $data["id"] . 
                  "&amp;withtemplate=1\">&nbsp;&nbsp;&nbsp;$templname&nbsp;&nbsp;&nbsp;</a></td>";

               echo "<td align='center' class='tab_bg_2'>";
               echo "<strong><a href=\"$target?itemtype=$itemtype&id=" . $data["id"] . 
                  "&amp;purge=purge&amp;withtemplate=1\">" . $LANG['buttons'][6] . "</a></strong>";
               echo "</td>";
            } else {
               echo "<a href=\"$target?itemtype=$itemtype&id=" . $data["id"] . 
                  "&amp;withtemplate=2\">&nbsp;&nbsp;&nbsp;$templname&nbsp;&nbsp;&nbsp;</a></td>";
            }

            echo "</tr>";

         }

         //if (haveTypeRight($itemtype, "w") &&!$add) {
         if (haveRight($itemtype, "w") &&!$add) {
            echo "<tr>";
            echo "<td colspan='2' align='center' class='tab_bg_2'>";
            echo "<strong><a href=\"$target?itemtype=$itemtype&withtemplate=1\">" . 
               $LANG['common'][9] . "</a></strong>";
            echo "</td>";
            echo "</tr>";
         }

         echo "</table></div>";
      }
      
   }

*/
   
   static function getLabel($name) {
      global $LANG;
      if (isset ($LANG['genericobject'][$name][1])) {
         return $LANG['genericobject'][$name][1];
      } else {
         return $name;
      }
   }

}