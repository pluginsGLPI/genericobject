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

include ('../../../inc/includes.php');

$family = new PluginGenericobjectTypeFamily();

if (!isset($_GET['id']) || !$family->getFromDB($_GET['id'])) {
   Html::header(__("Objects management", "genericobject"), $_SERVER['PHP_SELF'], "assets",
                   "genericobject");

   echo "<table class='tab_cadre_fixe'>";
    echo "<tr class='tab_bg_2'><th>".__("Empty family", "genericobject")."</th></tr>";
   echo "</table>";
} else {
   $family->getFromDB($_GET['id']);
   Html::header(__("Objects management", "genericobject"), $_SERVER['PHP_SELF'], "assets",
                   $family->getName());

   echo "<table class='tab_cadre_fixe'>";
   $types = PluginGenericobjectTypeFamily::getItemtypesByFamily($_GET['id']);
    echo "<tr class='tab_bg_2'><th>".Dropdown::getDropdownName("glpi_plugin_genericobject_typefamilies", $_GET['id'])."</th></tr>";
   foreach ($types as $type) {
      $itemtype = $type['itemtype'];
        echo "<tr class='tab_bg_1'><td align='center'>";
        echo "<a href='".$itemtype::getSearchURL()."'>";
        echo $itemtype::getTypeName();
        echo "</a></td></tr>";
   }
   echo "</table>";
}

Html::footer();
