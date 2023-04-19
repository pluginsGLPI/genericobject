<?php

/**
 * -------------------------------------------------------------------------
 * GenericObject plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GenericObject.
 *
 * GenericObject is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * GenericObject is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GenericObject. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2009-2023 by GenericObject plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/genericobject
 * -------------------------------------------------------------------------
 */

include ("../../../inc/includes.php");

$itemtype = null;

if (isset($_REQUEST['itemtype'])) {

   $types = array_keys(PluginGenericobjectType::getTypes());

   $requested_type = $_REQUEST['itemtype'];
   $error = [];

   if (!in_array($requested_type, $types)) {
      $error[] = __('The requested type has not been defined yet!');
      if (!PluginGenericobjectType::canCreate()) {
         $error[] = __('Please ask your administrator to create this type of object');
      };
   } else if (!class_exists($requested_type)) {
      $error[]= __('The generated files for the requested type of object are missing!');
      $error[]= __('You might need to regenerate the files under '.GENERICOBJECT_DOC_DIR.'.');
   }

   if (count($error) > 0) {
      Html::header(__('Type not found!'));
      Html::displayErrorAndDie(implode('<br/>', $error));

   } else {
      $itemtype = $requested_type;
   }
}

if (!is_null($itemtype)) {

   if (!isset($_REQUEST['id'])) {
      $id = -1;
   } else {
      $id = $_REQUEST['id'];
   }

   if (!isset($_GET["withtemplate"])) {
      $_GET["withtemplate"] = "";
   }

   $item = new $itemtype();

   if (isset ($_POST["add"])) {
      $item->check($id, CREATE);
      $newID = $item->add($_POST);

      if ($_SESSION['glpibackcreated']) {
         Html::redirect($itemtype::getFormURL()."&id=".$newID);
      } else {
         Html::back();
      }
   } else if (isset ($_POST["update"])) {
      $item->check($id, UPDATE);
      $item->update($_POST);
      Html::back();
   } else if (isset ($_POST["restore"])) {
      $item->check($id, DELETE);
      $item->restore($_POST);
      Html::back();
   } else if (isset($_POST["purge"])) {
      $item->check($id, PURGE);
      $item->delete($_POST, 1);
      $item->redirectToList();
   } else if (isset($_POST["delete"])) {
      $item->check($id, DELETE);
      $item->delete($_POST);
      $item->redirectToList();
   }
   $menu = PluginGenericobjectType::getFamilyNameByItemtype($_GET['itemtype']);
   Html::header($itemtype::getTypeName(), $_SERVER['PHP_SELF'],
             "assets", ($menu!==false?$menu:$itemtype), strtolower($itemtype));

   $item->display($_GET, ['withtemplate' => $_GET["withtemplate"]]);

   Html::footer();
} else {
   Html::header(__('Access Denied!'));
   Html::DisplayErrorAndDie(__("You can't access to this page directly!"));
}

