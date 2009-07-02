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
function plugin_genericobject_showPrevisualisationForm($type) {
	global $LANG;
	$name = plugin_genericobject_getNameByID($type);
	echo "<br>" . $LANG['genericobject']['config'][8] . "<br>";

	$object = new CommonItem;
	$object->setType($type, true);
	$object->obj->showForm('', '', '', true);
}

function plugin_genericobject_changeFieldOrder($field,$type,$action){
		global $DB;

		$sql ="SELECT ID, rank FROM glpi_plugin_genericobject_type_fields WHERE device_type='$type' AND name='$field'";

		if ($result = $DB->query($sql)){
			if ($DB->numrows($result)==1){
				
				$current_rank=$DB->result($result,0,"rank");
				$ID = $DB->result($result,0,"ID");
				// Search rules to switch
				$sql2="";
				switch ($action){
					case "up":
						$sql2 ="SELECT ID, rank FROM `glpi_plugin_genericobject_type_fields` WHERE device_type='$type' AND rank < '$current_rank' ORDER BY rank DESC LIMIT 1";
					break;
					case "down":
						$sql2 ="SELECT ID, rank FROM `glpi_plugin_genericobject_type_fields` WHERE device_type='$type' AND rank > '$current_rank' ORDER BY rank ASC LIMIT 1";
					break;
					default :
						return false;
					break;
				}
				
				if ($result2 = $DB->query($sql2)){
					if ($DB->numrows($result2)==1){
						list($other_ID,$new_rank)=$DB->fetch_array($result2);
						$query="UPDATE `glpi_plugin_genericobject_type_fields` SET rank='$new_rank' WHERE ID ='$ID'";
						$query2="UPDATE `glpi_plugin_genericobject_type_fields` SET rank='$current_rank' WHERE ID ='$other_ID'";
						return ($DB->query($query)&&$DB->query($query2));
					}
				}
			}
			return false;
		}
	}

function plugin_genericobject_reorderFields($device_type)
{
	global $DB;
	$query = "SELECT ID FROM `glpi_plugin_genericobject_type_fields` WHERE device_type='$device_type' ORDER BY rank ASC";
	$result = $DB->query($query);
	$i = 0;
	while ($datas = $DB->fetch_array($result))
	{
		$query = "UPDATE `glpi_plugin_genericobject_type_fields` SET rank=$i WHERE device_type='$device_type' AND ID=".$datas["ID"];
		$DB->query($query);
		$i++;	
	}	
}

function plugin_genericobject_showTemplateByDeviceType($target,$device_type,$entity,$add=0)
{
	global $LANG,$DB,$GENERICOBJECT_LINK_TYPES;
	$name = plugin_genericobject_getNameByID($device_type);
	$commonitem = new CommonItem;
	$commonitem->setType($device_type,true);
	$title = plugin_genericobject_getObjectName($name);
	$query = "SELECT * FROM `".$commonitem->obj->table."` WHERE is_template = '1' AND FK_entities='" . $_SESSION["glpiactive_entity"] . "' ORDER by tplname";
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
			echo "<a href=\"$target?device_type=$device_type&ID=-1&amp;withtemplate=2\">&nbsp;&nbsp;&nbsp;" . $LANG['common'][31] . "&nbsp;&nbsp;&nbsp;</a></td>";
			echo "</tr>";
		}
	
		while ($data = $DB->fetch_array($result)) {

			$templname = $data["tplname"];
			if ($_SESSION["glpiview_ID"]||empty($data["tplname"])){
            			$templname.= "(".$data["ID"].")";
			}
			echo "<tr>";
			echo "<td align='center' class='tab_bg_1'>";
			
			if (haveTypeRight($device_type, "w") && !$add) {
				echo "<a href=\"$target?device_type=$device_type&ID=" . $data["ID"] . "&amp;withtemplate=1\">&nbsp;&nbsp;&nbsp;$templname&nbsp;&nbsp;&nbsp;</a></td>";

				echo "<td align='center' class='tab_bg_2'>";
				echo "<strong><a href=\"$target?device_type=$device_type&ID=" . $data["ID"] . "&amp;purge=purge&amp;withtemplate=1\">" . $LANG['buttons'][6] . "</a></strong>";
				echo "</td>";
			} else {
				echo "<a href=\"$target?device_type=$device_type&ID=" . $data["ID"] . "&amp;withtemplate=2\">&nbsp;&nbsp;&nbsp;$templname&nbsp;&nbsp;&nbsp;</a></td>";
			}

			echo "</tr>";

		}

		if (haveTypeRight($device_type, "w") &&!$add) {
			echo "<tr>";
			echo "<td colspan='2' align='center' class='tab_bg_2'>";
			echo "<strong><a href=\"$target?device_type=$device_type&withtemplate=1\">" . $LANG['common'][9] . "</a></strong>";
			echo "</td>";
			echo "</tr>";
		}

		echo "</table></div>";
	}
	
}

function plugin_genericobject_showDevice($target,$device_type,$device_id) {
	global $DB,$CFG_GLPI, $LANG,$INFOFORM_PAGES,$LINK_ID_TABLE,$GENERICOBJECT_LINK_TYPES;
	
	$name = plugin_genericobject_getNameByID($device_type);
	
	if (!haveTypeRight($name,"r"))	return false;
	
	$rand=mt_rand();
	
	$commonitem = new CommonItem;
	if ($commonitem->getFromDB($device_type,$device_id)){
		$obj = $commonitem->obj;
		
		$canedit=$obj->can($device_id,'w'); 

		$query = "SELECT DISTINCT device_type 
				FROM `".plugin_genericobject_getLinkDeviceTableName($name)."` 
				WHERE source_id = '$device_id' 
				ORDER BY device_type";
		
		$result = $DB->query($query);
		$number = $DB->numrows($result);

		$i = 0;
		if (isMultiEntitiesMode()) {
			$colsup=1;
		}else {
			$colsup=0;
		}
		echo "<form method='post' name='link_type_form$rand' id='link_type_form$rand'  action=\"$target\">";
	
		echo "<div align='center'><table class='tab_cadrehov'>";
		echo "<tr><th colspan='".($canedit?(5+$colsup):(4+$colsup))."'>".$LANG['genericobject']['links'][2].":</th></tr><tr>";
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
			$type=$DB->result($result, $i, "device_type");
			if (haveTypeRight($type,"r")){
				$column="name";
				if ($type==TRACKING_TYPE) $column="ID";
				if ($type==KNOWBASE_TYPE) $column="question";

				$query = "SELECT ".$LINK_ID_TABLE[$type].".*, ".plugin_genericobject_getLinkDeviceTableName($name).".ID AS IDD "
					." FROM `".plugin_genericobject_getLinkDeviceTableName($name)."`, `".$LINK_ID_TABLE[$type]."`, `".$obj->table."`"
					." WHERE ".$LINK_ID_TABLE[$type].".ID = ".plugin_genericobject_getLinkDeviceTableName($name).".FK_device 
					AND ".plugin_genericobject_getLinkDeviceTableName($name).".device_type='$type' 
					AND ".plugin_genericobject_getLinkDeviceTableName($name).".source_id = '$device_id' ";
					$query.=getEntitiesRestrictRequest(" AND ",$LINK_ID_TABLE[$type],'','',isset($CFG_GLPI["recursive_type"][$type])); 

					if (in_array($LINK_ID_TABLE[$type],$CFG_GLPI["template_tables"])){
						$query.=" AND ".$LINK_ID_TABLE[$type].".is_template='0'";
				}
				$query.=" ORDER BY ".$obj->table.".FK_entities, ".$LINK_ID_TABLE[$type].".$column";

				if ($result_linked=$DB->query($query))
					if ($DB->numrows($result_linked)){
						$ci->setType($type);
						initNavigateListItems($type,plugin_genericobject_getObjectName($name)." = ".$obj->fields['name']);
						while ($data=$DB->fetch_assoc($result_linked)){
							addToNavigateListItems($type,$data["ID"]);
							$ID="";
							if ($type==TRACKING_TYPE) $data["name"]=$LANG['job'][38]." ".$data["ID"];
							if ($type==KNOWBASE_TYPE) $data["name"]=$data["question"];
							
							if($_SESSION["glpiview_ID"]||empty($data["name"])) $ID= " (".$data["ID"].")";
							$item_name= "<a href=\"".$CFG_GLPI["root_doc"]."/".$INFOFORM_PAGES[$type]."?ID=".$data["ID"]."&device_type=$type\">"
								.$data["name"]."$ID</a>";
	
							echo "<tr class='tab_bg_1'>";

							if ($canedit){
								echo "<td width='10'>";
								$sel="";
								if (isset($_GET["select"])&&$_GET["select"]=="all") $sel="checked";
								echo "<input type='checkbox' name='item[".$data["IDD"]."]' value='1' $sel>";
								echo "</td>";
							}
							echo "<td class='center'>".$ci->getType()."</td>";
							
							echo "<td class='center' ".(isset($data['deleted'])&&$data['deleted']?"class='tab_bg_2_2'":"").">".$item_name."</td>";

							if (isMultiEntitiesMode())
								echo "<td class='center'>".getDropdownName("glpi_entities",$data['FK_entities'])."</td>";
							
							echo "<td class='center'>".(isset($data["serial"])? "".$data["serial"]."" :"-")."</td>";
							echo "<td class='center'>".(isset($data["otherserial"])? "".$data["otherserial"]."" :"-")."</td>";
							
							echo "</tr>";
						}
					}
			}
			$i++;
		}
	
		if ($canedit)	{
			echo "<tr class='tab_bg_1'><td colspan='".(3+$colsup)."' class='center'>";
	
			echo "<input type='hidden' name='source_id' value='$device_id'>";
			dropdownAllItems("FK_device",0,0,($obj->fields['recursive']?-1:$obj->fields['FK_entities']),plugin_genericobject_getLinksByType($device_type));		
			echo "</td>";
			echo "<td colspan='2' class='center' class='tab_bg_2'>";
			echo "<input type='submit' name='add_type_link' value=\"".$LANG['buttons'][8]."\" class='submit'>";
			echo "</td></tr>";
			echo "</table></div>" ;
			
			echo "<div class='center'>";
			echo "<table width='80%' class='tab_glpi'>";
			echo "<tr><td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left.png\" alt=''></td><td class='center'><a onclick= \"if ( markCheckboxes('link_type_form$rand') ) return false;\" href='".$_SERVER['PHP_SELF']."?ID=$device_id&amp;select=all'>".$LANG['buttons'][18]."</a></td>";
			
			echo "<td>/</td><td class='center'><a onclick= \"if ( unMarkCheckboxes('link_type_form$rand') ) return false;\" href='".$_SERVER['PHP_SELF']."?ID=$device_id&amp;select=none'>".$LANG['buttons'][19]."</a>";
			echo "</td>";
			echo "<td align='left' width='80%'>";
			echo "<input type='submit' name='delete_type_link' value=\"".$LANG['buttons'][6]."\" class='submit'>";
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