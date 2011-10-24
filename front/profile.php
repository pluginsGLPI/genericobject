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
 
define('GLPI_ROOT', '../../..'); 
include (GLPI_ROOT."/inc/includes.php");
Session::checkRight("profile","r");


$prof = new PluginGenericobjectProfile();

/* save profile */
if (isset ($_POST['update_user_profile'])) {
   $prof->saveProfileToDB($_POST);
   PluginGenericobjectProfile::changeProfile();
} elseif (isset($_POST['update_all_rights']) && isset($_POST['profiles'])) {
   foreach ($_POST['profiles'] as $id => $values) {
      $values['id'] = $id;
      $prof->update($values);
   }
   PluginGenericobjectProfile::changeProfile();
}
Html::redirect($_SERVER['HTTP_REFERER']);
