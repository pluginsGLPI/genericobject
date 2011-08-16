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

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
header_nocache();

//useplugin('genericobject', true);
if (!isset ($_POST["itemtype"])) {
   $type = "";
}
else {
   $type = $_POST["itemtype"];
}

if (!isset ($_POST["id"])) {
   exit ();
}
if (!isset ($_POST["sort"])) {
   $_POST["sort"] = "";
}
if (!isset ($_POST["order"])) {
   $_POST["order"] = "";
}
if (!isset ($_POST["withtemplate"])) {
   $_POST["withtemplate"] = "";
}

if (empty ($_POST["id"])) {
   switch ($_POST['glpi_tab']) {
      default :
         break;
   }
} else {
   
   $commonitem = new PluginGenericobjectObject;
   $commonitem->getFromDB($_POST["id"]);
   
   //echo $commonitem->getType();
         
   switch ($_POST['glpi_tab']) {
      case -1 :
         /*if ($commonitem->canUseDirectConnections()) {
            //showConnect($_POST['target'], $_POST["id"], $type);
            //Computer_Item::showForItem($commonitem);
         }*/
         
         if ($commonitem->canUseNetworkPorts()) {
            /*showPortsAdd($_POST["id"], $type);
            showPorts($_POST["id"], $type, $_POST["withtemplate"]);*/
            NetworkPort::showForItem('PluginGenericobjectObject', $_POST["id"]);
         }

         if ($commonitem->canUseInfocoms()) {
            //showInfocomForm($CFG_GLPI["root_doc"] . "/front/infocom.form.php", $type, $_POST["id"], 1, $_POST["withtemplate"]);
            Infocom::showForItem($commonitem);
            //showContractAssociated($type, $_POST["id"], $_POST["withtemplate"]);
            Contract::showAssociated($commonitem);
         }
         if ($commonitem->canUseDocuments()) {
            //showDocumentAssociated($type, $_POST["id"], $_POST["withtemplate"]);
            Document::showAssociated($commonitem);
         }
         if ($commonitem->canUseTickets()) {
            //showJobListForItem($type, $_POST["id"]);
            Ticket::showListForItem($type, $_POST["id"]);
         }
         if ($commonitem->canUseNotes()) {
            //showNotesForm($_POST['target'], $type, $_POST["id"]);
            showNotesForm($_POST['target'], 'PluginGenericobjectObject', $_POST["id"]);
         }
         if ($commonitem->canUseLoans()) {
            //showDeviceReservations($_POST['target'], $type, $_POST["id"]);
            Reservation::showForItem('PluginGenericobjectObject', $_POST["id"]);
         }
         if ($commonitem->canUseHistory()) {
            //showHistory($type, $_POST["id"]);
            Log::showForItem($commonitem);
         }
         
         PluginGenericobjectObject::showDevice($_POST['target'], $type, $_POST["id"]);
         //if (!displayPluginAction($type, $_POST["id"], $_POST['glpi_tab'])) {
         if (!Plugin::displayAction($commonitem, $_POST['glpi_tab'])) {
         }
         break;
      case 3 :
         /*if ($commonitem->canUseDirectConnections()) {
            //showConnect($_POST['target'], $_POST["id"], $type);
            Computer_Item::showForItem($commonitem);
         }*/

         if ($commonitem->canUseNetworkPorts()) {
            /*showPortsAdd($_POST["id"], $type);
            showPorts($_POST["id"], $type, $_POST["withtemplate"]);*/
            NetworkPort::showForItem('PluginGenericobjectObject', $_POST["id"]);
         }
         break;
      case 4 :
         //showInfocomForm($CFG_GLPI["root_doc"] . "/front/infocom.form.php", $type, $_POST["id"], 1, $_POST["withtemplate"]);
         Infocom::showForItem($commonitem);
         //showContractAssociated($type, $_POST["id"], $_POST["withtemplate"]);
         Contract::showAssociated($commonitem);
         break;
      case 5 :
         //showDocumentAssociated($type, $_POST["id"], $_POST["withtemplate"]);
         Document::showAssociated($commonitem);
         break;
      case 6 :
         //showJobListForItem($type, $_POST["id"]);
         Ticket::showListForItem($type, $_POST["id"]);
         break;
      case 7 :
         PluginGenericobjectObject::showDevice($_POST['target'], $type, $_POST["id"]);
         break;
      case 10 :
         //showNotesForm($_POST['target'], $type, $_POST["id"]);
         showNotesForm($_POST['target'], 'PluginGenericobjectObject', $_POST["id"]);
         break;
      case 11 :
         //showDeviceReservations($_POST['target'], $type, $_POST["id"]);
         Reservation::showForItem('PluginGenericobjectObject', $_POST["id"]);
         break;
      case 12 :
         //showHistory($type, $_POST["id"]);
         Log::showForItem($commonitem);
         break;
      default :
         if (!Plugin::displayAction($commonitem, $_POST['glpi_tab'])) {
         }
         break;
   }
}
?>
