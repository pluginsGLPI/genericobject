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
class PluginGenericObject extends CommonDBTM {

	//Object type configuration
	private $type_infos = array ();

	//Internal field counter
	private $cpt = 0;

	function __construct($device_type = 0) {
		if ($device_type)
			$this->setType($device_type);
		else
			$this->setType($_SESSION["plugin_genericobject_device_type"]);
	}

	function setType($device_type) {
		$this->type = $device_type;
		$this->table = plugin_genericobject_getTableNameByID($device_type);
		$this->type_infos = plugin_genericobject_getObjectTypeConfiguration($this->type);
		$this->entity_assign = $this->type_infos['use_entity'];
		$this->may_be_recursive = $this->type_infos['use_recursivity'];
		$this->dohistory = $this->type_infos['use_history'];
	}

	function defineTabs($ID, $withtemplate) {
		global $LANG;
		$ong = array ();

		$ong[1] = $LANG['title'][26];
		
		if ($ID > 0){
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
			if (!empty($linked_types))
			{
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

	function canUseInfocoms()
	{
		return ($this->type_infos["use_infocoms"] && (haveRight("contract", "r") || haveRight("infocom", "r")));
	}
	
	function canUseDocuments()
	{
		return ($this->type_infos["use_documents"] && haveRight("document", "r"));
		
	}
	
	function canUseTickets()
	{
		return ($this->type_infos["use_tickets"] && haveRight("show_all_ticket", "1"));
	}
	
	function canUseNotes()
	{
		return ($this->type_infos["use_notes"] && haveRight("notes", "r"));
	}
	
	function canUseLoans()
	{
		return ($this->type_infos["use_loans"] && haveRight("reservation_central", "r"));
	}
	
	function canUseHistory()
	{
		return ($this->type_infos["use_history"]);
	}

	function canUsePluginDataInjection()
	{
		return ($this->type_infos["use_plugin_data_injection"]);
	}

	function canUsePluginPDF()
	{
		return ($this->type_infos["use_plugin_pdf"]);
	}

	function canUsePluginOrder()
	{
		return ($this->type_infos["use_plugin_order"]);
	}
	
	function title($name)
	{
		displayTitle('', plugin_genericobject_getObjectName($name), plugin_genericobject_getObjectName($name));
	}
	function showForm($target, $ID, $withtemplate = '',$previsualisation=false) {
		global $LANG;

		if($previsualisation)
		{
			$canedit = true;
			$this->getEmpty();
		}
		else
		{
			if ($ID > 0){
				$this->check($ID,'r');
			} else {
				// Create item 
				$this->check(-1,'w');
				$use_cache=false;
				$this->getEmpty();
			} 

			$this->showTabs($ID, '', $_SESSION['glpi_tab']);
			$canedit = $this->can($ID,'w');	
		}

		echo "<form name='form' method='post' action=\"$target?device_type=".$this->type."\">";
		echo "<input type='hidden' name='device_type' value='" . $this->type . "'>";

		if ($this->type_infos["use_entity"])
			echo "<input type='hidden' name='FK_entities' value='" . $this->fields["FK_entities"] . "'>";

		if(!$previsualisation)
			echo "<div class='center' id='tabsbody'>";
		else
			echo "<div class='center'>";
			
		echo "<table class='tab_cadre_fixe' >";
		$this->showFormHeader($ID, $withtemplate,2);

		foreach(plugin_genericobject_getFieldsByType($this->type) as $field => $tmp)
		{
			$value = $this->fields[$field];
			$this->displayField($canedit,$field, $value);
		}
		$this->closeColumn();

		if(!$previsualisation)
			$this->displayActionButtons($ID, $withtemplate, $canedit);
			
		echo "</table></div></form>";
		if(!$previsualisation)
		{
			echo "<div id='tabcontent'></div>";
			echo "<script type='text/javascript'>loadDefaultTab();</script>";
		}
	}

	function displayActionButtons($ID, $withtemplate, $canedit)
	{
		global $LANG;
		if ($canedit)
		{
				echo "<tr>";
				echo "<td class='tab_bg_2' colspan='4' align='center'>";

				if (empty ($ID) || $ID < 0 || $withtemplate==2) {
					echo "<input type='submit' name='add' value=\"" . $LANG['buttons'][8] . "\" class='submit'>";
				} else {
					echo "<input type='hidden' name='ID' value=\"$ID\">\n";
					echo "<input type='submit' name='update' value=\"" . $LANG['buttons'][7] . "\" class='submit'>";

						if (!$this->fields["deleted"]) {
							echo "&nbsp<input type='submit' name='delete' value=\"" . $LANG['buttons'][6] . "\" class='submit'>";
						} else {
							if ($this->type_infos["use_deleted"]) {
								echo "&nbsp<input type='submit' name='restore' value=\"" . $LANG['buttons'][21] . "\" class='submit'>";
								echo "&nbsp<input type='submit' name='purge' value=\"" . $LANG['buttons'][22] . "\" class='submit'>";
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
			$tabs[$value["device_type"]] = $LANG["genericobject"][$value["name"]][1];

		return $tabs;
	}

	function displayField($canedit,$name, $value) {
		global $GENERICOBJECT_AVAILABLE_FIELDS,$GENERICOBJECT_BLACKLISTED_FIELDS;

		if (isset ($GENERICOBJECT_AVAILABLE_FIELDS[$name]) && !in_array($name,$GENERICOBJECT_BLACKLISTED_FIELDS)) {
			
			$this->startColumn();
			echo $GENERICOBJECT_AVAILABLE_FIELDS[$name]['name'];
			$this->endColumn();
			$this->startColumn();
			switch ($GENERICOBJECT_AVAILABLE_FIELDS[$name]['input_type']) {
				case 'multitext':
					if ($canedit)
						echo "<textarea cols='40' rows='4' name='".$name."'>" . $value . "</textarea>";
					else
						echo $value;	
					break;
				case 'text' :
					if ($canedit)
					{
						$table = plugin_genericobject_getObjectTableNameByName($name);
						autocompletionTextField($name, $table, $GENERICOBJECT_AVAILABLE_FIELDS[$name]['field'], $value);
					}
					else
						echo $value;	
					break;
				case 'date':
					if ($canedit)
						showDateFormItem($name,$value,false,true);
					else
						echo convDate($value);
					break;	
				case 'dropdown' :
					if (plugin_genericobject_isDropdownTypeSpecific($name))
					{
						$device_name = plugin_genericobject_getNameByID($this->type);
						$table = plugin_genericobject_getDropdownTableName($device_name,$name);
					}
						
					else	
						$table = $GENERICOBJECT_AVAILABLE_FIELDS[$name]['table'];

					if ($canedit)
					{
						//if (isset($GENERICOBJECT_AVAILABLE_FIELDS[$name]['entity']) && $GENERICOBJECT_AVAILABLE_FIELDS[$name]['entity'] == 'entity_restrict')
							$entity_restrict = $this->fields["FK_entities"];
						//else
						//	$entity_restrict = getEntitySons($this->fields["FK_entities"]);
								
						dropdownValue($table, $name, $value, 1, $entity_restrict);
					}
						
					else
						echo getDropdownName($table, $value);	
					break;
				case 'dropdown_yesno' :
					if ($canedit)
						dropdownYesNo($name, $value);
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
		if ($this->cpt == 0)
			echo "<tr class='tab_bg_1'>";
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

		if (isset($input["ID"])&&$input["ID"]>0){
			$input["_oldID"]=$input["ID"];
		}
		unset($input['ID']);
		unset($input['withtemplate']);

		return $input;
	}

	function post_addItem($newID,$input) {
		global $DB;
		// Manage add from template
		if (isset($input["_oldID"])){
			// ADD Infocoms
			$ic= new Infocom();
			if ($ic->getFromDBforDevice($this->type,$input["_oldID"])){
				$ic->fields["FK_device"]=$newID;
				unset ($ic->fields["ID"]);
				if (isset($ic->fields["num_immo"])) {
					$ic->fields["num_immo"] = autoName($ic->fields["num_immo"], "num_immo", 1, INFOCOM_TYPE,$input['FK_entities']);
				}
				if (empty($ic->fields['use_date'])){
					unset($ic->fields['use_date']);
				}
				if (empty($ic->fields['buy_date'])){
					unset($ic->fields['buy_date']);
				}
				$ic->addToDB();
			}

    		// ADD Contract
			$query="SELECT FK_contract 
				FROM glpi_contract_device 
				WHERE FK_device='".$input["_oldID"]."' AND device_type='".$this->type."';";
			$result=$DB->query($query);
			if ($DB->numrows($result)>0){
				while ($data=$DB->fetch_array($result))
					addDeviceContract($data["FK_contract"],$this->type,$newID);
			}

			// ADD Documents
			$query="SELECT FK_doc 
				FROM glpi_doc_device 
				WHERE FK_device='".$input["_oldID"]."' AND device_type='".$this->type."';";
			$result=$DB->query($query);
			if ($DB->numrows($result)>0){
				while ($data=$DB->fetch_array($result))
					addDeviceDocument($data["FK_doc"],$this->type,$newID);
			}
		}
	}
}
?>
