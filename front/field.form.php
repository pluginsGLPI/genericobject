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

// Original Author of file: BALPE Dévi
// Purpose of file:
// ----------------------------------------------------------------------
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
         addMessageAfterRedirect($LANG['genericobject']['fields'][5].": $field", true);
      }
   }
} elseif (isset ($_POST["add_field"])) {
   $type     = new PluginGenericobjectType();
   if ($_POST["new_field"] && $type->can($_POST["id"], "w")) {
      PluginGenericobjectType::registerOneType($type);

      $itemtype = $type->fields['itemtype'];
      PluginGenericobjectField::addNewField(getTableForItemType($itemtype), $_POST["new_field"]);
      addMessageAfterRedirect($LANG['genericobject']['fields'][6].": $field");
   }
}

glpi_header($_SERVER['HTTP_REFERER']);