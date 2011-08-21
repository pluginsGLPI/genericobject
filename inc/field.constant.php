<?php

/*
 ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2008 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org/
 ----------------------------------------------------------------------

 LICENSE

	This file is part of GLPI.

    GLPI is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    GLPI is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with GLPI; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 ------------------------------------------------------------------------
*/

// Original Author of file: Walid Nouh
// Purpose of file:
// ----------------------------------------------------------------------
global $GENERICOBJECT_AVAILABLE_FIELDS, $LANG;

$GENERICOBJECT_AVAILABLE_FIELDS['id']['name']       = 'ID';
$GENERICOBJECT_AVAILABLE_FIELDS['id']['field']      = 'id';
$GENERICOBJECT_AVAILABLE_FIELDS['id']['input_type'] = 'text';
$GENERICOBJECT_AVAILABLE_FIELDS['id']['massiveaction'] = false;

$GENERICOBJECT_AVAILABLE_FIELDS['name']['name']       = $LANG['common'][16];
$GENERICOBJECT_AVAILABLE_FIELDS['name']['field']      = 'name';
$GENERICOBJECT_AVAILABLE_FIELDS['name']['input_type'] = 'text';
$GENERICOBJECT_AVAILABLE_FIELDS['name']['datatype']   = 'itemlink';

$GENERICOBJECT_AVAILABLE_FIELDS['serial']['name']       = $LANG['common'][19];
$GENERICOBJECT_AVAILABLE_FIELDS['serial']['field']      = 'serial';
$GENERICOBJECT_AVAILABLE_FIELDS['serial']['input_type'] = 'text';

$GENERICOBJECT_AVAILABLE_FIELDS['otherserial']['name']       = $LANG['common'][20];
$GENERICOBJECT_AVAILABLE_FIELDS['otherserial']['field']      = 'otherserial';
$GENERICOBJECT_AVAILABLE_FIELDS['otherserial']['input_type'] = 'text';

$GENERICOBJECT_AVAILABLE_FIELDS['comment']['name']       = $LANG['common'][25];
$GENERICOBJECT_AVAILABLE_FIELDS['comment']['field']      = 'comment';
$GENERICOBJECT_AVAILABLE_FIELDS['comment']['input_type'] = 'multitext';

$GENERICOBJECT_AVAILABLE_FIELDS['other']['name']         = $LANG['common'][62];
$GENERICOBJECT_AVAILABLE_FIELDS['other']['field']        = 'other';
$GENERICOBJECT_AVAILABLE_FIELDS['other']['input_type']   = 'text';

$GENERICOBJECT_AVAILABLE_FIELDS['creationdate']['name']       = $LANG['genericobject']['fields'][30];
$GENERICOBJECT_AVAILABLE_FIELDS['creationdate']['field']      = 'creationdate';
$GENERICOBJECT_AVAILABLE_FIELDS['creationdate']['input_type'] = 'date';
$GENERICOBJECT_AVAILABLE_FIELDS['creationdate']['datatype']   = 'date';

$GENERICOBJECT_AVAILABLE_FIELDS['expirationdate']['name']       = $LANG['genericobject']['fields'][31];
$GENERICOBJECT_AVAILABLE_FIELDS['expirationdate']['field']      = 'expirationdate';
$GENERICOBJECT_AVAILABLE_FIELDS['expirationdate']['input_type'] = 'date';
$GENERICOBJECT_AVAILABLE_FIELDS['expirationdate']['datatype']   = 'date';

$GENERICOBJECT_AVAILABLE_FIELDS['url']['name']       = $LANG['genericobject']['fields'][10];
$GENERICOBJECT_AVAILABLE_FIELDS['url']['field']      = 'url';
$GENERICOBJECT_AVAILABLE_FIELDS['url']['input_type'] = 'text';
$GENERICOBJECT_AVAILABLE_FIELDS['url']['datatype']   = 'weblink';

$GENERICOBJECT_AVAILABLE_FIELDS['types_id']['name']          = $LANG['common'][17];
$GENERICOBJECT_AVAILABLE_FIELDS['types_id']['field']         = 'name';
$GENERICOBJECT_AVAILABLE_FIELDS['types_id']['linkfield']     = 'type';
$GENERICOBJECT_AVAILABLE_FIELDS['types_id']['input_type']    = 'dropdown';
$GENERICOBJECT_AVAILABLE_FIELDS['types_id']['dropdown_type'] = 'type_specific';

$GENERICOBJECT_AVAILABLE_FIELDS['models_id']['name']          = $LANG['common'][22];
$GENERICOBJECT_AVAILABLE_FIELDS['models_id']['field']         = 'name';
$GENERICOBJECT_AVAILABLE_FIELDS['models_id']['linkfield']     = 'model';
$GENERICOBJECT_AVAILABLE_FIELDS['models_id']['input_type']    = 'dropdown';
$GENERICOBJECT_AVAILABLE_FIELDS['models_id']['dropdown_type'] = 'type_specific';

$GENERICOBJECT_AVAILABLE_FIELDS['category']['name']          = $LANG['common'][36];
$GENERICOBJECT_AVAILABLE_FIELDS['category']['field']         = 'name';
$GENERICOBJECT_AVAILABLE_FIELDS['category']['linkfield']     = 'category';
$GENERICOBJECT_AVAILABLE_FIELDS['category']['input_type']    = 'dropdown';
$GENERICOBJECT_AVAILABLE_FIELDS['category']['dropdown_type'] = 'type_specific';

$GENERICOBJECT_AVAILABLE_FIELDS['entities_id']['name']       = $LANG['entity'][0];
$GENERICOBJECT_AVAILABLE_FIELDS['entities_id']['table']      = 'glpi_entities';
$GENERICOBJECT_AVAILABLE_FIELDS['entities_id']['field']      = 'completename';
$GENERICOBJECT_AVAILABLE_FIELDS['entities_id']['linkfield']  = 'entities_id';
$GENERICOBJECT_AVAILABLE_FIELDS['entities_id']['input_type'] = 'dropdown';
$GENERICOBJECT_AVAILABLE_FIELDS['entities_id']['massiveaction'] = false;

$GENERICOBJECT_AVAILABLE_FIELDS['template_name']['name']       = $LANG['entity'][0];
$GENERICOBJECT_AVAILABLE_FIELDS['template_name']['field']      = 'template_name';
$GENERICOBJECT_AVAILABLE_FIELDS['template_name']['linkfield']  = 'entities_id';
$GENERICOBJECT_AVAILABLE_FIELDS['template_name']['input_type'] = 'text';
$GENERICOBJECT_AVAILABLE_FIELDS['template_name']['massiveaction'] = false;

$GENERICOBJECT_AVAILABLE_FIELDS['notepad']['name']       = $LANG['title'][37];
$GENERICOBJECT_AVAILABLE_FIELDS['notepad']['field']      = 'notepad';
$GENERICOBJECT_AVAILABLE_FIELDS['notepad']['input_type'] = 'multitext';

$GENERICOBJECT_AVAILABLE_FIELDS['is_recursive']['name']       = $LANG['entity'][9];
$GENERICOBJECT_AVAILABLE_FIELDS['is_recursive']['field']      = 'is_recursive';
$GENERICOBJECT_AVAILABLE_FIELDS['is_recursive']['linkfield']  = '';
$GENERICOBJECT_AVAILABLE_FIELDS['is_recursive']['input_type'] = 'dropdown_yesno';
$GENERICOBJECT_AVAILABLE_FIELDS['is_recursive']['datatype']   = 'bool';

$GENERICOBJECT_AVAILABLE_FIELDS['is_deleted']['name']       = $LANG['ocsconfig'][49];
$GENERICOBJECT_AVAILABLE_FIELDS['is_deleted']['field']      = 'is_deleted';
$GENERICOBJECT_AVAILABLE_FIELDS['is_deleted']['linkfield']  = '';
$GENERICOBJECT_AVAILABLE_FIELDS['is_deleted']['input_type'] = 'dropdown_yesno';
$GENERICOBJECT_AVAILABLE_FIELDS['is_deleted']['datatype']   = 'bool';
$GENERICOBJECT_AVAILABLE_FIELDS['is_deleted']['massiveaction'] = false;

$GENERICOBJECT_AVAILABLE_FIELDS['is_template']['name']       = $LANG['common'][13];
$GENERICOBJECT_AVAILABLE_FIELDS['is_template']['field']      = 'is_template';
$GENERICOBJECT_AVAILABLE_FIELDS['is_template']['linkfield']  = '';
$GENERICOBJECT_AVAILABLE_FIELDS['is_template']['input_type'] = 'dropdown_yesno';
$GENERICOBJECT_AVAILABLE_FIELDS['is_template']['datatype']   ='bool';
$GENERICOBJECT_AVAILABLE_FIELDS['is_template']['massiveaction'] = false;


$GENERICOBJECT_AVAILABLE_FIELDS['is_global']['name']       = $LANG['peripherals'][33];
$GENERICOBJECT_AVAILABLE_FIELDS['is_global']['field']      = 'is_global';
$GENERICOBJECT_AVAILABLE_FIELDS['is_global']['linkfield']  = '';
$GENERICOBJECT_AVAILABLE_FIELDS['is_global']['input_type'] = 'dropdown_global';
$GENERICOBJECT_AVAILABLE_FIELDS['is_global']['datatype']   = 'bool';
$GENERICOBJECT_AVAILABLE_FIELDS['is_global']['massiveaction'] = false;

$GENERICOBJECT_AVAILABLE_FIELDS['is_helpdesk_visible']['name']       = $LANG['software'][46];
$GENERICOBJECT_AVAILABLE_FIELDS['is_helpdesk_visible']['field']      = 'is_helpdesk_visible';
$GENERICOBJECT_AVAILABLE_FIELDS['is_helpdesk_visible']['linkfield']  = '';
$GENERICOBJECT_AVAILABLE_FIELDS['is_helpdesk_visible']['input_type'] = 'dropdown_yesno';
$GENERICOBJECT_AVAILABLE_FIELDS['is_helpdesk_visible']['datatype']   = 'bool';

$GENERICOBJECT_AVAILABLE_FIELDS['locations_id']['name']       = $LANG['common'][15];
$GENERICOBJECT_AVAILABLE_FIELDS['locations_id']['table']      = 'glpi_locations';
$GENERICOBJECT_AVAILABLE_FIELDS['locations_id']['field']      = 'completename';
$GENERICOBJECT_AVAILABLE_FIELDS['locations_id']['linkfield']  = 'location';
$GENERICOBJECT_AVAILABLE_FIELDS['locations_id']['input_type'] = 'dropdown';
$GENERICOBJECT_AVAILABLE_FIELDS['locations_id']['entity']     = 'entity_restrict';

$GENERICOBJECT_AVAILABLE_FIELDS['states_id']['name']       = $LANG['joblist'][0];
$GENERICOBJECT_AVAILABLE_FIELDS['states_id']['table']      = 'glpi_states';
$GENERICOBJECT_AVAILABLE_FIELDS['states_id']['field']      = 'name';
$GENERICOBJECT_AVAILABLE_FIELDS['states_id']['linkfield']  = 'state';
$GENERICOBJECT_AVAILABLE_FIELDS['states_id']['input_type'] = 'dropdown';

$GENERICOBJECT_AVAILABLE_FIELDS['users_id']['name']       = $LANG['common'][34];
$GENERICOBJECT_AVAILABLE_FIELDS['users_id']['table']      = 'glpi_users';
$GENERICOBJECT_AVAILABLE_FIELDS['users_id']['field']      = 'name';
$GENERICOBJECT_AVAILABLE_FIELDS['users_id']['linkfield']  = 'users_id';
$GENERICOBJECT_AVAILABLE_FIELDS['users_id']['input_type'] = 'dropdown';

$GENERICOBJECT_AVAILABLE_FIELDS['groups_id']['name']       = $LANG['common'][35];
$GENERICOBJECT_AVAILABLE_FIELDS['groups_id']['table']      = 'glpi_groups';
$GENERICOBJECT_AVAILABLE_FIELDS['groups_id']['field']      = 'name';
$GENERICOBJECT_AVAILABLE_FIELDS['groups_id']['linkfield']  = 'groups_id';
$GENERICOBJECT_AVAILABLE_FIELDS['groups_id']['input_type'] = 'dropdown';

$GENERICOBJECT_AVAILABLE_FIELDS['manufacturers_id']['name']       = $LANG['common'][5];
$GENERICOBJECT_AVAILABLE_FIELDS['manufacturers_id']['table']      = 'glpi_manufacturers';
$GENERICOBJECT_AVAILABLE_FIELDS['manufacturers_id']['field']      = 'name';
$GENERICOBJECT_AVAILABLE_FIELDS['manufacturers_id']['linkfield']  = 'manufacturers_id';
$GENERICOBJECT_AVAILABLE_FIELDS['manufacturers_id']['input_type'] = 'dropdown';

$GENERICOBJECT_AVAILABLE_FIELDS['tech_num']['name']       = $LANG['common'][10];
$GENERICOBJECT_AVAILABLE_FIELDS['tech_num']['table']      = 'glpi_users';
$GENERICOBJECT_AVAILABLE_FIELDS['tech_num']['field']      = 'name';
$GENERICOBJECT_AVAILABLE_FIELDS['tech_num']['linkfield']  = 'tech_num';
$GENERICOBJECT_AVAILABLE_FIELDS['tech_num']['input_type'] = 'dropdown';

$GENERICOBJECT_AVAILABLE_FIELDS['domains_id']['name']       = $LANG['setup'][89];
$GENERICOBJECT_AVAILABLE_FIELDS['domains_id']['table']      = 'glpi_domains';
$GENERICOBJECT_AVAILABLE_FIELDS['domains_id']['field']      = 'name';
$GENERICOBJECT_AVAILABLE_FIELDS['domains_id']['linkfield']  = 'domain';
$GENERICOBJECT_AVAILABLE_FIELDS['domains_id']['input_type'] = 'dropdown';

$GENERICOBJECT_AVAILABLE_FIELDS['contacts_id']['name']       = $LANG['common'][18];
$GENERICOBJECT_AVAILABLE_FIELDS['contacts_id']['field']      = 'contact';
$GENERICOBJECT_AVAILABLE_FIELDS['contacts_id']['linkfield']  = 'contact';
$GENERICOBJECT_AVAILABLE_FIELDS['contacts_id']['input_type'] = 'text';

$GENERICOBJECT_AVAILABLE_FIELDS['contact_num']['name']       = $LANG['common'][21];
$GENERICOBJECT_AVAILABLE_FIELDS['contact_num']['field']      = 'contact_num';
$GENERICOBJECT_AVAILABLE_FIELDS['contact_num']['linkfield']  = 'contact_num';
$GENERICOBJECT_AVAILABLE_FIELDS['contact_num']['input_type'] = 'text';
