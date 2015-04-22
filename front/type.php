<?php
/*
 This file is part of the genericobject plugin.

 Genericobject plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Genericobject plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Genericobject. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   genericobject
 @author    the genericobject plugin team
 @copyright Copyright (c) 2010-2011 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/genericobject
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

include ("../../../inc/includes.php");

if (isset($_GET['itemtype'])
        && !isset($_GET['search'])
        && !isset($_GET['sort'])) {
   $type = new PluginGenericobjectType();
   $type->getFromDBByType($_GET['itemtype']);
   Html::redirect(Toolbox::getItemTypeFormURL('PluginGenericobjectType').'?id='.$type->getID());
} else {
   Html::header(__("Type of objects", "genericobject"), $_SERVER['PHP_SELF'], "config",
                "PluginGenericobjectType");
   Search::Show('PluginGenericobjectType');
   Html::footer();
}
