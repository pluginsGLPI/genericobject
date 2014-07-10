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

class PluginGenericobjectObject_Item extends CommonDBChild {

   public $dohistory = true;

   // From CommonDBRelation
   static public $itemtype_1 = "PluginGenericobjectObject";
   static public $items_id_1 = 'plugin_genericobject_objects_id';

   static public $itemtype_2 = 'itemtype';
   static public $items_id_2 = 'items_id';

   //Get itemtype name
   static function getTypeName($nb=0) {
      global $LANG;
      $class    = get_called_class();
      //Datainjection : Don't understand why I need this trick : need to be investigated !
      if(preg_match("/Injection$/i",$class)) {
         $class = str_replace("Injection", "", $class);
      }
      $item     = new $class();
      //Itemtype name can be contained in a specific locale field : try to load it
      PluginGenericobjectType::includeLocales($item->objecttype->fields['name']);
      if(isset($LANG['genericobject'][$class][0])) {
         return $LANG['genericobject'][$class][0];
      } else {
         return $item->objecttype->fields['name'];
      }
   }

   static function canView() {
      return Session::haveRight($this->$itemtype_1, READ);
   }

   static function canCreate() {
      return Session::haveRight($this->$itemtype_1, CREATE);
   }

   /**
    *
    * Enter description here ...
    * @since 2.2.0
    * @param CommonDBTM $item
    */
   static function showItemsForSource(CommonDBTM $item) {

   }

   /**
    *
    * Enter description here ...
    * @since 2.2.0
    * @param CommonDBTM $item
    */
   static function showItemsForTarget(CommonDBTM $item) {

   }

   /**
    *
    * Enter description here ...
    * @since 2.2.0
    */
   static function registerType() {
      Plugin::registerClass(get_called_class(),
                            array('addtabon' => self::getLinkedItemTypes()));
   }

   static function getLinkedItemTypes() {
      $source_itemtype = self::getItemType1();
      $source_item = new $source_itemtype;
      return $source_item->getLinkedItemTypesAsArray();
   }

   static function getItemType1() {
      $classname   = get_called_class();
      return $classname::$itemtype_1;
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      if (!$withtemplate) {
         $itemtypes = self::getLinkedItemTypes();
         if (in_array(get_class($item), $itemtypes) || get_class($item) == self::getItemType1()) {
            return array(1 => __("Objects management", "genericobject"));
         }
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      $itemtypes = self::getLinkedItemTypes();
      if (get_class($item) == self::getItemType1()) {
         self::showItemsForSource($item);
      } elseif (in_array(get_class($item), $itemtypes)) {
         self::showItemsForTarget($item);
      }
      return true;
   }

}
