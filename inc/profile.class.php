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

class PluginGenericobjectProfile extends Profile
{
   /* if profile deleted */
    public function cleanProfiles($id)
    {
        $this->deleteByCriteria(['id' => $id]);
    }

    public static function getProfileNameForItemtype($itemtype)
    {
        return preg_replace("/^glpi_/", "", getTableForItemType($itemtype));
    }

    public static function install(Migration $migration)
    {
        /** @var DBmysql $DB */
        global $DB;

        $profileRight = new ProfileRight();
        $profile      = new Profile();

       //Update needed
        if ($DB->tableExists('glpi_plugin_genericobject_profiles')) {
            foreach (getAllDataFromTable('glpi_plugin_genericobject_profiles') as $right) {
                if (preg_match("/PluginGenericobject(.*)/", $right['itemtype'], $results)) {
                    $newrightname = 'plugin_genericobject_' . strtolower($results[1]) . 's';
                    if (
                        !countElementsInTable(
                            'glpi_profilerights',
                            ['profiles_id' => $right['profiles_id'],
                                'name' => $newrightname
                            ]
                        )
                    ) {
                        switch ($right['right']) {
                            case null:
                            case '':
                            default:
                                $rightvalue = 0;
                                break;
                            case 'r':
                                $rightvalue = READ;
                                break;
                            case 'w':
                                $rightvalue = ALLSTANDARDRIGHT;
                                break;
                        }

                        $profileRight->add(['profiles_id' => $right['profiles_id'],
                            'name'        => $newrightname,
                            'rights'      => $rightvalue
                        ]);

                        if (
                            !countElementsInTable(
                                'glpi_profilerights',
                                ['profiles_id' => $right['profiles_id'],
                                    'name'        => 'plugin_genericobject_types'
                                ]
                            )
                        ) {
                            $profileRight->add(['profiles_id' => $right['profiles_id'],
                                'name'        => 'plugin_genericobject_types',
                                'rights'      => 23
                            ]);
                        }
                    }

                    if ($right['open_ticket']) {
                        $profile->getFromDB($right['profiles_id']);
                        $helpdesk_item_types = json_decode($profile->fields['helpdesk_item_type'], true);
                        if (is_array($helpdesk_item_types)) {
                            if (!in_array($right['itemtype'], $helpdesk_item_types)) {
                                $helpdesk_item_types[] = $right['itemtype'];
                            }
                        } else {
                             $helpdesk_item_types = [$right['itemtype']];
                        }

                        $tmp['id'] = $profile->getID();
                        $tmp['helpdesk_item_type'] = json_encode($helpdesk_item_types);
                        $profile->update($tmp);
                    }
                }
            }
           //$migration->dropTable('glpi_plugin_genericobject_profiles');
        }
    }

    public static function uninstall()
    {
        /** @var DBmysql $DB */
        global $DB;
        $DB->dropTable('glpi_profilerights');
    }
}
