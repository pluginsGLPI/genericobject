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

$GENERICOBJECT_AVAILABLE_FIELDS['name']['name']=$LANG['common'][16];
$GENERICOBJECT_AVAILABLE_FIELDS['name']['field']='name';
$GENERICOBJECT_AVAILABLE_FIELDS['name']['input_type']='text';
$GENERICOBJECT_AVAILABLE_FIELDS['name']['datatype']='itemlink';

$GENERICOBJECT_AVAILABLE_FIELDS['serial']['name']=$LANG['common'][19];
$GENERICOBJECT_AVAILABLE_FIELDS['serial']['field']='serial';
$GENERICOBJECT_AVAILABLE_FIELDS['serial']['input_type']='text';

$GENERICOBJECT_AVAILABLE_FIELDS['otherserial']['name']=$LANG['common'][20];
$GENERICOBJECT_AVAILABLE_FIELDS['otherserial']['field']='otherserial';
$GENERICOBJECT_AVAILABLE_FIELDS['otherserial']['input_type']='text';

$GENERICOBJECT_AVAILABLE_FIELDS['comments']['name']=$LANG['common'][25];
$GENERICOBJECT_AVAILABLE_FIELDS['comments']['field']='comments';
$GENERICOBJECT_AVAILABLE_FIELDS['comments']['input_type']='multitext';

$GENERICOBJECT_AVAILABLE_FIELDS['other']['name']=$LANG['common'][62];
$GENERICOBJECT_AVAILABLE_FIELDS['other']['field']='other';
$GENERICOBJECT_AVAILABLE_FIELDS['other']['input_type']='text';

$GENERICOBJECT_AVAILABLE_FIELDS['creationdate']['name']=$LANG['genericobject']['fields'][30];
$GENERICOBJECT_AVAILABLE_FIELDS['creationdate']['field']='creationdate';
$GENERICOBJECT_AVAILABLE_FIELDS['creationdate']['input_type']='date';
$GENERICOBJECT_AVAILABLE_FIELDS['creationdate']['datatype']='date';

$GENERICOBJECT_AVAILABLE_FIELDS['expirationdate']['name']=$LANG['genericobject']['fields'][31];
$GENERICOBJECT_AVAILABLE_FIELDS['expirationdate']['field']='expirationdate';
$GENERICOBJECT_AVAILABLE_FIELDS['expirationdate']['input_type']='date';
$GENERICOBJECT_AVAILABLE_FIELDS['expirationdate']['datatype']='date';

$GENERICOBJECT_AVAILABLE_FIELDS['url']['name']=$LANG['genericobject']['fields'][10];
$GENERICOBJECT_AVAILABLE_FIELDS['url']['field']='url';
$GENERICOBJECT_AVAILABLE_FIELDS['url']['input_type']='text';
$GENERICOBJECT_AVAILABLE_FIELDS['url']['datatype']='weblink';

$GENERICOBJECT_AVAILABLE_FIELDS['type']['name']=$LANG['common'][17];
$GENERICOBJECT_AVAILABLE_FIELDS['type']['field']='name';
$GENERICOBJECT_AVAILABLE_FIELDS['type']['linkfield']='type';
$GENERICOBJECT_AVAILABLE_FIELDS['type']['input_type']='dropdown';
$GENERICOBJECT_AVAILABLE_FIELDS['type']['dropdown_type']='type_specific';

$GENERICOBJECT_AVAILABLE_FIELDS['model']['name']=$LANG['common'][22];
$GENERICOBJECT_AVAILABLE_FIELDS['model']['field']='name';
$GENERICOBJECT_AVAILABLE_FIELDS['model']['linkfield']='model';
$GENERICOBJECT_AVAILABLE_FIELDS['model']['input_type']='dropdown';
$GENERICOBJECT_AVAILABLE_FIELDS['model']['dropdown_type']='type_specific';

$GENERICOBJECT_AVAILABLE_FIELDS['category']['name']=$LANG['common'][36];
$GENERICOBJECT_AVAILABLE_FIELDS['category']['field']='name';
$GENERICOBJECT_AVAILABLE_FIELDS['category']['linkfield']='category';
$GENERICOBJECT_AVAILABLE_FIELDS['category']['input_type']='dropdown';
$GENERICOBJECT_AVAILABLE_FIELDS['category']['dropdown_type']='type_specific';

$GENERICOBJECT_AVAILABLE_FIELDS['entities_id']['name']=$LANG['entity'][0];
$GENERICOBJECT_AVAILABLE_FIELDS['entities_id']['table']='glpi_entities';
$GENERICOBJECT_AVAILABLE_FIELDS['entities_id']['field']='completename';
$GENERICOBJECT_AVAILABLE_FIELDS['entities_id']['linkfield']='entities_id';
$GENERICOBJECT_AVAILABLE_FIELDS['entities_id']['input_type']='dropdown';

/*
$GENERICOBJECT_AVAILABLE_FIELDS['recursive']['name']=$LANG['entity'][9];
$GENERICOBJECT_AVAILABLE_FIELDS['recursive']['field']='recursive';
$GENERICOBJECT_AVAILABLE_FIELDS['recursive']['linkfield']='';
$GENERICOBJECT_AVAILABLE_FIELDS['recursive']['input_type']='dropdown_yesno';
$GENERICOBJECT_AVAILABLE_FIELDS['recursive']['datatype']='bool';
*/
/*
$GENERICOBJECT_AVAILABLE_FIELDS['is_template']['name']=$LANG['common'][13];
$GENERICOBJECT_AVAILABLE_FIELDS['is_template']['field']='is_template';
$GENERICOBJECT_AVAILABLE_FIELDS['is_template']['linkfield']='';
$GENERICOBJECT_AVAILABLE_FIELDS['is_template']['input_type']='dropdown_yesno';
$GENERICOBJECT_AVAILABLE_FIELDS['is_template']['datatype']='bool';
*/

$GENERICOBJECT_AVAILABLE_FIELDS['is_global']['name']=$LANG['peripherals'][33];
$GENERICOBJECT_AVAILABLE_FIELDS['is_global']['field']='is_global';
$GENERICOBJECT_AVAILABLE_FIELDS['is_global']['linkfield']='';
$GENERICOBJECT_AVAILABLE_FIELDS['is_global']['input_type']='dropdown_global';
$GENERICOBJECT_AVAILABLE_FIELDS['is_global']['datatype']='bool';


$GENERICOBJECT_AVAILABLE_FIELDS['helpdesk_visible']['name']=$LANG['software'][46];
$GENERICOBJECT_AVAILABLE_FIELDS['helpdesk_visible']['field']='helpdesk_visible';
$GENERICOBJECT_AVAILABLE_FIELDS['helpdesk_visible']['linkfield']='';
$GENERICOBJECT_AVAILABLE_FIELDS['helpdesk_visible']['input_type']='dropdown_yesno';
$GENERICOBJECT_AVAILABLE_FIELDS['helpdesk_visible']['datatype']='bool';

$GENERICOBJECT_AVAILABLE_FIELDS['location']['name']=$LANG['common'][15];
$GENERICOBJECT_AVAILABLE_FIELDS['location']['table']='glpi_locations';
$GENERICOBJECT_AVAILABLE_FIELDS['location']['field']='completename';
$GENERICOBJECT_AVAILABLE_FIELDS['location']['linkfield']='location';
$GENERICOBJECT_AVAILABLE_FIELDS['location']['input_type']='dropdown';
$GENERICOBJECT_AVAILABLE_FIELDS['location']['entity']='entity_restrict';

$GENERICOBJECT_AVAILABLE_FIELDS['state']['name']=$LANG['joblist'][0];
$GENERICOBJECT_AVAILABLE_FIELDS['state']['table']='glpi_states';
$GENERICOBJECT_AVAILABLE_FIELDS['state']['field']='name';
$GENERICOBJECT_AVAILABLE_FIELDS['state']['linkfield']='state';
$GENERICOBJECT_AVAILABLE_FIELDS['state']['input_type']='dropdown';

$GENERICOBJECT_AVAILABLE_FIELDS['users_id']['name']=$LANG['common'][34];
$GENERICOBJECT_AVAILABLE_FIELDS['users_id']['table']='glpi_users';
$GENERICOBJECT_AVAILABLE_FIELDS['users_id']['field']='name';
$GENERICOBJECT_AVAILABLE_FIELDS['users_id']['linkfield']='users_id';
$GENERICOBJECT_AVAILABLE_FIELDS['users_id']['input_type']='dropdown';

$GENERICOBJECT_AVAILABLE_FIELDS['group']['name']=$LANG['common'][35];
$GENERICOBJECT_AVAILABLE_FIELDS['group']['table']='glpi_groups';
$GENERICOBJECT_AVAILABLE_FIELDS['group']['field']='name';
$GENERICOBJECT_AVAILABLE_FIELDS['group']['linkfield']='groups_id';
$GENERICOBJECT_AVAILABLE_FIELDS['group']['input_type']='dropdown';

$GENERICOBJECT_AVAILABLE_FIELDS['manufacturer']['name']=$LANG['common'][5];
$GENERICOBJECT_AVAILABLE_FIELDS['manufacturer']['table']='glpi_manufacturers';
$GENERICOBJECT_AVAILABLE_FIELDS['manufacturer']['field']='name';
$GENERICOBJECT_AVAILABLE_FIELDS['manufacturer']['linkfield']='manufacturers_id';
$GENERICOBJECT_AVAILABLE_FIELDS['manufacturer']['input_type']='dropdown';

$GENERICOBJECT_AVAILABLE_FIELDS['tech_num']['name']=$LANG['common'][10];
$GENERICOBJECT_AVAILABLE_FIELDS['tech_num']['table']='glpi_users';
$GENERICOBJECT_AVAILABLE_FIELDS['tech_num']['field']='name';
$GENERICOBJECT_AVAILABLE_FIELDS['tech_num']['linkfield']='tech_num';
$GENERICOBJECT_AVAILABLE_FIELDS['tech_num']['input_type']='dropdown';

$GENERICOBJECT_AVAILABLE_FIELDS['domain']['name']=$LANG['setup'][89];
$GENERICOBJECT_AVAILABLE_FIELDS['domain']['table']='glpi_domains';
$GENERICOBJECT_AVAILABLE_FIELDS['domain']['field']='name';
$GENERICOBJECT_AVAILABLE_FIELDS['domain']['linkfield']='domain';
$GENERICOBJECT_AVAILABLE_FIELDS['domain']['input_type']='dropdown';

$GENERICOBJECT_AVAILABLE_FIELDS['contact']['name']=$LANG['common'][18];
$GENERICOBJECT_AVAILABLE_FIELDS['contact']['field']='contact';
$GENERICOBJECT_AVAILABLE_FIELDS['contact']['linkfield']='contact';
$GENERICOBJECT_AVAILABLE_FIELDS['contact']['input_type']='text';

$GENERICOBJECT_AVAILABLE_FIELDS['contact_num']['name']=$LANG['common'][21];
$GENERICOBJECT_AVAILABLE_FIELDS['contact_num']['field']='contact_num';
$GENERICOBJECT_AVAILABLE_FIELDS['contact_num']['linkfield']='contact_num';
$GENERICOBJECT_AVAILABLE_FIELDS['contact_num']['input_type']='text';


?>
