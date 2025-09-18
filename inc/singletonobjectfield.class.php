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

// ----------------------------------------------------------------------
// Original Author of file: Walid Nouh
// Purpose of file:
// ----------------------------------------------------------------------
if (!defined('GLPI_ROOT')) {
    throw new \RuntimeException('Direct access to this file is not allowed');
}

class PluginGenericobjectSingletonObjectField
{
   /// Items list
    public static $_dbfields = [];

   /**
    * Singleton to store DB fields definition
    *
    * @since 2.1.0
    * @param string $itemtype itemtype to query
    * @param bool $reload reload db fields configuration from DB
    *
    * @return array an array which contains DB fields definition
    */
    public static function getInstance($itemtype, $reload = false)
    {
        /** @var DBmysql $DB */
        global $DB;
        if (!isset(self::$_dbfields[$itemtype]) || $reload) {
            self::$_dbfields[$itemtype] = $DB->listFields(getTableForItemType($itemtype));
        }
        return self::$_dbfields[$itemtype];
    }
}
