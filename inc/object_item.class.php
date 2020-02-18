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
   static function getTypeName($nb = 0) {
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

   static function canView() {
      return Session::haveRight(self::$itemtype_1, READ);
   }

   static function canCreate() {
      return Session::haveRight(self::$itemtype_1, CREATE);
   }

   static function canPurge() {
      //Note : can be add a right
      return true;
   }

   static function canDelete() { //useless
      //Note : can be add a right
      return true;
   }

   function post_purgeItem() {
      global $DB;
      // Delete the other genericobject link
      $obj_itemtype = $this->fields['itemtype'].'_Item';
      $obj_item = new $obj_itemtype();
      $itemtype = $this->fields['itemtype'];
      $obj = new $itemtype();
      $column = str_replace('glpi_','',$obj->table.'_id');
      $obj_item->deleteByCriteria(array(
                        'items_id' => $this->fields[static::$items_id_1],
                        $column => $this->fields['items_id'],
                        'itemtype' => static::$itemtype_1)
      );
      parent::post_purgeItem();
   }

   static function getSpecificValueToDisplay($field, $values, array $options = array()) {
      if (!is_array($values)) {
         $values = array($field => $values);
      }
      switch ($field) {
         // Column "Linked objects" : Display name of the object and type of object
         case 'id' :
            $itemtype = get_called_class();
            $objectItem = new $itemtype();
            $objectItem->getFromDB($values['id']);
            $namelinkedObject = $objectItem->fields['itemtype'];
            $oobjectLinked = new $namelinkedObject();
            $oobjectLinked->getFromDB($objectItem->fields['items_id']);
            return $oobjectLinked->getLink()." - (".$oobjectLinked->getTypeName().")";
      }
   }

   public static function getDropdownItemLinked($object, $itemType, $id) {
      $obj = new $itemType();
      $nameMainObjectItem = $itemType."_Item";
      $mainObjectItem = new $nameMainObjectItem();
      $column = str_replace('glpi_','',$obj->table."_id");
      $listeId = array();
      foreach ($mainObjectItem->find() as $record) {
         if ($record[$column] == $id) {
            $listeId[] = $record['items_id'];
         }
      }
      $object->dropdown(array('used' => $listeId));
   }

   static function getItemListForObject($itemtype, $obj_item, $idItemType) {
      $nameMainObject = $itemtype.'_item';
      $objectItem = new $nameMainObject();
      $mainObject = new $itemtype();
      $column = str_replace('glpi_','',$mainObject->table.'_id');
      $resultat = $objectItem->find("`itemtype` = '".$obj_item."' and `".$column."` = $idItemType");
      foreach ($resultat as $item) {
         $obj = new $item['itemtype']();
         $obj->getFromDB($item['items_id']);
         echo "<tr class='center'>";
         //if ($canedit) {
            echo "<td width='10'>";
            Html::showMassiveActionCheckBox($objectItem->getType(), $item["id"]);
            echo "</td>";
         //}
         echo "<td>".$obj->getTypeName()."</td>";
         echo "<td>".$item['items_id']."</td>";
         echo "<td>".$obj->getLink()."</td>";
         echo "</tr>";
      }
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

   static function showItems(CommonDBTM $item) {
      global $DB, $CFG_GLPI;
      $instID = $item->fields['id'];
      if (!$item->can($instID, READ)) {
         return false;
      }
      if ($item->canEdit($instID)) {
         echo "<div class='firstbloc'>";
         echo "<form method='post' action='".Toolbox::getItemTypeFormURL("PluginGenericobjectObject_Item")."'>";
         echo "<div class='spaced'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'>";
         echo "<td class='center'>".__("Select an object to link", 'genericobject')."&nbsp;&nbsp;";
         echo "<input type='hidden' name='items_id' value='$instID'>";
         //echo "<input type='hidden' name='idMainobject' value='".$item->getID()."'>";
         echo "<input type='hidden' name='mainobject' value='".$item->getType()."'>";
         $elements = array('' => Dropdown::EMPTY_VALUE);
         foreach ($item->getLinkedItemTypesAsArray() as $itemL) {
            $object = new $itemL();
            $elements[$itemL] = $object->getTypeName();
         }
         $rand = Dropdown::showFromArray('objectToAdd', $elements);
         $paramsselsoft = array('objectToAdd' => '__VALUE__',
                                'idMainobject' => $item->getID(),
                                'mainobject' => $item->getType());
         Ajax::updateItemOnSelectEvent("dropdown_objectToAdd$rand", "show_".$rand,
                                       $CFG_GLPI["root_doc"]."/plugins/genericobject/ajax/dropdownByItemtype.php",
                                       $paramsselsoft);
         echo "<span id='show_".$rand."'>&nbsp;</span>";
         echo "</td><td width='20%'>";
         echo "<input type='submit' name='add' value=\""._sx('button', 'Install')."\" class='submit'>";
         echo "</td>";
         echo "</tr>";
         echo "</table>";
         echo "</div>";
         Html::closeForm();
         echo "</div>";
      }
      echo "<div class='spaced'>";
      //if ($canedit && $number) {
         Html::openMassiveActionsForm('mass'.$item->getType().'_Item'.$rand);
         $massiveactionparams = array('container' => 'mass'.$item->getType().'_Item'.$rand);
         //Note : useless ?
         $massiveactionparams['check_itemtype'] = $item->getType();
         Html::showMassiveActions($massiveactionparams);
      //}
      echo "<table class='tab_cadre_fixehov'>";
      $header_begin  = "<tr>";
      $header_top    = '';
      $header_bottom = '';
      $header_end    = '';
      //if ($canedit && $number) {
         $header_top    .= "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.$item->getType().'_Item'.$rand);
         $header_top    .= "</th>";
         $header_bottom .= "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.$item->getType().'_Item'.$rand);
         $header_bottom .= "</th>";
      //}
      $header_end .= "<th>".__('Type')."</th>";
      $header_end .= "<th>".__('ID')."</th>";
      $header_end .= "<th>".__('Name')."</th>";
      echo $header_begin.$header_top.$header_end;
      foreach ($item->getLinkedItemTypesAsArray() as $itemL) {
         $object = new $itemL();
         self::getItemListForObject($item->accesObjectType()->fields['itemtype'], 
            $object->accesObjectType()->fields['itemtype'], $item->fields['id']);
      }
      //if ($number) {
         echo $header_begin.$header_bottom.$header_end;
      //}
      echo "</table>";
      //if ($canedit && $number) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      //}
      return true;
   }

   /**
    *
    * Enter description here ...
    * @since 2.2.0
    */
   static function registerType() {
//      Plugin::registerClass(get_called_class(), ['addtabon' => self::getLinkedItemTypes()]);
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

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if (!$withtemplate) {
         $itemtypes = self::getLinkedItemTypes(get_class($item));
         if (in_array(get_class($item), $itemtypes) || get_class($item) == self::getItemType1()) {
//            return [1 => __("Objects management", "genericobject")];
            $coluimn1 = str_replace('glpi_','',$item->table.'_id');
            $nb = countElementsInTable(getTableForItemType($item->getType().'_Item'),
                     array("$coluimn1" => $item->getID()));
            $str = _n("Linked object", "Linked objects", $nb == 0 ? 1 : $nb, "genericobject");
            return array(1 => self::createTabEntry($str, $nb));
         }
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      /*$itemtypes = self::getLinkedItemTypes();
      if (get_class($item) == self::getItemType1()) {
         self::showItemsForSource($item);
      } else if (in_array(get_class($item), $itemtypes)) {
         self::showItemsForTarget($item);
      }
      */
      if ($tabnum == 1) {
         self::showItems($item);
      }
      return true;
   }

}
