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

define('PLUGIN_GENERICOBJECT_VERSION', '3.0.0');

// Minimal GLPI version, inclusive
define("PLUGIN_GENERICOBJECT_MIN_GLPI", "11.0.0");
// Maximum GLPI version, exclusive
define("PLUGIN_GENERICOBJECT_MAX_GLPI", "11.0.99");

if (!defined("GENERICOBJECT_DIR")) {
    define("GENERICOBJECT_DIR", Plugin::getPhpDir("genericobject"));
}

if (!defined("GENERICOBJECT_DOC_DIR")) {
    define("GENERICOBJECT_DOC_DIR", GLPI_PLUGIN_DOC_DIR . "/genericobject");
    if (!file_exists(GENERICOBJECT_DOC_DIR)) {
        mkdir(GENERICOBJECT_DOC_DIR);
    }
}
if (!defined("GENERICOBJECT_FRONT_PATH")) {
    define("GENERICOBJECT_FRONT_PATH", GENERICOBJECT_DOC_DIR . "/front");
    if (!file_exists(GENERICOBJECT_FRONT_PATH)) {
        mkdir(GENERICOBJECT_FRONT_PATH);
    }
}
if (!defined("GENERICOBJECT_AJAX_PATH")) {
    define("GENERICOBJECT_AJAX_PATH", GENERICOBJECT_DOC_DIR . "/ajax");
    if (!file_exists(GENERICOBJECT_AJAX_PATH)) {
        mkdir(GENERICOBJECT_AJAX_PATH);
    }
}

if (!defined("GENERICOBJECT_CLASS_PATH")) {
    define("GENERICOBJECT_CLASS_PATH", GENERICOBJECT_DOC_DIR . "/inc");
    if (!file_exists(GENERICOBJECT_CLASS_PATH)) {
        mkdir(GENERICOBJECT_CLASS_PATH);
    }
}

if (!defined("GENERICOBJECT_LOCALES_PATH")) {
    define("GENERICOBJECT_LOCALES_PATH", GENERICOBJECT_DOC_DIR . "/locales");
    if (!file_exists(GENERICOBJECT_LOCALES_PATH)) {
        mkdir(GENERICOBJECT_LOCALES_PATH);
    }
}

if (!defined("GENERICOBJECT_FIELDS_PATH")) {
    define("GENERICOBJECT_FIELDS_PATH", GENERICOBJECT_DOC_DIR . "/fields");
    if (!file_exists(GENERICOBJECT_FIELDS_PATH)) {
        mkdir(GENERICOBJECT_FIELDS_PATH);
    }
}

if (!defined("GENERICOBJECT_PICS_PATH")) {
    define("GENERICOBJECT_PICS_PATH", GENERICOBJECT_DOC_DIR . "/pics");
    if (!file_exists(GENERICOBJECT_PICS_PATH)) {
        mkdir(GENERICOBJECT_PICS_PATH);
    }
}

// Autoload class generated in files/_plugins/genericobject/inc/
include_once(GENERICOBJECT_DIR . "/inc/autoload.php");
include_once(GENERICOBJECT_DIR . "/inc/functions.php");
if (file_exists(GENERICOBJECT_DIR . "/log_filter.settings.php")) {
    include_once(GENERICOBJECT_DIR . "/log_filter.settings.php");
}

$go_autoloader = new PluginGenericobjectAutoloader([
    GENERICOBJECT_CLASS_PATH
]);
$go_autoloader->register();

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_genericobject()
{
    /**
     * @var array $PLUGIN_HOOKS
     * @var array $GO_BLACKLIST_FIELDS
     * @var array $GENERICOBJECT_PDF_TYPES
     * @var array $GO_LINKED_TYPES
     * @var array $GO_READONLY_FIELDS
     * @var array $CFG_GLPI
     */
    global $PLUGIN_HOOKS, $GO_BLACKLIST_FIELDS,
          $GENERICOBJECT_PDF_TYPES, $GO_LINKED_TYPES, $GO_READONLY_FIELDS, $CFG_GLPI;

    $GO_READONLY_FIELDS  =  ["is_helpdesk_visible", "comment", "ticket_tco"];

    $GO_BLACKLIST_FIELDS =  ["itemtype", "table", "is_deleted", "id", "entities_id",
        "is_recursive", "is_template", "notepad", "template_name",
        "date_mod", "name", "is_helpdesk_visible", "comment",
        "date_creation", "ticket_tco"
    ];

    $GO_LINKED_TYPES     =  ['Computer', 'Phone', 'Peripheral', 'Software', 'Monitor',
        'Printer', 'NetworkEquipment'
    ];

    $PLUGIN_HOOKS['csrf_compliant']['genericobject'] = true;
    $GENERICOBJECT_PDF_TYPES                         =  [];

    if (Plugin::isPluginActive("genericobject") && isset($_SESSION['glpiactiveprofile'])) {

       // Config page
        if (Session::haveRight('config', READ)) {
            $PLUGIN_HOOKS['config_page']['genericobject'] = 'front/eol_info.php';
        }
    }
}

/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_genericobject()
{
    return [
        'name'           => __("Objects management (Migration Only)", "genericobject"),
        'version'        => PLUGIN_GENERICOBJECT_VERSION,
        'author'         => "<a href=\"mailto:contact@teclib.com\">Teclib'</a> & siprossii",
        'homepage'       => 'https://github.com/pluginsGLPI/genericobject',
        'license'        => 'GPLv2+',
        'requirements'   => [
            'glpi' => [
                'min' => PLUGIN_GENERICOBJECT_MIN_GLPI,
                'max' => PLUGIN_GENERICOBJECT_MAX_GLPI,
                'dev' => true, //Required to allow 9.2-dev
            ]
        ]
    ];
}

