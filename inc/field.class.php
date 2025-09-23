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

class PluginGenericobjectField extends CommonDBTM
{
    /**
     * Get the name of the field, as defined in a constant file
     * The name may be the same, or not depending if it's an isolated dropdown or not
     */
    public static function getFieldName($field, $itemtype, $options, $remove_prefix = false)
    {
        /** @var DBmysql $DB */
        global $DB;
        $field_table = null;
        $input_type = $options['input_type']
         ?? null;
        switch ($input_type) {
            case 'dropdown':
                $dropdown_type = $options['dropdown_type']
                 ?? null;
                $fk = getForeignKeyFieldForTable(getTableForItemType($itemtype));

                if ($dropdown_type == 'isolated') {
                    if (!$remove_prefix) {
                        $field = preg_replace("/s_id$/", $field, $fk);
                    } else {
                        $fk    = preg_replace("/s_id$/", "", $fk);
                        $field = preg_replace("/" . $fk . "/", "", $field);
                    }
                }
                $field_table = getTableNameForForeignKeyField($field);

                //Prepend plugin's table prefix if this dropdown is not already handled by GLPI natively
                if (
                    (!str_starts_with($field, 'plugin_genericobject') && substr($field_table, strlen('glpi_')) === substr($field, 0, strlen($field) - strlen('_id')) && !$DB->tableExists($field_table)) && !$remove_prefix
                ) {
                    $field = 'plugin_genericobject_' . $field;
                }
                break;
        }
        return $field;
    }

    /**
     *
     * Get field's options defined in constant files.
     * If this field has not been defined, it means that this field has been defined globally and
     * must be dynamically created.
     *
     * @param $field the current field
     * @param $itemtype the itemtype
     * @return array which contains the full field definition
     */
    public static function getFieldOptions($field, $itemtype = "")
    {
        /** @var array $GO_FIELDS */
        global $GO_FIELDS;

        $options = [];
        $cleaned_field = preg_replace("/^plugin_genericobject_/", '', $field);
        if (!isset($GO_FIELDS[$cleaned_field]) && !empty($itemtype)) {
            // This field has been dynamically defined because it's an isolated dropdown
            $tmpfield = self::getFieldName(
                $field,
                $itemtype,
                [
                    'dropdown_type' => 'isolated',
                    'input_type'    => 'dropdown',
                ],
                true,
            );
            $options             = $GO_FIELDS[$tmpfield];
            $options['realname'] = $tmpfield;
        } elseif (isset($GO_FIELDS[$cleaned_field])) {
            $options             = $GO_FIELDS[$cleaned_field];
            $options['realname'] = $cleaned_field;
        }
        return $options;
    }

    public static function install(Migration $migration) {}

    public static function uninstall() {}
}
