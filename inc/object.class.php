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
class PluginGenericobjectObject extends CommonDBTM
{
    use Glpi\Features\Clonable;

    protected $objecttype;

    //Internal field counter
    private $cpt = 0;

    //Get itemtype name
    public static function getTypeName($nb = 0)
    {
        /** @var array $LANG */
        global $LANG;
        $class    = get_called_class();
      //Datainjection : Don't understand why I need this trick : need to be investigated !
        if (preg_match("/Injection$/i", $class)) {
            $class = str_replace("Injection", "", $class);
        }
        $item     = new $class();
      //Itemtype name can be contained in a specific locale field : try to load it
        PluginGenericobjectType::includeLocales($item->objecttype->fields['name']);
        if (isset($LANG['genericobject'][$class][0])) {
            $type_name = $LANG['genericobject'][$class][0];
        } else {
            $type_name = $item->objecttype->fields['name'];
        }
        return ucwords($type_name);
    }


    public function __construct()
    {
        $class = get_called_class();
        if (class_exists($class)) {
            $this->objecttype = PluginGenericobjectType::getInstance($class);
        }
        $this->dohistory = $this->canUseHistory();

        if (preg_match("/PluginGenericobject(.*)/", $class, $results)) {
            if (preg_match("/^(.*)y$/i", $results[1], $end_results)) {
                static::$rightname = 'plugin_genericobject_' . strtolower($end_results[1]) . 'ies';
            } else if (preg_match("/^(.*)(ss|x)$/i", $results[1])) {
                static::$rightname = 'plugin_genericobject_' . strtolower($results[1]) . 'es';
            } else {
                static::$rightname = 'plugin_genericobject_' . strtolower($results[1]) . 's';
            }
        }

        if ($this->canUseNotepad()) {
            $this->usenotepad = true;
        }
    }

    public function getCloneRelations(): array
    {
        return [
            Computer_Item::class,
            Contract_Item::class,
            Document_Item::class,
            Infocom::class,
            Item_Devices::class,
            NetworkPort::class,
        ];
    }

    public static function install()
    {
    }

    public static function uninstall()
    {
    }

    public static function registerType()
    {
        /**
         * @var array $PLUGIN_HOOKS
         * @var array $UNINSTALL_TYPES
         * @var array $ORDER_TYPES
         * @var array $CFG_GLPI
         * @var array $GO_LINKED_TYPES
         * @var array $GENINVENTORYNUMBER_TYPES
         */
        global $PLUGIN_HOOKS, $UNINSTALL_TYPES, $ORDER_TYPES, $CFG_GLPI, $GO_LINKED_TYPES, $GENINVENTORYNUMBER_TYPES;

        $class  = get_called_class();
        $item   = new $class();
        $fields = PluginGenericobjectSingletonObjectField::getInstance($class);
        $plugin = new Plugin();

        PluginGenericobjectType::includeLocales($item->getObjectTypeName());
        PluginGenericobjectType::includeConstants($item->getObjectTypeName());

        $options = [
            "document_types"                => $item->canUseDocuments(),
            "helpdesk_visible_types"        => $item->canUseTickets(),
            "linkgroup_types"               => isset($fields["groups_id"]),
            "linkuser_types"                => isset($fields["users_id"]),
            "linkgroup_tech_types"          => isset($fields["groups_id_tech"]),
            "linkuser_tech_types"           => isset($fields["users_id_tech"]),
            "ticket_types"                  => $item->canUseTickets(),
            "infocom_types"                 => $item->canUseInfocoms(),
            "networkport_types"             => $item->canUseNetworkPorts(),
            "reservation_types"             => $item->canBeReserved(),
            "contract_types"                => $item->canUseContracts(),
            "unicity_types"                 => $item->canUseUnicity(),
            "location_types"                => isset($fields['locations_id']),
            "itemdevices_types"             => $item->canUseItemDevice(),
            "itemdevicememory_types"        => $item->canUseItemDevice(),
            "itemdevicepowersupply_types"   => $item->canUseItemDevice(),
            "itemdevicenetworkcard_types"   => $item->canUseItemDevice(),
            "itemdeviceharddrive_types"     => $item->canUseItemDevice(),
            "itemdevicebattery_types"       => $item->canUseItemDevice(),
            "itemdevicefirmware_types"      => $item->canUseItemDevice(),
            "itemdevicesimcard_types"       => $item->canUseItemDevice(),
            "itemdevicegeneric_types"       => $item->canUseItemDevice(),
            "itemdevicepci_types"           => $item->canUseItemDevice(),
            "itemdevicesensor_types"        => $item->canUseItemDevice(),
            "itemdeviceprocessor_types"     => $item->canUseItemDevice(),
            "itemdevicesoundcard_types"     => $item->canUseItemDevice(),
            "itemdevicegraphiccard_types"   => $item->canUseItemDevice(),
            "itemdevicemotherboard_types"   => $item->canUseItemDevice(),
            "itemdevicecamera_types"        => $item->canUseItemDevice(),

        ];

        $glpiVersion = new Plugin();
        $glpiVersion = $glpiVersion->getGlpiVersion();

        if (version_compare($glpiVersion, "10.0.19", '>=')) {
            $options["itemdevicedrive_types"] = $item->canUseItemDevice();
            $options["itemdevicecontrol_types"] = $item->canUseItemDevice();
        }

        Plugin::registerClass($class, $options);

        if (plugin_genericobject_haveRight($class, READ)) {
            //Change url for adding a new object, depending on template management activation
            if ($item->canUseTemplate()) {
                //Template management is active
                $add_url = "/front/setup.templates.php?itemtype=$class&add=1";
                $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['links']['template'] = "/front/setup.templates.php?itemtype=$class&add=0";
            } else {
                //Template management is not active
                $add_url = Toolbox::getItemTypeFormURL($class, false);
            }
            //Menu management
            $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['title'] = $class::getTypeName();
            $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['page'] = Toolbox::getItemTypeSearchURL($class, false);
            $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['links']['search'] = Toolbox::getItemTypeSearchURL($class, false);

            if (plugin_genericobject_haveRight($class, UPDATE)) {
                $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['links']['add'] = $add_url;
            }

            //Add configuration icon, if user has right
            if (Session::haveRight('config', UPDATE)) {
                $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['links']['config'] = Toolbox::getItemTypeSearchURL('PluginGenericobjectType', false) . "?itemtype=$class";
            }

            if ($item->canUsePluginUninstall()) {
                if (!in_array($class, $UNINSTALL_TYPES)) {
                    array_push($UNINSTALL_TYPES, $class);
                }
            }
            if ($item->canUsePluginSimcard()) {
                if ($plugin->isActivated('simcard') && $plugin->isActivated('simcard')) {
                    //@phpstan-ignore-next-line
                    PluginSimcardSimcard_Item::registerItemtype($class);
                }
            }
            if ($item->canUsePluginOrder()) {
                if (!in_array($class, $ORDER_TYPES)) {
                    array_push($ORDER_TYPES, $class);
                }
            }
            if ($item->canBeReserved()) {
                //Manage name used for sector
                //See object.form.php L101
                //it can be 'itemtype' name or 'family' name
                if (($name = PluginGenericobjectType::getFamilyNameByItemtype($class)) === false) {
                    $name = $class;
                }
                //from define.php $CFG_GLPI['javascript']['assets'] seems to be computed only once (from start)
                //need to add manually js for sector and itemtype/family
                $CFG_GLPI['javascript']['assets'][strtolower($name)] = ['fullcalendar', 'reservations'];
            }

            if ($item->canUseGlobalSearch()) {
                if (!in_array($class, $CFG_GLPI['asset_types'])) {
                    array_push($CFG_GLPI['asset_types'], $class);
                }

                if (!in_array($class, $CFG_GLPI['globalsearch_types'])) {
                    array_push($CFG_GLPI['globalsearch_types'], $class);
                }
            }

            if ($item->canUseDirectConnections()) {
                if (!in_array($class, $GO_LINKED_TYPES)) {
                    array_push($GO_LINKED_TYPES, $class);
                }
                $items_class = $class . "_Item";
                $items_class::registerType();
            }

            if ($item->canUseProjects()) {
                if (!in_array($class, $CFG_GLPI['project_asset_types'])) {
                    array_push($CFG_GLPI['project_asset_types'], $class);
                }
            }

            $plugin_gen_path = Plugin::getPhpDir('geninventorynumber');
            if ($item->canUsePluginGeninventorynumber()) {
                if (!in_array($class, $GENINVENTORYNUMBER_TYPES)) {
                    include_once("$plugin_gen_path/inc/profile.class.php");
                    //@phpstan-ignore-next-line
                    PluginGeninventorynumberConfigField::registerNewItemType($class);
                    array_push($GENINVENTORYNUMBER_TYPES, $class);
                }
            } else if ($plugin->isActivated('geninventorynumber')) {
                include_once("$plugin_gen_path/inc/profile.class.php");
                //@phpstan-ignore-next-line
                PluginGeninventorynumberConfigField::unregisterNewItemType($class);
            }
        }

        foreach (PluginGenericobjectType::getDropdownForItemtype($class) as $table) {
            $itemtype = getItemTypeForTable($table);
            if (class_exists($itemtype)) {
                $item     = new $itemtype();
                //If entity dropdown, check rights to view & create
                if ($itemtype::canView()) {
                    $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$itemtype]['links']['search'] = Toolbox::getItemTypeSearchURL($itemtype, false);
                    if ($itemtype::canCreate()) {
                        $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['links']['add'] = Toolbox::getItemTypeFormURL($class, false);
                    }
                }
            }
        }
    }

    public static function checkItemtypeRight($class, $right)
    {
        if (!is_null($class) and class_exists($class)) {
            $right_name = PluginGenericobjectProfile::getProfileNameForItemtype(
                $class
            );

            return Session::haveRight($right_name, $right);
        }
    }

    public static function canCreate(): bool
    {
        $class    = get_called_class();
        //Datainjection : Don't understand why I need this trick : need to be investigated !
        if (preg_match("/Injection$/i", $class)) {
            $class = str_replace("Injection", "", $class);
        }
        return static::checkItemtypeRight($class, CREATE);
    }

    public static function canView(): bool
    {
        $class = get_called_class();
        return static::checkItemtypeRight($class, READ);
    }

    public static function canUpdate(): bool
    {
        $class = get_called_class();
        return static::checkItemtypeRight($class, UPDATE);
    }

    public static function canDelete(): bool
    {
        $class = get_called_class();
        return static::checkItemtypeRight($class, DELETE);
    }

    public static function canPurge(): bool
    {
        $class = get_called_class();
        return static::checkItemtypeRight($class, PURGE);
    }

   //------------------------ CAN methods -------------------------------------//

    public function getObjectTypeName()
    {
        return $this->objecttype->getName();
    }

    public function canUseInfocoms()
    {
        return ($this->objecttype->canUseInfocoms() && Session::haveRight("infocom", READ));
    }

    public function canUseContracts()
    {
        return ($this->objecttype->canUseContracts() && Session::haveRight("contract", READ));
    }


    public function canUseTemplate()
    {
        return $this->objecttype->canUseTemplate();
    }


    public function canUseNotepad()
    {
        return $this->objecttype->canUseNotepad();
    }

    public function canUseUnicity()
    {
       // Disable unicity feature (for GLPI 0.85 onward) : see issue #16
       // Related code : search for #16
       // FIXME : The bug may be in GLPI itself
        return ($this->objecttype->canUseUnicity() && Session::haveRight("config", READ));
    }


    public function canUseDocuments()
    {
        return ($this->objecttype->canUseDocuments() && Session::haveRight("document", READ));
    }


    public function canUseTickets()
    {
        return ($this->objecttype->canUseTickets());
    }


    public function canUseGlobalSearch()
    {
        return ($this->objecttype->canUseGlobalSearch());
    }


    public function canBeReserved()
    {
        return (
         $this->objecttype->canBeReserved()
         and Session::haveRight(ReservationItem::$rightname, ReservationItem::RESERVEANITEM)
        );
    }


    public function canUseHistory()
    {
        return ($this->objecttype->canUseHistory());
    }


    public function canUsePluginDataInjection()
    {
        return ($this->objecttype->canUsePluginDataInjection());
    }


    public function canUsePluginPDF()
    {
        return ($this->objecttype->canUsePluginPDF());
    }


    public function canUsePluginOrder()
    {
        return ($this->objecttype->canUsePluginOrder());
    }

    public function canUsePluginGeninventorynumber()
    {
        return ($this->objecttype->canUsePluginGeninventorynumber());
    }

    public function canUseNetworkPorts()
    {
        return ($this->objecttype->canUseNetworkPorts());
    }


    public function canUseDirectConnections()
    {
        return ($this->objecttype->canUseDirectConnections());
    }

    public function canUseProjects()
    {
        return ($this->objecttype->canUseProjects());
    }


    public function canUsePluginUninstall()
    {
        return ($this->objecttype->canUsePluginUninstall());
    }

    public function canUsePluginSimcard()
    {
        return ($this->objecttype->canUsePluginSimcard());
    }

    public function getLinkedItemTypesAsArray()
    {
        return $this->objecttype->getLinkedItemTypesAsArray();
    }

    public function canUseItemDevice()
    {
        return ($this->objecttype->canUseItemDevice());
    }

    public function canUseImpact()
    {
        return ($this->objecttype->canUseImpact());
    }

    public function title()
    {
    }

    public static function getFieldsToHide()
    {
        return ['id', 'is_recursive', 'is_template', 'template_name', 'is_deleted',
            'entities_id', 'notepad', 'date_mod', 'date_creation', 'ticket_tco'
        ];
    }

    public function prepareInputForAdd($input)
    {

       //Template management
        if (isset($input["id"]) && $input["id"] > 0) {
            $input["_oldID"] = $input["id"];
        }
        unset($input['id']);
        unset($input['withtemplate']);

        return $input;
    }


    public function cleanDBonPurge()
    {
        $parameters = ['items_id' => $this->getID(), 'itemtype' => get_called_class()];
        $types      = ['Computer_Item', 'ReservationItem', 'Document_Item', 'Infocom', 'Contract_Item'];
        foreach ($types as $type) {
            $item = new $type();
            $item->deleteByCriteria($parameters);
        }

        foreach (['NetworkPort', 'Computer_Item', 'ReservationItem', 'ReservationItem', 'Document_Item', 'Infocom', 'Contract_Item', 'Item_Problem', 'Change_Item', 'Item_Project'] as $itemtype) {
            $ip = new $itemtype();
            $ip->cleanDBonItemDelete(get_called_class(), $this->getID());
        }
    }

    //Datainjection specific methods
    public function isPrimaryType()
    {
        return true;
    }


    public function connectedTo()
    {
        return [];
    }


    /**
     * Standard method to add an object into glpi
     *
     * @param array $values fields to add into glpi
     * @param array $options options used during creation
     * @return array
     *
    **/
    public function addOrUpdateObject($values = [], $options = [])
    {
        //@phpstan-ignore-next-line
        $lib = new PluginDatainjectionCommonInjectionLib($this, $values, $options);
        $lib->processAddOrUpdate();
        return $lib->getInjectionResults();
    }


    public function getOptions($primary_type = '')
    {
        return Search::getOptions($primary_type);
    }


    public function transfer($new_entity)
    {
        if ($this->fields['id'] > 0 && $this->fields['entities_id'] != $new_entity) {
           //Update entity for this object
            $tmp['id']          = $this->fields['id'];
            $tmp['entities_id'] = $new_entity;
            $this->update($tmp);

            $toupdate = ['id' => $this->fields['id']];
            foreach (PluginGenericobjectSingletonObjectField::getInstance(get_called_class()) as $field => $data) {
                $table = getTableNameForForeignKeyField($field);

              //It is a dropdown table !
                if (
                    $field != 'entities_id'
                    && $table != ''
                    && isset($this->fields[$field]) && $this->fields[$field] > 0
                ) {
                    //Instanciate a new dropdown object
                    $dropdown_itemtype = getItemTypeForTable($table);
                    $dropdown          = new $dropdown_itemtype();
                    $dropdown->getFromDB($this->fields[$field]);

                    //If dropdown is only accessible in the other entity
                    //do not go further
                    if (!$dropdown->isEntityAssign() || in_array($new_entity, getAncestorsOf('glpi_entities', $dropdown->getEntityID()))) {
                        continue;
                    } else {
                        $tmp   = [];
                        $where = [];
                        if ($dropdown instanceof CommonTreeDropdown) {
                            $tmp['completename']   = $dropdown->fields['completename'];
                            $where['completename'] = Toolbox::addslashes_deep($tmp['completename']);
                        } else {
                            $tmp['name']   = $dropdown->fields['name'];
                            $where['name'] = Toolbox::addslashes_deep($tmp['name']);
                        }
                        $tmp['entities_id']   = $new_entity;
                        $where['entities_id'] = $tmp['entities_id'];
                  //There's a dropdown value in the target entity
                        if ($found = $dropdown->find($where)) {
                            $myfound = array_pop($found);
                            if ($myfound['id'] != $this->fields[$field]) {
                                $toupdate[$field] = $myfound['id'];
                            }
                        } else {
                            $clone = $dropdown->fields;
                            if ($dropdown instanceof CommonTreeDropdown) {
                                unset($clone['completename']);
                            }
                            unset($clone['id']);
                            $clone['entities_id'] = $new_entity;
                            $new_id               = $dropdown->import($clone);
                            $toupdate[$field]     = $new_id;
                        }
                    }
                }
            }
            $this->update($toupdate);
        }
        return true;
    }

    public static function getMenuContent()
    {
        $types = PluginGenericobjectType::getTypes();
        $menu = [];
        foreach ($types as $type) {
            $itemtype = $type['itemtype'];
            if (!class_exists($itemtype)) {
                continue;
            }
            $item     = new $itemtype();

            $itemtype_rightname = PluginGenericobjectProfile::getProfileNameForItemtype($itemtype);
            if (
                class_exists($itemtype)
                && Session::haveRight($itemtype_rightname, READ)
            ) {
                $links           = [];
                $links['search'] = $itemtype::getSearchUrl(false);
                $links['lists']  = '';

                if ($item->canUseTemplate()) {
                    $links['template'] = "/front/setup.templates.php?itemtype=$itemtype&add=0";
                    if (Session::haveRight($itemtype_rightname, CREATE)) {
                        $links['add'] = "/front/setup.templates.php?itemtype=$itemtype&add=1";
                    }
                } else {
                    if (Session::haveRight($itemtype_rightname, CREATE)) {
                        $links['add'] = $itemtype::getFormUrl(false);
                    }
                }

                if (
                    $type['plugin_genericobject_typefamilies_id'] > 0
                    && (!isset($_GET['itemtype'])
                    || !preg_match("/itemtype=" . $_GET['itemtype'] . "/", $_GET['itemtype']))
                ) {
                    $family_id = $type['plugin_genericobject_typefamilies_id'];
                    $name      = Dropdown::getDropdownName("glpi_plugin_genericobject_typefamilies", $family_id, 0, false);
                    $str_name  = strtolower($name);
                    $menu[$str_name]['title'] = Dropdown::getDropdownName("glpi_plugin_genericobject_typefamilies", $family_id);
                    $menu[$str_name]['page']  = '/' . Plugin::getWebDir('genericobject', false) . '/front/familylist.php?id=' . $family_id;
                    $menu[$str_name]['options'][strtolower($itemtype)] = [
                        'title' => $type['itemtype']::getMenuName(),
                        'page'  => $itemtype::getSearchUrl(false),
                        'links' => $links,
                        'lists_itemtype' => $itemtype,
                    ];
                } else {
                    $menu[strtolower($itemtype)] = [
                        'title' => $type['itemtype']::getMenuName(),
                        'page'  => $itemtype::getSearchUrl(false),
                        'links' => $links,
                        'lists_itemtype' => $itemtype,
                    ];
                }
            }
        }

       // Sort by menu entries name
        uasort($menu, fn($a, $b) => $a['title'] <=> $b['title']);

       // Mark as multi entries
        $menu['is_multi_entries'] = true;

        return $menu;
    }
}
