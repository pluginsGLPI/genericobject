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
$NEEDED_ITEMS=array("reservation","link","computer","printer","networking","monitor","software","peripheral","phone","tracking","document","user","enterprise","contract","infocom","group");

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
header_nocache();

useplugin('genericobject',true);
$type = $_POST["type"];


if(!isset($_POST["ID"])) {
	exit();
}
if(!isset($_POST["sort"])) $_POST["sort"] = "";
if(!isset($_POST["order"])) $_POST["order"] = "";
if(!isset($_POST["withtemplate"])) $_POST["withtemplate"] = "";

	if (empty($_POST["ID"])){
		switch($_POST['glpi_tab']){
			default :
				break;
		}
	}else{
		$commonitem = new CommonItem;
		$commonitem->getFromDB($type,$_POST["ID"]);
		switch($_POST['glpi_tab']){
			case -1:
				if ($commonitem->obj->canUseInfocoms())
				{
					showInfocomForm($CFG_GLPI["root_doc"]."/front/infocom.form.php",$type,$_POST["ID"],1,$_POST["withtemplate"]);
					showContractAssociated($type,$_POST["ID"],$_POST["withtemplate"]);
				}
				if ($commonitem->obj->canUseDocuments())
					showDocumentAssociated($type,$_POST["ID"],$_POST["withtemplate"]);
				if ($commonitem->obj->canUseTickets())
					showJobListForItem($type,$_POST["ID"]);
				if ($commonitem->obj->canUseNotes())
					showNotesForm($_POST['target'],$type,$_POST["ID"]);
				if ($commonitem->obj->canUseLoans())
					showReservationForm($type,$_POST["ID"]);
				if ($commonitem->obj->canUseHistory())
					showHistory($type,$_POST["ID"]);
				plugin_genericobject_showDevice($_POST['target'],$type,$_POST["ID"]);
				if (!displayPluginAction($type,$_POST["ID"],$_POST['glpi_tab'])){
				}
				break;
			case 4 :
				showInfocomForm($CFG_GLPI["root_doc"]."/front/infocom.form.php",$type,$_POST["ID"],1,$_POST["withtemplate"]);
				showContractAssociated($type,$_POST["ID"],$_POST["withtemplate"]);
				break;
			case 5 :
				showDocumentAssociated($type,$_POST["ID"],$_POST["withtemplate"]);
				break;
			case 6:
				showJobListForItem($type,$_POST["ID"]);
				break;
			case 7 :
				plugin_genericobject_showDevice($_POST['target'],$type,$_POST["ID"]);
				break;
/*
			case 7 :
				showLinkOnDevice($type,$_POST["ID"]);
				break;
*/
			case 10 :
				showNotesForm($_POST['target'],$type,$_POST["ID"]);
				break;
			case 11 :
				showReservationForm($type,$_POST["ID"]);
				break;
			case 12 :
				showHistory($type,$_POST["ID"]);
				break;
			default :
				if (!displayPluginAction($type,$_POST["ID"],$_POST['glpi_tab'])){
				}
				break;
		}
	}
?>