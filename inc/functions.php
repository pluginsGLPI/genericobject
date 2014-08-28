<?php

/*
 * return the name of a dropdown type
 * This shared by the following classes :
 *    - PluginGenericobjectCommonDropdown
 *    - PluginGenericobjectCommonTreeDropdown
 */
function dropdown_getTypeName($class,$nb=0) {
      global $GO_FIELDS;
      $fk = getForeignKeyFieldForTable(getTableForItemType($class));
      $instance = new $class();
      $options = PluginGenericobjectField::getFieldOptions($fk, $instance->linked_itemtype);
      Toolbox::logDebug($fk, "\n", $options);
      $dropdown_type = isset($options['dropdown_type'])
         ? $options['dropdown_type']
         : null;
      $label = $options['name'];
      if (!is_null($dropdown_type) and $dropdown_type==='isolated') {
         $linked_itemtype_object = new $instance->linked_itemtype();
         $label .= " (" . __($linked_itemtype_object::getTypeName(), 'genericobject') . ")";
      }
      if($label != '') {
         return $label;
      } else {
         return $class;
      }
}
