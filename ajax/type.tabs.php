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
header("Content-Type: text/html; charset=UTF-8");
header_nocache();

$type = new PluginGenericobjectType();
if ($_POST['glpi_tab'] == 1) {
   $type->showBehaviorForm($_POST["id"]);
}

if(!isset($_POST["id"]) || $_POST['id'] == '') {
   exit();
}

if ($_POST["id"] != '') {
   $type->getFromDB($_POST["id"]);
   PluginGenericobjectType::registerOneType($type);
}

foreach (array ('sort', 'order') as $field) {
   if(!isset($_POST[$field])) {
      $_POST[$field] = "";
   }
   
}

switch($_POST['glpi_tab']){
   case -1:
      $type->showBehaviorForm($_POST["id"]);
      PluginGenericobjectField::showObjectFieldsForm($_POST["id"]);
      $type->getFromDB($_POST["id"]);
      PluginGenericobjectObject::showPrevisualisationForm($type);
      PluginGenericobjectProfile::showForItemtype($type);
      //Log::showForItem($type);
     break;
   case 3 :
     PluginGenericobjectField::showObjectFieldsForm($_POST["id"]);
     break;

   case 5 :
     $type->getFromDB($_POST["id"]);
     PluginGenericobjectObject::showPrevisualisationForm($type);
     break;
   
   case 6 :
      PluginGenericobjectProfile::showForItemtype($type);
      break;
}