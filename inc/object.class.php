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
 @link      https://forge.indepnet.n$/projects/genericobject
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

class PluginGenericobjectObject extends CommonDBTM {
   use Glpi\Features\Clonable;

   protected $objecttype;

   //Internal field counter
   private $cpt = 0;

   //Get itemtype name
   static function getTypeName($nb = 0) {
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


   public function __construct() {
      $class       = get_called_class();
      $this->table = getTableForItemType($class);
      if (class_exists($class)) {
         $this->objecttype = PluginGenericobjectType::getInstance($class);
      }
      $this->dohistory = $this->canUseHistory();

      if (preg_match("/PluginGenericobject(.*)/", $class, $results)) {
         if (preg_match("/^(.*)y$/i", $results[1], $end_results)) {
            static::$rightname = 'plugin_genericobject_'.strtolower($end_results[1]).'ies';
         } else if (preg_match("/^(.*)ss$/i", $results[1])) {
            static::$rightname = 'plugin_genericobject_'.strtolower($results[1]).'es';
         } else {
            static::$rightname = 'plugin_genericobject_'.strtolower($results[1]).'s';
         }
      }

      if ($this->canUseNotepad()) {
         // For GLPI 0.85.x
         $this->usenotepadrights = true;
         // For GLPI 0.90.x
         $this->usenotepad = true;
      }
   }

   public function getCloneRelations() :array {
      return [
         Computer_Item::class,
         Contract_Item::class,
         Document_Item::class,
         Infocom::class,
         Item_devices::class,
         NetworkPort::class,
      ];
   }


   /**
   * Display information on treeview plugin
   *
   * @params itemtype, id, pic, url, name
   *
   * @return params
   **/
   static function showGenericObjectTreeview($params) {
      global $CFG_GLPI;

      if (array_key_exists($params['itemtype'], PluginGenericobjectType::getTypes())) {
         $item = new $params['itemtype']();
         if ($item->getFromDB($params['id'])) {
            $params['name'] = $item->fields["name"];
            $params['url'] = Plugin::getWebDir('genericobject')."/front/object.form.php".
                        "?itemtype=".$params['itemtype']."&id=".$params['id'];
         }
      }
      return $params;
   }

   /**
   * Display node search url on treeview plugin
   *
   * @params itemtype, id, pic, url, name
   *
   * @return params
   **/
   static function getParentNodeSearchUrl($params) {

      if (array_key_exists($params['itemtype'], PluginGenericobjectType::getTypes())) {

         $item = new $params['itemtype']();
         $search = $item->rawSearchOptions();
         //get searchoption id for location_id
         foreach ($search as $key => $val) {
            if (isset($val['table']) && $val['table'] === 'glpi_locations') {
               $index= $key;
            }
         }

         $token = Session::getNewCSRFToken();

         $params['searchurl'] = $params['itemtype']::getSearchURL()."&is_deleted=0&criteria[0][field]=".$index."&criteria[0]".
               "[searchtype]=equals&criteria[0][value]=".$params['locations_id']."&search=Rechercher&start=0&_glpi_csrf_token=$token";
         return $params;

      }

      return $params;
   }

   static function install() {
   }

   static function uninstall() {
   }

   static function registerType() {
      global $DB, $PLUGIN_HOOKS, $UNINSTALL_TYPES, $ORDER_TYPES, $CFG_GLPI,
              $GO_LINKED_TYPES, $GENINVENTORYNUMBER_TYPES;

      $class  = get_called_class();
      $item   = new $class();
      $fields = PluginGenericobjectSingletonObjectField::getInstance($class);
      $plugin = new Plugin();

      PluginGenericobjectType::includeLocales($item->getObjectTypeName());
      PluginGenericobjectType::includeConstants($item->getObjectTypeName());

      Plugin::registerClass($class, [
         "document_types"         => $item->canUseDocuments(),
         "helpdesk_visible_types" => $item->canUseTickets(),
         "linkgroup_types"        => isset($fields["groups_id"]),
         "linkuser_types"         => isset($fields["users_id"]),
         "linkgroup_tech_types"   => isset($fields["groups_id_tech"]),
         "linkuser_tech_types"    => isset($fields["users_id_tech"]),
         "ticket_types"           => $item->canUseTickets(),
         "infocom_types"          => $item->canUseInfocoms(),
         "networkport_types"      => $item->canUseNetworkPorts(),
         "reservation_types"      => $item->canBeReserved(),
         "contract_types"         => $item->canUseContracts(),
         "unicity_types"          => $item->canUseUnicity(),
         "location_types"         => isset($fields['locations_id']),
         "itemdevices_types"      => $item->canUseItemDevice()
      ]);

      if (plugin_genericobject_haveRight($class, READ)) {
         //Change url for adding a new object, depending on template management activation
         if ($item->canUseTemplate()) {
            //Template management is active
            $add_url = "/front/setup.templates.php?itemtype=$class&amp;add=1";
            $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['links']['template']
                                                        = "/front/setup.templates.php?itemtype=$class&amp;add=0";
         } else {
            //Template management is not active
            $add_url = Toolbox::getItemTypeFormURL($class, false);
         }
         //Menu management
         $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['title']
                                                   = $class::getTypeName();
         $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['page']
                                                   = Toolbox::getItemTypeSearchURL($class, false);
         $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['links']['search']
                                                    = Toolbox::getItemTypeSearchURL($class, false);

         if (plugin_genericobject_haveRight($class, UPDATE)) {
            $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['links']['add']
                                                      = $add_url;

         }

         //Add configuration icon, if user has right
         if (Session::haveRight('config', UPDATE)) {
            $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['links']['config']
               = Toolbox::getItemTypeSearchURL('PluginGenericobjectType', false)."?itemtype=$class";
         }

         if ($item->canUsePluginUninstall()) {
            if (!in_array($class, $UNINSTALL_TYPES)) {
               array_push($UNINSTALL_TYPES, $class);
            }
         }
         if ($item->canUsePluginSimcard()) {
            if ($plugin->isActivated('simcard') && $plugin->isActivated('simcard')) {
               PluginSimcardSimcard_Item::registerItemtype($class);
            }
         }
         if ($item->canUsePluginOrder()) {
            if (!in_array($class, $ORDER_TYPES)) {
               array_push($ORDER_TYPES, $class);
            }
         }
         if ($item->canUseGlobalSearch()) {
            if (!in_array($class, $CFG_GLPI['asset_types'])) {
               array_push($CFG_GLPI['asset_types'], $class);
            }

            if (!in_array($class, $CFG_GLPI['state_types'])) {
               array_push($CFG_GLPI['state_types'], $class);
            }

            if (!in_array($class, $CFG_GLPI['globalsearch_types'])) {
               array_push($CFG_GLPI['globalsearch_types'], $class);
            }
         }

         if ($item->canUseDirectConnections()) {
            if (!in_array($class, $GO_LINKED_TYPES)) {
               array_push($GO_LINKED_TYPES, $class);
            }
            $items_class = $class."_Item";
            //if (class_exists($items_class)) {
               $items_class::registerType();
            //}
         }

         if ($item->canUseProjects()) {
            if (!in_array($class, $CFG_GLPI['project_asset_types'])) {
               array_push($CFG_GLPI['project_asset_types'], $class);
            }
         }

         $plugin_gen_path = Plugin::getPhpDir('geninventorynumber');
         if ($item->canUsePluginGeninventorynumber()) {
            if (!in_array($class, $GENINVENTORYNUMBER_TYPES)) {
               include_once ("$plugin_gen_path/inc/profile.class.php");
               PluginGeninventorynumberConfigField::registerNewItemType($class);
               array_push($GENINVENTORYNUMBER_TYPES, $class);
            }
         } else if ($plugin->isActivated('geninventorynumber')) {
            include_once ("$plugin_gen_path/inc/profile.class.php");
            PluginGeninventorynumberConfigField::unregisterNewItemType($class);
         }
      }

      foreach (PluginGenericobjectType::getDropdownForItemtype($class) as $table) {
         $itemtype = getItemTypeForTable($table);
         if (class_exists($itemtype)) {
            $item     = new $itemtype();
            //If entity dropdown, check rights to view & create
            if ($itemtype::canView()) {
               $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$itemtype]['links']['search']
                  = Toolbox::getItemTypeSearchURL($itemtype, false);
               if ($itemtype::canCreate()) {
                  $PLUGIN_HOOKS['submenu_entry']['genericobject']['options'][$class]['links']['add']
                     = Toolbox::getItemTypeFormURL($class, false);
               }
            }
         }
      }
   }

   static function getMenuIcon($itemtype) {
      global $CFG_GLPI;
      $itemtype_table = getTableForItemType($itemtype);
      $itemtype_shortname = preg_replace("/^glpi_plugin_genericobject_/", "", $itemtype_table);
      $itemtype_icons = glob(
         GENERICOBJECT_PICS_PATH . '/' . getSingular($itemtype_shortname) . ".*"
      );
      $finfo = new finfo(FILEINFO_MIME);
      $icon_found = null;
      foreach ($itemtype_icons as $icon) {
         if (preg_match("|^image/|", $finfo->file($icon))) {
            $icon_found = preg_replace("|^".GLPI_ROOT."|", "", $icon);
         }
      }
      if (!is_null($icon_found)) {
         $icon_path = $CFG_GLPI['root_doc'] . $icon_found;
      } else {
         $icon_path = Plugin::getWebDir('genericobject') . "/pics/default-icon.png";
      }
      return "".
         "<img ".
         "  class='genericobject_menu_icon' ".
         "src='".$icon_path."'".
         "/>";
   }

   static function checkItemtypeRight($class, $right) {
      if (!is_null($class) and class_exists($class)) {
         $right_name = PluginGenericobjectProfile::getProfileNameForItemtype(
            $class
         );

         return Session::haveRight($right_name, $right);
      }
   }

   static function canCreate() {
      $class    = get_called_class();
      //Datainjection : Don't understand why I need this trick : need to be investigated !
      if (preg_match("/Injection$/i", $class)) {
         $class = str_replace("Injection", "", $class);
      }
      return static::checkItemtypeRight($class, CREATE);
   }

   static function canView() {
      $class = get_called_class();
      return static::checkItemtypeRight($class, READ);
   }

   static function canUpdate() {
      $class = get_called_class();
      return static::checkItemtypeRight($class, UPDATE);
   }

   static function canDelete() {
      $class = get_called_class();
      return static::checkItemtypeRight($class, DELETE);
   }

   static function canPurge() {
      $class = get_called_class();
      return static::checkItemtypeRight($class, PURGE);
   }

   function defineTabs($options = []) {
      $tabs = [];

      $this->addDefaultFormTab($tabs);

      if (!$this->isNewItem()) {

         if ($this->canUseNetworkPorts()) {
            $this->addStandardTab('NetworkPort', $tabs, $options);
         }

         if ($this->canUseItemDevice()) {
            $this->addStandardTab('Item_Devices', $tabs, $options);
         }

         if ($this->canUseInfocoms()) {
            $this->addStandardTab('Infocom', $tabs, $options);
         }

         if ($this->canUseContracts()) {
            $this->addStandardTab('Contract_Item', $tabs, $options);
         }

         if ($this->canUseDocuments()) {
            $this->addStandardTab('Document_Item', $tabs, $options);
         }

         if ($this->canUseTickets()) {
            $this->addStandardTab('Ticket', $tabs, $options);
            $this->addStandardTab('Item_Problem', $tabs, $options);
            $this->addStandardTab('Change_Item', $tabs, $options);
         }

         if ($this->canUseNotepad()) {
            $this->addStandardTab('Notepad', $tabs, $options);
         }

         if ($this->canBeReserved()) {
            $this->addStandardTab('Reservation', $tabs, $options);
         }

         if ($this->canUseHistory()) {
            $this->addStandardTab('Log', $tabs, $options);
         }
      }
      return $tabs;
   }


   //------------------------ CAN methods -------------------------------------//

   function getObjectTypeName() {
      return $this->objecttype->getName();
   }

   function canUseInfocoms() {
      return ($this->objecttype->canUseInfocoms() && Session::haveRight("infocom", READ));
   }

   function canUseContracts() {
      return ($this->objecttype->canUseContracts() && Session::haveRight("contract", READ));
   }


   function canUseTemplate() {
      return $this->objecttype->canUseTemplate();
   }


   function canUseNotepad() {
      return $this->objecttype->canUseNotepad();
   }

   function canUseUnicity() {
      // Disable unicity feature (for GLPI 0.85 onward) : see issue #16
      // Related code : search for #16
      // FIXME : The bug may be in GLPI itself
      return ($this->objecttype->canUseUnicity() && Session::haveRight("config", READ));
   }


   function canUseDocuments() {
      return ($this->objecttype->canUseDocuments() && Session::haveRight("document", READ));
   }


   function canUseTickets() {
      return ($this->objecttype->canUseTickets());
   }


   function canUseGlobalSearch() {
      return ($this->objecttype->canUseGlobalSearch());
   }


   function canBeReserved() {
      return (
         $this->objecttype->canBeReserved()
         and Session::haveRight(ReservationItem::$rightname, ReservationItem::RESERVEANITEM)
      );
   }


   function canUseHistory() {
      return ($this->objecttype->canUseHistory());
   }


   function canUsePluginDataInjection() {
      return ($this->objecttype->canUsePluginDataInjection());
   }


   function canUsePluginPDF() {
      return ($this->objecttype->canUsePluginPDF());
   }


   function canUsePluginOrder() {
      return ($this->objecttype->canUsePluginOrder());
   }

   function canUsePluginGeninventorynumber() {
      return ($this->objecttype->canUsePluginGeninventorynumber());
   }

   function canUseNetworkPorts() {
      return ($this->objecttype->canUseNetworkPorts());
   }


   function canUseDirectConnections() {
      return ($this->objecttype->canUseDirectConnections());
   }

   function canUseProjects() {
      return ($this->objecttype->canUseProjects());
   }


   function canUsePluginUninstall() {
      return ($this->objecttype->canUsePluginUninstall());
   }

   function canUsePluginSimcard() {
      return ($this->objecttype->canUsePluginSimcard());
   }

   function getLinkedItemTypesAsArray() {
      return $this->objecttype->getLinkedItemTypesAsArray();
   }

   function canUseItemDevice() {
      return ($this->objecttype->canUseItemDevice());
   }

   function title() {
   }


   function showForm($id, $options = [], $previsualisation = false) {
      global $DB;

      $display_date = (!method_exists('CommonDBTM', 'showDates'));
      if ($previsualisation) {
         $canedit = true;
         $this->getEmpty();
      } else {
         if ($id > 0) {
            $this->check($id, READ);
         } else {
            // Create item
            $this->check(-1, CREATE);
            $this->getEmpty();
         }

         $canedit = $this->can($id, UPDATE);
      }

      if (isset($options['withtemplate']) && $options['withtemplate'] == 2) {
         $template   = "newcomp";
         $date = sprintf(__('Created on %s'), Html::convDateTime($_SESSION["glpi_currenttime"]));
      } else if (isset($options['withtemplate']) && $options['withtemplate'] == 1) {
         $template   = "newtemplate";
         $date = sprintf(__('Created on %s'), Html::convDateTime($_SESSION["glpi_currenttime"]));
      } else {
         $date = sprintf(__('Last update on %s'), Html::convDateTime($this->fields["date_mod"]));
         $template   = false;
      }

      $this->fields['id'] = $id;
      $this->initForm($id, $options);
      $this->showFormHeader($options);

      if ($previsualisation) {
         echo "<tr><th colspan='4'>".__("Object preview", "genericobject").":&nbsp;";
         $itemtype = $this->objecttype->fields['itemtype'];
         echo $itemtype::getTypeName();
         echo "</th></tr>";
      }

      //Reset fields definition only to keep the itemtype ones
      $GO_FIELDS = [];
      plugin_genericobject_includeCommonFields(true);
      PluginGenericobjectType::includeConstants($this->getObjectTypeName(), true);

      foreach (PluginGenericobjectSingletonObjectField::getInstance($this->objecttype->fields['itemtype'])
               as $field => $description) {
         if ($field == "is_helpdesk_visible" && $id <= 0) {
            $this->displayField($canedit, $field, 1, $template, $description);
         } else {
            $this->displayField($canedit, $field, $this->fields[$field], $template, $description);
         }
      }
      $this->closeColumn();

      if ($display_date && !$this->isNewID($id)) {
         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='2' class='center'>".$date;
         if (!$template && !empty($this->fields['template_name'])) {
            echo "<span class='small_space'>(".__("Template name")."&nbsp;: ".
                  $this->fields['template_name'].")</span>";
         }
         echo "</td></tr>";
      }

      if (!$previsualisation) {
         $this->showFormButtons($options);
      } else {
         echo "</table></div>";
         Html::closeForm();
      }
   }


   static function getFieldsToHide() {
      return ['id', 'is_recursive', 'is_template', 'template_name', 'is_deleted',
              'entities_id', 'notepad', 'date_mod', 'date_creation'];
   }


   function displayField($canedit, $name, $value, $template, $description = []) {
      global $GO_BLACKLIST_FIELDS;

      $searchoption  = PluginGenericobjectField::getFieldOptions($name, get_called_class());

      if (!empty($searchoption)
         && !in_array($name, self::getFieldsToHide())) {

         if (isset($searchoption['input_type']) && 'emptyspace' === $searchoption['input_type']) {
            $searchoption['name'] = "&nbsp;";
            $description['Type'] = 'emptyspace';
         }

         $this->startColumn();
         echo $searchoption['name'];
         if (isset($searchoption['autoname']) && $searchoption['autoname'] && $template) {
            echo "*&nbsp;";
         }
         $this->endColumn();
         $this->startColumn();
         switch (preg_replace('/\(\d+\)$/', '', $description['Type'])) {
            case "int":
               $fk_table = getTableNameForForeignKeyField($name);
               if ($fk_table != '') {
                  $itemtype   = getItemTypeForTable($fk_table);
                  $dropdown   = new $itemtype();
                  $parameters = ['name' => $name, 'value' => $value, 'comments' => true];
                  if ($dropdown->isEntityAssign()) {
                     $parameters["entity"] = $this->fields['entities_id'];
                  }
                  if ($dropdown->maybeRecursive()) {
                     $parameters['entity_sons'] = true;
                  }
                  if (isset($searchoption['condition'])) {
                     $parameters['condition'] = $searchoption['condition'];
                  }
                  if ($dropdown instanceof User) {
                     $parameters['entity'] = $this->fields["entities_id"];
                     $parameters['right'] = 'all';
                     User::dropdown($parameters);
                  } else {
                     Dropdown::show($itemtype, $parameters);
                  }
               } else {
                  $min = $max = $step = 0;
                  if (isset($searchoption['min'])) {
                     $min = $searchoption['min'];
                  } else {
                     $min = 0;
                  }
                  if (isset($searchoption['max'])) {
                     $max = $searchoption['max'];
                  } else {
                     $max = 100;
                  }
                  if (isset($searchoption['step'])) {
                     $step = $searchoption['step'];
                  } else {
                     $step = 1;
                  }
                  Dropdown::showNumber(
                     $name, [
                        'value'  => $value,
                        'min'    => $min,
                        'max'    => $max,
                        'step'   => $step
                     ]
                  );
               }
               break;

            case "tinyint":
               Dropdown::showYesNo($name, $value);
               break;

            case "varchar":
               if (isset($searchoption['autoname']) && $searchoption['autoname']) {
                  $objectName = autoName($this->fields[$name], $name, ($template === "newcomp"),
                                         $this->getType(), $this->fields["entities_id"]);
               } else {
                   $objectName = $this->fields[$name];
               }
               Html::autocompletionTextField($this, $name, ['value' => $objectName]);
               break;

            case "longtext":
            case "text":
               echo "<textarea cols='40' rows='4' name='" . $name . "'>" . $value .
                     "</textarea>";
               break;

            case "emptyspace":
               echo '&nbsp;';
               break;

            case "date":
                  Html::showDateField(
                     $name, [
                        'value'        => $value,
                        'maybeempty'   => true,
                        'canedit'      => true
                     ]
                  );
                  break;

            case "datetime":
            case "timestamp":
                  Html::showDateTimeField(
                     $name, [
                        'value'        => $value,
                        'timestep'     => true,
                        'maybeempty'   => true
                     ]
                  );
                  break;

            default:
            case "float":
                  echo "<input type='text' name='$name' value='$value'>";
                  break;

            case 'decimal':
                  echo "<input type='text' name='$name' value='".Html::formatNumber($value)."'>";
                  break;
         }
         $this->endColumn();
      }
   }



   /**
   * Add a new column
   **/
   function startColumn() {
      if ($this->cpt == 0) {
         echo "<tr class='tab_bg_1'>";
      }

      echo "<td>";
      $this->cpt++;
   }



   /**
   * End a column
   **/
   function endColumn() {
      echo "</td>";

      if ($this->cpt == 4) {
         echo "</tr>";
         $this->cpt = 0;
      }

   }



   /**
   * Close a column
   **/
   function closeColumn() {
      if ($this->cpt > 0) {
         while ($this->cpt < 4) {
            echo "<td></td>";
            $this->cpt++;
         }
         echo "</tr>";
      }
   }


   function prepareInputForAdd($input) {

      //Template management
      if (isset ($input["id"]) && $input["id"] > 0) {
         $input["_oldID"] = $input["id"];
      }
      unset ($input['id']);
      unset ($input['withtemplate']);

      return $input;
   }


   function cleanDBonPurge() {
      $parameters = ['items_id' => $this->getID(), 'itemtype' => get_called_class()];
      $types      = ['Computer_Item', 'ReservationItem', 'Document_Item', 'Infocom', 'Contract_Item'];
      foreach ($types as $type) {
         $item = new $type();
         $item->deleteByCriteria($parameters);
      }

      foreach (['NetworkPort', 'Computer_Item', 'ReservationItem',
                'ReservationItem', 'Document_Item', 'Infocom', 'Contract_Item',
                'Item_Problem', 'Change_Item', 'Item_Project'] as $itemtype) {
         $ip = new $itemtype();
         $ip->cleanDBonItemDelete(get_called_class(), $this->getID());
      }
   }

   /**
    * Display object preview form
    * @param type the object type
    */
   static function showPrevisualisationForm(PluginGenericobjectType $type) {
      $itemtype = $type->fields['itemtype'];
      $item     = new $itemtype();

      $right_name = PluginGenericobjectProfile::getProfileNameForItemtype(
         $itemtype
      );

      if (Session::haveRight($right_name, READ) && Session::haveRight($right_name, CREATE)) {
         $item->showForm(-1, [], true);
      } else {
         echo "<br><strong>" . __("You must configure rights to enable the preview",
                                  "genericobject") . "</strong><br>";
      }
   }

   function rawSearchOptions() {
      global $DB, $GO_FIELDS, $GO_BLACKLIST_FIELDS;

      $datainjection_blacklisted = ['id', 'date_mod', 'entities_id', 'date_creation'];
      $index_exceptions = ['name' => 1, 'id' => 2, 'comment' => 16, 'date_mod' => 19,
                           'entities_id' => 80, 'is_recursive' => 86, 'notepad' => 90,
                           'date_creation' => 121];

      // Don't use indexes blacklisted by other item types in plugin DataInjection.
      $plugin = new Plugin();
      if ($plugin->isActivated("datainjection")
         && class_exists('PluginDatainjectionCommonInjectionLib')) {
         $blacklisted_indexes = PluginDatainjectionCommonInjectionLib::getBlacklistedOptions(
            get_called_class() //A class that extends PluginGenericobjectObject
         );
      } else {
         $blacklisted_indexes = [];
      }

      $index   = 3;

      $options = [];

      $options[] = [
         'id'   => 'common',
         'name' => __('Characteristics'),
      ];

      $table   = getTableForItemType(get_called_class());

      // Prevent usage of reserved and blacklisted indexes
      $taken_indexes = array_merge($index_exceptions, $blacklisted_indexes);

      foreach (PluginGenericobjectSingletonObjectField::getInstance(get_called_class())
         as $field => $values
      ) {
         $searchoption = PluginGenericobjectField::getFieldOptions(
            $field,
            $this->objecttype->fields['itemtype']
         );

         if ($field == 'is_deleted') {
            continue;
         }

         //Some fields have fixed index values...
         $currentindex = $index;
         if (isset($index_exceptions[$field])) {
            $currentindex = $index_exceptions[$field];
         } else {
            //If this index is reserved, jump to next available one.
            while (in_array($currentindex, $taken_indexes)) {
               $currentindex++;
            }
         }

         $option = [
            'id' => $currentindex,
         ];
         $taken_indexes[] = $option['id'];

         $item = new $this->objecttype->fields['itemtype'];

         //Table definition
         //We test if it ends with s_id, in order to be sure that this pattern
         //was found in a field that doesn't represent a foreign key
         //for exemple a field called : is_identification
         if (preg_match("/(s_id$|s_id_)/", $field)) {
            $tmp  = getTableNameForForeignKeyField($field);
         } else {
            $tmp = '';
         }

         if (preg_match("/(s_id_)/", $field)) {
            $option['linkfield'] = $field;
         }

         if ($tmp != '') {
            $itemtype   = getItemTypeForTable($tmp);
            $tmpobj     = new $itemtype();

            //Set table
            $option['table'] = $tmp;

            //Set field
            if ($tmpobj instanceof CommonTreeDropdown) {
               $option['field'] = 'completename';
            } else {
               $option['field'] = 'name';
            }

         } else {
            $option['table'] = $table;
            $option['field'] = $field;

         }

         $option['name']  = $searchoption['name'];

         //Massive action or not
         if (isset($searchoption['massiveaction'])) {
            $option['massiveaction'] = $searchoption['massiveaction'];
         }

         //Datainjection option
         if (!in_array($field, $datainjection_blacklisted)) {
            $option['injectable'] = 1;
         } else {
            $option['injectable'] = 0;
         }

         //Field type
         switch ($values['Type']) {
            default:
            case "varchar(255)":
               if ($field == 'name') {
                  $option['datatype']      = 'itemlink';
                  $option['itemlink_type'] = get_called_class();
                  $option['massiveaction'] = false;
                  // Enable autocomplete only for name, other fields may contains sensitive data
                  $option['autocomplete']  = true;
               } else {
                  if (isset($searchoption['datatype']) && $searchoption['datatype'] == 'weblink') {
                     $option['datatype'] = 'weblink';
                  } else {
                     $option['datatype'] = 'string';
                  }
               }
               if ($item->canUsePluginDataInjection()) {
                  //Datainjection specific
                  $option['checktype']   = 'text';
                  $option['displaytype'] = 'text';
               }
               break;
            case "tinyint(1)":
               $option['datatype'] = 'bool';
               if ($item->canUsePluginDataInjection()) {
                  //Datainjection specific
                  $option['displaytype'] = 'bool';
               }
               break;
            case "text":
            case "longtext":
               $option['datatype'] = 'text';
               if ($item->canUsePluginDataInjection()) {
                  //Datainjection specific
                  $option['displaytype'] = 'multiline_text';
               }
               break;
            case "int(11)":
               if ($tmp != '') {
                  $option['datatype'] = 'dropdown';
               } else {
                  $option['datatype'] = 'integer';
               }

               if ($item->canUsePluginDataInjection()) {
                  if ($tmp != '') {
                     $option['displaytype'] = 'dropdown';
                     $option['checktype']   = 'text';
                  } else {
                     //Datainjection specific
                     $option['displaytype'] = 'dropdown_integer';
                     $option['checktype']   = 'integer';
                  }
               }
               break;
            case "float":
            case "decimal":
               $option['datatype'] = $values['Type'];
               if ($item->canUsePluginDataInjection()) {
                  //Datainjection specific
                  $option['display']   = 'text';
                  $option['checktype'] = $values['Type'];
               }
               break;
            case "date":
               $option['datatype'] = 'date';
               if ($item->canUsePluginDataInjection()) {
                  //Datainjection specific
                  $option['displaytype'] = 'date';
                  $option['checktype']   = 'date';
               }
               break;
            case "datetime":
            case "timestamp":
               $option['datatype'] = 'datetime';
               if ($item->canUsePluginDataInjection()) {
                  //Datainjection specific
                  $option['displaytype'] = 'date';
                  $option['checktype']   = 'date';
               }
               if ($field == 'date_mod') {
                  $option['massiveaction'] = false;
               }
               break;
         }

         $options[] = $option;

         $index = $currentindex + 1;
      }

      usort(
         $options,
         function ($a, $b) {
            return ($a['id'] < $b['id']) ? -1 : 1;
         }
      );

      return $options;
   }



   //Datainjection specific methods
   function isPrimaryType() {
      return true;
   }


   function connectedTo() {
      return [];
   }



   /**
    * Standard method to add an object into glpi
    *
    * @param values fields to add into glpi
    * @param options options used during creation
    * @return an array of IDs of newly created objects : for example [Computer=>1, Networkport=>10]
    *
   **/
   function addOrUpdateObject($values = [], $options = []) {

      $lib = new PluginDatainjectionCommonInjectionLib($this, $values, $options);
      $lib->processAddOrUpdate();
      return $lib->getInjectionResults();
   }


   function getOptions($primary_type = '') {
      return Search::getOptions($primary_type);
   }


   function transfer($new_entity) {
      global $DB;
      if ($this->fields['id'] > 0 && $this->fields['entities_id'] != $new_entity) {
         //Update entity for this object
         $tmp['id']          = $this->fields['id'];
         $tmp['entities_id'] = $new_entity;
         $this->update($tmp);

         $toupdate = ['id' => $this->fields['id']];
         foreach (PluginGenericobjectSingletonObjectField::getInstance(get_called_class()) as $field => $data) {
            $table = getTableNameForForeignKeyField($field);

            //It is a dropdown table !
            if ($field != 'entities_id'
               && $table != ''
                  && isset($this->fields[$field]) && $this->fields[$field] > 0) {
               //Instanciate a new dropdown object
               $dropdown_itemtype = getItemTypeForTable($table);
               $dropdown          = new $dropdown_itemtype();
               $dropdown->getFromDB($this->fields[$field]);

               //If dropdown is only accessible in the other entity
               //do not go further
               if (!$dropdown->isEntityAssign()
                  || in_array($new_entity, getAncestorsOf('glpi_entities',
                                                          $dropdown->getEntityID()))) {
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

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
   **/
   static function showMassiveActionsSubForm(MassiveAction $ma) {
      global $GENINVENTORYNUMBER_TYPES;

      // KK TODO: check if MassiveAction itemtypes are concerned
      //if (in_array ($options['itemtype'], $GENINVENTORYNUMBER_TYPES)) {
      switch ($ma->action) {
         case "plugin_genericobject_transfer" :
               Dropdown::show('Entity', ['name' => 'new_entity']);
               echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" .
                  _sx('button', 'Post') . "\" >";
            break;
         default :
            break;
      }
      //}
      return true;
   }

   function plugin_genericobject_MassiveActionsProcess($data) {
      global $DB;

      switch ($data['action']) {
         case 'plugin_genericobject_transfer':
            $item = new $data['itemtype']();
            foreach ($data["item"] as $key => $val) {
               if ($val == 1) {
                  $item->getFromDB($key);
                  $item->transfer($_POST['new_entity']);
               }
            }
            break;
      }
   }

   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {
      $results = [
         'ok'       => 0,
         'ko'       => 0,
         'noright'  => 0,
         'messages' => []
      ];

      switch ($ma->action) {
         case "plugin_genericobject_transfer" :
            foreach ($ma->items as $itemtype => $val) {
               foreach ($val as $key => $item_id) {
                  $item = new $itemtype;
                  $item->getFromDB($item_id);
                  $item->transfer($_POST['new_entity']);
                  $results['ok']++;
               }
            }
            break;

         default :
            break;
      }
      $ma->results=$results;
   }

   static function getMenuContent() {
      $types = PluginGenericobjectType::getTypes();
      foreach ($types as $type) {

         $itemtype = $type['itemtype'];
         if (!class_exists($itemtype)) {
            continue;
         }
         $item     = new $itemtype();

         $itemtype_rightname = PluginGenericobjectProfile::getProfileNameForItemtype($itemtype);
         if (class_exists($itemtype)
            && Session::haveRight($itemtype_rightname, READ)) {

            $links           = [];
            $links['search'] = $itemtype::getSearchUrl(false);

            if ($item->canUseTemplate()) {
               $links['template'] = "/front/setup.templates.php?itemtype=$itemtype&amp;add=0";
               if (Session::haveRight($itemtype_rightname, CREATE)) {
                  $links['add'] = "/front/setup.templates.php?itemtype=$itemtype&amp;add=1";
               }
            } else {
               if (Session::haveRight($itemtype_rightname, CREATE)) {
                  $links['add'] = $itemtype::getFormUrl(false);
               }
            }

            if ($type['plugin_genericobject_typefamilies_id'] > 0
               && (!isset($_GET['itemtype'])
                  || !preg_match("/itemtype=".$_GET['itemtype']."/", $_GET['itemtype']))) {
               $family_id = $type['plugin_genericobject_typefamilies_id'];
               $name      = Dropdown::getDropdownName("glpi_plugin_genericobject_typefamilies", $family_id, 0, false);
               $str_name  = strtolower($name);
               $menu[$str_name]['title'] = Dropdown::getDropdownName("glpi_plugin_genericobject_typefamilies", $family_id);
               $menu[$str_name]['page']  = '/'.Plugin::getWebDir('genericobject', false).'/front/familylist.php?id='.$family_id;
               $menu[$str_name]['options'][strtolower($itemtype)] = [
                  'title' => $type['itemtype']::getMenuName(),
                  'page'  => $itemtype::getSearchUrl(false),
                  'links' => $links
               ];
            } else {
               $menu[strtolower($itemtype)]= [
                  'title' => $type['itemtype']::getMenuName(),
                  'page'  => $itemtype::getSearchUrl(false),
                  'links' => $links
               ];
            }
         }
      }

      $menu['is_multi_entries']= true;
      return $menu;
   }

}
