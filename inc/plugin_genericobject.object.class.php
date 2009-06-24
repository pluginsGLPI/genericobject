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

	//Fields not to be used in form display
	private $blacklisted_display_fields = array (
		"object_type",
		"table",
		"deleted",
		"ID",
		"FK_entities",
		"recursive",
		"is_template"
	);

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
		if ($this->type_infos["use_infocoms"] && (haveRight("contract", "r") || haveRight("infocom", "r"))) {
			$ong[4] = $LANG['Menu'][26];
		}

		if ($this->type_infos["use_documents"] && haveRight("document", "r")) {
			$ong[5] = $LANG['Menu'][27];
		}

		if ($this->type_infos["use_tickets"] && haveRight("show_all_ticket", "1")) {
			$ong[6] = $LANG['title'][28];
		}

		if ($this->type_infos["use_links"] && haveRight("link", "r")) {
			$ong[7] = $LANG['title'][34];
		}

		if ($this->type_infos["use_notes"] && haveRight("notes", "r")) {
			$ong[10] = $LANG['title'][37];
		}

		if ($this->type_infos["use_loans"] && haveRight("reservation_central", "r")) {
			$ong[11] = $LANG['Menu'][17];
		}

		if ($this->type_infos["use_history"])
			$ong[12] = $LANG['title'][38];

		return $ong;

	}

	function showForm($target, $ID, $withtemplate = '') {
		global $LANG;

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

		echo "<form name='form' method='post' action=\"$target\">";

		if ($this->type_infos["use_entity"])
			echo "<input type='hidden' name='FK_entities' value='" . $this->fields["FK_entities"] . "'>";

		echo "<div class='center' id='tabsbody'>";
		echo "<table class='tab_cadre_fixe' >";
		$this->showFormHeader($ID, $withtemplate,2);

		foreach ($this->fields as $field => $value) {
			$this->displayField($canedit,$field, $value);
		}
		$this->closeColumn();

		$this->displayActionButtons($ID, $canedit);
		echo "</table></div>";
		echo "<div id='tabcontent'></div>";
		echo "<script type='text/javascript'>loadDefaultTab();</script>";
	}

	function displayActionButtons($ID, $canedit)
	{
		global $LANG;
		if ($canedit)
		{
				echo "<tr>";
				echo "<td class='tab_bg_2' colspan='4' align='center'>";

				if (empty ($ID) || $ID < 0) {
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
		global $GENERICOBJECT_AVAILABLE_FIELDS;

		if (isset ($GENERICOBJECT_AVAILABLE_FIELDS[$name]) && !in_array($name,$this->blacklisted_display_fields)) {
			
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
				case 'dropdown' :
					if ($canedit)
						dropdownValue($GENERICOBJECT_AVAILABLE_FIELDS[$name]['table'], $name, $value, 1);
					else
						echo getDropdownName($GENERICOBJECT_AVAILABLE_FIELDS[$name]['table'], $value);	
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

}
?>
