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

global $GO_FIELDS;

$GO_FIELDS['id']['name']          = __("ID");
$GO_FIELDS['id']['input_type']    = 'text';
$GO_FIELDS['id']['massiveaction'] = false;

$GO_FIELDS['name']['name']       = __("Name");
$GO_FIELDS['name']['field']      = 'name';
$GO_FIELDS['name']['input_type'] = 'text';
$GO_FIELDS['name']['autoname']   = true;

$GO_FIELDS['serial']['name']       = __("Serial number");
$GO_FIELDS['serial']['field']      = 'serial';
$GO_FIELDS['serial']['input_type'] = 'text';

$GO_FIELDS['otherserial']['name']       = __("Inventory number");
$GO_FIELDS['otherserial']['field']      = 'otherserial';
$GO_FIELDS['otherserial']['input_type'] = 'text';
$GO_FIELDS['otherserial']['autoname']   = true;

$GO_FIELDS['comment']['name']       = __("Comments");
$GO_FIELDS['comment']['field']      = 'comment';
$GO_FIELDS['comment']['input_type'] = 'multitext';

$GO_FIELDS['other']['name']         = __("Others");
$GO_FIELDS['other']['input_type']   = 'text';

$GO_FIELDS['creationdate']['name']       = __("Creation date");
$GO_FIELDS['creationdate']['input_type'] = 'date';

$GO_FIELDS['expirationdate']['name']       = __("Expiration date");
$GO_FIELDS['expirationdate']['input_type'] = 'date';

$GO_FIELDS['date_mod']['name']       = __("Last update");
$GO_FIELDS['date_mod']['input_type'] = 'datetime';

$GO_FIELDS['date_creation']['name']       = __('Creation date');
$GO_FIELDS['date_creation']['input_type'] = 'datetime';

$GO_FIELDS['url']['name']       = __("URL");
$GO_FIELDS['url']['field']      = 'url';
$GO_FIELDS['url']['input_type'] = 'text';
$GO_FIELDS['url']['datatype']   = 'weblink';

$GO_FIELDS['types_id']['name']          = __("Type");
$GO_FIELDS['types_id']['linkfield']     = 'type';
$GO_FIELDS['types_id']['input_type']    = 'dropdown';
// The 'isolated' dropdown type will create a isolated table for each type that will be assigned
// with this field.
$GO_FIELDS['types_id']['dropdown_type'] = 'isolated';

$GO_FIELDS['models_id']['name']          = __("Model");
$GO_FIELDS['models_id']['input_type']    = 'dropdown';
$GO_FIELDS['models_id']['dropdown_type'] = 'isolated';

$GO_FIELDS['categories_id']['name']          = __("Category");
$GO_FIELDS['categories_id']['input_type']    = 'dropdown';
$GO_FIELDS['categories_id']['dropdown_type'] = 'isolated';

$GO_FIELDS['entities_id']['name']          = __("Entity");
$GO_FIELDS['entities_id']['input_type']    = 'dropdown';
$GO_FIELDS['entities_id']['massiveaction'] = false;

$GO_FIELDS['template_name']['name']          = __("Template name");
$GO_FIELDS['template_name']['input_type']    = 'text';
$GO_FIELDS['template_name']['massiveaction'] = false;

$GO_FIELDS['notepad']['name']       = _n('Note', 'Notes', 2);
$GO_FIELDS['notepad']['input_type'] = 'multitext';

$GO_FIELDS['is_recursive']['name']       = __("Child entities");
$GO_FIELDS['is_recursive']['input_type'] = 'bool';

$GO_FIELDS['is_deleted']['name']          = __("Item in the dustbin");
$GO_FIELDS['is_deleted']['input_type']    = 'bool';
$GO_FIELDS['is_deleted']['massiveaction'] = false;

$GO_FIELDS['is_template']['name']          = __("Templates");
$GO_FIELDS['is_template']['input_type']    = 'bool';
$GO_FIELDS['is_template']['massiveaction'] = false;

$GO_FIELDS['is_global']['name']          = __("Management type");
$GO_FIELDS['is_global']['input_type']    = 'bool';
$GO_FIELDS['is_global']['massiveaction'] = false;

$GO_FIELDS['is_helpdesk_visible']['name']       = __("Associable to a ticket");
$GO_FIELDS['is_helpdesk_visible']['input_type'] = 'bool';

$GO_FIELDS['ticket_tco']['name']       = __("TCO");
$GO_FIELDS['ticket_tco']['input_type'] = 'decimal';

$GO_FIELDS['locations_id']['name']       = __("Item location");
$GO_FIELDS['locations_id']['input_type'] = 'dropdown';

$GO_FIELDS['states_id']['name']       = __("Status");
$GO_FIELDS['states_id']['input_type'] = 'dropdown';

$GO_FIELDS['users_id']['name']       = __("User");
$GO_FIELDS['users_id']['input_type'] = 'dropdown';

$GO_FIELDS['groups_id']['name']       = __("Group");
$GO_FIELDS['groups_id']['input_type'] = 'dropdown';
$GO_FIELDS['groups_id']['condition']  = ['is_itemgroup' => 1];

$GO_FIELDS['manufacturers_id']['name']       = __("Manufacturer");
$GO_FIELDS['manufacturers_id']['input_type'] = 'dropdown';

$GO_FIELDS['users_id_tech']['name']       = __("Technician in charge");
$GO_FIELDS['users_id_tech']['input_type'] = 'dropdown';

$GO_FIELDS['domains_id']['name']       = __("Domain");
$GO_FIELDS['domains_id']['input_type'] = 'dropdown';

$GO_FIELDS['contact']['name']       = __("Alternate username");
$GO_FIELDS['contact']['input_type'] = 'text';

$GO_FIELDS['contact_num']['name']       = __("Alternate username number");
$GO_FIELDS['contact_num']['input_type'] = 'text';

$GO_FIELDS['groups_id_tech']['name']       = __("Group in charge");
$GO_FIELDS['groups_id_tech']['input_type'] = 'dropdown';
$GO_FIELDS['groups_id_tech']['condition']  = ['is_assign' => 1];
