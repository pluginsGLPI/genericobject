<?php


/*----------------------------------------------------------------------
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
   ----------------------------------------------------------------------*/
/*----------------------------------------------------------------------
    Original Author of file: 
    Purpose of file:
    ----------------------------------------------------------------------*/
define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
header_nocache();

if(!isset($_POST["id"]) || $_POST['id'] == '') {
   exit();
}
$type = new PluginGenericobjectType();

if ($_POST["id"] != '') {
   $type->getFromDB($_POST["id"]);
   PluginGenericobjectType::registerOneType($type);
}

foreach (array ('sort', 'order') as $field) {
   if(!isset($_POST[$field])) {
      $_POST[$field] = "";
   }
   
}
/*
if(!isset($_POST["withtemplate"])) {
   $_POST["withtemplate"] = "";
} elseif(!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}
*/

switch($_POST['glpi_tab']){
      case -1:
         $type->showBehaviourForm($_POST["id"]);
         PluginGenericobjectField::showObjectFieldsForm($_POST["id"]);
         PluginGenericobjectLink::showDeviceTypeLinks($_POST['target'],
                                                                           $_POST["id"]);
         $type->getFromDB($_POST["id"]);
         PluginGenericobjectObject::showPrevisualisationForm($type->fields["itemtype"]);
         Log::showForItem($type);
        break;
        
      case 1 :
         $type->showBehaviourForm($_POST["id"]);
         break;

      case 3 :
         PluginGenericobjectField::showObjectFieldsForm($_POST["id"]);
         break;

      case 4 :
         PluginGenericobjectLink::showDeviceTypeLinks($_POST['target'],
                                                                           $_POST["id"]);
         break; 

      case 5 :
         $type->getFromDB($_POST["id"]);
         PluginGenericobjectObject::showPrevisualisationForm($type->fields["itemtype"]);
      break;
      
      case 12 :
         Log::showForItem($type);
         break;
      }
?>
