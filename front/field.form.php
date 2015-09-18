<?php
/*
 This file is part of the genericobject plugin.

 Genericobject plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Genericobject plugin is distributed in the hope that it will be useful,
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
if (isset ($_POST["delete"])) {
   if (isset($_POST["fields"]) && count($_POST["fields"] > 0 )) {
      $type = new PluginGenericobjectType();
      $type->getFromDB($_POST["id"]);
      $itemtype = $type->fields['itemtype'];
      PluginGenericobjectType::registerOneType($itemtype);
   
      foreach ($_POST["fields"] as $field => $value) {
         if ($type->can($_POST["id"], PURGE)
            && $value == 1
               && PluginGenericobjectField::checkNecessaryFieldsDelete($itemtype,  $field)) {
            PluginGenericobjectField::deleteField(getTableForItemType($itemtype), $field);
            Session::addMessageAfterRedirect(__("Field(s) deleted successfully", "genericobject"), true, INFO);
         }
      }
   }
} elseif (isset ($_POST["add_field"])) {
   $type     = new PluginGenericobjectType();
   if ($_POST["new_field"] && $type->can($_POST["id"], UPDATE)) {
      $itemtype = $type->fields['itemtype'];
      PluginGenericobjectType::registerOneType($itemtype);
      PluginGenericobjectField::addNewField(getTableForItemType($itemtype), $_POST["new_field"]);
      Session::addMessageAfterRedirect(__("Field added successfully", "genericobject"));
   }
} elseif (isset($_POST['action'])) {
   //Move field
   PluginGenericobjectField::changeFieldOrder($_POST);
}

Html::back();

