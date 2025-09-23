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

/**
 * Class to display End of Life information for GenericObject
 */
class PluginGenericobjectEOLInfo extends CommonGLPI
{
    public static $rightname = 'config';

    /**
     * Get menu name
     *
     * @return string
     */
    public static function getMenuName()
    {
        return __('GenericObject EOL Info', 'genericobject');
    }

    /**
     * Get menu content
     *
     * @return array
     */
    public static function getMenuContent()
    {
        $menu = [];

        if (static::canView()) {
            $menu['title'] = static::getMenuName();
            $menu['page'] = '/plugins/genericobject/front/eol_info.php';
            $menu['icon'] = 'ti ti-alert-triangle';
            $menu['links']['search'] = '/plugins/genericobject/front/eol_info.php';
        }

        return $menu;
    }

    /**
     * Check if user can view EOL info
     *
     * @return bool
     */
    public static function canView(): bool
    {
        return Session::haveRight('config', READ);
    }

    /**
     * Get type name
     *
     * @param int $nb
     * @return string
     */
    public static function getTypeName($nb = 0)
    {
        return __('GenericObject End of Life Information', 'genericobject');
    }

    /**
     * Show EOL information form using Twig template
     *
     * @return void
     */
    public function showForm()
    {
        /** @var array $CFG_GLPI */
        global $CFG_GLPI;

        TemplateRenderer::getInstance()->display('@genericobject/eol_info.html.twig', [
            'plugin_version' => PLUGIN_GENERICOBJECT_VERSION,
            'plugin_web_dir' => $CFG_GLPI['root_doc'] . '/plugins/genericobject',
        ]);
    }

    /**
     * Display EOL warning on central dashboard using Twig template
     *
     * @return void
     */
    public static function displayCentralEOLWarning()
    {
        /** @var array $CFG_GLPI */
        global $CFG_GLPI;

        if (!static::canView()) {
            return;
        }

        $_SESSION['formcreator_eol_central_shown'] = true;

        TemplateRenderer::getInstance()->display('@genericobject/central_eol_warning.html.twig', [
            'plugin_version' => PLUGIN_GENERICOBJECT_VERSION,
            'root_doc' => $CFG_GLPI['root_doc'],
        ]);
    }
}
