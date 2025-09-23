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

/** @var DBmysql $DB */
global $DB;

// Get all GenericObject types
$genericobject_types = [];
if ($DB->tableExists(PluginGenericobjectType::getTable())) {
    $query = [
        'SELECT' => ['itemtype', 'name'],
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
