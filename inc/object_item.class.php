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

// @codingStandardsIgnoreStart
class PluginGenericobjectObject_Item extends CommonDBChild
{
    // @codingStandardsIgnoreEnd
    public $dohistory = true;

   // From CommonDBRelation
    public static $itemtype_1 = "PluginGenericobjectObject";
    public static $items_id_1 = 'plugin_genericobject_objects_id';

    public static $itemtype_2 = 'itemtype';
    public static $items_id_2 = 'items_id';

    //Get itemtype name
    public static function getTypeName($nb = 0)
    {
        /** @var array $LANG */
        global $LANG;
        $class    = get_called_class();
       //Datainjection : Don't understand why I need this trick : need to be investigated !
        if (preg_match("/Injection$/i", $class)) {
            $class = str_replace("Injection", "", $class);
        }
        $item     = new $class();
       //Itemtype name can be contained in a specific locale field : try to load it
        PluginGenericobjectType::includeLocales($item->objecttype->fields['name']);
        if (isset($LANG['genericobject'][$class][0])) {
            return $LANG['genericobject'][$class][0];
        } else {
            return $item->objecttype->fields['name'];
        }
    }

    public static function canView()
    {
        return Session::haveRight(self::$itemtype_1, READ);
    }

    public static function canCreate()
    {
        return Session::haveRight(self::$itemtype_1, CREATE);
    }

   /**
    *
    * Enter description here ...
    * @since 2.2.0
    * @param CommonDBTM $item
    */
    public static function showItemsForSource(CommonDBTM $item)
    {
    }

   /**
    *
    * Enter description here ...
    * @since 2.2.0
    * @param CommonDBTM $item
    */
    public static function showItemsForTarget(CommonDBTM $item)
    {
    }

   /**
    *
    * Enter description here ...
    * @since 2.2.0
    */
    public static function registerType()
    {
        Plugin::registerClass(get_called_class(), ['addtabon' => self::getLinkedItemTypes()]);
    }

    public static function getLinkedItemTypes()
    {
        $source_itemtype = self::getItemType1();
        $source_item = new $source_itemtype();
        return $source_item->getLinkedItemTypesAsArray();
    }

    public static function getItemType1()
    {
        $classname   = get_called_class();
        return $classname::$itemtype_1;
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (!$withtemplate) {
            $itemtypes = self::getLinkedItemTypes();
            if (in_array(get_class($item), $itemtypes) || get_class($item) == self::getItemType1()) {
                return [1 => __("Objects management", "genericobject")];
            }
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        $itemtypes = self::getLinkedItemTypes();
        if (get_class($item) == self::getItemType1()) {
            self::showItemsForSource($item);
        } else if (in_array(get_class($item), $itemtypes)) {
            self::showItemsForTarget($item);
        }
        return true;
    }
}
