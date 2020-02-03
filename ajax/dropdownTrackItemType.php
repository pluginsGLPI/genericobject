<?php
/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2018 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

include ('../../../inc/includes.php');
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (!isset($_POST["itemtype"]) || $_POST["itemtype"] == "0") {
   exit();
}

if ($_POST["source"] == "0") {
   //load all  items_id already linked
   $linkfield = getForeignKeyFieldForItemType($_POST["itemtype"]);
   $mainItemtypeLink = new $_POST['main_itemtype_link']();
   $linked_data = $mainItemtypeLink->find(["items_id" => $_POST['itemtype_link_id'],
                                          "itemtype" => $_POST["itemtype_link"]]);
   $linked_id = [];
   foreach ($linked_data as $key => $value) {
      $linked_id[$key] = $value[$linkfield];
   }
   $_POST['used'] = $linked_id;
   Dropdown::show($_POST["itemtype"], $_POST);
} else {
   //load all  items_id already linked
   $linkfield = getForeignKeyFieldForItemType($_POST['itemtype_link']);
   $mainItemtypeLink = new $_POST['main_itemtype_link']();
   $linked_data = $mainItemtypeLink->find(["itemtype" => $_POST["itemtype"],
                                           $linkfield => $_POST['itemtype_link_id']]);
   $linked_id = [];
   foreach ($linked_data as $key => $value) {
      $linked_id[$key] = $value['items_id'];
   }
   $_POST['used'] = $linked_id;
   Dropdown::show($_POST["itemtype"], $_POST);
}
