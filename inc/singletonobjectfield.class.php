<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

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
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Walid Nouh
// Purpose of file:
// ----------------------------------------------------------------------
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginGenericobjectSingletonObjectField {
   /// Items list
   static $_dbfields = array();

   /**
    * Singleton to store DB fields definition
    *
    * @since 2.1.0
    * @param itemtype itemtype to query
    * @param reload reload db fields configuration from DB
    *
    * @return an array which contains DB fields definition
    */
   public static function getInstance($itemtype, $reload = false) {
      global $DB;
      if (!isset(self::$_dbfields[$itemtype]) || $reload) {
         self::$_dbfields[$itemtype] = $DB->list_fields(getTableForItemType($itemtype));
      } else {
      }
      return self::$_dbfields[$itemtype];
   }
}

