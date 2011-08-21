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

if (!isset($_GET['id'])) {
   $id = 0;
} else {
   $id = $_GET['id'];
}

if (isset ($_POST["add"])) {
   $item->check($_POST['id'],'w');
   $item->add($_POST);
   glpi_header($_SERVER["HTTP_REFERER"]);
} elseif (isset ($_POST["update"])) {
   $item->check($_POST['id'],'w');
   $item->update($_POST);
   glpi_header($_SERVER["HTTP_REFERER"]);
} elseif (isset ($_POST["restore"])) {
   $item->check($_POST['id'],'w');
   $item->restore($_POST);
   glpi_header($_SERVER["HTTP_REFERER"]);
} elseif (isset($_REQUEST["purge"])) {
   $input["id"]=$_REQUEST["id"];
   $item->check($_POST['id'],'w');
   $item->delete($_POST,1);
   $item->redirectToList();
} elseif (isset($_SERVER["delete"])) {
   $item->delete($_POST);
   $item->redirectToList();
}
$itemtype = get_class($item);
commonHeader(call_user_func(array($itemtype, 'getTypeName')), $_SERVER['PHP_SELF'], 
             "plugins", "genericobject", $itemtype);

$item->title();
$item->showForm($id);

commonFooter();