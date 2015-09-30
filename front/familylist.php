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

include ('../../../inc/includes.php');

$family = new PluginGenericobjectTypeFamily();

if (!isset($_GET['id']) || !$family->getFromDB($_GET['id'])) {
	Html::header(__("Objects management", "genericobject"), $_SERVER['PHP_SELF'], "assets",
	                "genericobject");

	echo "<table class='tab_cadre_fixe'>";
    echo "<tr class='tab_bg_2'><th>".__("Empty family","genericobject")."</th></tr>";
	echo "</table>";
} else {
	$family->getFromDB($_GET['id']);
	Html::header(__("Objects management", "genericobject"), $_SERVER['PHP_SELF'], "assets",
	                $family->getName());

	echo "<table class='tab_cadre_fixe'>";
	$types = PluginGenericobjectTypeFamily::getItemtypesByFamily($_GET['id']);
    echo "<tr class='tab_bg_2'><th>".Dropdown::getDropdownName("glpi_plugin_genericobject_typefamilies", $_GET['id'])."</th></tr>";
	foreach ($types as $type) {
		$itemtype = $type['itemtype'];
        echo "<tr class='tab_bg_1'><td align='center'>";
        echo "<a href='".$itemtype::getSearchURL()."'>";
        echo $itemtype::getTypeName();
        echo "</a></td></tr>";
    }
	echo "</table>";
}

Html::footer();
