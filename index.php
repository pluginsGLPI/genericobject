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

define('GLPI_ROOT', '../..');
include (GLPI_ROOT . "/inc/includes.php");

if (isset($_GET['itemtypes_id']) && $_GET['itemtypes_id']!='') {
   $type = new PluginGenericobjectType();
   $type->getFromDB($_GET['itemtypes_id']);
   $url = Toolbox::getItemTypeSearchURL($type->fields['itemtype']);
   Html::redirect($url);
} else {
   $types = PluginGenericobjectType::getTypes();
   foreach ($types as $ID => $value) {
      if (!Session::haveRight($value['itemtype'], 'r')) {
         unset($types[$ID]);
      }
   }
   if (count($types) == 1) {
      $type = array_pop($types);
      Html::redirect(Toolbox::getItemTypeSearchURL($type['itemtype']));
   } else {
      Html::header($LANG['genericobject']['title'][1],$_SERVER['PHP_SELF'], "plugins", "genericobject");
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_2'><th>" . $LANG["genericobject"]["title"][1]."</th></tr>";
      if (!count($types)) {
         echo "<tr class='tab_bg_1'><td align='center'>".$LANG['stats'][2]."</td></tr>";
      } else {
         foreach(PluginGenericobjectType::getTypes() as $ID => $value) {
            echo "<tr class='tab_bg_1'><td align='center'>";
            echo "<a href='".Toolbox::getItemTypeSearchURL($value['itemtype'])."'>";
            echo call_user_func(array($value['itemtype'], 'getTypeName'));
            echo "</a></td></tr>";
         }
      }
      
      echo "</table></div>";
      Html::footer();
   }
}