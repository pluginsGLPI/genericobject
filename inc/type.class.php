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

class PluginGenericobjectType extends CommonDBTM
{
    const INACTIVE = 0;
    const ACTIVE   = 1;

    const DRAFT     = 0;
    const PUBLISHED = 1;

    const CLASS_TEMPLATE              = "/objects/generic.class.tpl";
    const FORM_TEMPLATE               = "/objects/generic.form.tpl";
    const CLASS_DROPDOWN_TEMPLATE     = "/objects/generic.dropdown.class.tpl";
    const FRONTFORM_DROPDOWN_TEMPLATE = "/objects/front.form.tpl";
    const FRONT_DROPDOWN_TEMPLATE     = "/objects/front.tpl";
    const SEARCH_TEMPLATE             = "/objects/front.tpl";
    const AJAX_DROPDOWN_TEMPLATE      = "/objects/dropdown.tabs.tpl";
    const AJAX_TEMPLATE               = "/objects/ajax.tabs.tpl";
    const LOCALE_TEMPLATE             = "/objects/locale.tpl";
    const OBJECTINJECTION_TEMPLATE    = "/objects/objectinjection.class.tpl";
    const OBJECTITEM_TEMPLATE         = "/objects/object_item.class.tpl";

    const CAN_OPEN_TICKET             = 1024;

    public $dohistory                    = true;

    public static $rightname                 = 'plugin_genericobject_types';


    public function __construct($itemtype = false)
    {
        if ($itemtype) {
            $this->getFromDBByType($itemtype);
        }
    }

    public function isEntityAssign()
    {
        return false;
    }

    public static function getTypeName($nb = 0)
    {
        return __("Type of objects", "genericobject");
    }

    public static function &getInstance($itemtype, $refresh = false)
    {
        static $singleton = [];
        if (!isset($singleton[$itemtype]) || $refresh) {
            $singleton[$itemtype] = new self($itemtype);
        }
        return $singleton[$itemtype];
    }


    public function getFromDBByType($itemtype)
    {
        /** @var DBmysql $DB */
        global $DB;

        $query  = "SELECT * FROM `" . getTableForItemType(__CLASS__) . "` " .
                "WHERE `itemtype`='$itemtype'";
        $result = $DB->query($query);
        if ($DB->numrows($result) > 0) {
            $this->fields = $DB->fetchArray($result);
        } else {
            $this->getEmpty();
        }
    }


   //------------------------------------ Tabs management -----------------------------------
    public function defineTabs($options = [])
    {
        $tabs = [];
        $this->addStandardTab(__CLASS__, $tabs, $options);
        return $tabs;
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (!$withtemplate) {
            switch ($item->getType()) {
                case __CLASS__:
                   // Number of fields in database
                    $itemtype = $item->fields['itemtype'];
                    $nb_fields = 0;
                    if (class_exists($itemtype)) {
                        $obj = new $itemtype();
                        $obj->getEmpty();
                        $nb_fields = count($obj->fields);
                    }

                    $tabs =  [
                        1  => __("Main"),
                        3 => self::createTabEntry(_n("Field", "Fields", Session::getPluralNumber()), $nb_fields),
                        5 => __("Preview")
                    ];
                    if ($item->canUseDirectConnections()) {
                        $tabs[7] = __("Associated element");
                    }
                    return $tabs;
            }
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == __CLASS__) {
            switch ($tabnum) {
                case 1:
                    $item->showBehaviorForm($item->getID());
                    break;

                case 3:
                    PluginGenericobjectField::showObjectFieldsForm($item->getID());
                    break;

                case 5:
                    PluginGenericobjectObject::showPrevisualisationForm($item);
                    break;

                case 6:
                    PluginGenericobjectProfile::showForItemtype($item);
                    break;
            }
        }
        return true;
    }
   //------------------------------------- End tabs management ------------------------------

   //------------------------------------- Framework hooks ----------------------------------
    public function prepareInputForAdd($input)
    {
       //Name must not be empty
        if (isset($input['name']) && $input['name'] == '') {
            Session::addMessageAfterRedirect(__("Type name is missing", "genericobject"), ERROR, true);
            return [];
        }

       // Name must be more than 1 char
        if (isset($input['name']) && strlen($input['name']) < 2) {
            Session::addMessageAfterRedirect(__("Type name must be longer", "genericobject"), ERROR, true);
            return [];
        }

       //Name must not match specific names
        if (in_array($input['name'], ['field', 'object', 'type'])) {
            Session::addMessageAfterRedirect(__(
                "Types 'field', 'object' and 'type' are reserved. Please choose another one",
                "genericobject"
            ), ERROR, true);
            return [];
        }

       //Name must start with a letter
        if (!preg_match("/^[a-zA-Z]+/i", $input['name'])) {
            Session::addMessageAfterRedirect(__("Type must start with a letter", "genericobject"), ERROR, true);
            return [];
        }
        $input['name']     = self::filterInput($input['name']);

       //Name must not be present in DB
        if (countElementsInTable(getTableForItemType(__CLASS__), ['name' => $input['name']])) {
            Session::addMessageAfterRedirect(__("A type already exists with the same name", "genericobject"), ERROR, true);
            return [];
        } else {
            $input['itemtype'] = self::getClassByName($input['name']);
            return $input;
        }
    }

    // @codingStandardsIgnoreStart
    public function post_addItem()
    {
        // @codingStandardsIgnoreEnd
        self::addNewObject(
            $this->input["name"],
            $this->input["itemtype"],
            ['add_table' => 1, 'create_default_profile' => 1, 'overwrite_locales' => true]
        );
        return true;
    }

    public function prepareInputForUpdate($input)
    {
       // Handle impact_icon
        $input = $this->handleImpactIconUpdate($input);

       // Handle use_impact
        $input = $this->handleUseImpactUpdate($input);

       //If itemtype is active : register it !
        if (isset($input["is_active"]) && $input["is_active"]) {
            self::registerOneType($this->fields['itemtype']);
        }
        return $input;
    }

    public function handleImpactIconUpdate($input)
    {
       // Read submitted icon
        $icon = $input['_impact_icon'][0] ?? null;

       // Icon wasn't submitted, nothing more to do
        if (empty($icon)) {
            return $input;
        }

       // Convert to realpath
        $icon_path = realpath(GLPI_TMP_DIR . "/$icon");

       // Realpath didn't find the file, shouldn't really happenn but just in case
        if (!$icon_path) {
            return $input;
        }

       // Wrong file type, ignore
        if (!Document::isImage($icon_path)) {
            return $input;
        }

       // File is outside of GLPI_TMP_DIR
        if (!str_starts_with($icon_path, realpath(GLPI_TMP_DIR))) {
            trigger_error("Trying to read forbidden file: $icon_path", E_USER_WARNING);
            return $input;
        }

       // Reread base file name
        $icon_filename = pathinfo($icon_path, PATHINFO_BASENAME);

       // Remove previous icon if exist
        $existing_icon_path = self::getImpactIconFileStoragePath(
            $this->fields['impact_icon'],
            $this->fields['itemtype']
        );
        if (
            $existing_icon_path
            && file_exists($existing_icon_path)
            && str_starts_with(
                realpath($existing_icon_path),
                realpath(GLPI_PLUGIN_DOC_DIR . "/genericobject/impact_icons/")
            )
        ) {
            unlink($existing_icon_path);
        }

       // Move file and update input on success
        $icons_dir = GLPI_PLUGIN_DOC_DIR . '/genericobject/impact_icons/';
        if (!is_dir($icons_dir) && !mkdir($icons_dir)) {
            trigger_error(sprintf('Unable to create "%s" directory.', $icons_dir), E_USER_WARNING);
            return $input;
        }

        $new_path = self::getImpactIconFileStoragePath(
            $icon_filename,
            $this->fields['itemtype']
        );
        if (rename($icon_path, $new_path)) {
            $input['impact_icon'] = $icon_filename;
        }

        return $input;
    }

    public function handleUseImpactUpdate($input)
    {
        $use_impact = $input['use_impact'] ?? null;
        unset($input['use_impact']);

       // Value wasn't modified, nothing to be done
        if ($use_impact === null) {
            return $input;
        }

       // Impact analysis will now be enabled, update conf if needed
        if ($use_impact && !Impact::isEnabled($this->fields['itemtype'])) {
            $enabled = Config::getConfigurationValue('core', Impact::CONF_ENABLED);
            $enabled = importArrayFromDB($enabled);
            $enabled[] = $this->fields['itemtype'];
            Config::setConfigurationValues('core', [
                Impact::CONF_ENABLED => exportArrayToDB($enabled)
            ]);
            return $input;
        }

       // Impact analysis will now be disabled, update config if needed
        if (!$use_impact && Impact::isEnabled($this->fields['itemtype'])) {
            $enabled = Config::getConfigurationValue('core', Impact::CONF_ENABLED);
            $enabled = importArrayFromDB($enabled);
            $enabled = array_filter(
                $enabled,
                fn($i) => $i != $this->fields['itemtype']
            );
            Config::setConfigurationValues('core', [
                Impact::CONF_ENABLED => exportArrayToDB($enabled)
            ]);

            return $input;
        }

        return $input;
    }

    // @codingStandardsIgnoreStart
    public function post_updateItem($history = true)
    {
        // @codingStandardsIgnoreEnd
       //Check if some fields need to be added, because of GLPI framework
        $this->checkNecessaryFieldsUpdate();
    }

    // @codingStandardsIgnoreStart
    public function pre_deleteItem()
    {
        // @codingStandardsIgnoreEnd
        if ($this->getFromDB($this->fields["id"])) {
            $name     = $this->fields['name'];
            $itemtype = $this->fields['itemtype'];

           //Delete all network ports
            self::deleteNetworking($itemtype);

           //Drop all dropdowns associated with itemtype
            self::deleteDropdownsForItemtype($itemtype);

           //Delete loans associated with this type
            self::deleteLoans($itemtype);

           //Delete loans associated with this type
            self::deleteUnicity($itemtype);

           //Delete reservations with this tyoe
            self::deleteReservations($itemtype);
            self::deleteReservationItems($itemtype);

           //Remove datainjection specific file
            self::deleteInjectionFile($name);

           //Delete profile informations associated with this type
            PluginGenericobjectProfile::deleteTypeFromProfile($itemtype);

            self::deleteTicketAssignation($itemtype);

           //Remove associations to simcards with this type
            self::deleteSimcardAssignation($itemtype);

           //Remove existing datainjection models
            self::removeDataInjectionModels($itemtype);

           //Delete specific locale directory
            self::deleteLocales($name, $itemtype);

            self::deleteItemtypeReferencesInGLPI($itemtype);

            self::deleteItemTypeFilesAndClasses($name, $this->getTable(), $itemtype);

           //self::deleteNotepad($itemtype);

            if (preg_match("/PluginGenericobject(.*)/", $itemtype, $results)) {
                  $newrightname = 'plugin_genericobject_' . strtolower($results[1]) . 's';
                ProfileRight::deleteProfileRights([$newrightname]);
            }

            $prof     = new Profile();
            $profiles = getAllDataFromTable('glpi_profiles');
            foreach ($profiles as $profile) {
                $helpdesk_item_types = json_decode($profile['helpdesk_item_type'], true);
                if ($helpdesk_item_types !== null) {
                    $index               = array_search($itemtype, $helpdesk_item_types);
                    if ($index) {
                        unset($helpdesk_item_types[$index]);
                        $tmp['id']                 = $profile['id'];
                        $tmp['helpdesk_item_type'] = json_encode($helpdesk_item_types);
                        $prof->update($tmp);
                    }
                }
            }

            return true;
        } else {
            return false;
        }
    }

    // @codingStandardsIgnoreStart
    public function post_deleteItem()
    {
        // @codingStandardsIgnoreEnd
    }

    public function rawSearchOptions()
    {
        $sopt = [];

        $sopt[] = [
            'id'   => 'common',
            'name' => __("Objects management", "genericobject"),
        ];

        $sopt[] = [
            'id'            => 1,
            'table'         => $this->getTable(),
            'field'         => 'name',
            'name'          => __('Model'),
            'datatype'      => 'itemlink',
            'autocomplete'  => true,
        ];

        $sopt[] = [
            'id'            => 5,
            'table'         => $this->getTable(),
            'field'         => 'is_active',
            'name'          => __('Active'),
            'datatype'      => 'bool',
        ];

        $sopt[] = [
            'id'            => 6,
            'table'         => $this->getTable(),
            'field'         => 'use_tickets',
            'name'          => __('Associable to a ticket'),
            'datatype'      => 'bool',
        ];

        $sopt[] = [
            'id'            => 9,
            'table'         => $this->getTable(),
            'field'         => 'use_history',
            'name'          => _sx('button', 'Use') . ' ' . __('Historical'),
            'datatype'      => 'bool',
        ];

        $sopt[] = [
            'id'            => 13,
            'table'         => $this->getTable(),
            'field'         => 'use_infocoms',
            'name'          => _sx('button', 'Use') . ' ' . __('Financial and administratives information'),
            'datatype'      => 'bool',
        ];

        $sopt[] = [
            'id'            => 14,
            'table'         => $this->getTable(),
            'field'         => 'use_documents',
            'name'          => _sx('button', 'Use') . ' ' . _n('Document', 'Documents', 2),
            'datatype'      => 'bool',
        ];

        $sopt[] = [
            'id'            => 15,
            'table'         => $this->getTable(),
            'field'         => 'use_loans',
            'name'          => _sx('button', 'Use') . ' ' . _n('Reservation', 'Reservations', 2),
            'datatype'      => 'bool',
        ];

        $sopt[] = [
            'id'            => 16,
            'table'         => $this->getTable(),
            'field'         => 'use_contracts',
            'name'          => _sx('button', 'Use') . ' ' . _n('Contract', 'Contracts', 2),
            'datatype'      => 'bool',
        ];

        $sopt[] = [
            'id'            => 17,
            'table'         => $this->getTable(),
            'field'         => 'use_unicity',
            'name'          => _sx('button', 'Use') . ' ' . __('Fields unicity'),
            'datatype'      => 'bool',
        ];

        $sopt[] = [
            'id'            => 18,
            'table'         => $this->getTable(),
            'field'         => 'use_global_search',
            'name'          => __('Global search'),
            'datatype'      => 'bool',
        ];

        $sopt[] = [
            'id'            => 19,
            'table'         => 'glpi_plugin_genericobject_typefamilies',
            'field'         => 'name',
            'name'          => __('Family of type of objects', 'genericobject'),
            'datatype'      => 'dropdown',
        ];

        $sopt[] = [
            'id'            => 20,
            'table'         => $this->getTable(),
            'field'         => 'use_projects',
            'name'          => _n('Project', 'Projects', 2),
            'datatype'      => 'bool',
        ];

        $sopt[] = [
            'id'            => 21,
            'table'         => $this->getTable(),
            'field'         => 'date_mod',
            'name'          => __('Last update'),
            'datatype'      => 'datetime',
            'massiveaction' => false,
        ];

        $sopt[] = [
            'id'            => 22,
            'table'         => $this->getTable(),
            'field'         => 'use_itemdevices',
            'name'          => _sx('button', 'Use') . ' ' . _n('Component', 'Components', 2),
            'datatype'      => 'bool',
        ];

        $sopt[] = [
            'id'            => 121,
            'table'         => $this->getTable(),
            'field'         => 'date_creation',
            'name'          => __('Creation date'),
            'datatype'      => 'datetime',
            'massiveaction' => false,
        ];

        return $sopt;
    }

   /**
    * Define name of type to display in menu
    *
    * @return string type name
    */
    public static function getMenuName()
    {
        return __('Objects management', 'genericobject');
    }

   //------------------------------------- End Framework hooks -----------------------------

   //------------------------------------- Forms -------------------------------------------
    public function showForm($ID, $options = [])
    {

        if ($ID > 0) {
            $this->check($ID, READ);
        } else {
           // Create item
            $this->check(-1, CREATE);
            $this->getEmpty();
        }

        $this->initForm($ID);

        $item = new self();
        $item->showBehaviorForm($ID);

        return true;
    }

    public function showBehaviorForm($ID, $options = [])
    {
        /** @var array $CFG_GLPI */
        global $CFG_GLPI;

        if ($ID > 0) {
            $this->check($ID, READ);
        } else {
           // Create item
            $this->check($ID, CREATE);
            $use_cache = false;
            $this->getEmpty();
        }

        $this->fields['id'] = $ID;

        $right_name = PluginGenericobjectProfile::getProfileNameForItemtype(
            __CLASS__
        );

        $canedit = Session::haveRight($right_name, UPDATE);

        self::includeLocales($this->fields["name"]);
        self::includeConstants($this->fields["name"]);

        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Internal identifier", "genericobject") . "</td>";
        echo "<td>";
        if (!$ID) {
            echo Html::input(
                'name',
                [
                    'value' => $this->fields['name'],
                ]
            );
        } else {
            echo "<input type='hidden' name='name' value='" . $this->fields["name"] . "'>";
            echo $this->fields["name"];
        }

        echo "</td>";
        echo "<td></td>";
        echo "<td></td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Label") . "</td>";
        echo "<td>";
        if ($ID) {
            $itemtype = $this->fields["itemtype"];
            echo $itemtype::getTypeName();
        }
        echo "</td>";
        echo "<td rowspan='3' class='middle right'>" . __("Comments") . "&nbsp;: </td>";
        echo "<td class='center middle' rowspan='3'><textarea cols='45' rows='4'
             name='comment' >" . $this->fields["comment"] . "</textarea></td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Active") . "</td>";
        echo "<td>";
        if (!$ID) {
            echo __("No");
        } else {
            Dropdown::showYesNo("is_active", $this->fields["is_active"]);
        }
        echo "</td></td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Family of type of objects", 'genericobject') . "</td>";
        echo "<td>";
        PluginGenericobjectTypeFamily::dropdown([
            'value' => $this->fields["plugin_genericobject_typefamilies_id"]
        ]);
        echo "</td></td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'></td>";
        echo "</tr>";

        if (!$this->isNewID($ID)) {
            $canedit = $this->can($ID, CREATE);
            echo "<tr class='tab_bg_1'><th colspan='4'>";
            echo __("Behaviour", "genericobject");
            echo "</th></tr>";

            $use = [
                "use_recursivity"   => __("Child entities"),
                "use_tickets"       => __("Assistance"),
                "use_deleted"       => __("Item in the dustbin"),
                "use_notepad"       => _n('Note', 'Notes', 2),
                "use_history"       => __("Historical"),
                "use_template"      => __("Templates"),
                "use_infocoms"      => __("Financial and administratives information"),
                "use_contracts"     => _n("Contract", "Contracts", 2),
                "use_documents"     => _n("Document", "Documents", 2),
                "use_loans"         => _n("Reservation", "Reservations", 2),
              // Disable unicity feature; see #16
              // Related code : search for #16
                "use_unicity"       => __("Fields unicity"),
                "use_global_search" => __("Global search"),
                "use_projects"      => _n("Project", "Projects", 2),
                "use_network_ports" => __("Network connections", "genericobject"),
                "use_itemdevices"   => _n('Component', 'Components', 2),
                "use_impact"        => Impact::getTypeName(),
            ];

            $plugins = [
                "use_plugin_datainjection"      => __("injection file plugin", "genericobject"),
            //"use_plugin_pdf"                => __("PDF plugin", "genericobject"),
                "use_plugin_geninventorynumber" => __("geninventorynumber plugin", "genericobject"),
                "use_plugin_order"              => __("order plugin", "genericobject"),
                "use_plugin_uninstall"          => __("item's uninstallation plugin", "genericobject"),
                "use_plugin_simcard"            => __("simcard plugin", "genericobject"),
                "use_plugin_treeview"           => __("treeview plugin", "genericobject"),
            ];

            $plugin = new Plugin();
            $odd = 0;
            foreach ($use as $right => $label) {
                if (!$odd) {
                    echo "<tr class='tab_bg_2'>";
                }
                echo "<td>" . _sx('button', 'Use') . " " . $label . "</td>";
                echo "<td>";

                switch ($right) {
                    case 'use_deleted':
                        Html::showCheckbox(['name'    => $right,
                            'checked' => $this->canBeDeleted()
                        ]);
                        break;

                    case 'use_recursivity':
                        Html::showCheckbox(['name'    => $right,
                            'value'   => $this->canBeRecursive(),
                            'checked' => $this->canBeRecursive()
                        ]);
                        break;

                    case 'use_notes':
                        Html::showCheckbox(['name'    => $right,
                            'checked' => $this->canUseNotepad()
                        ]);
                        break;

                    case 'use_template':
                        Html::showCheckbox(['name'    => $right,
                            'checked' => $this->canUseTemplate()
                        ]);
                        break;

                    case 'use_impact':
                        Html::showCheckbox([
                            'name'    => $right,
                            'checked' => Impact::isEnabled($this->fields['itemtype'])
                        ]);
                        break;

                    default:
                        Html::showCheckbox(['name'    => $right,
                            'checked' => $this->fields[$right]
                        ]);
                        break;
                }
                echo "</td>";
                if ($odd == 1) {
                    $odd = 0;
                    echo "</tr>";
                } else {
                    $odd++;
                }
            }
            if ($odd != 0) {
                echo "<td></td></tr>";
            }

            echo "<tr class='tab_bg_1'><th colspan='4'>";
            echo __("Icon (impact analysis)", "genericobject");
            echo "</th></tr>";

            echo '<tr>';
            echo "<td colspan='4'>";
            $src = $this->getImpactIconUrl() ?? $CFG_GLPI["root_doc"] . "/pics/impact/default.png";
            echo "<img src='$src' height='128px'></img>";
            echo "</td>";
            echo '</tr>';

            echo '<tr>';
            echo "<td colspan='2'>";
            echo Html::file([
                'name'       => "impact_icon",
                'onlyimages' => true,
            ]);
            echo "</td>";
            echo "<td></td>";
            echo '</tr>';

            echo "<tr class='tab_bg_1'><th colspan='4'>";
            echo _n("Plugin", "Plugins", 2);
            echo "</th></tr>";
            $odd = 0;
            foreach ($plugins as $right => $label) {
                if (!$odd) {
                    echo "<tr class='tab_bg_2'>";
                }
                echo "<td>" . _sx('button', 'Use') . " " . $label . "</td>";
                echo "<td>";
                switch ($right) {
                    case 'use_plugin_datainjection':
                        if ($plugin->isActivated('datainjection')) {
                            Html::showCheckbox(['name'    => $right,
                                'checked' => $this->fields[$right]
                            ]);
                        } else {
                            echo Dropdown::EMPTY_VALUE;
                            echo "<input type='hidden' name='use_plugin_datainjection' value='0'>\n";
                        }
                        break;

                    case 'use_plugin_pdf':
                        if ($plugin->isActivated('pdf')) {
                            Html::showCheckbox(['name'    => $right,
                                'checked' => $this->fields[$right]
                            ]);
                        } else {
                            echo Dropdown::EMPTY_VALUE;
                            echo "<input type='hidden' name='use_plugin_pdf' value='0'>\n";
                        }
                        break;

                    case 'use_plugin_order':
                        if ($plugin->isActivated('order')) {
                            Html::showCheckbox(['name'    => $right,
                                'checked' => $this->fields[$right]
                            ]);
                        } else {
                            echo Dropdown::EMPTY_VALUE;
                            echo "<input type='hidden' name='use_plugin_order' value='0'>\n";
                        }
                        break;

                    case 'use_plugin_uninstall':
                        if ($plugin->isActivated('uninstall')) {
                            Html::showCheckbox(['name'    => $right,
                                'checked' => $this->fields[$right]
                            ]);
                        } else {
                            echo Dropdown::EMPTY_VALUE;
                            echo "<input type='hidden' name='use_plugin_uninstall' value='0'>\n";
                        }
                        break;

                    case 'use_plugin_simcard':
                        if ($plugin->isActivated('simcard')) {
                            Html::showCheckbox(['name'    => $right,
                                'checked' => $this->fields[$right]
                            ]);
                        } else {
                            echo Dropdown::EMPTY_VALUE;
                            echo "<input type='hidden' name='use_plugin_simcard' value='0'>\n";
                        }
                        break;
                    case 'use_plugin_treeview':
                        if ($plugin->isActivated('treeview')) {
                            Html::showCheckbox(['name' => $right,
                                'checked' => $this->fields[$right]
                            ]);
                        } else {
                            echo Dropdown::EMPTY_VALUE;
                            echo "<input type='hidden' name='use_plugin_treeview' value='0'>\n";
                        }

                        break;
                    case 'use_plugin_geninventorynumber':
                        if ($plugin->isActivated('geninventorynumber')) {
                            Html::showCheckbox(['name'    => $right,
                                'checked' => $this->fields[$right]
                            ]);
                        } else {
                            echo Dropdown::EMPTY_VALUE;
                            echo "<input type='hidden' name='use_plugin_geninventorynumber' value='0'>\n";
                        }
                        break;
                }
                echo "</td>";
                if ($odd == 1) {
                       $odd = 0;
                       echo "</tr>";
                } else {
                    $odd++;
                }
            }
            if ($odd != 0) {
                echo "<td></td></tr>";
            }
        }

        $this->showFormButtons($options);
    }

   /**
    *
    * Show a form with a button to regenerate all files
    * @since 2.2.0
    * @param $ID type ID
    * @return void
    */
    public function showFilesForm()
    {
        echo "<form name='generate' method='post'>";
        echo "<div class='center'>";
        echo "<table class='tab_cadre_fixe'>";
        echo "<tr class='tab_bg_1'>";
        echo "<td class='center'>";
        echo "<input type='hidden' name='id' value='" . $this->getID() . "'>";
        echo "<input type='submit' class='submit' name='regenerate'
                    value='" . __("Regenerate files", "genericobject") . "'>";
        echo "</td></tr></table></div>";
        Html::closeForm();
    }

    public function showLinkedTypesForm()
    {
        /** @var array $GO_LINKED_TYPES */
        global $GO_LINKED_TYPES;

        $this->showFormHeader();
        echo "<form name='link' method='post'>";
        echo "<div class='center'>";
        echo "<table class='tab_cadre_fixe'>";
        echo "<tr class='tab_bg_1'>";
        echo "<th colspan='2'>" . __("Link to other objects", "genericobject") . "</th></tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . _n("Type", "Types", 2) . "</td>";
        echo "<td class='center'>";
        echo "<select name='itemtypes[]' multiple size='10'>";
        $selected = [];
        if (!empty($this->fields['linked_itemtypes'])) {
            $selected = json_decode($this->fields['linked_itemtypes'], false);
        }
        foreach ($GO_LINKED_TYPES as $itemtype) {
            if ($itemtype == $this->fields['itemtype']) {
                continue;
            }
            echo "<option value='$itemtype'";
            if (in_array($itemtype, $selected)) {
                echo " selected ";
            }
            echo ">" . $itemtype::getTypeName() . "</options>";
        }
        echo "</select>";
        echo "</td></tr>";
        echo "<input type='hidden' name='id' value='" . $this->getID() . "'>";
        $this->showFormButtons(['candel' => false, 'canadd' => false]);
        Html::closeForm();
    }
   //------------------------------------- End Forms --------------------------------------

   /**
    * Create an object, it's table, files and rights
    *
    * @since 2.1.5
    * @param string $name object short name
    * @param string $itemtype object class name
    * @param array $options create options :
    *    - add_table : add the object table (default is no)
    *    - create_default_profile : add default right (default is no) for current user profile
    *    - add_injection_file : add file to integrate itemtype into the datainjection plugin
    *    - add_language_file : create a default language for the itemtype
    * @return void
    */
    public static function addNewObject($name, $itemtype, $options = [])
    {
        $params['add_table']              = false;
        $params['create_default_profile'] = false;
        $params['add_injection_file']     = false;
        $params['add_language_file']      = true;
        $params['overwrite_locales']      = false;

        foreach ($options as $key => $value) {
            $params[$key] = $value;
        }

        if ($params['add_table']) {
            self::addTable($itemtype);
        }

       //Write object class on the filesystem
        self::addClassFile($name, $itemtype);

       //Write the form on the filesystem
        self::addFormFile($name, $itemtype);
        self::addSearchFile($name, $itemtype);

        if ($params['overwrite_locales']) {
           //Add language file
            self::addLocales($name, $itemtype);
        }

       //Add file needed by datainjectin plugin
        if ($params['add_injection_file']) {
            self::addDatainjectionFile($name);
        }
        PluginGenericobjectProfile::installRights();
        if ($params['create_default_profile']) {
           //Create rights for this new object
            PluginGenericobjectProfile::createAccess($_SESSION["glpiactiveprofile"]["id"], $itemtype, true);
           //Reload profiles
            PluginGenericobjectProfile::changeProfile();
        }
    }

   /**
    *
    * Add a new dropdown :class & files
    * @param string $name
    * @param string $itemtype
    * @param array $options
    */
    public static function addNewDropdown($name, $itemtype, $options = [])
    {
        $params['entities_id']     = false;
        $params['is_recursive']    = false;
        $params['is_tree']         = false;
        $params['linked_itemtype'] = false;
        foreach ($options as $key => $value) {
            $params[$key] = $value;
        }
       //Add files on the disk
        self::addDropdownClassFile($name, $itemtype, $params);
        self::addDropdownTable(getTableForItemType($itemtype), $params);
        self::addDropdownFrontFile($name);
        self::addDropdownFrontformFile($name);

       // Invalidate submenu data in current session
        unset($_SESSION['glpimenu']);
    }

   /**
    *
    * Add or delete, if needed some fields to make sure that the itemtype is compatible with
    * GLPI framework
    */
    public function checkNecessaryFieldsUpdate()
    {
        /** @var DBmysql $DB */
        global $DB;

        $itemtype = $this->fields["itemtype"];
        $item     = new $itemtype();
        $item->getEmpty();
        $table    = getTableForItemType($itemtype);

       //Global search (inventory > status)
        if (isset($this->input['use_global_search']) && $this->input['use_global_search']) {
            PluginGenericobjectField::addNewField($table, 'serial', 'name');
            PluginGenericobjectField::addNewField($table, 'otherserial', 'serial');
            PluginGenericobjectField::addNewField($table, 'locations_id', 'otherserial');
            PluginGenericobjectField::addNewField($table, 'states_id', 'locations_id');
            PluginGenericobjectField::addNewField($table, 'users_id', 'states_id');
            PluginGenericobjectField::addNewField($table, 'groups_id', 'users_id');
            PluginGenericobjectField::addNewField($table, 'manufacturers_id', 'groups_id');
            PluginGenericobjectField::addNewField($table, 'users_id_tech', 'manufacturers_id');
            PluginGenericobjectField::addNewField($table, 'is_deleted', 'id');
        }

        if (isset($this->input['use_recursivity']) && $this->input['use_recursivity']) {
            PluginGenericobjectField::addNewField($table, 'is_recursive', 'entities_id');
        } else {
            PluginGenericobjectField::deleteField($table, 'is_recursive');
        }

       //Template
        if (isset($this->input['use_template']) && $this->input['use_template']) {
            PluginGenericobjectField::addNewField($table, 'is_template', 'id');
            PluginGenericobjectField::addNewField($table, 'template_name', 'is_template');
        } else {
            PluginGenericobjectField::deleteField($table, 'is_template');
            PluginGenericobjectField::deleteField($table, 'template_name');
        }

       //Trash
        if (isset($this->input['use_deleted']) && $this->input['use_deleted']) {
            PluginGenericobjectField::addNewField($table, 'is_deleted', 'id');
        } else {
            if (!$this->canBeReserved()) {
                PluginGenericobjectField::deleteField($table, 'is_deleted');
            } else {
                _log($DB->fieldExists($table, 'is_deleted'));
                if ($DB->fieldExists($table, 'is_deleted')) {
                    Session::addMessageAfterRedirect(
                        __("Dustbin can't be removed since Reservations are used on this type."),
                        false,
                        WARNING
                    );
                }
            }
        }

       //Reservation needs is_deleted field !
        if ($this->canBeReserved()) {
            PluginGenericobjectField::addNewField($table, 'is_deleted', 'id');
            PluginGenericobjectField::addNewField($table, 'locations_id');
            PluginGenericobjectField::addNewField($table, 'users_id');
        }

       //Helpdesk post-only
        if ($this->canUseTickets()) {
           //TODO rename is_helpdesk_visible into is_helpdeskvisible
            PluginGenericobjectField::addNewField($table, 'is_helpdesk_visible', 'comment');
        } else {
            PluginGenericobjectField::deleteField($table, 'is_helpdesk_visible');
        }

       //Notes
        if (isset($this->input['use_notepad']) && $this->input['use_notepad']) {
            PluginGenericobjectField::addNewField($table, 'notepad', 'id');
        } else {
            PluginGenericobjectField::deleteField($table, 'notepad');
        }

       //Networkport
        if ($this->canUseNetworkPorts()) {
            PluginGenericobjectField::addNewField($table, 'locations_id');
        }

        if ($this->canUseDirectConnections()) {
            self::addItemsTable($itemtype);
           //self::addItemClassFile($this->fields['name'], $itemtype);
        } else {
            self::deleteItemsTable($itemtype);
            self::deleteClassFile($this->fields['name'] . "_item");
        }

        if (
            $this->canUsePluginDataInjection() &&
            !file_exists(self::getCompleteInjectionFilename($this->fields['name']))
        ) {
            self::addDatainjectionFile($this->fields['name']);
        }

        if (
            !$this->canUsePluginDataInjection() &&
            file_exists(self::getCompleteInjectionFilename($this->fields['name']))
        ) {
            self::deleteInjectionFile($this->fields['name']);
        }

       //Device item needs locations_id field !
        if ($this->canUseItemDevice()) {
            PluginGenericobjectField::addNewField($table, 'locations_id');
        }
    }


   /**
    * Add object type table + entries in glpi_display
    * @name object type's name
    * @return void
    */
    public static function addTable($itemtype)
    {
        /** @var DBmysql $DB */
        global $DB;

        $default_charset = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

        $query = "CREATE TABLE IF NOT EXISTS `" . getTableForItemType($itemtype) . "` (
                  `id` INT {$default_key_sign} NOT NULL AUTO_INCREMENT,
                  `entities_id` INT {$default_key_sign} NOT NULL DEFAULT '0',
                  `name` VARCHAR( 255 ) NOT NULL DEFAULT '',
                  `comment` text,
                  `notepad` text,
                  `date_mod` TIMESTAMP NULL DEFAULT NULL,
                  `date_creation` TIMESTAMP NULL DEFAULT NULL,
                  PRIMARY KEY ( `id` ),
                  KEY `date_mod` (`date_mod`),
                  KEY `date_creation` (`date_creation`)
                  ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
        $DB->query($query);

        $query = "INSERT INTO `glpi_displaypreferences` (`id`, `itemtype`, `num`, `rank`, `users_id`) " .
               "VALUES (NULL, '$itemtype', '2', '1', '0');";
        $DB->query($query);
    }

   /**
    * Add object_items table to connect an object to others
    * @name object type's name
    * @return void
    */
    public static function addItemsTable($itemtype)
    {
        /** @var DBmysql $DB */
        global $DB;

        $default_charset = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

        $table = getTableForItemType($itemtype);
        $fk    = getForeignKeyFieldForTable($table);
        $query = "CREATE TABLE IF NOT EXISTS `" . getTableForItemType($itemtype) . "_items` (
        `id` int {$default_key_sign} NOT NULL AUTO_INCREMENT,
        `items_id` int {$default_key_sign} NOT NULL DEFAULT '0' COMMENT 'RELATION to various table, according to itemtype (ID)',
        `date_mod` TIMESTAMP NULL DEFAULT NULL,
        `date_creation` TIMESTAMP NULL DEFAULT NULL,
        `$fk` int {$default_key_sign} NOT NULL DEFAULT '0',
        `itemtype` varchar(100) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `$fk` (`$fk`),
        KEY `date_mod` (`date_mod`),
        KEY `date_creation` (`date_creation`),
        KEY `item` (`itemtype`,`items_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
        $DB->query($query);
    }


   //-------------------------------- FILE CREATION / DELETION ----------------------------//
    public static function deleteFile($filename)
    {
        if (file_exists($filename)) {
            unlink($filename);
        }
    }


    public static function getCompleteClassFilename($name)
    {
        return GENERICOBJECT_CLASS_PATH . "/" . self::getSystemName($name) . ".class.php";
    }


    public static function getCompleteItemClassFilename($name)
    {
        return GENERICOBJECT_CLASS_PATH . "/" . self::getSystemName($name) . "_item.class.php";
    }


    public static function getCompleteFormFilename($name)
    {
        return GENERICOBJECT_FRONT_PATH . "/" . self::getSystemName($name) . ".form.php";
    }


    public static function getCompleteSearchFilename($name)
    {
        return GENERICOBJECT_FRONT_PATH . "/" . self::getSystemName($name) . ".php";
    }


    public static function getCompleteAjaxTabFilename($name)
    {
        return GENERICOBJECT_AJAX_PATH . "/" . self::getSystemName($name) . ".tabs.php";
    }


    public static function getCompleteInjectionFilename($name)
    {
        return GENERICOBJECT_CLASS_PATH . "/" . self::getSystemName($name) . ".injection.class.php";
    }


    public static function getCompleteConstantFilename($name)
    {
        return GENERICOBJECT_FIELDS_PATH . "/" . self::getSystemName($name) . ".constant.php";
    }


   /**
    * Delete an used form file
    * @param string $name the name of the object type
    * @return void
    */
    public static function deleteFormFile($name)
    {
        self::deleteFile(self::getCompleteFormFilename($name));
    }


    public static function deleteSearchFile($name)
    {
        self::deleteFile(self::getCompleteSearchFilename($name));
    }


    public static function deleteAjaxFile($name)
    {
        self::deleteFile(self::getCompleteAjaxTabFilename($name));
    }


   /**
    * Delete an used class file
    * @param string $name the name of the object type
    * @return void
    */
    public static function deleteClassFile($name)
    {
        self::deleteFile(self::getCompleteClassFilename($name));
    }


    public static function deleteInjectionFile($name)
    {
        self::deleteFile(self::getCompleteInjectionFilename($name));
    }


    public static function addLocales($name, $itemtype)
    {
        /** @var array $CFG_GLPI */
        global $CFG_GLPI;

        $fsname = self::getSystemName($name);

        $locale_dir = GENERICOBJECT_LOCALES_PATH . "/" . $fsname;
        if (!is_dir($locale_dir)) {
            @ mkdir($locale_dir, 0755, true);
        }

        $locale_files = [
            $fsname . '.' . $_SESSION['glpilanguage'],
        ];
        if ($CFG_GLPI['language'] != $_SESSION['glpilanguage']) {
            $locale_files[] = $fsname . '.' . $CFG_GLPI['language'];
        }

        foreach ($locale_files as $locale_file) {
            self::addFileFromTemplate(
                [
                    'NAME'      => $name,
                    'CLASSNAME' => self::getClassByName($name),
                ],
                self::LOCALE_TEMPLATE,
                $locale_dir,
                $locale_file
            );
        }
    }


    public static function deleteLocales($name, $itemtype)
    {
        $locale_dir = GENERICOBJECT_LOCALES_PATH . "/" . self::getSystemName($name);
        if (file_exists($locale_dir)) {
            foreach (glob($locale_dir . '/*.php') as $file) {
                @unlink($file);
            }
            @rmdir($locale_dir);
        }
    }


    public static function addFileFromTemplate(
        $mappings,
        $template,
        $directory,
        $filename
    ) {
        if (!empty($mappings)) {
            $file_read = @fopen(GENERICOBJECT_DIR . $template, "rt");
            if ($file_read) {
                $template_file = fread($file_read, filesize(GENERICOBJECT_DIR . $template));
                foreach ($mappings as $name => $value) {
                    $template_file = str_replace("%%$name%%", $value, $template_file);
                }
                fclose($file_read);
                $file_write = @fopen($directory . "/" . $filename . ".php", "w");
                if ($file_write) {
                    fwrite($file_write, $template_file);
                    fclose($file_write);
                }
            }
        }
    }


    public static function addDatainjectionFile($name)
    {
        self::addFileFromTemplate(
            ['CLASSNAME' => self::getClassByName($name),
                'INJECTIONCLASS' => self::getClassByName($name) . "Injection"
            ],
            self::OBJECTINJECTION_TEMPLATE,
            GENERICOBJECT_CLASS_PATH,
            self::getSystemName($name) . "injection.class"
        );
    }


    public static function addDropdownFrontFile($name)
    {
        self::addFileFromTemplate(
            ['CLASSNAME' => self::getClassByName($name)],
            self::FRONT_DROPDOWN_TEMPLATE,
            GENERICOBJECT_FRONT_PATH,
            self::getSystemName($name)
        );
    }


    public static function addAjaxFile($name, $field)
    {
        self::addFileFromTemplate(
            ['CLASSNAME' => self::getClassByName($name)],
            self::AJAX_TEMPLATE,
            GENERICOBJECT_AJAX_PATH,
            self::getSystemName($name) . ".tabs"
        );
    }


    public static function addDropdownFrontformFile($name)
    {
        self::addFileFromTemplate(
            ['CLASSNAME' => self::getClassByName($name)],
            self::FRONTFORM_DROPDOWN_TEMPLATE,
            GENERICOBJECT_FRONT_PATH,
            self::getSystemName($name) . ".form"
        );
    }


    public static function addDropdownClassFile($name, $field, $options)
    {
        $params['is_tree']            = false;
        $params['realname']        = false;
        $params['linked_itemtype'] = false;
        foreach ($options as $key => $value) {
            $params[$key] = $value;
        }
        self::addFileFromTemplate([
            'CLASSNAME'       => self::getClassByName($name),
            'EXTENDS'         =>
            'PluginGenericobject' . ($params['is_tree'] ? 'CommonTree' : 'Common') . 'Dropdown',
            'FIELDNAME'       => $params['realname'],
            'LINKED_ITEMTYPE' => $params['linked_itemtype']
        ], self::CLASS_DROPDOWN_TEMPLATE, GENERICOBJECT_CLASS_PATH, self::getSystemName($name) . ".class");
    }


   /**
    * Write on the the class file for the new object type
    * @param string $name the name of the object type
    * @param string $classname the name of the new object
    * @return void
    */
    public static function addClassFile($name, $classname)
    {
        self::addFileFromTemplate(
            ['CLASSNAME' => self::getClassByName($name)],
            self::CLASS_TEMPLATE,
            GENERICOBJECT_CLASS_PATH,
            self::getSystemName($name) . ".class"
        );
    }

   /**
    * Write on the the _Item class file for the new object type
    * @param string $name the name of the object type
    * @param string $classname the name of the new object
    * @return void
    */
    public static function addItemClassFile($name, $classname)
    {
        $class = self::getClassByName($name) . "_Item";
        self::addFileFromTemplate(
            ['CLASSNAME'    => $class,
                'FOREIGNKEY'   => getForeignKeyFieldForItemType($classname),
                'SOURCEOBJECT' => $classname
            ],
            self::OBJECTITEM_TEMPLATE,
            GENERICOBJECT_CLASS_PATH,
            self::getSystemName($name) . "_item.class"
        );
    }

   /**
    * Write on the the form file for the new object type
    * @param string $name the name of the object type
    * @param string $classname the name of the new object
    * @return void
    */
    public static function addFormFile($name, $classname)
    {
        self::addFileFromTemplate(
            ['CLASSNAME' => self::getClassByName($name)],
            self::FORM_TEMPLATE,
            GENERICOBJECT_FRONT_PATH,
            self::getSystemName($name) . ".form"
        );
    }


   /**
    * Write on the the form file for the new object type
    * @param string $name the name of the object type
    * @param string $classname the name of the new object
    * @return void
    */
    public static function addSearchFile($name, $classname)
    {
        self::addFileFromTemplate(
            ['CLASSNAME' => self::getClassByName($name)],
            self::SEARCH_TEMPLATE,
            GENERICOBJECT_FRONT_PATH,
            self::getSystemName($name)
        );
    }


   /**
    * Create, if needed files for an itemtype and it's dropdown
    *
    * @since 2.2.0
    *
    * @return void
    */
    public static function checkClassAndFilesForItemType()
    {
        foreach (self::getTypes(true) as $type) {
           //ensure old files has been removed,
            $fsname = self::getSystemName($type['name']);
            if (file_exists(GENERICOBJECT_DIR . "/inc/{$fsname}.class.php")) {
                unlink(GENERICOBJECT_DIR . "/inc/{$fsname}.class.php");
            }
            if (file_exists(GENERICOBJECT_DIR . "/front/{$fsname}.form.php")) {
                unlink(GENERICOBJECT_DIR . "/front/{$fsname}.form.php");
            }
            if (file_exists(GENERICOBJECT_DIR . "/front/{$fsname}.php")) {
                unlink(GENERICOBJECT_DIR . "/front/{$fsname}.form.php");
            }
            if (file_exists(GENERICOBJECT_DIR . "/ajax/{$fsname}.tabs.php")) {
                unlink(GENERICOBJECT_DIR . "/ajax/{$fsname}.tabs.php");
            }
            if (file_exists(GENERICOBJECT_DIR . "/inc/{$fsname}.injection.class.php")) {
                unlink(GENERICOBJECT_DIR . "/inc/{$fsname}.injection.class.php");
            }

            self::checkClassAndFilesForOneItemType($type['itemtype'], $type['name'], true, false);
        }
    }

   /**
    *
    * Create or overwrite files for an itemtype
    * @since 2.2.0
    * @param string $itemtype the itemtype to check
    * @param string $name type's short name
    * @param boolean $overwrite force to overwrite existing files
    * @param boolean $overwrite_locales force to overwrite existing locales
    * @return void
    */
    public static function checkClassAndFilesForOneItemType($itemtype, $name, $overwrite = false, $overwrite_locales = true)
    {
        /** @var DBmysql $DB */
        global $DB;
        $table = getTableForItemType($itemtype);

       //If class doesn't exist but table exists, create class
        if ($DB->tableExists($table) && ($overwrite || !class_exists($itemtype))) {
            self::addNewObject($name, $itemtype, ['add_table'              => false,
                'create_default_profile' => false,
                'add_injection_file'     => $overwrite,
                'add_language_file'      => false,
                'overwrite_locales'      => $overwrite_locales
            ]);
        }

        foreach ($DB->listFields($table) as $field => $options) {
            if (preg_match("/s_id$/", $field)) {
                $dropdowntable = getTableNameForForeignKeyField($field);
                $dropdownclass = getItemTypeForTable($dropdowntable);

                if ($DB->tableExists($dropdowntable) && ! class_exists($dropdownclass)) {
                    $name                       = str_replace("glpi_plugin_genericobject_", "", $dropdowntable);
                    $name                       = getSingular($name);
                    $params = PluginGenericobjectField::getFieldOptions($field, $dropdownclass);
                    if (
                        isset($params['dropdown_type'])
                        and $params['dropdown_type'] === 'isolated'
                    ) {
                        $params['linked_itemtype'] = $itemtype;
                    }
                    self::addNewDropdown($name, self::getClassByName($name), $params);
                }
            }
        }
    }

   /**
    *
    * Delete all files and classes for an itemtype (including dropdowns)
    * @since 2.2.0
    * @param string $name file name
    */
    public static function deleteItemTypeFilesAndClasses($name, $table, $itemtype)
    {
        /** @var DBmysql $DB */
        global $DB;

        _log("Delete Type", [
            "table" => $table,
            "name" => $name,
            "itemtype" => $itemtype,

        ]);
       //Delete files related to dropdowns
        foreach ($DB->listFields($table) as $field => $options) {
            if (preg_match("/plugin_genericobject_(.*)_id/", $field, $results)) {
                $table = getTableNameForForeignKeyField($field);

                if ($table != getTableForItemType("PluginGenericobjectTypeFamily")) {
                    self::deleteFilesAndClassesForOneItemtype(getSingular($results[1]));
                    $DB->query("DROP TABLE IF EXISTS `$table`");
                }
            }
        }

       //Delete reference in various GLPI core tables
        self::deleteItemtypeReferencesInGLPI($itemtype);

       //Delete itemtype files
        self::deleteFilesAndClassesForOneItemtype($name);

       //Drop itemtype table
        self::deleteItemsTable($itemtype);
        self::deleteTable($itemtype);
    }

   /**
    * Delete all files for an itemtype
    *
    * @since 2.2.0
    * @param string $name class file name
    */
    public static function deleteFilesAndClassesForOneItemtype($name)
    {
       //This is for compatibility with older versions of GLPI
       //(where ajax files were used for tabs display, which is not the case anymore with GLPI 0.83+)
        self::deleteAjaxFile($name);
       //Delete itemtype class
        self::deleteClassFile($name);
       //Delete forms
        self::deleteSearchFile($name);
        self::deleteFormFile($name);
       //Delete datainjection compatiblity file
        self::deleteInjectionFile($name);
    }

    public static function deleteItemtypeReferencesInGLPI($itemtype)
    {
       //Delete references to PluginGenericobjectType in the following tables
        $itemtypes =  ["Contract_Item", "DisplayPreference", "Document_Item", "SavedSearch", "Log"];
        foreach ($itemtypes as $type) {
            $item     = new $type();
            $item->deleteByCriteria(['itemtype' => $itemtype]);
        }
    }

   //-------------------- ADD / DELETE TABLES ----------------------------------//

   /**
    * Add a new dropdown table
    * @param string $table the table name
    * @param array $options
    * @return void
    */
    public static function addDropdownTable($table, $options = [])
    {
        /** @var DBmysql $DB */
        global $DB;

        $default_charset = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

        $params['entities_id']  = false;
        $params['is_recursive'] = false;
        $params['is_tree']      = false;
        foreach ($options as $key => $value) {
            $params[$key] = $value;
        }

        if (!$DB->tableExists($table)) {
            $query = "CREATE TABLE IF NOT EXISTS `$table` (
                       `id` int {$default_key_sign} NOT NULL auto_increment,
                       `name` varchar(255) default NULL,
                       `comment` text,
                       `date_mod` TIMESTAMP NULL DEFAULT NULL,
                       `date_creation` TIMESTAMP NOT NULL,
                       PRIMARY KEY  (`id`),
                       KEY `date_mod` (`date_mod`),
                       KEY `date_creation` (`date_creation`),
                       KEY `name` (`name`)
                     ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
            $DB->query($query);
        }
        if ($params['entities_id']) {
            $query = "ALTER TABLE `$table` ADD `entities_id` INT {$default_key_sign} NOT NULL DEFAULT '0'";
            $DB->query($query);
            if ($params['is_recursive']) {
                $query = "ALTER TABLE `$table` " .
                     "ADD `is_recursive` TINYINT NOT NULL DEFAULT '0' AFTER `entities_id`";
                $DB->query($query);
            }
        }
        if ($params['is_tree']) {
            $fk    = getForeignKeyFieldForTable($table);
            $query = "ALTER TABLE `$table` ADD `completename` text,
                                        ADD `$fk` int {$default_key_sign} NOT NULL DEFAULT '0',
                                        ADD `level` int NOT NULL DEFAULT '0',
                                        ADD `ancestors_cache` longtext,
                                        ADD `sons_cache` longtext";
            $DB->query($query);
        }
    }


   /**
    * Delete object type table + entries in glpi_display
    * @name string $itemtype object type's name
    * @return void
    */
    public static function deleteTable($itemtype)
    {
        /** @var DBmysql $DB */
        global $DB;
        _log($itemtype);
        $preferences = new DisplayPreference();
        $preferences->deleteByCriteria(["itemtype" => $itemtype]);
        $DB->query("DROP TABLE IF EXISTS `" . getTableForItemType($itemtype) . "`");
    }


   /**
    * Delete object _items table
    * @name string $itemtype object type's name
    * @return void
    */
    public static function deleteItemsTable($itemtype)
    {
        /** @var DBmysql $DB */
        global $DB;
        $DB->query("DROP TABLE IF EXISTS `" . getTableForItemType($itemtype) . "_items`");
    }

   /**
    * Get object name by ID
    * @param string $itemtype
    * @return string the name associated with the ID
    */
    public static function getNameByID($itemtype)
    {
        /** @var DBmysql $DB */
        global $DB;
        $query = "SELECT `name` FROM `" . getTableForItemType(__CLASS__) . "` " .
               "WHERE `itemtype`='$itemtype'";
        $result = $DB->query($query);
        if ($DB->numrows($result)) {
            return $DB->result($result, 0, "name");
        } else {
            return "";
        }
    }


   /**
    * Delete all tickets for an itemtype
    * @param string $itemtype
    * @return void
    */
    public static function deleteTicketAssignation($itemtype)
    {
        $types = ['Item_Ticket', 'Item_Problem', 'Change_Item'];
        foreach ($types as $type) {
            $item = new $type();
            $item->deleteByCriteria(['itemtype' => $itemtype]);
        }
    }

   /**
    * Delete all simcards for an itemtype
    * @param string $itemtype
    * @return void
    */
    public static function deleteSimcardAssignation($itemtype)
    {
        $plugin = new Plugin();
        if ($plugin->isActivated('simcard') && $plugin->isActivated('simcard')) {
            $types = ['PluginSimcardSimcard_Item'];
            foreach ($types as $type) {
                //@phpstan-ignore-next-line
                $item = new $type();
                $item->deleteByCriteria([
                    'itemtype' => $itemtype
                ]);
            }
        }
    }

   /**
    * Remove datainjection models for an itemtype
    * @param string $itemtype
    * @return void
    */
    public static function removeDataInjectionModels($itemtype)
    {
       //Delete if exists datainjection models
        if (Plugin::isPluginActive("datainjection")) {
            //@phpstan-ignore-next-line
            $model = new PluginDatainjectionModel();
            foreach ($model->find(['itemtype' => $itemtype]) as $data) {
                $model->delete($data);
            }
        }
    }


   /**
    * Delete all loans associated with a itemtype
    * @param string $itemtype
    * @return void
    */
    public static function deleteLoans($itemtype)
    {
        $reservation_item = new ReservationItem();
        foreach ($reservation_item->find(['itemtype' => $itemtype]) as $data) {
            $reservation_item->delete($data);
        }
    }


   /**
    * Delete all loans associated with a itemtype
    * @param string $itemtype
    * @return void
    */
    public static function deleteUnicity($itemtype)
    {
        $unicity = new FieldUnicity();
        $unicity->deleteByCriteria(['itemtype' => $itemtype]);
    }


   /**
    * Delete all notes associated with a itemtype
    * @param string $itemtype
    * @return void
    */
    public static function deleteNotepad($itemtype)
    {
        $notepad = new Notepad();
        $notepad->deleteByCriteria(['itemtype' => $itemtype]);
    }


   /**
    * Delete network ports for an itemtype
    * @param string $itemtype
    * @return void
    */
    public static function deleteNetworking($itemtype)
    {
        $networkport = new NetworkPort();
        foreach ($networkport->find(['itemtype' => $itemtype]) as $port) {
            $networkport->delete($port);
        }
    }

   /**
    * Delete reservations for an itemtype
    * @param string $itemtype
    * @return void
    */
    public static function deleteReservations($itemtype)
    {
        /** @var DBmysql $DB */
        global $DB;

        $query = "DELETE FROM
            `glpi_reservations`
         WHERE `reservationitems_id` in (
            SELECT `id` from `glpi_reservationitems` WHERE `itemtype`='$itemtype'
         )";
        $DB->query($query);
    }

   /**
    * Delete reservations for an itemtype
    * @param string $itemtype
    * @return void
    */
    public static function deleteReservationItems($itemtype)
    {
        $reservationItem = new ReservationItem();
        $reservationItem->deleteByCriteria(['itemtype' => $itemtype], true);
    }

   /**
    * Filter values inserted by users : remove accented chars
    * @param string $value the value to be filtered
    * @return string the filtered value
    */
    public static function filterInput($value)
    {
        $value = strtolower($value);
       //Itemtype must always be singular, otherwise it breaks when using GLPI's framework
        $value = getSingular($value);

        $search  = explode(",", "ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
        $replace = explode(",", "c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u");
        $value = str_replace($search, $replace, $value);
        $value = preg_replace("/[^a-zA-Z0-9]/", '', $value);

        return  str_replace($search, $replace, $value);
    }


   /**
    * Get the object system name (for files and itemtype), by giving the name
    *
    * @param string $name
    *
    * @return string
    */
    public static function getSystemName($name)
    {
       // Force filtering of name (will have no effect if already done).
        $name = self::filterInput($name);

       // Replace numbers by letters
        return str_replace(
            ['0',    '1',   '2',   '3',     '4',    '5',    '6',   '7',     '8',     '9'],
            ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'],
            $name
        );
    }

   /**
    * Get the object class, by giving the name
    * @param string $name the object's internal identifier
    * @return string the class associated with the object
    */
    public static function getClassByName($name)
    {
        return 'PluginGenericobject' . ucfirst(self::getSystemName($name));
    }


    public static function getFamilyNameByItemtype($itemtype)
    {
        $types = getAllDataFromTable(
            "glpi_plugin_genericobject_types",
            ['itemtype' => $itemtype, 'is_active' => 1]
        );
        if (empty($types)) {
            return false;
        } else {
            $type = array_pop($types);
            if ($type['plugin_genericobject_typefamilies_id'] > 0) {
                $family = new PluginGenericobjectTypeFamily();
                $family->getFromDB($type['plugin_genericobject_typefamilies_id']);
                return $family->getName();
            } else {
                return false;
            }
        }
    }

   /**
    * Get all types of active&published objects
    */
    public static function getTypes($all = false)
    {
        /** @var DBmysql $DB */
        global $DB;
        $table = getTableForItemType(__CLASS__);
        if ($DB->tableExists($table)) {
            $mytypes = [];
            $all_types = getAllDataFromTable(
                $table,
                [
                    'WHERE' => !$all ? ['is_active' => self::ACTIVE] : [],
                    'ORDER' => 'name',
                ]
            );
            foreach ($all_types as $data) {
                 //If class is not present on the filesystem, do not list itemtype
                 $mytypes[$data['itemtype']] = $data;
            }
            return $mytypes;
        } else {
            return  [];
        }
    }

   /**
    * Get all types of active&published objects
    * order by family
    */
    public static function getTypesByFamily($all = false)
    {
        /** @var DBmysql $DB */
        global $DB;
        $table = getTableForItemType(__CLASS__);
        if ($DB->tableExists($table)) {
            $mytypes = [];
            foreach (getAllDataFromTable($table, (!$all ? ['is_active' => self::ACTIVE] : [])) as $data) {
                //If class is not present on the filesystem, do not list itemtype
                if (file_exists(self::getCompleteClassFilename($data['name']))) {
                    $mytypes[$data['plugin_genericobject_typefamilies_id']][$data['itemtype']] = $data;
                }
            }
            return $mytypes;
        } else {
            return  [];
        }
    }

    public static function getTypesForFormcreator($param)
    {
        $families = PluginGenericobjectTypeFamily::getFamilies();
        $familyFk = PluginGenericobjectTypeFamily::getForeignKeyField();
        foreach (self::getTypes() as $type => $typeData) {
            $familyName = isset($families[$typeData[$familyFk]])
                       ? $families[$typeData[$familyFk]]
                       : _n('Other', 'Others', Session::getPluralNumber(), 'genericobject');
            $param[$familyName][$type] = $typeData['name'];
        }
        return $param;
    }

   /**
    * Register all variables for a type
    * @param string $itemtype the type's attributes
    * @return void
    */
    public static function registerOneType($itemtype)
    {
       //If table doesn't exists, do not try to register !
        if (class_exists($itemtype)) {
            $itemtype::registerType();
        }
    }


   /**
    * Include locales for a specific type
    * @name object type's name
    * @return void
    */
    public static function includeLocales($name)
    {
        /** @var array $CFG_GLPI */
        global $CFG_GLPI;

        $fsname = self::getSystemName($name);

        $prefix = GENERICOBJECT_LOCALES_PATH . "/$fsname/$fsname";
        //Dirty hack because the plugin doesn't support gettext...
        $language = str_replace('.mo', '', $CFG_GLPI["languages"][$_SESSION["glpilanguage"]][1]);
        if (
            isset($_SESSION["glpilanguage"])
             && file_exists("$prefix.$language.php")
        ) {
            include_once("$prefix.$language.php");
        } else {
            if (file_exists($prefix . ".en_GB.php")) {
                include_once($prefix . ".en_GB.php");
            } else {
                if (file_exists($prefix . ".fr_FR.php")) {
                    include_once($prefix . ".fr_FR.php");
                } else {
                    return false;
                }
            }
        }
        return true;
    }


    public static function includeConstants($name, $force = false)
    {

        $file = self::getCompleteConstantFilename($name);
        if (file_exists($file)) {
            if (!$force) {
                include_once($file);
            } else {
                include($file);
            }
        }
    }


   /**
    * Get all dropdown fields associated with an itemtype
    * @param string $itemtype the itemtype
    * @return array or fields that represents the dropdown tables
    */
    public static function getDropdownForItemtype($itemtype)
    {
        $associated_tables = [];
        if (class_exists($itemtype)) {
            $source_table = getTableForItemType($itemtype);
            foreach (PluginGenericobjectSingletonObjectField::getInstance($itemtype) as $field => $value) {
                $table = getTableNameForForeignKeyField($field);
                $options = PluginGenericobjectField::getFieldOptions($field, $itemtype);
                if (
                    isset($options['input_type'])
                    and $options['input_type'] === 'dropdown'
                    and preg_match('/^glpi_plugin_genericobject/', $table)
                ) {
                    $associated_tables[] = $table;
                }
            }
        }
        return $associated_tables;
    }


    public static function deleteDropdownsForItemtype($itemtype)
    {
        /** @var DBmysql $DB */
        global $DB;
       //Foreach dropdown : drop table & remove files !
        foreach (self::getDropdownForItemtype($itemtype) as $table) {
            $results = [];
            if (
                preg_match("/glpi_plugin_genericobject_(.*)/i", getSingular($table), $results)
                && isset($results[1])
            ) {
                $name = $results[1];
                $DB->query("DROP TABLE IF EXISTS `$table`");
                self::deleteFormFile($name);
                self::deleteSearchFile($name);
                self::deleteClassFile($name);
            }
        }

       // Invalidate submenu data in current session for minor cleanup
        unset($_SESSION['glpimenu']);
    }
   //------------------------------- GETTERS -------------------------//

    public function canUseTickets()
    {
        return $this->fields['use_tickets'];
    }

    public function canBeLinked()
    {
        return $this->fields['use_links'];
    }

    public function canUseTemplate()
    {
        /** @var DBmysql $DB */
        global $DB;
        return $DB->fieldExists(getTableForItemType($this->fields['itemtype']), 'is_template');
    }


    public function canUseUnicity()
    {
        return $this->fields['use_unicity'];
    }


    public function canBeDeleted()
    {
        /** @var DBmysql $DB */
        global $DB;
        return $DB->fieldExists(getTableForItemType($this->fields['itemtype']), 'is_deleted');
    }


    public function canBeEntityAssigned()
    {
        /** @var DBmysql $DB */
        global $DB;
        return $DB->fieldExists(getTableForItemType($this->fields['itemtype']), 'entities_id');
    }


    public function canBeRecursive()
    {
        /** @var DBmysql $DB */
        global $DB;
        return $DB->fieldExists(getTableForItemType($this->fields['itemtype']), 'is_recursive');
    }


    public function canBeReserved()
    {
        return $this->fields['use_loans'];
    }


    public function canUseNotepad()
    {
        return $this->fields['use_notepad'] != 0;
    }


    public function canUseHistory()
    {
        return $this->fields['use_history'];
    }


    public function canUseDocuments()
    {
        return $this->fields['use_documents'];
    }


    public function canUseInfocoms()
    {
        return $this->fields['use_infocoms'];
    }

    public function canUseItemDevice()
    {
        return $this->fields['use_itemdevices'];
    }

    public function canUseImpact()
    {
        return Impact::isEnabled($this->fields['itemtype']);
    }

    public function canUseContracts()
    {
        return $this->fields['use_contracts'];
    }


    public function canUseGlobalSearch()
    {
        return $this->fields['use_global_search'];
    }


    public function canUseNetworkPorts()
    {
        return $this->fields['use_network_ports'];
    }


    public function canUseDirectConnections()
    {
        return $this->fields['use_direct_connections'];
    }

    public function canUseProjects()
    {
        return $this->fields['use_projects'];
    }

    public function canUsePluginDataInjection()
    {
        if (!Plugin::isPluginActive("datainjection")) {
            return false;
        }
        return $this->fields['use_plugin_datainjection'];
    }


    public function canUsePluginOrder()
    {
        if (!Plugin::isPluginActive("order")) {
            return false;
        }
        return $this->fields['use_plugin_order'];
    }


    public function canUsePluginPDF()
    {
        if (!Plugin::isPluginActive("pdf")) {
            return false;
        }
        return $this->fields['use_plugin_pdf'];
    }


    public function canUsePluginUninstall()
    {
        if (!Plugin::isPluginActive("uninstall")) {
            return false;
        }
        return $this->fields['use_plugin_uninstall'];
    }

    public function canUsePluginSimcard()
    {
        if (!Plugin::isPluginActive("simcard")) {
            return false;
        }
        return $this->fields['use_plugin_simcard'];
    }

    public function canUsePluginTreeview()
    {
        if (!Plugin::isPluginActive("treeview")) {
            return false;
        }
        return $this->fields['use_plugin_treeview'];
    }

    public function canUsePluginGeninventoryNumber()
    {
        if (!Plugin::isPluginActive("geninventorynumber")) {
            return false;
        }
        return $this->fields['use_plugin_geninventorynumber'];
    }


    public function isTransferable()
    {
        return Session::isMultiEntitiesMode();
    }

    public function getLinkedItemTypesAsArray()
    {
        if (!empty($this->fields['linked_itemtypes'])) {
            return json_decode($this->fields['linked_itemtypes'], true);
        } else {
            return [];
        }
    }

    public static function canViewAtLeastOneType()
    {
        $types = self::getTypes();
        $view  = false;
        foreach ($types as $ID => $value) {
            if (Session::haveRight($value['itemtype'], READ)) {
                $view = true;
                break;
            }
        }
        return $view;
    }

   /**
    * Display debug information for current object
    **/
    public function showDebug()
    {
        $this->showFilesForm();
       //NotificationEvent::debugEvent($this);
    }
   //------------------------------- INSTALL / UNINSTALL METHODS -------------------------//


    public static function install(Migration $migration)
    {
        /** @var DBmysql $DB */
        global $DB;

        $default_charset = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

        $table = getTableForItemType(__CLASS__);
        if (!$DB->tableExists($table)) {
            $query = "CREATE TABLE `$table` (
                           `id` INT {$default_key_sign} NOT NULL AUTO_INCREMENT,
                           `entities_id` INT {$default_key_sign} NOT NULL DEFAULT 0,
                           `itemtype` varchar(255) default NULL,
                           `is_active` tinyint NOT NULL default '0',
                           `name` varchar(255) default NULL,
                           `comment` text NULL,
                           `date_mod` TIMESTAMP NULL DEFAULT NULL,
                           `date_creation` TIMESTAMP NULL DEFAULT NULL,
                           `use_global_search` tinyint NOT NULL default '0',
                           `use_unicity` tinyint NOT NULL default '0',
                           `use_history` tinyint NOT NULL default '0',
                           `use_infocoms` tinyint NOT NULL default '0',
                           `use_contracts` tinyint NOT NULL default '0',
                           `use_documents` tinyint NOT NULL default '0',
                           `use_tickets` tinyint NOT NULL default '0',
                           `use_links` tinyint NOT NULL default '0',
                           `use_loans` tinyint NOT NULL default '0',
                           `use_network_ports` tinyint NOT NULL default '0',
                           `use_direct_connections` tinyint NOT NULL default '0',
                           `use_plugin_datainjection` tinyint NOT NULL default '0',
                           `use_plugin_pdf` tinyint NOT NULL default '0',
                           `use_plugin_order` tinyint NOT NULL default '0',
                           `use_plugin_uninstall` tinyint NOT NULL default '0',
                           `use_plugin_geninventorynumber` tinyint NOT NULL default '0',
                           `use_menu_entry` tinyint NOT NULL default '0',
                           `use_projects` tinyint NOT NULL default '0',
                           `linked_itemtypes` text NULL,
                           `plugin_genericobject_typefamilies_id` INT {$default_key_sign} NOT NULL DEFAULT 0,
                           `use_itemdevices` tinyint NOT NULL default '0',
                           `impact_icon` varchar(255) default NULL,
                           PRIMARY KEY ( `id` )
                           ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
            $DB->query($query) or die($DB->error());
        }

        $migration->addField($table, "use_network_ports", "bool");
        $migration->addField($table, "use_direct_connections", "bool");
        $migration->addField($table, "use_plugin_geninventorynumber", "bool");
        $migration->addField($table, "use_contracts", "bool");
        $migration->addField($table, "use_menu_entry", "bool");
        $migration->addField($table, "use_global_search", "bool");
        $migration->addField($table, "use_projects", "bool");
        $migration->addField($table, "use_notepad", "bool");
        $migration->addField($table, "comment", "text");
        if (!$migration->addField($table, "date_mod", "timestamp")) {
            $migration->changeField($table, "date_mod", "date_mod", "timestamp");
        }
        $migration->addField($table, "date_creation", "timestamp");
        $migration->addField($table, "linked_itemtypes", "text");
        $migration->addField($table, "plugin_genericobject_typefamilies_id", "INT {$default_key_sign} NOT NULL DEFAULT 0");
        $migration->addField($table, "use_plugin_simcard", "bool");
        $migration->addField($table, "use_plugin_treeview", "bool");
        $migration->addField($table, "use_itemdevices", "bool");
        $migration->addField($table, "impact_icon", "string");
        $migration->migrationOneTable($table);

       //Normalize names and itemtypes (prior to using them).
        self::normalizeNamesAndItemtypes($migration);

       //If files are missing, recreate them!
        self::checkClassAndFilesForItemType();

       // Migrate notepad data
        $allGenericObjectTypes = PluginGenericobjectType::getTypes(true);

        $notepad = new Notepad();
        foreach ($allGenericObjectTypes as $genericObjectType => $genericObjectData) {
            $itemtype = $genericObjectData['itemtype'];
            if (! class_exists($itemtype, true)) {
               // TRANS:  %1$s is itemtype name
                $warning  = sprintf(__('Unable to load the class %1$s.', 'genericobject'), $itemtype);
               // TRANS:  %1$s is itemtype name
                $warning .= sprintf(__('You probably have garbage data in your database for this plugin and missing files in %1$s', 'genericobject'), GENERICOBJECT_DOC_DIR);
                $migration->displayWarning($warning, true);
                die();
            }
            $genericObjectTypeInstance = new $genericObjectType();
            if ($DB->fieldExists($genericObjectTypeInstance->getTable(), "notepad")) {
                $query = "INSERT INTO `" . $notepad->getTable() . "`
                  (`items_id`,
                  `itemtype`,
                  `date_creation`,
                  `date_mod`,
                  `content`
               )
               SELECT
                  `id` as `items_id`,
                  '" . $genericObjectType . "' as `itemtype`,
                  now() as `date_creation`,
                  now() as `date_mod`,
                  `notepad` as `content`
               FROM `" . $genericObjectTypeInstance->getTable() . "`
               WHERE notepad IS NOT NULL
               AND notepad <> ''";
                $DB->query($query) or die($DB->error());
            }
            $migration->dropField($genericObjectTypeInstance->getTable(), "notepad");
            $migration->migrationOneTable($genericObjectTypeInstance->getTable());
        }

       //Displayprefs
        $prefs = [10 => 6, 9 => 5, 8 => 4, 7 => 3, 6 => 2, 2 => 1, 4 => 1, 11 => 7,  12 => 8,
            14 => 10, 15 => 11
        ];
        foreach ($prefs as $num => $rank) {
            if (
                !countElementsInTable(
                    "glpi_displaypreferences",
                    ['itemtype' => __CLASS__, 'num' => $num, 'users_id' => 0]
                )
            ) {
                $preference      = new DisplayPreference();
                $tmp['itemtype'] = __CLASS__;
                $tmp['num']      = $num;
                $tmp['rank']     = $rank;
                $tmp['users_id'] = 0;
                $preference->add($tmp);
            }
        }
    }


    public static function uninstall()
    {
        /** @var DBmysql $DB */
        global $DB;

       //Delete references to PluginGenericobjectType in the following tables
        self::deleteItemtypeReferencesInGLPI(__CLASS__);

        foreach ($DB->request("glpi_plugin_genericobject_types") as $type) {
           //Delete references to PluginGenericobjectType in the following tables
            self::deleteItemtypeReferencesInGLPI($type['itemtype']);
           //Dropd files and classes
            self::deleteItemTypeFilesAndClasses($type['name'], getTableForItemType($type['itemtype']), $type['itemtype']);
        }

       //Delete table
        $query = "DROP TABLE IF EXISTS `glpi_plugin_genericobject_types`";
        $DB->query($query) or die($DB->error());
    }


    public static function getIcon()
    {
        return "fas fa-car";
    }

   /**
    * Normalize itemtype and name for all types.
    * This method will ensure that new normalization rules will be taken into account
    * during migration from an old version without loosing existing data.
    *
    * @param Migration $migration
    * @return void
    */
    private static function normalizeNamesAndItemtypes(Migration $migration)
    {
        /** @var DBmysql $DB */
        global $DB;
        $DB->disableTableCaching();

        $types_iterator = $DB->request(
            [
                'FROM'  => self::getTable(),
                'ORDER' => 'name',
            ]
        );

        foreach ($types_iterator as $type) {
            $old_name     = $type['name'];
            $new_name     = self::filterInput($old_name);
            $old_itemtype = $type['itemtype'];
            $new_itemtype = self::getClassByName($new_name);

            if ($old_name == $new_name && $old_itemtype == $new_itemtype) {
                continue;
            }

            self::updateNameAndItemtype(
                $migration,
                $old_name,
                $new_name,
                $old_itemtype,
                $new_itemtype
            );

            $DB->update(
                self::getTable(),
                [
                    'name'     => $new_name,
                    'itemtype' => $new_itemtype,
                ],
                ['id' => $type['id']]
            );

            $DB->update(
                self::getTable(),
                [
                    'linked_itemtypes' => new \QueryExpression(
                        'REPLACE('
                        . $DB->quoteName('linked_itemtypes')
                        . ','
                        . $DB->quoteValue('"' . $old_itemtype . '"') // itemtype is surrounded by quotes
                        . ','
                        . $DB->quoteValue('"' . $new_itemtype . '"') // itemtype is surrounded by quotes
                        . ')'
                    ),
                ],
                ['linked_itemtypes' => ['LIKE', '%"' . $old_itemtype . '"%']]
            );

           // Handle dropdowns related to itemtype
            $table  = getTableForItemType($new_itemtype);
            $fields = $DB->listFields($table);
            foreach ($fields as $field => $options) {
                if (preg_match("/s_id$/", $field)) {
                    $dropdown_old_table    = getTableNameForForeignKeyField($field);

                    if (!preg_match('/^glpi_plugin_genericobject_/', $dropdown_old_table)) {
                        continue;
                    }

                    $dropdown_old_name     = getSingular(
                        str_replace(
                            "glpi_plugin_genericobject_",
                            "",
                            $dropdown_old_table
                        )
                    );
                    $dropdown_old_itemtype = 'PluginGenericobject' . ucfirst($dropdown_old_name);
                    $dropdown_new_name     = self::filterInput($dropdown_old_name);
                    $dropdown_new_itemtype = self::getClassByName($dropdown_new_name);

                    if (
                        $dropdown_old_name == $dropdown_new_name
                        && $dropdown_old_itemtype == $dropdown_new_itemtype
                    ) {
                            continue;
                    }

                    self::updateNameAndItemtype(
                        $migration,
                        $dropdown_old_name,
                        $dropdown_new_name,
                        $dropdown_old_itemtype,
                        $dropdown_new_itemtype
                    );
                }
            }
        }

        ProfileRight::cleanAllPossibleRights(); // Clean all possible rights are their name may have change
    }

   /**
    * Update itemtype and/or name for a given itemtype.
    *
    * @param Migration $migration
    * @param string    $old_name      Current type name in database
    * @param string    $new_name      New type name to use
    * @param string    $old_itemtype  Current itemtype
    * @param string    $new_itemtype  New itemtype to use
    *
    * @return void
    */
    private static function updateNameAndItemtype(
        Migration $migration,
        $old_name,
        $new_name,
        $old_itemtype,
        $new_itemtype
    ) {
        /** @var DBmysql $DB */
        global $DB;

        if ($old_itemtype != $new_itemtype) {
            $migration->renameItemtype($old_itemtype, $new_itemtype);
            $migration->executeMigration(); // Execute migration to flush updates on tables that may be renamed
        }

        $destination_files = [];

        $old_systemname = $old_name; // Old system name was same as name in DB
        $new_systemname = self::getSystemName($new_name);
        $old_fkey       = getForeignKeyFieldForItemType($old_itemtype);
        $new_fkey       = getForeignKeyFieldForItemType($new_itemtype);
        $old_locale_dir = GENERICOBJECT_LOCALES_PATH . '/' . $old_systemname;
        $new_locale_dir = GENERICOBJECT_LOCALES_PATH . '/' . $new_systemname;

        $destination_files = [
            self::getCompleteClassFilename($new_name),
            self::getCompleteItemClassFilename($new_name),
            self::getCompleteFormFilename($new_name),
            self::getCompleteSearchFilename($new_name),
            self::getCompleteAjaxTabFilename($new_name),
            self::getCompleteInjectionFilename($new_name),
            self::getCompleteConstantFilename($new_name),
        ];

       // Rename locale folder and map files
        if (is_dir($old_locale_dir) && $old_locale_dir != $new_locale_dir) {
            if (rename($old_locale_dir, $new_locale_dir)) {
               // Add all locale files to destination files
                foreach (glob($new_locale_dir . '/*.php') as $old_filename) {
                    $destination_files[] = preg_replace(
                        '/(.*\/)' . preg_quote($old_systemname, '/') . '([^\/]*)$/',
                        '$1' . $new_systemname . '$2',
                        $old_filename
                    );
                }
            } else {
                $migration->displayWarning(
                    sprintf('Unable to rename "%s" locale directory to "%s"', $old_locale_dir, $new_locale_dir),
                    true
                );
            }
        }

       // Handle *_item class table/itemtype
        if ($DB->tableExists(getTableForItemType($old_itemtype . '_Item'))) {
            $migration->renameItemtype($old_itemtype . '_Item', $new_itemtype . '_Item');
            $migration->executeMigration(); // Execute migration to flush updates on tables that may be renamed
        }

       // Add all constant files as they may contains foreign keys to update
        foreach (glob(GENERICOBJECT_FIELDS_PATH . '/*.php') as $constant_filename) {
            $destination_files[] = $constant_filename;
        }

       // Rename files (replace "/{$old name}*" by "/{$new_system_name}*")
        $migration->displayMessage(
            sprintf('Rename files related to "%s" itemtype and update their content', $old_itemtype)
        );
        foreach ($destination_files as $new_filename) {
            $old_filename = preg_replace(
                '/(.*\/)' . preg_quote($new_systemname, '/') . '([^\/]*)$/',
                '$1' . $old_systemname . '$2',
                $new_filename
            );

            if (!file_exists($old_filename)) {
                 // Do nothing if old file does not exists
                 continue;
            }

            if ($old_filename != $new_filename) {
                if (!rename($old_filename, $new_filename)) {
                    $migration->displayWarning(
                        sprintf('Unable to rename "%s" file to "%s"', $old_filename, $new_filename),
                        true
                    );
                    continue;
                }
            } else {
                $migration->displayMessage(
                    sprintf('Update "%s" file content', $old_filename),
                );
            }

            $file_contents = file_get_contents($new_filename);
            if (!$file_contents) {
                $migration->displayWarning(
                    sprintf('Unable to read "%s" file contents', $new_filename),
                    true
                );
                continue;
            }

            $replace_count = 0;
            $old_fkey_truncated = preg_replace('/^plugin_genericobject_/', '', $old_fkey);
            $new_fkey_truncated = preg_replace('/^plugin_genericobject_/', '', $new_fkey);
            $file_contents = str_replace(
                [$old_itemtype, $old_fkey, $old_fkey_truncated],
                [$new_itemtype, $new_fkey, $new_fkey_truncated],
                $file_contents,
                $replace_count
            );
            if ($replace_count > 0 && !file_put_contents($new_filename, $file_contents)) {
                 $migration->displayWarning(
                     sprintf('Unable to update "%s" file contents', $new_filename),
                     true
                 );
            }
        }

       // Update profile rights
        if ($old_itemtype != $new_itemtype) {
            $migration->addPostQuery(
                $DB->buildUpdate(
                    ProfileRight::getTable(),
                    ['name' => PluginGenericobjectProfile::getProfileNameForItemtype($new_itemtype)],
                    ['name' => PluginGenericobjectProfile::getProfileNameForItemtype($old_itemtype)]
                )
            );
        }
    }

   /**
    * Given an impact icon filename, return the expected full or relative path
    * where it should be stored
    *
    * @param string $filename
    * @param string $itemtype Impact itemtype, needed to avoid filename colision
    * @param bool   $relative (default: false)
    *
    * @return null|string
    */
    public static function getImpactIconFileStoragePath(
        ?string $filename,
        string $itemtype,
        bool $relative = false
    ): ?string {
        if (empty($filename)) {
            return null;
        }

       // Make sure $filename does not contains any directory changes like ".."
        if ($filename != pathinfo($filename)['basename']) {
            trigger_error(
                "Trying to access forbidden file: $filename",
                E_USER_WARNING
            );
            return null;
        }

        $filename = "{$itemtype}_{$filename}";
        $path = GLPI_PLUGIN_DOC_DIR . "/genericobject/impact_icons/$filename";

        if ($relative) {
            $path = str_replace(GLPI_ROOT, "", $path);
        }

        return $path;
    }

   /**
    * Get file path to impact icon file
    *
    * @return string|null
    */
    public function getImpactIconFilePath(): ?string
    {
        if (empty($this->fields['impact_icon'])) {
            return null;
        }

        $path = self::getImpactIconFileStoragePath(
            $this->fields['impact_icon'],
            $this->fields['itemtype']
        );
        if (empty($path) || !file_exists($path)) {
            return null;
        }

        return $path;
    }

   /**
    * Get public URL to impact icon file
    *
    * @return null|string
    */
    public function getImpactIconUrl($full = true): ?string
    {
       // Check that the file exist
        if (!$this->getImpactIconFilePath()) {
            return null;
        }

        return Plugin::getWebDir('genericobject', $full) . "/front/getimpacticon.php?itemtype=" . $this->fields['itemtype'];
    }
}
