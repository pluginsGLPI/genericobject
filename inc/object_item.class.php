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

use function Docopt\get_class_name;

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


   /**
    * Get items for an itemtype
    */
   static function getAllItemLinkedBySource($item, $items_id, $itemtype, $linkfield) {
      global $DB;

      $tableItemtype = getTableForItemType($itemtype);
      $tableItemtypeItem = getTableForItemType($item);
      $where = [$linkfield  => $items_id,
              "itemtype"   => $itemtype];

      $params = [
        'SELECT' => [
           $tableItemtype . '.*',
           $tableItemtypeItem . '.id AS linkid'
        ],
        'FROM'   => $tableItemtypeItem,
        'WHERE'  => $where,
        'LEFT JOIN' => [
           $tableItemtype => [
              'FKEY' => [
                 $tableItemtype       => 'id',
                 $tableItemtypeItem   => 'items_id'
              ]
           ]
        ]
      ];

      $iterator = $DB->request($params);
      return $iterator;

   }

   /**
    * Get items for an itemtype
    */
   static function getAllItemLinkedByTarget($item, $items_id, $itemtype, $linkfield) {
      global $DB;

      $tableItemtype = getTableForItemType($item);
      $tableItemtypeItem = getTableForItemType($item."_Item");
      $where = ["items_id"  => $items_id,
              "itemtype"   => $itemtype];

      $params = [
        'SELECT' => [
           $tableItemtype . '.*',
           $tableItemtypeItem . '.id AS linkid'
        ],
        'FROM'   => $tableItemtypeItem,
        'WHERE'  => $where,
        'LEFT JOIN' => [
           $tableItemtype => [
              'FKEY' => [
                 $tableItemtype       => 'id',
                 $tableItemtypeItem   => $linkfield
              ]
           ]
        ]
      ];
      $iterator = $DB->request($params);
      return $iterator;

   }

   /**
    *
    * Enter description here ...
    * @since 2.2.0
    * @param CommonDBTM $item
    */
   static function showItems(CommonDBTM $item) {
      global $CFG_GLPI;
      $instID = $item->fields['id'];

      if (!$item->can($instID, READ)) {
         return false;
      }

      $canedit = $item->canAddItem($instID);
      $rand    = mt_rand();

      $source = 0;
      $mainItemtype_link = "";
      if (get_class($item) == self::getItemType1()) {
         $source = true;
         $types = self::getLinkedItemTypes();
         $number = count($types);
         $mainItemtype_link = get_class($item)."_Item";
      } else {
         $types = [self::getItemType1()];
         $number = count($types);
         $mainItemtype_link = self::getItemType1()."_Item";
      }

      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form name='object_item_form$rand' id='object_item_form$rand' method='post'
                action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th colspan='2'>".__('Add an item')."</th></tr>";

         echo "<tr class='tab_bg_1'><td>";
         $myname = "itemtype_to_link";
         Dropdown::showItemTypes($myname, $types,
                                       ['emptylabel' => "---",
                                             'rand'       => $rand, 'display_emptychoice' => true]);

         $p = ['itemtype'              => '__VALUE__',
               //'used'                => $params['used'],
               'multiple'              => false,
               'rand'                  => $rand,
               'main_itemtype_link'    => $mainItemtype_link,
               'name'                  => 'item_to_link_id',
               'display'               => false,
               'myname'                => "add_items_id"];

         Ajax::updateItemOnSelectEvent("dropdown_$myname$rand", "results_$myname$rand",
                                       $CFG_GLPI["root_doc"].
                                          "/plugins/genericobject/ajax/dropdownTrackItemType.php",
                                       $p);
         echo "<span id='results_$myname$rand'>\n";
         echo "</span>";

         echo "</td><td class='center' width='30%'>";
         echo "<input type='submit' name='add' value=\""._sx('button', 'Add')."\" class='submit'>";
         echo "<input type='hidden' name='items_id' value='$instID'>";
         echo "<input type='hidden' name='type' value='$source'>";
         echo "<input type='hidden' name='itemtype_link' value='".get_called_class()."'>";
         echo "<input type='hidden' name='itemtype' value='".get_class($item)."'>";
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }

      echo "<div class='spaced'>";
      if ($canedit && $number) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = ['container' => 'mass'.__CLASS__.$rand];
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixehov'>";
      $header_begin  = "<tr>";
      $header_top    = '';
      $header_bottom = '';
      $header_end    = '';
      if ($canedit && $number) {
         $header_top    .= "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
         $header_top    .= "</th>";
         $header_bottom .= "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
         $header_bottom .= "</th>";
      }
      $header_end .= "<th>".__('Type')."</th>";
      $header_end .= "<th>".__('Name')."</th>";
      $header_end .= "<th>".__('Entity')."</th>";
      echo "<tr>";
      echo $header_begin.$header_top.$header_end;

      $totalnb = 0;
      foreach ($types as $itemtype) {

         if ($source) {
            $iterator = self::getAllItemLinkedBySource(get_called_class(), $instID, $itemtype, getForeignKeyFieldForItemType(get_class($item)));
         } else {
            $iterator = self::getAllItemLinkedByTarget($itemtype, $instID, get_class($item), getForeignKeyFieldForItemType($itemtype));
         }

         $nb = count($iterator);
         $prem = true;
         while ($data = $iterator->next()) {
            $name = $data["name"];
            if ($_SESSION["glpiis_ids_visible"]
                  || empty($data["name"])) {
               $name = sprintf(__('%1$s (%2$s)'), $name, $data["id"]);
            }
            if ((Session::getCurrentInterface() != 'helpdesk') && $item::canView()) {
               $link     = $itemtype::getFormURLWithID($data['id']);
               $namelink = "<a href=\"".$link."\">".$name."</a>";
            } else {
               $namelink = $name;
            }

            echo "<tr class='tab_bg_1'>";
            if ($canedit) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(get_called_class(), $data["linkid"]);
               echo "</td>";
            }

            $typename = $itemtype::getTypeName();
            echo "<td class='center top' >".$typename."</td>";

            echo "<td class='center".
                     (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">".$namelink."</td>";
            echo "<td class='center'>";
            echo Dropdown::getDropdownName("glpi_entities", $data['entities_id'])."</td>";
            echo "</tr>";
         }
         $totalnb += $nb;
      }

      if ($number) {
         echo $header_begin.$header_bottom.$header_end;
      }

      echo "</table>";
      if ($canedit && $number) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }
      echo "</div>";
   }

   function prepareInputForAdd($input) {
      return $input;
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
   **/
   static function showMassiveActionsSubForm(MassiveAction $ma) {
      switch ($ma->action) {
         case "plugin_genericobject_purge_link" :
               echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" .
                  _sx('button', 'Post') . "\" >";
            break;
         default :
            break;
      }
      return true;
   }

   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {
      $results = [
         'ok'       => 0,
         'ko'       => 0,
         'noright'  => 0,
         'messages' => []
      ];

      switch ($ma->action) {
         case "plugin_genericobject_purge_link" :
            foreach ($ma->items as $itemtype => $val) {
               foreach ($val as $key => $item_id) {
                  $item = new $itemtype;
                  $item->getFromDB($item_id);
                  $item->delete(["id" => $item_id]);
                  $results['ok']++;
               }
            }
            break;

         default :
            break;
      }
      $ma->results=$results;
   }


   /**
    *
    * Enter description here ...
    * @since 2.2.0
    */
   static function registerType() {
      Plugin::registerClass(get_called_class(), ['addtabon' => self::getLinkedItemTypes()]);
      Plugin::registerClass(get_called_class(), ['addtabon' => self::getItemType1()]);
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
         $itemtypes = self::getLinkedItemTypes();
         if (get_class($item) == self::getItemType1()) {
            $nb = countDistinctElementsInTable(getTableForItemType(self::getItemType1()), "id");
            return [1 => __("Objects management", "genericobject").' ('.$nb.')'];
         }

         if (in_array(get_class($item), $itemtypes)) {
            return [1 => __("Objects management", "genericobject")];
         }
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      self::showItems($item);
      return true;
   }

}
