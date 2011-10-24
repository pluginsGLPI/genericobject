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
 
define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

if (isset($_GET['action'])) {
   //Move field
   PluginGenericobjectField::changeFieldOrder($_GET);
} elseif (isset ($_POST["delete"])) {
   $type = new PluginGenericobjectType();
   $type->getFromDB($_POST["id"]);
   PluginGenericobjectType::registerOneType($type);

   foreach ($_POST["fields"] as $field => $value) {
      $itemtype = $type->fields['itemtype'];
      if ($type->can($_POST["id"], "w") 
         && $value == 1  
            && PluginGenericobjectField::checkNecessaryFieldsDelete($itemtype,  $field)) {
         PluginGenericobjectField::deleteField(getTableForItemType($itemtype), $field);
         Session::addMessageAfterRedirect($LANG['genericobject']['fields'][5]);
      }
   }
} elseif (isset ($_POST["add_field"])) {
   $type     = new PluginGenericobjectType();
   if ($_POST["new_field"] && $type->can($_POST["id"], "w")) {
      PluginGenericobjectType::registerOneType($type);

      $itemtype = $type->fields['itemtype'];
      PluginGenericobjectField::addNewField(getTableForItemType($itemtype), $_POST["new_field"]);
      Session::addMessageAfterRedirect($LANG['genericobject']['fields'][6]);
   }
}

Html::redirect($_SERVER['HTTP_REFERER']);