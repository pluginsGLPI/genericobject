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

if (!isset ($_REQUEST["id"])) {
   $_REQUEST["id"] = '';
}
$type = new PluginGenericobjectType();

$extraparams = array ();
if (isset ($_POST["select"]) && $_POST["select"] == "all") {
   $extraparams["selected"] = "checked";
}

if (isset ($_GET["action"])) {
   $type->getFromDB($_REQUEST["id"]);
   PluginGenericobjectType::registerOneType($type);
   PluginGenericobjectObject::changeFieldOrder($_GET["field"], $type->fields["itemtype"],
                                               $_GET["action"]);
   Html::redirect($_SERVER['HTTP_REFERER']);
}
if (isset ($_POST["add"])) {
   $new_id = $type->add($_POST);
   Html::redirect(Toolbox::getItemTypeFormURL('PluginGenericobjectType')."?id=$new_id");
} elseif (isset ($_POST["update"])) {
   $type->update($_POST);
   Html::redirect($_SERVER["HTTP_REFERER"]);
} elseif (isset ($_POST["delete"])) {
   $type->delete($_POST);
   $type->redirectToList();
} elseif (isset($_POST['regenerate'])) {
   $type->getFromDB($_POST["id"]);
   PluginGenericobjectType::checkClassAndFilesForOneItemType($type->fields['itemtype'],
                                                             $type->fields['name']);
   Html::back();
}

Html::header($LANG['genericobject']['title'][1], $_SERVER['PHP_SELF'], "plugins", "genericobject",
             "type");
$type->showForm($_REQUEST["id"]);

Html::footer();