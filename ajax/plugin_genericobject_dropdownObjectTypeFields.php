<?php
/*
 * @version $Id: dropdownMassiveAction.php 8192 2009-04-18 13:27:45Z remi $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2009 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

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
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Julien Dombre
// Purpose of file:
// ----------------------------------------------------------------------


define('GLPI_ROOT','../../..');
include (GLPI_ROOT."/inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
header_nocache();

if (isset($_POST["action"])&&isset($_POST["itemtype"])&&!empty($_POST["itemtype"])){
	echo "<input type='hidden' name='itemtype' value='".$_POST["itemtype"]."'>";
	switch($_POST["action"]){
		case "move_field":
			echo "<select name='move_type'>";
			echo "<option value='after' selected>".$LANG['buttons'][47]."</option>";
			echo "<option value='before'>".$LANG['buttons'][46]."</option>";
			echo "</select>&nbsp;";
			dropdownRules($_POST['sub_type'],"ranking");
			echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
		break;
		case "delete":
			echo "<input type=\"submit\" name=\"delete_field\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
		break;
	}
}
?>
