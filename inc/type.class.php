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

use Glpi\DBAL\QueryExpression;

class PluginGenericobjectType extends CommonDBTM
{
    public const INACTIVE = 0;
    public const ACTIVE   = 1;

    public const DRAFT     = 0;
    public const PUBLISHED = 1;

    public const CLASS_TEMPLATE              = "/objects/generic.class.tpl";
    public const FORM_TEMPLATE               = "/objects/generic.form.tpl";
    public const CLASS_DROPDOWN_TEMPLATE     = "/objects/generic.dropdown.class.tpl";
    public const FRONTFORM_DROPDOWN_TEMPLATE = "/objects/front.form.tpl";
    public const FRONT_DROPDOWN_TEMPLATE     = "/objects/front.tpl";
    public const SEARCH_TEMPLATE             = "/objects/front.tpl";
    public const AJAX_DROPDOWN_TEMPLATE      = "/objects/dropdown.tabs.tpl";
    public const AJAX_TEMPLATE               = "/objects/ajax.tabs.tpl";
    public const LOCALE_TEMPLATE             = "/objects/locale.tpl";
    public const OBJECTINJECTION_TEMPLATE    = "/objects/objectinjection.class.tpl";
    public const OBJECTITEM_TEMPLATE         = "/objects/object_item.class.tpl";

    public const CAN_OPEN_TICKET             = 1024;

    public $dohistory                 = true;

    public static $rightname          = 'plugin_genericobject_types';


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
        return __s("Type of objects", "genericobject");
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

        $query  = [
            'FROM' => getTableForItemType(self::class),
            'WHERE' => ['itemtype' => $itemtype],
        ];
        $result = $DB->request($query);
        if ($result->numrows() > 0) {
            foreach ($result as $field) {
                $this->fields = $field;
            }
        } else {
            $this->getEmpty();
        }
    }

    //-------------------------------- FILE DELETION ----------------------------//
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
                    $DB->dropTable($table, true);
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
        $DB->dropTable(getTableForItemType($itemtype), true);
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
        $DB->dropTable(getTableForItemType($itemtype) . "_items", true);
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
                    'itemtype' => $itemtype,
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
        if (Plugin::isPluginActive("datainjection") && class_exists('PluginDatainjectionModel')) {
            /** @var CommonDBTM $model */
            $model = new PluginDatainjectionModel(); // @phpstan-ignore-line
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
        $reservation = new Reservation();
        $reservation_item = new ReservationItem();
        $reservation_items = $reservation_item->find(['itemtype' => $itemtype]);
        foreach ($reservation_items as $data) {
            $reservation->deleteByCriteria(['reservationitems_id' => $data['id']]);
        }
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
            $name,
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

    /**
     * Get all types of active&published objects
     */
    public static function getTypes($all = false)
    {
        /** @var DBmysql $DB */
        global $DB;
        $table = getTableForItemType(self::class);
        if ($DB->tableExists($table)) {
            $mytypes = [];
            $all_types = getAllDataFromTable(
                $table,
                [
                    'WHERE' => !$all ? ['is_active' => self::ACTIVE] : [],
                    'ORDER' => 'name',
                ],
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

    //------------------------------- INSTALL / UNINSTALL METHODS -------------------------//

    public static function install(Migration $migration)
    {
        /** @var DBmysql $DB */
        global $DB;

        $default_charset = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

        $table = getTableForItemType(self::class);
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
            $DB->doQuery($query);
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

        // Migrate notepad data
        $allGenericObjectTypes = PluginGenericobjectType::getTypes(true);

        $notepad = new Notepad();
        foreach ($allGenericObjectTypes as $genericObjectType => $genericObjectData) {
            if (!class_exists($genericObjectType, true)) {
                // Skip missing classes during migration
                continue;
            }
            /** @var CommonDBTM $genericObjectTypeInstance */
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
                $DB->doQuery($query);
            }
            $migration->dropField($genericObjectTypeInstance->getTable(), "notepad");
            $migration->migrationOneTable($genericObjectTypeInstance->getTable());
        }

        //Displayprefs
        $prefs = [10 => 6, 9 => 5, 8 => 4, 7 => 3, 6 => 2, 2 => 1, 4 => 1, 11 => 7,  12 => 8,
            14 => 10, 15 => 11,
        ];
        foreach ($prefs as $num => $rank) {
            if (
                !countElementsInTable(
                    "glpi_displaypreferences",
                    ['itemtype' => self::class, 'num' => $num, 'users_id' => 0],
                )
            ) {
                $preference      = new DisplayPreference();
                $tmp['itemtype'] = self::class;
                $tmp['num']      = $num;
                $tmp['rank']     = $rank;
                $tmp['users_id'] = 0;
                $preference->add($tmp);
            }
        }

        $types = new self();
        $object_use_infocoms = $types->find(['use_infocoms' => 1]);
        foreach ($object_use_infocoms as $object) {
            $object_table = $object['itemtype']::getTable();
            $migration->addField($object_table, "ticket_tco", "decimal");
            $migration->migrationOneTable($object_table);
        }
    }


    public static function uninstall()
    {
        /** @var DBmysql $DB */
        global $DB;

        $migration = new Migration(PLUGIN_GENERICOBJECT_VERSION);

        //Delete references to PluginGenericobjectType in the following tables
        self::deleteItemtypeReferencesInGLPI(self::class);

        foreach ($DB->request(['FROM' => 'glpi_plugin_genericobject_types']) as $type) {
            //Delete references to PluginGenericobjectType in the following tables
            self::deleteItemtypeReferencesInGLPI($type['itemtype']);
            //Dropd files and classes
            self::deleteItemTypeFilesAndClasses($type['name'], getTableForItemType($type['itemtype']), $type['itemtype']);
        }

        //Delete table
        $migration->dropTable('glpi_plugin_genericobject_types');
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
            ],
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
                $new_itemtype,
            );

            $DB->update(
                self::getTable(),
                [
                    'name'     => $new_name,
                    'itemtype' => $new_itemtype,
                ],
                ['id' => $type['id']],
            );

            $DB->update(
                self::getTable(),
                [
                    'linked_itemtypes' => new QueryExpression(
                        'REPLACE('
                        . $DB->quoteName('linked_itemtypes')
                        . ','
                        . $DB->quoteValue('"' . $old_itemtype . '"') // itemtype is surrounded by quotes
                        . ','
                        . $DB->quoteValue('"' . $new_itemtype . '"') // itemtype is surrounded by quotes
                        . ')',
                    ),
                ],
                ['linked_itemtypes' => ['LIKE', '%"' . $old_itemtype . '"%']],
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
                            $dropdown_old_table,
                        ),
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
                        $dropdown_new_itemtype,
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
                        $old_filename,
                    );
                }
            } else {
                $migration->displayMessage(
                    sprintf('Unable to rename "%s" locale directory to "%s"', $old_locale_dir, $new_locale_dir),
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
            sprintf('Rename files related to "%s" itemtype and update their content', $old_itemtype),
        );
        foreach ($destination_files as $new_filename) {
            $old_filename = preg_replace(
                '/(.*\/)' . preg_quote($new_systemname, '/') . '([^\/]*)$/',
                '$1' . $old_systemname . '$2',
                $new_filename,
            );

            if (!file_exists($old_filename)) {
                // Do nothing if old file does not exists
                continue;
            }

            if ($old_filename != $new_filename) {
                if (!rename($old_filename, $new_filename)) {
                    $migration->displayMessage(
                        sprintf('Unable to rename "%s" file to "%s"', $old_filename, $new_filename),
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
                $migration->displayMessage(
                    sprintf('Unable to read "%s" file contents', $new_filename),
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
                $replace_count,
            );
            if ($replace_count > 0 && !file_put_contents($new_filename, $file_contents)) {
                $migration->displayMessage(
                    sprintf('Unable to update "%s" file contents', $new_filename),
                );
            }
        }

        // Update profile rights
        if ($old_itemtype != $new_itemtype) {
            $migration->addPostQuery(
                $DB->buildUpdate(
                    ProfileRight::getTable(),
                    ['name' => PluginGenericobjectProfile::getProfileNameForItemtype($new_itemtype)],
                    ['name' => PluginGenericobjectProfile::getProfileNameForItemtype($old_itemtype)],
                ),
            );
        }
    }
}
