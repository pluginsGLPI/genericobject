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
if (isset ($_POST["delete"])) {
   if (isset($_POST["fields"]) && count($_POST["fields"]) > 0 ) {
      $type = new PluginGenericobjectType();
      $type->getFromDB($_POST["id"]);
      $itemtype = $type->fields['itemtype'];
      PluginGenericobjectType::registerOneType($itemtype);

      foreach ($_POST["fields"] as $field => $value) {
         if ($type->can($_POST["id"], PURGE)
            && $value == 1
               && PluginGenericobjectField::checkNecessaryFieldsDelete($itemtype, $field)) {
            PluginGenericobjectField::deleteField(getTableForItemType($itemtype), $field);
            Session::addMessageAfterRedirect(__("Field(s) deleted successfully", "genericobject"), true, INFO);
         }
      }
   }
} else if (isset ($_POST["add_field"])) {
   $type     = new PluginGenericobjectType();
   if ($_POST["new_field"] && $type->can($_POST["id"], UPDATE)) {
      $itemtype = $type->fields['itemtype'];
      PluginGenericobjectType::registerOneType($itemtype);
      PluginGenericobjectField::addNewField(getTableForItemType($itemtype), $_POST["new_field"]);
      Session::addMessageAfterRedirect(__("Field added successfully", "genericobject"));
   }
} else if (isset($_POST['action'])) {
   //Move field
   PluginGenericobjectField::changeFieldOrder($_POST);
}

Html::back();

