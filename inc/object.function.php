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
/**
 * Display object preview form
 * @param type the object type
 */
function plugin_genericobject_showPrevisualisationForm($type) {
   global $LANG;
   
   if (plugin_genericobject_haveTypeRight($type,'r'))
   {
      $name = plugin_genericobject_getNameByID($type);
      echo "<br><strong>" . $LANG['genericobject']['config'][8] . "</strong><br>";
   
      //$object = new $type();
      //$_SESSION["glpi_plugin_genericobject_itemtype"] = $type;
      $object = new PluginGenericobjectObject($type);
      //$object->setType($type, true);
      $object->showForm('', null, true);
   }
   else
      echo "<br><strong>" . $LANG['genericobject']['fields'][9] . "</strong><br>";
}

/**
 * Change object field's order
 * @param field the field to move up/down
 * @param itemtype object item type
 * @param action up/down
 */
function plugin_genericobject_changeFieldOrder($field,$itemtype,$action){
      global $DB;

      $sql ="SELECT id, rank " .
            "FROM glpi_plugin_genericobject_type_fields " .
            "WHERE itemtype='$itemtype' AND name='$field'";

      if ($result = $DB->query($sql)){
         if ($DB->numrows($result)==1){
            
            $current_rank=$DB->result($result,0,"rank");
            $ID = $DB->result($result,0,"ID");
            // Search rules to switch
            $sql2="";
            switch ($action){
               case "up":
                  $sql2 ="SELECT id, rank FROM `glpi_plugin_genericobject_type_fields` " .
                         "WHERE itemtype='$itemtype' " .
                         "   AND rank < '$current_rank' ORDER BY rank DESC LIMIT 1";
               break;
               case "down":
                  $sql2 ="SELECT id, rank FROM `glpi_plugin_genericobject_type_fields` " .
                         "WHERE itemtype='$itemtype' " .
                         "   AND rank > '$current_rank' ORDER BY rank ASC LIMIT 1";
               break;
               default :
                  return false;
               break;
            }
            
            if ($result2 = $DB->query($sql2)){
               if ($DB->numrows($result2)==1){
                  list($other_ID,$new_rank)=$DB->fetch_array($result2);
                  $query="UPDATE `glpi_plugin_genericobject_type_fields` " .
                         "SET rank='$new_rank' WHERE id ='$ID'";
                  $query2="UPDATE `glpi_plugin_genericobject_type_fields` " .
                          "SET rank='$current_rank' WHERE id ='$other_ID'";
                  return ($DB->query($query)&&$DB->query($query2));
               }
            }
         }
         return false;
      }
   }

/**
 * Reorder all fields for a type
 * @param itemtype the object type
 */
function plugin_genericobject_reorderFields($itemtype)
{
   global $DB;
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
   }  
}

function plugin_genericobject_showTemplateByDeviceType($target,$itemtype,$entity,$add=0)
{
   global $LANG,$DB,$GENERICOBJECT_LINK_TYPES;
   $name = plugin_genericobject_getNameByID($itemtype);
   $commonitem = new PluginGenericobjectObject($itemtype);
   //$commonitem->setType($itemtype,true);
   $title = plugin_genericobject_getObjectLabel($name);
   $query = "SELECT * FROM `".$commonitem->getTable()."` " .
            "WHERE is_template = '1' AND entities_id='" . 
               $_SESSION["glpiactive_entity"] . "' ORDER by tplname";
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

         $templname = $data["tplname"];
         if ($_SESSION["glpiview_ID"]||empty($data["tplname"])){
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

function plugin_genericobject_showDevice($target,$itemtype,$item_id) {
   global $DB,$CFG_GLPI, $LANG,$INFOFORM_PAGES,$LINK_ID_TABLE,$GENERICOBJECT_LINK_TYPES;
   
   $name = plugin_genericobject_getNameByID($itemtype);
   
   if (!haveRight($name,"r")) return false;
   //if (!haveTypeRight($name,"r")) return false;
   
   $rand=mt_rand();
   
   $commonitem = new PluginGenericobjectObject($itemtype);
   
   
   if ($commonitem->getFromDB($item_id)){
      $obj = $commonitem;
      
      $canedit=$obj->can($item_id,'w'); 

      $query = "SELECT DISTINCT itemtype 
            FROM `".plugin_genericobject_getLinkDeviceTableName($name)."` 
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
                        plugin_genericobject_getLinkDeviceTableName($name).".id AS IDD "
                    ." FROM `".plugin_genericobject_getLinkDeviceTableName($name)."`, `".
                        $LINK_ID_TABLE[$type]."`, `".$obj->table."`"
                    ." WHERE ".$LINK_ID_TABLE[$type].".id = ".
                  plugin_genericobject_getLinkDeviceTableName($name).".items_id 
               AND ".plugin_genericobject_getLinkDeviceTableName($name).".itemtype='$type' 
               AND ".plugin_genericobject_getLinkDeviceTableName($name).".source_id = '$item_id' ";
               $query.=getEntitiesRestrictRequest(" AND ",$LINK_ID_TABLE[$type],'','',
                        isset($CFG_GLPI["recursive_type"][$type])); 

               if (in_array($LINK_ID_TABLE[$type],$CFG_GLPI["template_tables"])){
                  $query.=" AND ".$LINK_ID_TABLE[$type].".is_template='0'";
            }
            $query.=" ORDER BY ".$obj->table.".entities_id, ".$LINK_ID_TABLE[$type].".$column";

            if ($result_linked=$DB->query($query))
               if ($DB->numrows($result_linked)){
                  $ci->setType($type);
                  initNavigateListItems($type,plugin_genericobject_getObjectLabel($name)." = ".
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
                     
                     echo "<td class='center' ".(isset($data['deleted'])
                                                   && $data['deleted']?"class='tab_bg_2_2'":"").">".
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
                          plugin_genericobject_getLinksByType($itemtype));     
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

?>
