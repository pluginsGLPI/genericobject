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
global $GENERICOBJECT_AVAILABLE_FIELDS;

$GENERICOBJECT_AVAILABLE_FIELDS['ID']['name']=$LANG['common'][2];
$GENERICOBJECT_AVAILABLE_FIELDS['ID']['field']='ID';
$GENERICOBJECT_AVAILABLE_FIELDS['ID']['input_type']='text';
$GENERICOBJECT_AVAILABLE_FIELDS['ID']['datatype']='itemlink';

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
$GENERICOBJECT_AVAILABLE_FIELDS['comments']['field']='otherserial';
$GENERICOBJECT_AVAILABLE_FIELDS['comments']['input_type']='multitext';

$GENERICOBJECT_AVAILABLE_FIELDS['FK_entities']['name']=$LANG['entity'][0];
$GENERICOBJECT_AVAILABLE_FIELDS['FK_entities']['table']='glpi_entities';
$GENERICOBJECT_AVAILABLE_FIELDS['FK_entities']['field']='completename';
$GENERICOBJECT_AVAILABLE_FIELDS['FK_entities']['linkfield']='FK_entities';
$GENERICOBJECT_AVAILABLE_FIELDS['FK_entities']['input_type']='dropdown';

$GENERICOBJECT_AVAILABLE_FIELDS['recursive']['name']=$LANG['entity'][9];
$GENERICOBJECT_AVAILABLE_FIELDS['recursive']['field']='recursive';
$GENERICOBJECT_AVAILABLE_FIELDS['recursive']['linkfield']='recursive';
$GENERICOBJECT_AVAILABLE_FIELDS['recursive']['input_type']='dropdown_yesno';
$GENERICOBJECT_AVAILABLE_FIELDS['recursive']['datatype']='bool';

$GENERICOBJECT_AVAILABLE_FIELDS['is_template']['name']=$LANG['entity'][9];
$GENERICOBJECT_AVAILABLE_FIELDS['is_template']['field']='is_template';
$GENERICOBJECT_AVAILABLE_FIELDS['is_template']['field']='recursive';
$GENERICOBJECT_AVAILABLE_FIELDS['is_template']['input_type']='dropdown_yesno';
$GENERICOBJECT_AVAILABLE_FIELDS['is_template']['datatype']='bool';

$GENERICOBJECT_AVAILABLE_FIELDS['location']['name']=$LANG['common'][15];
$GENERICOBJECT_AVAILABLE_FIELDS['location']['table']='glpi_dropdown_locations';
$GENERICOBJECT_AVAILABLE_FIELDS['location']['field']='name';
$GENERICOBJECT_AVAILABLE_FIELDS['location']['linkfield']='location';
$GENERICOBJECT_AVAILABLE_FIELDS['location']['input_type']='dropdown';

$GENERICOBJECT_AVAILABLE_FIELDS['state']['name']=$LANG['joblist'][0];
$GENERICOBJECT_AVAILABLE_FIELDS['state']['table']='glpi_dropdown_state';
$GENERICOBJECT_AVAILABLE_FIELDS['state']['field']='name';
$GENERICOBJECT_AVAILABLE_FIELDS['state']['linkfield']='state';
$GENERICOBJECT_AVAILABLE_FIELDS['state']['input_type']='dropdown';

$GENERICOBJECT_AVAILABLE_FIELDS['FK_users']['name']=$LANG['common'][34];
$GENERICOBJECT_AVAILABLE_FIELDS['FK_users']['table']='glpi_users';
$GENERICOBJECT_AVAILABLE_FIELDS['FK_users']['field']='name';
$GENERICOBJECT_AVAILABLE_FIELDS['FK_users']['linkfield']='FK_users';
$GENERICOBJECT_AVAILABLE_FIELDS['FK_users']['input_type']='dropdown';

$GENERICOBJECT_AVAILABLE_FIELDS['FK_groups']['name']=$LANG['common'][35];
$GENERICOBJECT_AVAILABLE_FIELDS['FK_groups']['table']='glpi_groups';
$GENERICOBJECT_AVAILABLE_FIELDS['FK_groups']['field']='name';
$GENERICOBJECT_AVAILABLE_FIELDS['FK_groups']['linkfield']='FK_groups';
$GENERICOBJECT_AVAILABLE_FIELDS['FK_groups']['input_type']='dropdown';

$GENERICOBJECT_AVAILABLE_FIELDS['FK_glpi_enterprise']['name']=$LANG['common'][5];
$GENERICOBJECT_AVAILABLE_FIELDS['FK_glpi_enterprise']['table']='glpi_dropdown_manufacturer';
$GENERICOBJECT_AVAILABLE_FIELDS['FK_glpi_enterprise']['field']='name';
$GENERICOBJECT_AVAILABLE_FIELDS['FK_glpi_enterprise']['linkfield']='FK_glpi_enterprise';
$GENERICOBJECT_AVAILABLE_FIELDS['FK_glpi_enterprise']['input_type']='dropdown';

?>
