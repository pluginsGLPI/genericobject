<?php

/**
 * -------------------------------------------------------------------------
 * GenericObject plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GenericObject.
 *
 * GenericObject is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * GenericObject is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GenericObject. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2009-2023 by GenericObject plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/genericobject
 * -------------------------------------------------------------------------
 */

use Glpi\Application\View\TemplateRenderer;
use Glpi\Asset\AssetDefinition;

// Check if user has admin rights
Session::checkRight('config', UPDATE);

/** @var array $CFG_GLPI */
/** @var DBmysql $DB */
global $CFG_GLPI, $DB;

// Handle rename POST action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type_id'], $_POST['new_name'])) {
    $type_id  = (int) $_POST['type_id'];
    $new_name = (string) $_POST['new_name'];
    PluginGenericobjectType::renameType($type_id, $new_name);
    Html::redirect($CFG_GLPI['root_doc'] . '/plugins/genericobject/front/migration_status.php');
}

// Get all GenericObject types
$genericobject_types = [];
if ($DB->tableExists(PluginGenericobjectType::getTable())) {
    $query = [
        'SELECT' => ['id', 'name'],
        'FROM'   => PluginGenericobjectType::getTable(),
    ];
    $request = $DB->request($query);
    foreach ($request as $data) {
        $genericobject_types[$data['name']] = $data;
    }
}

// Get all custom asset definitions
$customassets = [];
if ($DB->tableExists(AssetDefinition::getTable())) {
    $query = [
        'SELECT' => ['id', 'system_name', 'label', 'icon'],
        'FROM'   => AssetDefinition::getTable(),
    ];
    $request = $DB->request($query);
    foreach ($request as $data) {
        // If genericobject asset is migrated to native custom asset, count linked items
        $customassets[$data['system_name']] = $data;
        $customassets[$data['system_name']]['items'] = countElementsInTable('glpi_assets_assets', ['assets_assetdefinitions_id' => $data['id']]);
    }
}

// Display GLPI header
Html::header(__s('GenericObject Migration Status', 'genericobject'), '', "tools", "migration");

// Render the template content
TemplateRenderer::getInstance()->display('@genericobject/migration_status.html.twig', [
    'genericobject_types'  => $genericobject_types,
    'customassets'         => $customassets,
    'reserved_names'       => array_map('strtolower', PluginGenericobjectType::getReservedNames()),
]);

// Display GLPI footer
Html::footer();
