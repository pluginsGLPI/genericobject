<?php

class %%CLASSNAME%%_ItemInjection extends %%CLASSNAME%%_Item
                                                implements PluginDatainjectionInjectionInterface {

   static function getTable() {
      $parenttype = get_parent_class();
      return $parenttype::getTable();
   }

   static function getTypeName($nb=0) {
      $str  = _n('Linked object', 'Linked objects', Session::getPluralNumber(), 'genericobject');
      $str .= " ".__("to")." ".%%CLASSNAME%%::getTypeName($nb);
      return $str;
   }

   function isPrimaryType() {
      return false;
   }

   function connectedTo() {
      $parenttype = get_parent_class();
      return $parenttype::getLinkedItemTypes();
   }

   function getOptions($primary_type='') {

      // Import bind by name :

      $tab[110]['table']        = getTableForItemType('%%CLASSNAME%%');
      $tab[110]['field']        = 'name';
      $tab[110]['linkfield']    = 'name';
      $tab[110]['name']         = __('Name');
      $tab[110]['injectable']   = true;
      $tab[110]['displaytype']  = 'dropdown';
      $tab[110]['checktype']    = 'text';

      if (! empty($primary_type)) {
         $foreign = getForeignKeyFieldForTable(getTableForItemType($primary_type));
         $tab[110]['storevaluein'] = $foreign;
      }

      return $tab;
   }

   function lastCheck($values) {

      $parenttype = get_parent_class();

      unset($values[$parenttype]['id']);
      unset($values[$parenttype]['name']);

      foreach ($values[$parenttype] as $val) {
         if (empty($val)) {
            return false;
         }
      }

      // Check if bind exist in database

      // Get $key
      foreach ($values as $key => $val) {
         break;
      }

      $itemtype = $key.'_Item';

      $itemtype2 = $parenttype::getItemType1();

      $foreign = getForeignKeyFieldForTable(getTableForItemType($key));

      $item = new $itemtype();
      $item->fields = array('items_id' => $values[$parenttype][$foreign],
                           $foreign => $values[$parenttype]['items_id'],
                           'itemtype' => $itemtype2);
      $exist = $item->getFromDBByQuery("WHERE `items_id` = ".$values[$parenttype][$foreign]." AND `".$foreign."` = ".$values[$parenttype]['items_id']." AND `itemtype` = '".$itemtype2."'");

      return ! $exist;
   }

   /**
    * @param $values
    * @param $add                (true by default)
    * @param $rights    array
    */
   function processAfterInsertOrUpdate($values, $add=true, $rights=array()) {

      //Note : this function is called for each CSV line

      $parenttype = get_parent_class();

      unset($values[$parenttype]['id']);
      unset($values[$parenttype]['name']);

      foreach ($values[$parenttype] as $val) {
         if (empty($val)) {
            var_dump("empty");
            return ;
         }
      }

      //Add bind :

      // Get $key
      foreach ($values as $key => $val) {
         break;
      }

      $itemtype = $key.'_Item';

      $itemtype2 = $parenttype::getItemType1();

      $foreign = getForeignKeyFieldForTable(getTableForItemType($key));

      $item = new $itemtype();
      $item->fields = array('items_id' => $values[$parenttype][$foreign],
                           $foreign => $values[$parenttype]['items_id'],
                           'itemtype' => $itemtype2);
      $bind_added = $item->addToDB();
      
      if ($bind_added) {
         //Add other bind :

         $foreign2 = getForeignKeyFieldForTable(getTableForItemType($itemtype2));

         $item = new $parenttype();
         $item->fields = array('items_id' => $values[$parenttype]['items_id'],
                              $foreign2 => $values[$parenttype][$foreign],
                              'itemtype' => $values[$parenttype]['itemtype']);
         $item->addToDB();
      }

   }

   /**
    * @see plugins/datainjection/inc/PluginDatainjectionInjectionInterface::addOrUpdateObject()
   **/
   function addOrUpdateObject($values=array(), $options=array()) {

      $lib = new PluginDatainjectionCommonInjectionLib($this, $values, $options);
      $lib->processAddOrUpdate();
      $results = $lib->getInjectionResults();

      return $results;
   }

   /**
    * @param $primary_type
    * @param $values
   **/
   function addSpecificNeededFields($primary_type, $values) {

      $fields['items_id'] = $values[$primary_type]['id'];
      $fields['itemtype'] = $primary_type;
      return $fields;
   }

}
