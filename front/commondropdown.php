<?php

include ("../../../inc/includes.php"); 

if ( isset($_REQUEST['itemtype']) ) {
   $itemtype = $_REQUEST['itemtype'];
   if (class_exists($itemtype)) {
      $dropdown = new $itemtype();
      include (GLPI_ROOT . "/front/dropdown.common.php");
   } else {
      Html::displayErrorAndDie(__('The requested dropdown does not exists', 'genericobject'));
   }
} else {

   Html::displayErrorAndDie(__('Not Found!'));
}


