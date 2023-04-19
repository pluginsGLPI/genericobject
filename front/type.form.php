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

if (!isset ($_GET["id"])) {
   $_GET["id"] = '';
}
$type        = new PluginGenericobjectType();
$extraparams =  [];
if (isset ($_POST["select"]) && $_POST["select"] == "all") {
   $extraparams["selected"] = "checked";
}

//Change fields order
if (isset ($_GET["action"])) {
   $type->getFromDB($_REQUEST["id"]);
   PluginGenericobjectType::registerOneType($type);
   PluginGenericobjectObject::changeFieldOrder($_GET["field"], $type->fields["itemtype"],
                                               $_GET["action"]);
   Html::back();

} else if (isset ($_POST["add"])) {
   //Add a new itemtype
   $new_id = $type->add($_POST);
   Html::redirect(Toolbox::getItemTypeFormURL('PluginGenericobjectType')."?id=$new_id");

} else if (isset ($_POST["update"])) {
   //Update an existing itemtype
   if (isset($_POST['itemtypes']) && is_array($_POST['itemtypes'])) {
      $_POST['linked_itemtypes'] = json_encode($_POST['itemtypes']);
   }
   $type->update($_POST);
   Html::back();

} else if (isset ($_POST["purge"])) {
   //Delete an itemtype
   $type->delete($_POST);
   $type->redirectToList();

} else if (isset($_POST['regenerate'])) {
   //Regenerate files for an itemtype
   $type->getFromDB($_POST["id"]);
   PluginGenericobjectType::checkClassAndFilesForOneItemType($type->fields['itemtype'],
                                                             $type->fields['name'], true);
   Html::back();
}

Html::header(__("Objects management", "genericobject"), $_SERVER['PHP_SELF'], "config",
    "PluginGenericobjectType");
$type->display($_GET);

Html::footer();
