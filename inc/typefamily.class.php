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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginGenericobjectTypeFamily extends CommonDropdown {
   var $can_be_translated       = true;

   static function getTypeName($nb=0) {
      return __('Family of type of objects', 'genericobject');
   }

   static function install(Migration $migration) {
      global $DB;

      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $query = "CREATE TABLE `$table` (
                           `id` INT( 11 ) NOT NULL AUTO_INCREMENT,
                           `name` varchar(255) collate utf8_unicode_ci default NULL,
                           `comment` text NULL,
                           `date_mod` DATETIME DEFAULT NULL,
                           `date_creation` DATETIME DEFAULT NULL,
                           PRIMARY KEY (`id`),
                           KEY `date_mod` (`date_mod`),
                           KEY `date_creation` (`date_creation`)
                           ) ENGINE = MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die($DB->error());
      }
   }

   static function uninstall() {
      global $DB;

      $table = getTableForItemType(__CLASS__);
      if (TableExists($table)) {
         $query = "DROP TABLE IF EXISTS `$table`";
         $DB->query($query) or die($DB->error());
      }
   }

   static function getFamilies() {
      global $DB;

      $query     = "SELECT f.id as id, f.name as name, t.itemtype as itemtype
                    FROM glpi_plugin_genericobject_typefamilies as f
                    LEFT JOIN glpi_plugin_genericobject_types AS t
                       ON (f.id = t.plugin_genericobject_typefamilies_id)
                    WHERE t.id IN (SELECT DISTINCT `id`
                                   FROM glpi_plugin_genericobject_types
                                   WHERE is_active=1)";
      $families = array();
      foreach($DB->request($query) as $fam) {
         $itemtype = $fam['itemtype'];
         if ($itemtype::canCreate()) {
           $families[$fam['id']] = $fam['name'];
         }
      }
      return $families;
   }


   static function getItemtypesByFamily($families_id) {
      return getAllDatasFromTable('glpi_plugin_genericobject_types',
                                  "plugin_genericobject_typefamilies_id='$families_id'
                                     AND is_active='1'");
   }
}
