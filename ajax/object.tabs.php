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

header("Content-Type: text/html; charset=UTF-8");
header_nocache();

//useplugin('genericobject', true);
if (!isset ($_POST["itemtype"])) {
   $itemtype = "";
}
else {
   $itemtype = $_POST["itemtype"];
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
   
   $item = new $itemtype();
   $item->getFromDB($_POST["id"]);

   switch ($_POST['glpi_tab']) {
      case -1 :
         
         if ($item->canUseNetworkPorts()) {
            NetworkPort::showForItem($itemtype, $_POST["id"]);
         }

         if ($item->canUseInfocoms() || $item->canUseContract()) {
            Infocom::showForItem($item);
            Contract::showAssociated($item);
         }
         if ($item->canUseDocuments()) {
            Document::showAssociated($item);
         }
         if ($item->canUseTickets()) {
            Ticket::showListForItem($itemtype, $_POST["id"]);
         }
         if ($item->canUseNotepad()) {
            showNotesForm($_POST['target'], $itemtype, $_POST["id"]);
         }
         if ($item->canBeReserved()) {
            Reservation::showForItem($itemtype, $_POST["id"]);
         }
         if ($item->canUseHistory()) {
            Log::showForItem($item);
         }
         
         if (!Plugin::displayAction($item, $_POST['glpi_tab'])) {
         }
         break;
      case 3 :
         /*if ($item->canUseDirectConnections()) {
            //showConnect($_POST['target'], $_POST["id"], $itemtype);
            Computer_Item::showForItem($item);
         }*/

         if ($item->canUseNetworkPorts()) {
            NetworkPort::showForItem($itemtype, $_POST["id"]);
         }
         break;
      case 4 :
         if ($item->canUseInfocoms() || $item->canUseContract()) {
            Infocom::showForItem($item);
            Contract::showAssociated($item);
         }
         break;
      case 5 :
         if ($item->canUseDocuments()) {
            Document::showAssociated($item);
         }
         break;
      case 6 :
         if ($item->canUseTickets()) {
            Ticket::showListForItem($itemtype, $_POST["id"]);
         }
         break;
      case 7 :
         //PluginGenericobjectObject::showDevice($_POST['target'], $itemtype, $_POST["id"]);
         break;
      case 10 :
         if ($item->canUseNotepad()) {
            showNotesForm($_POST['target'], $itemtype, $_POST["id"]);
         }
         break;
      case 11 :
         if ($item->canBeReserved()) {
            Reservation::showForItem($itemtype, $_POST["id"]);
         }
         break;
      case 12 :
         if ($item->canUseHistory()) {
            Log::showForItem($item);
         }
         break;
      default :
         if (!Plugin::displayAction($item, $_POST['glpi_tab'])) {
         }
         break;
   }
}