<?php

include ("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

//Session::checkRight("software", UPDATE);

$itemtype = $_REQUEST['objectToAdd'];

$object = new $itemtype();
PluginGenericobjectObject_Item::getDropdownItemLinked($object, $_REQUEST['mainobject'], $_REQUEST['idMainobject']);
