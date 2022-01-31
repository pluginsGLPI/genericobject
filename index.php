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
 * @copyright Copyright (C) 2009-2022 by GenericObject plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/genericobject
 * -------------------------------------------------------------------------
 */

define('GLPI_ROOT', '../..');
include (GLPI_ROOT . "/inc/includes.php");

if (isset($_GET['itemtypes_id']) && $_GET['itemtypes_id']!='') {
   $type = new PluginGenericobjectType();
   $type->getFromDB($_GET['itemtypes_id']);
   Html::redirect(Toolbox::getItemTypeSearchURL($type->fields['itemtype']));
} else {
   $types = PluginGenericobjectType::getTypesByFamily();
   foreach ($types as $family => $typeData) {
      foreach ($typeData as $ID => $value) {
         if (!Session::haveRight($value['itemtype'], READ)) {
            unset($types[$family][$ID]);
         }
      }
   }

   //There's only one family
   if (count($types) == 1) {
      //There's only one itemtype ? If yes, then automatically
      //redirect to the search engine
      if (key($types) == null) {
         $mytypes = $types;
         $tmp = array_pop($mytypes);
         if (count($tmp) == 1) {
            Html::redirect(Toolbox::getItemTypeSearchURL(key($tmp)));
         }
      }
   }

   Html::header(__("Objects management", "genericobject"), $_SERVER['PHP_SELF'], "plugins",
      "genericobject");

   foreach ($types as $family => $typeData) {

      $PluginGenericobjectTypefamily = new PluginGenericobjectTypefamily();
      $PluginGenericobjectTypefamily->getFromDB($family);

      echo "<table class='tab_cadre_fixe'>";
      if ($family == 0) {
         echo "<tr class='tab_bg_2'><th>".__("Empty family", "genericobject")."</th></tr>";
      } else {
         echo "<tr class='tab_bg_2'><th>".$PluginGenericobjectTypefamily->getField("name")."</th></tr>";
      }
      if (!count($types)) {
         echo "<tr class='tab_bg_1'><td align='center'>".__("No item to display")."</td></tr>";
      } else {
         foreach ($typeData as $ID => $value) {
            echo "<tr class='tab_bg_1'><td align='center'>";
            echo "<a href='".Toolbox::getItemTypeSearchURL($value['itemtype'])."'>";
            $itemtype = $value['itemtype'];
            echo $itemtype::getTypeName();
            echo "</a></td></tr>";
         }
      }
      echo "</table>";
   }

   Html::footer();
}
