<?php
/**
 * ---------------------------------------------------------------------
 * Formcreator v3.0.0 - Migration Status Interface
 * ---------------------------------------------------------------------
 * This page provides administrators with migration status and tools
 * for migrating from Formcreator to GLPI 11 native forms.
 * ---------------------------------------------------------------------
 */

include('../../../inc/includes.php');

use Glpi\Application\View\TemplateRenderer;
use Glpi\Asset\AssetDefinition;

// Check if user has admin rights
Session::checkRight('config', UPDATE);

/** @var \DBmysql $DB */
global $DB;

// Collect basic statistics - simple and reliable
$genericobject_types = [];
if ($DB->tableExists(PluginGenericobjectType::getTable())) {
    $query = [
        'SELECT' => ['itemtype', 'name'],
        'FROM'   => PluginGenericobjectType::getTable(),
    ];
    $request = $DB->request($query);
    foreach($request as $data) {
        $genericobject_types[$data['name']] = $data;
    }

}

$customassets = [];
if ($DB->tableExists(AssetDefinition::getTable())) {
    $query = [
        'SELECT' => ['id', 'system_name', 'label', 'icon'],
        'FROM'   => AssetDefinition::getTable(),
    ];
    $request = $DB->request($query);
    foreach($request as $data) {
        $customassets[$data['system_name']] = $data;
        $customassets[$data['system_name']]['items'] = 0;
        if ($DB->tableExists('glpi_forms_forms')) {
            $customassets[$data['system_name']]['items'] = countElementsInTable('glpi_assets_assets', ['assets_assetdefinitions_id' => $data['id']]);
        }
    }
}

// Display GLPI header
Html::header(__('GenericObject Migration Status', 'genericobject'), '', "tools", "migration");

// Render the template content
TemplateRenderer::getInstance()->display('@genericobject/migration_status.html.twig', [
    'genericobject_types' => $genericobject_types,
    'customassets'        => $customassets,
]);

// Display GLPI footer
Html::footer();
