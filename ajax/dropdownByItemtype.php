<?php
include ("../../../inc/includes.php");

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (isset($_REQUEST['objectToAdd'])) {
	$itemtype = $_REQUEST['objectToAdd'];
} else {
	$itemtype = $_REQUEST['criteria'];
}

if (get_parent_class($itemtype) == 'PluginGenericobjectObject') {

   $params = array();

   if (isset($_REQUEST['idMainobject'])) {
   	$itemtype_main = $_REQUEST['mainobject'];

   	$obj = new $itemtype_main();

      $nameMainObjectItem = $itemtype_main."_Item";
      $mainObjectItem = new $nameMainObjectItem();

      $column = str_replace('glpi_','',$obj->table."_id");

      $listeId = array();
      foreach ($mainObjectItem->find() as $record) {
         if ($record[$column] == $_REQUEST['idMainobject']) {
            $listeId[] = $record['items_id'];
         }
      }
      $params['used'] = $listeId;
   }

   if (isset($_REQUEST['value'])) {
   	$params['value'] = $_REQUEST["value"];
   }

   if (isset($_REQUEST['name'])) {
   	$params['name'] = $_REQUEST['name'];
   }


   $object = new $itemtype();
   $object->dropdown($params);
}
