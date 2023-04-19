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

class PluginGenericobjectCommonDropdown extends CommonDropdown {

   //Get itemtype name
   static function getTypeName($nb = 0) {
      $class=get_called_class();
      return dropdown_getTypeName($class, $nb);
   }

   static function getFormURL($full = true) {
      return Toolbox::getItemTypeFormURL(  get_parent_class(get_called_class()), $full) .
         "?itemtype=".get_called_class();
   }
   static function getSearchURL($full = true) {
      return Toolbox::getItemTypeSearchURL( get_parent_class(get_called_class()), $full) .
         "?itemtype=".get_called_class();

   }

}

