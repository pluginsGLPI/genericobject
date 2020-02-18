<?php

include('../../../inc/includes.php');

if (! isset($_POST['objectToAdd']) || empty($_POST['objectToAdd'])) {
	Session::addMessageAfterRedirect(__('Error'), true, ERROR, true);
   Html::back();
   exit(); 
}

$idMainObject 		= $_POST['items_id'];
$nameMainObject 	= $_POST['mainobject'];
$nameObjectToAdd 	= $_POST['objectToAdd'];
//$IdToAdd 			= $_POST['items_id'];

$objectToAdd 	= new $nameObjectToAdd();
$coluimn1 = str_replace('glpi_','',$objectToAdd->table.'_id');

if (! isset($_POST[$coluimn1]) || empty($_POST[$coluimn1])) {
	Session::addMessageAfterRedirect(__('Error'), true, ERROR, true);
} else {

	$IdToAdd 			= $_POST[$coluimn1];

	$mainObject 	= new $nameMainObject;
	$mainObject->getFromDB(1); //useless ?
	$coluimn = str_replace('glpi_','',$mainObject->table.'_id');

	$nameMainObject 	= $nameMainObject.'_item';
	$nameObjectToAdd 	= $nameObjectToAdd.'_item';

	$mainObjectItem 		= new $nameMainObject();
	$mainObjectToAddItem = new $nameObjectToAdd();

	//id de l'objet rajouté
	$objectToAdd->getFromDB($IdToAdd);
	$idObjectToAdd = $objectToAdd->fields['id'];

	// Probably SQL injection
	$res = $mainObjectItem->getFromDBByCrit(array(
		'items_id' => $idObjectToAdd,
		$coluimn => $idMainObject,
		'itemtype' => $_POST['objectToAdd']));
	if ($res) {
		Session::addMessageAfterRedirect(__('This Object is already link','genericobject'),
	                                 true, ERROR, true);
	} else {
		$values = array();
		$values['items_id'] = $idObjectToAdd;
		$values[$coluimn] = $idMainObject;
		$values['itemtype'] = $_POST['objectToAdd'];

		$mainObjectItem->fields = $values;
		$mainObjectItem->addToDB();

		$values = array();
		$values['items_id'] = $idMainObject;
		$values[$coluimn1] = $idObjectToAdd;
		$values['itemtype'] = $_POST['mainobject'];

		$mainObjectToAddItem->fields = $values;
		$mainObjectToAddItem->addToDB($values);
	}
}

Html::back();
<?php

include('../../../inc/includes.php');

if (! isset($_POST['objectToAdd']) || empty($_POST['objectToAdd'])) {
	Session::addMessageAfterRedirect(__('Error'), true, ERROR, true);
   Html::back();
   exit(); 
}

$idMainObject 		= $_POST['items_id'];
$nameMainObject 	= $_POST['mainobject'];
$nameObjectToAdd 	= $_POST['objectToAdd'];
//$IdToAdd 			= $_POST['items_id'];

$objectToAdd 	= new $nameObjectToAdd();
$coluimn1 = str_replace('glpi_','',$objectToAdd->table.'_id');

if (! isset($_POST[$coluimn1]) || empty($_POST[$coluimn1])) {
	Session::addMessageAfterRedirect(__('Error'), true, ERROR, true);
} else {

	$IdToAdd 			= $_POST[$coluimn1];

	$mainObject 	= new $nameMainObject;
	$mainObject->getFromDB(1); //useless ?
	$coluimn = str_replace('glpi_','',$mainObject->table.'_id');

	$nameMainObject 	= $nameMainObject.'_item';
	$nameObjectToAdd 	= $nameObjectToAdd.'_item';

	$mainObjectItem 		= new $nameMainObject();
	$mainObjectToAddItem = new $nameObjectToAdd();

	//id de l'objet rajouté
	$objectToAdd->getFromDB($IdToAdd);
	$idObjectToAdd = $objectToAdd->fields['id'];

	// Probably SQL injection
	$res = $mainObjectItem->getFromDBByCrit(array(
		'items_id' => $idObjectToAdd,
		$coluimn => $idMainObject,
		'itemtype' => $_POST['objectToAdd']));
	if ($res) {
		Session::addMessageAfterRedirect(__('This Object is already link','genericobject'),
	                                 true, ERROR, true);
	} else {
		$values = array();
		$values['items_id'] = $idObjectToAdd;
		$values[$coluimn] = $idMainObject;
		$values['itemtype'] = $_POST['objectToAdd'];

		$mainObjectItem->fields = $values;
		$mainObjectItem->addToDB();

		$values = array();
		$values['items_id'] = $idMainObject;
		$values[$coluimn1] = $idObjectToAdd;
		$values['itemtype'] = $_POST['mainobject'];

		$mainObjectToAddItem->fields = $values;
		$mainObjectToAddItem->addToDB($values);
	}
}

Html::back();
