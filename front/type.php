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

include ("../../../inc/includes.php");

if (isset($_GET['itemtype'])
        && !isset($_GET['search'])
        && !isset($_GET['sort'])) {
   $type = new PluginGenericobjectType();
   $type->getFromDBByType($_GET['itemtype']);
   Html::redirect(Toolbox::getItemTypeFormURL('PluginGenericobjectType').'?id='.$type->getID());

} else if (Session::haveRightsOr('plugin_genericobject_types', [READ, CREATE, UPDATE, PURGE])) {
   Html::header(__("Type of objects", "genericobject"), $_SERVER['PHP_SELF'], "config",
                "PluginGenericobjectType");
   Search::Show('PluginGenericobjectType');
   Html::footer();

} else {
   Html::redirect($CFG_GLPI['root_doc']."/front/central.php");
}
