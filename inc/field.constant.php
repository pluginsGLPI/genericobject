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
global $GO_FIELDS, $LANG;

$GO_FIELDS['id']['name']          = $LANG['common'][2];
$GO_FIELDS['id']['input_type']    = 'text';
$GO_FIELDS['id']['massiveaction'] = false;

$GO_FIELDS['name']['name']       = $LANG['common'][16];
$GO_FIELDS['name']['field']      = 'name';
$GO_FIELDS['name']['input_type'] = 'text';

$GO_FIELDS['serial']['name']       = $LANG['common'][19];
$GO_FIELDS['serial']['field']      = 'serial';
$GO_FIELDS['serial']['input_type'] = 'text';

$GO_FIELDS['otherserial']['name']       = $LANG['common'][20];
$GO_FIELDS['otherserial']['field']      = 'otherserial';
$GO_FIELDS['otherserial']['input_type'] = 'text';

$GO_FIELDS['comment']['name']       = $LANG['common'][25];
$GO_FIELDS['comment']['field']      = 'comment';
$GO_FIELDS['comment']['input_type'] = 'multitext';

$GO_FIELDS['other']['name']         = $LANG['common'][62];
$GO_FIELDS['other']['input_type']   = 'text';

$GO_FIELDS['creationdate']['name']       = $LANG['genericobject']['fields'][30];
$GO_FIELDS['creationdate']['input_type'] = 'date';

$GO_FIELDS['expirationdate']['name']       = $LANG['genericobject']['fields'][31];
$GO_FIELDS['expirationdate']['input_type'] = 'date';

$GO_FIELDS['date_mod']['name']       = $LANG['login'][24];
$GO_FIELDS['date_mod']['input_type'] = 'datetime';

$GO_FIELDS['url']['name']       = $LANG['genericobject']['fields'][10];
$GO_FIELDS['url']['field']      = 'url';
$GO_FIELDS['url']['input_type'] = 'text';
$GO_FIELDS['url']['datatype']   = 'weblink';

$GO_FIELDS['types_id']['name']          = $LANG['common'][17];
$GO_FIELDS['types_id']['linkfield']     = 'type';
$GO_FIELDS['types_id']['input_type']    = 'dropdown';
$GO_FIELDS['types_id']['dropdown_type'] = 'global'; //Means that 

$GO_FIELDS['models_id']['name']          = $LANG['common'][22];
$GO_FIELDS['models_id']['input_type']    = 'dropdown';
$GO_FIELDS['models_id']['dropdown_type'] = 'global';

$GO_FIELDS['categories_id']['name']          = $LANG['common'][36];
$GO_FIELDS['categories_id']['input_type']    = 'dropdown';
$GO_FIELDS['categories_id']['dropdown_type'] = 'global';

$GO_FIELDS['entities_id']['name']          = $LANG['entity'][0];
$GO_FIELDS['entities_id']['input_type']    = 'dropdown';
$GO_FIELDS['entities_id']['massiveaction'] = false;

$GO_FIELDS['template_name']['name']          = $LANG['entity'][0];
$GO_FIELDS['template_name']['input_type']    = 'text';
$GO_FIELDS['template_name']['massiveaction'] = false;

$GO_FIELDS['notepad']['name']       = $LANG['title'][37];
$GO_FIELDS['notepad']['input_type'] = 'multitext';

$GO_FIELDS['is_recursive']['name']       = $LANG['entity'][9];
$GO_FIELDS['is_recursive']['input_type'] = 'bool';

$GO_FIELDS['is_deleted']['name']          = $LANG['ocsconfig'][49];
$GO_FIELDS['is_deleted']['input_type']    = 'bool';
$GO_FIELDS['is_deleted']['massiveaction'] = false;

$GO_FIELDS['is_template']['name']          = $LANG['common'][13];
$GO_FIELDS['is_template']['input_type']    = 'bool';
$GO_FIELDS['is_template']['massiveaction'] = false;

$GO_FIELDS['is_global']['name']          = $LANG['peripherals'][33];
$GO_FIELDS['is_global']['input_type']    = 'bool';
$GO_FIELDS['is_global']['massiveaction'] = false;

$GO_FIELDS['is_helpdesk_visible']['name']       = $LANG['software'][46];
$GO_FIELDS['is_helpdesk_visible']['input_type'] = 'bool';

$GO_FIELDS['locations_id']['name']       = $LANG['common'][15];
$GO_FIELDS['locations_id']['input_type'] = 'dropdown';

$GO_FIELDS['states_id']['name']       = $LANG['joblist'][0];
$GO_FIELDS['states_id']['input_type'] = 'dropdown';

$GO_FIELDS['users_id']['name']       = $LANG['common'][34];
$GO_FIELDS['users_id']['input_type'] = 'dropdown';

$GO_FIELDS['groups_id']['name']       = $LANG['common'][35];
$GO_FIELDS['groups_id']['input_type'] = 'dropdown';

$GO_FIELDS['manufacturers_id']['name']       = $LANG['common'][5];
$GO_FIELDS['manufacturers_id']['input_type'] = 'dropdown';

$GO_FIELDS['users_id_tech']['name']       = $LANG['common'][10];
$GO_FIELDS['users_id_tech']['input_type'] = 'dropdown';

$GO_FIELDS['domains_id']['name']       = $LANG['setup'][89];
$GO_FIELDS['domains_id']['input_type'] = 'dropdown';

$GO_FIELDS['contacts_id']['name']       = $LANG['common'][18];
$GO_FIELDS['contacts_id']['input_type'] = 'text';

$GO_FIELDS['contact_num']['name']       = $LANG['common'][21];
$GO_FIELDS['contact_num']['input_type'] = 'text';

$GO_FIELDS['testinteger']['name']       = 'testinteger';
$GO_FIELDS['testinteger']['input_type'] = 'integer';
$GO_FIELDS['testinteger']['min']        = 10;
$GO_FIELDS['testinteger']['max']        = 40;
$GO_FIELDS['testinteger']['step']       = 3;
