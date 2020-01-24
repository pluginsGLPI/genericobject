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


include ("../../../inc/includes.php");



if (isset($_POST['add'])) {

   $itemtype_to_link = $_POST['itemtype_to_link'];
   $item_to_link_id  = $_POST['item_to_link_id'];
   $items_id         = $_POST['items_id'];
   $itemtype_link    = $_POST['itemtype_link'];
   $itemtype         = $_POST['itemtype'];

   $mainItem = new $itemtype();
   $mainItem->check($items_id, UPDATE);

   $isPluginItemtype = false;
   foreach (PluginGenericobjectType::getTypes() as $key => $val) {
      if ($key == $itemtype_to_link) {
         $isPluginItemtype = true;
         break;
      }
   }

   $linked = new $itemtype_link();
   //is generic object itemtype
   if ($isPluginItemtype) {
      //prevent already linked item
      if ($linked->getFromDBByCrit([
         getForeignKeyFieldForItemType($itemtype_to_link) => $item_to_link_id,
         "itemtype" => $itemtype,
         "items_id" => $items_id])) {
         Session::addMessageAfterRedirect(__('This object is already linked'), false, INFO, false);
         Html::back();
      } else {
         $linked->add([
            getForeignKeyFieldForItemType($itemtype_to_link) => $item_to_link_id,
            "itemtype" => $itemtype,
            "items_id" => $items_id
         ]);
      }
   } else {
      //prevent already linked item
      if ($linked->getFromDBByCrit([
         getForeignKeyFieldForItemType($itemtype) => $items_id,
         "itemtype" => $itemtype_to_link,
         "items_id" => $item_to_link_id ])) {
         Session::addMessageAfterRedirect(__('This object is already linked'), false, INFO, false);
         Html::back();
      } else {
         $linked->add([
            getForeignKeyFieldForItemType($itemtype) => $items_id,
            "itemtype" => $itemtype_to_link,
            "items_id" => $item_to_link_id
         ]);
      }
   }

   Html::back();

}

