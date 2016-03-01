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

   /*
   static function canView() {
      return Session::haveRight(self::$itemtype_1, READ);
   }
   */

   static function canCreate() {
      //Note : can be add a right
      return true;
   }

   static function canPurge() {
      //Note : can be add a right
      return true;
   }

   static function canDelete() { //useless
      //Note : can be add a right
      return true;
   }

   //Note : for datainjection, injection of a bind
   function post_updateItem($history = 1) {
      //Don't call parent
   }

   function post_purgeItem() {
      global $DB;

      // Delete the other genericobject link

      $obj_itemtype = $this->fields['itemtype'].'_Item';
      $obj_item = new $obj_itemtype();

      $itemtype = $this->fields['itemtype'];
      $obj = new $itemtype();
      $column  = str_replace('glpi_','',$obj->table.'_id');

      $query = "DELETE FROM ".$obj_item->getTable(). " WHERE itemtype = '".static::$itemtype_1."' AND $column = '".$this->fields['items_id']."' AND items_id = '".$this->fields[static::$items_id_1]."'";

      $DB->query($query);

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

            if (! class_exists($namelinkedObject)) {
               return '';
            }
            $oobjectLinked = new $namelinkedObject();
            $oobjectLinked->getFromDB($objectItem->fields['items_id']);

            return $oobjectLinked->getLink()." - (".$oobjectLinked->getTypeName().")";
      }
   }


   static function getItemListForObject($itemtype, $obj_item, $idItemType) {

      $nameMainObject = $itemtype.'_item';
      $objectItem = new $nameMainObject();
      $mainObject = new $itemtype();

      $column = str_replace('glpi_','',$mainObject->table.'_id');

      $result = $objectItem->find("`itemtype` = '".$obj_item."' AND `".$column."` = $idItemType");

      foreach ($result as $item) {

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

   static function showItemsInMassiveActions($ma) {
      global $CFG_GLPI;

      echo __("Select an object to bind", 'genericobject')."&nbsp";

      $elements = array('' => Dropdown::EMPTY_VALUE);

      $tmp = array();
      foreach ($ma->items as $itemtype => $id) {
         $item = new $itemtype();
         $tmp[] = $item->getLinkedItemTypesAsArray();
      }
      
      $intersect = $tmp[0];
      for ($i = 1; $i < count($tmp); $i++){
         $intersect = array_intersect($intersect, $tmp[$i]);
      }

      foreach ($intersect as $itemtype) {
         $type = new PluginGenericobjectType();
         $type->getFromDBByType($itemtype);

         if ($type->fields['is_active']) {
            $object = new $itemtype();
            $elements[$itemtype] = $object->getTypeName();
         }
      }

      $rand = Dropdown::showFromArray('objectToAdd', $elements);

      $params = array('objectToAdd' => '__VALUE__');

      Ajax::updateItemOnSelectEvent("dropdown_objectToAdd$rand", "show_".$rand,
                                    $CFG_GLPI["root_doc"]."/plugins/genericobject/ajax/dropdownByItemtype.php",
                                    $params);

      echo "<span id='show_".$rand."'>&nbsp;</span>";

      echo '<br /><br />' . Html::submit(_x('button', "Associate"), array('name' => 'massiveaction'));
   }

   function getForbiddenStandardMassiveAction() {
      // No need 'update' fields
      $forbidden = array('update');
      return $forbidden;
   }


   /**
    * 
    * @since 2.2.0
    * @param CommonDBTM $item
    */
   static function showItems(CommonDBTM $item) {
      global $DB, $CFG_GLPI;

      $instID = $item->fields['id'];

      if (!$item->can($instID, READ)) {
         return false;
      }

      if ($item->canEdit($instID)) {
         echo "<div class='firstbloc'>";

         echo "<form method='post' action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";

         echo "<div class='spaced'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'>";

         echo "<td class='center'>".__("Select an object to bind", 'genericobject')."&nbsp;&nbsp;";

         echo "<input type='hidden' name='items_id' value='$instID'>";
         //echo "<input type='hidden' name='idMainobject' value='".$item->getID()."'>";
         echo "<input type='hidden' name='mainobject' value='".$item->getType()."'>";

         $elements = array('' => Dropdown::EMPTY_VALUE);
         foreach ($item->getLinkedItemTypesAsArray() as $itemL) {
            $type = new PluginGenericobjectType();
            $type->getFromDBByType($itemL);
         
            if ($type->fields['is_active']) {
               $object = new $itemL();
               $elements[$itemL] = $object->getTypeName();
            }
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
         echo "<input type='submit' name='add' value=\""._x('button', "Associate")."\" class='submit'>";
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
    * 
    * @since 2.2.0
    */
   static function registerType() {
      /*
      Plugin::registerClass(get_called_class(),
                            array('addtabon' => self::getLinkedItemTypes()));
                            */
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

            $coluimn1 = str_replace('glpi_','',$item->table.'_id');

            $nb = countElementsInTable(getTableForItemType($item->getType().'_Item'),
                     "$coluimn1 = '".$item->getID()."'");
            $str = _n("Linked object", "Linked objects", $nb == 0 ? 1 : $nb, "genericobject");
            return array(1 => self::createTabEntry($str, $nb));
         }
      }

      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($tabnum == 1) {
         self::showItems($item);
      }

      return true;
   }

}
