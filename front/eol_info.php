<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator v3.0.0 - End of Life Information Page
 * ---------------------------------------------------------------------
 * This page displays End of Life information and migration guidance.
 * ---------------------------------------------------------------------
 */

// Check if user has admin rights
Session::checkRight('config', READ);

/** @var array $CFG_GLPI */
global $CFG_GLPI;

Html::header(
   __('Genericobject End of Life Information', 'genericobject'), 
   $_SERVER['PHP_SELF'], 
   'tools', 
   'PluginGenericobjectEOLInfo'
);

$eolInfo = new PluginGenericobjectEOLInfo();
$eolInfo->showForm();

Html::footer();
