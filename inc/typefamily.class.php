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

if (!defined('GLPI_ROOT')) {
    throw new RuntimeException('Direct access to this file is not allowed');
}

class PluginGenericobjectTypeFamily extends CommonDropdown
{
    public $can_be_translated       = true;

    public static function getTypeName($nb = 0)
    {
        return __s('Family of type of objects', 'genericobject');
    }

    public static function install(Migration $migration)
    {
        /** @var DBmysql $DB */
        global $DB;

        $default_charset = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

        $table = getTableForItemType(__CLASS__);
        if (!$DB->tableExists($table)) {
            $query = "CREATE TABLE `$table` (
                           `id` INT {$default_key_sign} NOT NULL AUTO_INCREMENT,
                           `name` varchar(255) default NULL,
                           `comment` text NULL,
                           `date_mod` TIMESTAMP NULL DEFAULT NULL,
                           `date_creation` TIMESTAMP NULL DEFAULT NULL,
                           PRIMARY KEY (`id`),
                           KEY `date_mod` (`date_mod`),
                           KEY `date_creation` (`date_creation`)
                           ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
            $DB->doQuery($query);
        }
    }

    public static function uninstall()
    {
        /** @var DBmysql $DB */
        global $DB;

        $table = getTableForItemType(__CLASS__);
        $DB->dropTable($table, true);
    }
}
