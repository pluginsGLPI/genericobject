<?php

/*
 This file is part of the genericobject plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Genericobject. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   genericobject
 @author    the genericobject plugin team
 @copyright Copyright (c) 2010-2011 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/genericobject
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

if (!isset($_REQUEST['id'])) {
   $id = -1;
} else {
   $id = $_REQUEST['id'];
}

if (!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

if (isset ($_POST["add"])) {
   $item->check($id,'w');
   $item->add($_POST);
   glpi_header($_SERVER["HTTP_REFERER"]);
} elseif (isset ($_POST["update"])) {
   $item->check($id,'w');
   $item->update($_POST);
   glpi_header($_SERVER["HTTP_REFERER"]);
} elseif (isset ($_POST["restore"])) {
   $item->check($id,'w');
   $item->restore($_POST);
   glpi_header($_SERVER["HTTP_REFERER"]);
} elseif (isset($_REQUEST["purge"])) {
   $item->check($id,'w');
   $item->delete($_POST,1);
   $item->redirectToList();
} elseif (isset($_SERVER["delete"])) {
   $item->check($id,'w');
   $item->delete($_POST);
   $item->redirectToList();
}
$itemtype = get_class($item);
commonHeader(call_user_func(array($itemtype, 'getTypeName')), $_SERVER['PHP_SELF'], 
             "plugins", "genericobject", $itemtype);

$item->title();
$item->showForm($id, array('withtemplate' => $_GET["withtemplate"]));

commonFooter();