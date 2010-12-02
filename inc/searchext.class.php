<?php
/*
 * @version $Id: search.class.php 12622 2010-10-05 14:21:38Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

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
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginGenericobjectSearchext extends Search {
	static function show ($itemtype) {
		
      Search::manageGetValues($itemtype);
      Search::showGenericSearch($itemtype,$_GET);
      PluginGenericobjectSearchext::showList($itemtype,$_GET);
   }
	
	static function showList ($itemtype,$params) {
      global $DB,$CFG_GLPI,$LANG,
            $PLUGIN_HOOKS;

      // Instanciate an object to access method
      $item = NULL;
		
		

      if ($itemtype!='States' && class_exists($itemtype)) {
         $item = new $itemtype();
      }
		
		$itemtype2 = $itemtype;
		if (isset($_SESSION["glpi_plugin_genericobject_itemtype"]))
			$itemtype2 = "PluginGenericobject".ucfirst(plugin_genericobject_getNameByID($_SESSION["glpi_plugin_genericobject_itemtype"]));

      // Default values of parameters
      $p['link']        = array();//
      $p['field']       = array();//
      $p['contains']    = array();//
      $p['searchtype']  = array();//
      $p['sort']        = '1'; //
      $p['order']       = 'ASC';//
      $p['start']       = 0;//
      $p['is_deleted']  = 0;
      $p['export_all']  = 0;
      $p['link2']       = '';//
      $p['contains2']   = '';//
      $p['field2']      = '';//
      $p['itemtype2']   = '';
      $p['searchtype2']  = '';

      foreach ($params as $key => $val) {
            $p[$key]=$val;
      }

      if ($p['export_all']) {
         $p['start']=0;
      }

      // Manage defautll seachtype value : for bookmark compatibility
      if (count($p['contains'])) {
         foreach ($p['contains'] as $key => $val) {
            if (!isset($p['searchtype'][$key])) {
               $p['searchtype'][$key]='contains';
            }
         }
      }
      if (is_array($p['contains2']) && count($p['contains2'])) {
         foreach ($p['contains2'] as $key => $val) {
            if (!isset($p['searchtype2'][$key])) {
               $p['searchtype2'][$key]='contains';
            }
         }
      }

      $target= getItemTypeSearchURL($itemtype);

      $limitsearchopt=Search::getCleanedOptions($itemtype);

      if (isset($CFG_GLPI['union_search_type'][$itemtype])) {
         $itemtable=$CFG_GLPI['union_search_type'][$itemtype];
      } else {
         $itemtable=getTableForItemType($itemtype2);
      }

      $LIST_LIMIT=$_SESSION['glpilist_limit'];

      // Set display type for export if define
      $output_type=HTML_OUTPUT;
      if (isset($_GET['display_type'])) {
         $output_type=$_GET['display_type'];
         // Limit to 10 element
         if ($_GET['display_type']==GLOBAL_SEARCH) {
            $LIST_LIMIT=GLOBAL_SEARCH_DISPLAY_COUNT;
         }
      }
      // hack for States
      if (isset($CFG_GLPI['union_search_type'][$itemtype])) {
         $entity_restrict = true;
      } else {
         $entity_restrict = $item->isEntityAssign();
      }

      $metanames = array();

      // Get the items to display
      $toview=Search::addDefaultToView($itemtype);

      // Add items to display depending of personal prefs
      $displaypref=DisplayPreference::getForTypeUser($itemtype,getLoginUserID());
      if (count($displaypref)) {
         foreach ($displaypref as $val) {
            array_push($toview,$val);
         }
      }

      // Add searched items
      if (count($p['field'])>0) {
         foreach($p['field'] as $key => $val) {
            if (!in_array($val,$toview) && $val!='all' && $val!='view') {
               array_push($toview,$val);
            }
         }
      }

      // Add order item
      if (!in_array($p['sort'],$toview)) {
         array_push($toview,$p['sort']);
      }

      // Special case for Ticket : put ID in front
      if ($itemtype=='Ticket') {
         array_unshift($toview,2);
      }

      // Clean toview array
      $toview=array_unique($toview);
      foreach ($toview as $key => $val) {
         if (!isset($limitsearchopt[$val])) {
            unset($toview[$key]);
         }
      }

      $toview_count=count($toview);

      // Construct the request

      //// 1 - SELECT
      $SELECT = "SELECT ".Search::addDefaultSelect($itemtype);

      // Add select for all toview item
      foreach ($toview as $key => $val) {
         $SELECT.= Search::addSelect($itemtype,$val,$key,0);
      }


      //// 2 - FROM AND LEFT JOIN
      // Set reference table
      $FROM = " FROM `$itemtable`";

      // Init already linked tables array in order not to link a table several times
      $already_link_tables=array();
      // Put reference table
      array_push($already_link_tables,$itemtable);

      // Add default join
      $COMMONLEFTJOIN = Search::addDefaultJoin($itemtype,$itemtable,$already_link_tables);
      $FROM .= $COMMONLEFTJOIN;

      $searchopt=array();
      $searchopt[$itemtype]=&Search::getOptions($itemtype);
      // Add all table for toview items
      foreach ($toview as $key => $val) {
         $FROM .= Search::addLeftJoin($itemtype,$itemtable,$already_link_tables,
                              $searchopt[$itemtype][$val]["table"],
                              $searchopt[$itemtype][$val]["linkfield"]);
      }

      // Search all case :
      if (in_array("all",$p['field'])) {
         foreach ($searchopt[$itemtype] as $key => $val) {
            // Do not search on Group Name
            if (is_array($val)) {
               $FROM .= Search::addLeftJoin($itemtype,$itemtable,$already_link_tables,
                                    $searchopt[$itemtype][$key]["table"],
                                    $searchopt[$itemtype][$key]["linkfield"]);
            }
         }
      }


      //// 3 - WHERE

      // default string
      $COMMONWHERE = Search::addDefaultWhere($itemtype);
      $first=empty($COMMONWHERE);

      // Add deleted if item have it
      if ($item && $item->maybeDeleted()) {
         $LINK= " AND " ;
         if ($first) {
            $LINK=" ";
            $first=false;
         }
         $COMMONWHERE .= $LINK."`$itemtable`.`is_deleted` = '".$p['is_deleted']."' ";
      }

      // Remove template items
      if ($item && $item->maybeTemplate()) {
         $LINK= " AND " ;
         if ($first) {
            $LINK=" ";
            $first=false;
         }
         $COMMONWHERE .= $LINK."`$itemtable`.`is_template` = '0' ";
      }

      // Add Restrict to current entities
      if ($entity_restrict) {
         $LINK= " AND " ;
         if ($first) {
            $LINK=" ";
            $first=false;
         }

         if ($itemtype == 'Entity') {
            $COMMONWHERE .= getEntitiesRestrictRequest($LINK,$itemtable,'id','',true);
         } else if (isset($CFG_GLPI["union_search_type"][$itemtype])) {

            // Will be replace below in Union/Recursivity Hack
            $COMMONWHERE .= $LINK." ENTITYRESTRICT ";
         } else {
            $COMMONWHERE .= getEntitiesRestrictRequest($LINK,$itemtable,'','',$item->maybeRecursive());
         }
      }
      $WHERE="";
      $HAVING="";

      // Add search conditions
      // If there is search items
      if ($_SESSION["glpisearchcount"][$itemtype]>0 && count($p['contains'])>0) {
         for ($key=0 ; $key<$_SESSION["glpisearchcount"][$itemtype] ; $key++) {
            // if real search (strlen >0) and not all and view search
            if (isset($p['contains'][$key]) && strlen($p['contains'][$key])>0) {
               // common search
               if ($p['field'][$key]!="all" && $p['field'][$key]!="view") {
                  $LINK=" ";
                  $NOT=0;
                  $tmplink="";
                  if (is_array($p['link']) && isset($p['link'][$key])) {
                     if (strstr($p['link'][$key],"NOT")) {
                        $tmplink=" ".str_replace(" NOT","",$p['link'][$key]);
                        $NOT=1;
                     } else {
                        $tmplink=" ".$p['link'][$key];
                     }
                  } else {
                     $tmplink=" AND ";
                  }

                  if (isset($searchopt[$itemtype][$p['field'][$key]]["usehaving"])) {
                     // Manage Link if not first item
                     if (!empty($HAVING)) {
                        $LINK=$tmplink;
                     }
                     // Find key
                     $item_num=array_search($p['field'][$key],$toview);
                     $HAVING .= Search::addHaving($LINK,$NOT,$itemtype,$p['field'][$key],$p['searchtype'][$key],$p['contains'][$key],0,$item_num);
                  } else {
                     // Manage Link if not first item
                     if (!empty($WHERE)) {
                        $LINK=$tmplink;
                     }
                     $WHERE .= Search::addWhere($LINK,$NOT,$itemtype,$p['field'][$key],$p['searchtype'][$key],$p['contains'][$key]);
                  }

               // view and all search
               } else {
                  $LINK=" OR ";
                  $NOT=0;
                  $globallink=" AND ";
                  if (is_array($p['link']) && isset($p['link'][$key])) {
                     switch ($p['link'][$key]) {
                        case "AND" :
                           $LINK=" OR ";
                           $globallink=" AND ";
                           break;

                        case "AND NOT" :
                           $LINK=" AND ";
                           $NOT=1;
                           $globallink=" AND ";
                           break;

                        case "OR" :
                           $LINK=" OR ";
                           $globallink=" OR ";
                           break;

                        case "OR NOT" :
                           $LINK=" AND ";
                           $NOT=1;
                           $globallink=" OR ";
                           break;
                     }
                  } else {
                     $tmplink=" AND ";
                  }

                  // Manage Link if not first item
                  if (!empty($WHERE)) {
                     $WHERE .= $globallink;
                  }
                  $WHERE.= " ( ";
                  $first2=true;

                  $items=array();
                  if ($p['field'][$key]=="all") {
                     $items=$searchopt[$itemtype];
                  } else { // toview case : populate toview
                     foreach ($toview as $key2 => $val2) {
                        $items[$val2]=$searchopt[$itemtype][$val2];
                     }
                  }

                  foreach ($items as $key2 => $val2) {
                     if (is_array($val2)) {
                        // Add Where clause if not to be done in HAVING CLAUSE
                        if (!isset($val2["usehaving"])) {
                           $tmplink=$LINK;
                           if ($first2) {
                              $tmplink=" ";
                              $first2=false;
                           }
                           $WHERE .= Search::addWhere($tmplink,$NOT,$itemtype,$key2,$p['searchtype'][$key],$p['contains'][$key]);
                        }
                     }
                  }
                  $WHERE.=" ) ";
               }
            }
         }
      }

      //// 4 - ORDER
      $ORDER=" ORDER BY `id` ";
      foreach($toview as $key => $val) {
         if ($p['sort']==$val) {
            $ORDER= Search::addOrderBy($itemtype,$p['sort'],$p['order'],$key);
         }
      }


      //// 5 - META SEARCH
      // Preprocessing
      if ($_SESSION["glpisearchcount2"][$itemtype]>0 && is_array($p['itemtype2'])) {

         // a - SELECT
         for ($i=0 ; $i<$_SESSION["glpisearchcount2"][$itemtype] ; $i++) {
            if (isset($p['itemtype2'][$i]) && !empty($p['itemtype2'][$i]) && isset($p['contains2'][$i])
               && strlen($p['contains2'][$i])>0) {

               $SELECT .= Search::addSelect($p['itemtype2'][$i],$p['field2'][$i],$i,1,$p['itemtype2'][$i]);
            }
         }

         // b - ADD LEFT JOIN
         // Already link meta table in order not to linked a table several times
         $already_link_tables2=array();
         // Link reference tables
         for ($i=0 ; $i<$_SESSION["glpisearchcount2"][$itemtype] ; $i++) {
            if (isset($p['itemtype2'][$i]) && !empty($p['itemtype2'][$i]) && isset($p['contains2'][$i])
               && strlen($p['contains2'][$i])>0) {
               if (!in_array(getTableForItemType($p['itemtype2'][$i]),$already_link_tables2)) {
                  $FROM .= Search::addMetaLeftJoin($itemtype,$p['itemtype2'][$i],$already_link_tables2,
                                          (($p['contains2'][$i]=="NULL")||(strstr($p['link2'][$i],"NOT"))));
               }
            }
         }
         // Link items tables
         for ($i=0 ; $i<$_SESSION["glpisearchcount2"][$itemtype] ; $i++) {
            if (isset($p['itemtype2'][$i]) && !empty($p['itemtype2'][$i]) && isset($p['contains2'][$i])
               && strlen($p['contains2'][$i])>0) {
               if (!isset($searchopt[$p['itemtype2'][$i]])) {
                  $searchopt[$p['itemtype2'][$i]]=&Search::getOptions($p['itemtype2'][$i]);
               }
               if (!in_array($searchopt[$p['itemtype2'][$i]][$p['field2'][$i]]["table"]."_".$p['itemtype2'][$i],
                           $already_link_tables2)) {

                  $FROM .= Search::addLeftJoin($p['itemtype2'][$i],getTableForItemType($p['itemtype2'][$i]),$already_link_tables2,
                                       $searchopt[$p['itemtype2'][$i]][$p['field2'][$i]]["table"],
                                       $searchopt[$p['itemtype2'][$i]][$p['field2'][$i]]["linkfield"],
                                       1,$p['itemtype2'][$i]);
               }
            }
         }
      }


      //// 6 - Add item ID
      // Add ID to the select
      if (!empty($itemtable)) {
         $SELECT .= "`$itemtable`.`id` AS id ";
      }


      //// 7 - Manage GROUP BY
      $GROUPBY = "";
      // Meta Search / Search All / Count tickets
      if ($_SESSION["glpisearchcount2"][$itemtype]>0 || !empty($HAVING) || in_array('all',$p['field'])) {
         $GROUPBY = " GROUP BY `$itemtable`.`id`";
      }

      if (empty($GROUPBY)) {
         foreach ($toview as $key2 => $val2) {
            if (!empty($GROUPBY)) {
               break;
            }
            if (isset($searchopt[$itemtype][$val2]["forcegroupby"])) {
               $GROUPBY = " GROUP BY `$itemtable`.`id`";
            }
         }
      }

      // Specific search for others item linked  (META search)
      if (is_array($p['itemtype2'])) {
         for ($key=0 ; $key<$_SESSION["glpisearchcount2"][$itemtype] ; $key++) {
            if (isset($p['itemtype2'][$key]) && !empty($p['itemtype2'][$key]) && isset($p['contains2'][$key])
               && strlen($p['contains2'][$key])>0) {
               $LINK="";

               // For AND NOT statement need to take into account all the group by items
               if (strstr($p['link2'][$key],"AND NOT")
                  || isset($searchopt[$p['itemtype2'][$key]][$p['field2'][$key]]["usehaving"])) {

                  $NOT=0;
                  if (strstr($p['link2'][$key],"NOT")) {
                     $tmplink = " ".str_replace(" NOT","",$p['link2'][$key]);
                     $NOT=1;
                  } else {
                     $tmplink = " ".$p['link2'][$key];
                  }
                  if (!empty($HAVING)) {
                     $LINK=$tmplink;
                  }
                  $HAVING .= Search::addHaving($LINK,$NOT,$p['itemtype2'][$key],$p['field2'][$key],$p['searchtype2'][$key],$p['contains2'][$key],1,$key);
               } else { // Meta Where Search
                  $LINK=" ";
                  $NOT=0;
                  // Manage Link if not first item
                  if (is_array($p['link2']) && isset($p['link2'][$key]) && strstr($p['link2'][$key],"NOT")) {
                     $tmplink = " ".str_replace(" NOT","",$p['link2'][$key]);
                     $NOT=1;
                  } else if (is_array($p['link2']) && isset($p['link2'][$key])) {
                     $tmplink = " ".$p['link2'][$key];
                  } else {
                     $tmplink = " AND ";
                  }
                  if (!empty($WHERE)) {
                     $LINK=$tmplink;
                  }
                  $WHERE .= Search::addWhere($LINK,$NOT,$p['itemtype2'][$key],$p['field2'][$key],$p['searchtype2'][$key],$p['contains2'][$key],1);
               }
            }
         }
      }

      // If no research limit research to display item and compute number of item using simple request
      $nosearch=true;
      for ($i=0 ; $i<$_SESSION["glpisearchcount"][$itemtype] ; $i++) {
         if (isset($p['contains'][$i]) && strlen($p['contains'][$i])>0) {
            $nosearch=false;
         }
      }

      if ($_SESSION["glpisearchcount2"][$itemtype]>0) {
         $nosearch=false;
      }

      $LIMIT="";
      $numrows=0;
      //No search : count number of items using a simple count(ID) request and LIMIT search
      if ($nosearch) {
         $LIMIT= " LIMIT ".$p['start'].", ".$LIST_LIMIT;

         // Force group by for all the type -> need to count only on table ID
         if (!isset($searchopt[$itemtype][1]['forcegroupby'])) {
            $count = "count(*)";
         } else {
            $count = "count(DISTINCT `$itemtable`.`id`)";
         }
         $query_num = "SELECT $count
                     FROM `$itemtable`".
                     $COMMONLEFTJOIN;

         $first=true;

         if (!empty($COMMONWHERE)) {
            $LINK= " AND " ;
            if ($first) {
               $LINK = " WHERE ";
               $first=false;
            }
            $query_num .= $LINK.$COMMONWHERE;
         }
         // Union Search :
         if (isset($CFG_GLPI["union_search_type"][$itemtype])) {
            $tmpquery=$query_num;
            $numrows=0;

            foreach ($CFG_GLPI[$CFG_GLPI["union_search_type"][$itemtype]] as $ctype) {
               $ctable=getTableForItemType($ctype);
               $citem=new $ctype();
               if ($citem->canView()) {
                  // State case
                  if ($itemtype == 'States') {
                     $query_num=str_replace($CFG_GLPI["union_search_type"][$itemtype],
                                          $ctable,$tmpquery);
                     $query_num .= " AND $ctable.`states_id` > '0' ";
                  } else {// Ref table case
                     $reftable=getTableForItemType($itemtype);
                     $replace = "FROM `$reftable`
                                 INNER JOIN `$ctable`
                                 ON (`$reftable`.`items_id`=`$ctable`.`id`
                                    AND `$reftable`.`itemtype` = '$ctype')";

                     $query_num=str_replace("FROM `".$CFG_GLPI["union_search_type"][$itemtype]."`",
                                          $replace,$tmpquery);
                     $query_num=str_replace($CFG_GLPI["union_search_type"][$itemtype],
                                          $ctable,$query_num);
                  }
                  $query_num=str_replace("ENTITYRESTRICT",
                                       getEntitiesRestrictRequest('',$ctable,'','',$citem->maybeRecursive()),
                                       $query_num);
                  $result_num = $DB->query($query_num);
                  $numrows+= $DB->result($result_num,0,0);
               }
            }
         } else {
            $result_num = $DB->query($query_num);
            $numrows= $DB->result($result_num,0,0);
         }
      }

      // If export_all reset LIMIT condition
      if ($p['export_all']) {
         $LIMIT="";
      }

      if (!empty($WHERE) || !empty($COMMONWHERE)) {
         if (!empty($COMMONWHERE)) {
            $WHERE =' WHERE '.$COMMONWHERE.(!empty($WHERE)?' AND ( '.$WHERE.' )':'');
         } else {
            $WHERE =' WHERE '.$WHERE.' ';
         }
         $first=false;
      }

      if (!empty($HAVING)) {
         $HAVING=' HAVING '.$HAVING;
      }

      $DB->query("SET SESSION group_concat_max_len = 9999999;");

      // Create QUERY
      if (isset($CFG_GLPI["union_search_type"][$itemtype])) {
         $first=true;
         $QUERY="";
         foreach ($CFG_GLPI[$CFG_GLPI["union_search_type"][$itemtype]] as $ctype) {
            $ctable = getTableForItemType($ctype);
            $citem = new $ctype();
            if ($citem->canView()) {
               if ($first) {
                  $first=false;
               } else {
                  $QUERY.=" UNION ";
               }
               $tmpquery="";
               // State case
               if ($itemtype == 'States') {
                  $tmpquery = $SELECT.", '$ctype' AS TYPE ".
                              $FROM.
                              $WHERE;
                  $tmpquery = str_replace($CFG_GLPI["union_search_type"][$itemtype],
                                          $ctable,$tmpquery);
                  $tmpquery .= " AND `$ctable`.`states_id` > '0' ";
               } else {// Ref table case
                  $reftable=getTableForItemType($itemtype);

                  $tmpquery = $SELECT.", '$ctype' AS TYPE, `$reftable`.`id` AS refID, ".
                                    "`$ctable`.`entities_id` AS ENTITY ".
                              $FROM.
                              $WHERE;
                  $replace = "FROM `$reftable`".
                     " INNER JOIN `$ctable`".
                     " ON (`$reftable`.`items_id`=`$ctable`.`id`".
                     " AND `$reftable`.`itemtype` = '$ctype')";
                  $tmpquery = str_replace("FROM `".$CFG_GLPI["union_search_type"][$itemtype]."`",$replace,
                                          $tmpquery);
                  $tmpquery = str_replace($CFG_GLPI["union_search_type"][$itemtype],
                                          $ctable,$tmpquery);
               }
               $tmpquery = str_replace("ENTITYRESTRICT",
                                    getEntitiesRestrictRequest('',$ctable,'','',$citem->maybeRecursive()),
                                    $tmpquery);

               // SOFTWARE HACK
               if ($ctype == 'Software') {
                  $tmpquery = str_replace("glpi_softwares.serial","''",$tmpquery);
                  $tmpquery = str_replace("glpi_softwares.otherserial","''",$tmpquery);
               }
               $QUERY .= $tmpquery;
            }
         }
         if (empty($QUERY)) {
            echo Search::showError($output_type);
            return;
         }
         $QUERY .= str_replace($CFG_GLPI["union_search_type"][$itemtype].".","",$ORDER).
                  $LIMIT;
      } else {
         $QUERY = $SELECT.
                  $FROM.
                  $WHERE.
                  $GROUPBY.
                  $HAVING.
                  $ORDER.
                  $LIMIT;
      }

      // Get it from database and DISPLAY
      if ($result = $DB->query($QUERY)) {
         // if real search or complete export : get numrows from request
         if (!$nosearch||$p['export_all']) {
            $numrows= $DB->numrows($result);
         }
         // Contruct Pager parameters
         $globallinkto = Search::getArrayUrlLink("field",$p['field']).
                        Search::getArrayUrlLink("link",$p['link']).
                        Search::getArrayUrlLink("contains",$p['contains']).
                        Search::getArrayUrlLink("field2",$p['field2']).
                        Search::getArrayUrlLink("contains2",$p['contains2']).
                        Search::getArrayUrlLink("itemtype2",$p['itemtype2']).
                        Search::getArrayUrlLink("link2",$p['link2']);

         $parameters = "sort=".$p['sort']."&amp;order=".$p['order'].$globallinkto;

         // Not more used : clean pages : try to comment it
         /*
         $tmp=explode('?',$target,2);
         if (count($tmp)>1) {
            $target = $tmp[0];
            $parameters = $tmp[1].'&amp;'.$parameters;
         }
         */
         if ($output_type==GLOBAL_SEARCH) {
            if (class_exists($itemtype)) {
               echo "<div class='center'><h2>".$item->getTypeName();
               // More items
               if ($numrows>$p['start']+GLOBAL_SEARCH_DISPLAY_COUNT) {
                  echo " <a href='$target?$parameters'>".$LANG['common'][66]."</a>";
               }
               echo "</h2></div>\n";
            } else {
               return false;
            }
         }

         // If the begin of the view is before the number of items
         if ($p['start']<$numrows) {
            // Display pager only for HTML
            if ($output_type==HTML_OUTPUT) {
               // For plugin add new parameter if available
               if ($plug=isPluginItemType($itemtype)) {
                  $function='plugin_'.$plug['plugin'].'_addParamFordynamicReport';

                  if (function_exists($function)) {
                     $out=$function($itemtype);
                     if (is_array($out) && count($out)) {
                        foreach ($out as $key => $val) {
                           if (is_array($val)) {
                              $parameters .= Search::getArrayUrlLink($key,$val);
                           } else {
                              $parameters .= "&amp;$key=$val";
                           }
                        }
                     }
                  }
               }
               printPager($p['start'],$numrows,$target,$parameters,$itemtype);
            }

            // Form to massive actions
            $isadmin=($item && $item->canUpdate());
            if (!$isadmin && in_array($itemtype,$CFG_GLPI["infocom_types"])){
               $infoc=new Infocom();
               $isadmin=($infoc->canUpdate() || $infoc->canCreate());
            }

            if ($isadmin && $output_type==HTML_OUTPUT) {
               echo "<form method='post' name='massiveaction_form' id='massiveaction_form' action=\"".
                     $CFG_GLPI["root_doc"]."/front/massiveaction.php\">";
            }

            // Compute number of columns to display
            // Add toview elements
            $nbcols=$toview_count;
            // Add meta search elements if real search (strlen>0) or only NOT search
            if ($_SESSION["glpisearchcount2"][$itemtype]>0 && is_array($p['itemtype2'])) {
               for ($i=0 ; $i<$_SESSION["glpisearchcount2"][$itemtype] ; $i++) {
                  if (isset($p['itemtype2'][$i])
                     && isset($p['contains2'][$i])
                     && strlen($p['contains2'][$i])>0
                     && !empty($p['itemtype2'][$i])
                     && (!isset($p['link2'][$i]) || !strstr($p['link2'][$i],"NOT"))) {

                     $nbcols++;
                  }
               }
            }

            if ($output_type==HTML_OUTPUT) { // HTML display - massive modif
               $nbcols++;
            }

            // Define begin and end var for loop
            // Search case
            $begin_display=$p['start'];
            $end_display=$p['start']+$LIST_LIMIT;

            // No search Case
            if ($nosearch) {
               $begin_display=0;
               $end_display=min($numrows-$p['start'],$LIST_LIMIT);
            }

            // Export All case
            if ($p['export_all']) {
               $begin_display=0;
               $end_display=$numrows;
            }

            // Display List Header
            echo Search::showHeader($output_type,$end_display-$begin_display+1,$nbcols);

            // New Line for Header Items Line
            echo Search::showNewLine($output_type);
            $header_num=1;

            if ($output_type==HTML_OUTPUT) { // HTML display - massive modif
               $search_config="";
               if (haveRight("search_config","w") || haveRight("search_config_global","w")) {
                  $tmp = " class='pointer' onClick=\"var w = window.open('".$CFG_GLPI["root_doc"].
                        "/front/popup.php?popup=search_config&amp;itemtype=$itemtype' ,'glpipopup', ".
                        "'height=400, width=1000, top=100, left=100, scrollbars=yes' ); w.focus();\"";

                  $search_config = "<img alt='".$LANG['setup'][252]."' title='".$LANG['setup'][252].
                                    "' src='".$CFG_GLPI["root_doc"]."/pics/options_search.png' ";
                  $search_config .= $tmp.">";
               }
               echo Search::showHeaderItem($output_type,$search_config,$header_num,"",0,$p['order']);
            }

            // Display column Headers for toview items
            foreach ($toview as $key => $val) {
               $linkto='';
               if (!isset($searchopt[$itemtype][$val]['nosort'])
                     || !$searchopt[$itemtype][$val]['nosort']) {
                  $linkto = "$target?itemtype=$itemtype&amp;sort=".$val."&amp;order=".($p['order']=="ASC"?"DESC":"ASC").
                           "&amp;start=".$p['start'].$globallinkto;
               }
               echo Search::showHeaderItem($output_type,$searchopt[$itemtype][$val]["name"],
                                          $header_num,$linkto,$p['sort']==$val,$p['order']);
            }

            // Display columns Headers for meta items
            if ($_SESSION["glpisearchcount2"][$itemtype]>0 && is_array($p['itemtype2'])) {
               for ($i=0 ; $i<$_SESSION["glpisearchcount2"][$itemtype] ; $i++) {
                  if (isset($p['itemtype2'][$i]) && !empty($p['itemtype2'][$i]) && isset($p['contains2'][$i])
                     && strlen($p['contains2'][$i])>0) {

                     if (!isset($metanames[$p['itemtype2'][$i]])) {
                        $metaitem = new $p['itemtype2'][$i]();
                        $metanames[$p['itemtype2'][$i]]=$metaitem->getTypeName();
                     }

                     echo Search::showHeaderItem($output_type,$metanames[$p['itemtype2'][$i]]." - ".
                                                $searchopt[$p['itemtype2'][$i]][$p['field2'][$i]]["name"],
                                                $header_num);
                  }
               }
            }

            // Add specific column Header
            if ($itemtype == 'CartridgeItem') {
               echo Search::showHeaderItem($output_type,$LANG['cartridges'][0],$header_num);
            }
            if ($itemtype == 'ConsumableItem') {
               echo Search::showHeaderItem($output_type,$LANG['consumables'][0],$header_num);
            }
            if ($itemtype == 'States' || $itemtype == 'ReservationItem') {
               echo Search::showHeaderItem($output_type,$LANG['state'][6],$header_num);
            }
            if ($itemtype == 'ReservationItem' && $output_type == HTML_OUTPUT) {
               if (haveRight("reservation_central","w")) {
                  echo Search::showHeaderItem($output_type,"&nbsp;",$header_num);
                  echo Search::showHeaderItem($output_type,"&nbsp;",$header_num);
               }
               echo Search::showHeaderItem($output_type,"&nbsp;",$header_num);
            }
            // End Line for column headers
            echo Search::showEndLine($output_type);

            // if real search seek to begin of items to display (because of complete search)
            if (!$nosearch) {
               $DB->data_seek($result,$p['start']);
            }

            // Define begin and end var for loop
            // Search case
            $i=$begin_display;

            // Init list of items displayed
            if ($output_type==HTML_OUTPUT) {
               initNavigateListItems($itemtype);
            }

            // Num of the row (1=header_line)
            $row_num=1;
            // Display Loop
            while ($i < $numrows && $i<($end_display)) {
               // Column num
               $item_num=1;
               // Get data and increment loop variables
               $data=$DB->fetch_assoc($result);
               $i++;
               $row_num++;
               // New line
               echo Search::showNewLine($output_type,($i%2));

               // Add item in item list
               addToNavigateListItems($itemtype,$data["id"]);

               if ($output_type==HTML_OUTPUT) { // HTML display - massive modif
                  $tmpcheck="";
                  if ($isadmin) {
                     if ($itemtype == 'Entity'
                        && !in_array($data["id"],$_SESSION["glpiactiveentities"])) {

                        $tmpcheck="&nbsp;";
                     } else if ($item->maybeRecursive()
                              && !in_array($data["entities_id"],$_SESSION["glpiactiveentities"])) {
                        $tmpcheck="&nbsp;";
                     } else {
                        $sel="";
                        if (isset($_GET["select"]) && $_GET["select"]=="all") {
                           $sel="checked";
                        }
                        if (isset($_SESSION['glpimassiveactionselected'][$data["id"]])) {
                           $sel="checked";
                        }
                        $tmpcheck="<input type='checkbox' name='item[".$data["id"]."]' value='1' $sel>";
                     }
                  }
                  echo Search::showItem($output_type,$tmpcheck,$item_num,$row_num,"width='10'");
               }

               // Print other toview items
               foreach ($toview as $key => $val) {
                  echo Search::showItem($output_type,Search::giveItem($itemtype,$val,$data,$key),$item_num,
                                       $row_num,
                           Search::displayConfigItem($itemtype,$val,$data,$key));
               }

               // Print Meta Item
               if ($_SESSION["glpisearchcount2"][$itemtype]>0 && is_array($p['itemtype2'])) {
                  for ($j=0 ; $j<$_SESSION["glpisearchcount2"][$itemtype] ; $j++) {
                     if (isset($p['itemtype2'][$j]) && !empty($p['itemtype2'][$j]) && isset($p['contains2'][$j])
                        && strlen($p['contains2'][$j])>0) {

                        // General case
                        if (strpos($data["META_$j"],"$$$$")===false) {
                           $out=Search::giveItem ($p['itemtype2'][$j],$p['field2'][$j],$data,$j,1);
                           echo Search::showItem($output_type,$out,$item_num,$row_num);

                        // Case of GROUP_CONCAT item : split item and multilline display
                        } else {
                           $split=explode("$$$$",$data["META_$j"]);
                           $count_display=0;
                           $out="";
                           $unit="";
                           $separate='<br>';
                           if (isset($searchopt[$p['itemtype2'][$j]][$p['field2'][$j]]['splititems'])
                              && $searchopt[$p['itemtype2'][$j]][$p['field2'][$j]]['splititems']) {
                              $separate='<hr>';
                           }

                           if (isset($searchopt[$p['itemtype2'][$j]][$p['field2'][$j]]['unit'])) {
                              $unit=$searchopt[$p['itemtype2'][$j]][$p['field2'][$j]]['unit'];
                           }
                           for ($k=0 ; $k<count($split) ; $k++) {
                              if ($p['contains2'][$j]=="NULL" || strlen($p['contains2'][$j])==0
                                 ||preg_match('/'.$p['contains2'][$j].'/i',$split[$k])
                                 || isset($searchopt[$p['itemtype2'][$j]][$p['field2'][$j]]['forcegroupby'])) {

                                 if ($count_display) {
                                    $out.= $separate;
                                 }
                                 $count_display++;

                                 // Manage Link to item
                                 $split2=explode("$$",$split[$k]);
                                 if (isset($split2[1])) {
                                    $out .= "<a href=\"".getItemTypeFormURL($p['itemtype2'][$j])."?id=".$split2[1]."\">";
                                    $out .= $split2[0].$unit;
                                    if ($_SESSION["glpiis_ids_visible"] || empty($split2[0])) {
                                       $out .= " (".$split2[1].")";
                                    }
                                    $out .= "</a>";
                                 } else {
                                    $out .= $split[$k].$unit;
                                 }
                              }
                           }
                           echo Search::showItem($output_type,$out,$item_num,$row_num);
                        }
                     }
                  }
               }
               // Specific column display
               if ($itemtype == 'CartridgeItem') {
                  echo Search::showItem($output_type,
                                       Cartridge::getCount($data["id"],$data["ALARM"],$output_type),
                                       $item_num,$row_num);
               }
               if ($itemtype == 'ConsumableItem') {
                  echo Search::showItem($output_type,
                                       Consumable::getCount($data["id"],$data["ALARM"],$output_type),
                                       $item_num,$row_num);
               }
               if ($itemtype == 'States' || $itemtype == 'ReservationItem') {
                  $typename=$data["TYPE"];
                  if (class_exists($data["TYPE"])) {
                     $itemtmp = new $data["TYPE"]();
                     $typename=$itemtmp->getTypeName();
                  }
                  echo Search::showItem($output_type,$typename,$item_num,$row_num);
               }
               if ($itemtype == 'ReservationItem' && $output_type == HTML_OUTPUT) {
                  if (haveRight("reservation_central","w")) {
                     if (!haveAccessToEntity($data["ENTITY"])) {
                        echo Search::showItem($output_type,"&nbsp;",$item_num,$row_num);
                        echo Search::showItem($output_type,"&nbsp;",$item_num,$row_num);
                     } else {
                        echo Search::showItem($output_type,
                              "<a href=\"".getItemTypeFormURL($itemtype)."?id=".$data["refID"].
                              "&amp;is_active=".($data["ACTIVE"]?0:1)."&amp;update=update\" ".
                              "title='".($data["ACTIVE"]?$LANG['buttons'][42]:$LANG['buttons'][41])."'><img src=\"".
                              $CFG_GLPI["root_doc"]."/pics/".($data["ACTIVE"]?"moins":"plus").
                              ".png\" alt='' title=''></a>",
                              $item_num,$row_num,"class='center'");
                        echo Search::showItem($output_type,"<a href=\"javascript:confirmAction('".
                                       addslashes($LANG['reservation'][38])."\\n".
                                       addslashes($LANG['reservation'][39])."','".
                                       getItemTypeFormURL($itemtype)."?id=".$data["refID"].
                                       "&amp;delete=delete')\" title='".
                                       $LANG['reservation'][6]."'><img src=\"".
                                       $CFG_GLPI["root_doc"]."/pics/delete.png\" alt='' title=''></a>",
                                       $item_num,$row_num,"class='center'");
                     }
                  }
                  if ($data["ACTIVE"]) {
                     echo Search::showItem($output_type,"<a href='reservation.php?reservationitems_id=".
                                    $data["refID"]."' title='".$LANG['reservation'][21]."'><img src=\"".
                                    $CFG_GLPI["root_doc"]."/pics/reservation-3.png\" alt='' title=''></a>",
                                    $item_num,$row_num,"class='center'");
                  } else {
                     echo Search::showItem($output_type,"&nbsp;",$item_num,$row_num);
                  }
               }
               // End Line
               echo Search::showEndLine($output_type);
            }

            $title="";
            // Create title
            if ($output_type==PDF_OUTPUT_LANDSCAPE || $output_type==PDF_OUTPUT_PORTRAIT) {
               if ($_SESSION["glpisearchcount"][$itemtype]>0 && count($p['contains'])>0) {
                  for ($key=0 ; $key<$_SESSION["glpisearchcount"][$itemtype] ; $key++) {
                     if (strlen($p['contains'][$key])>0) {
                        if (isset($p["link"][$key])) {
                           $title.=" ".$p["link"][$key]." ";
                        }
                        switch ($p['field'][$key]) {
                           case "all" :
                              $title .= $LANG['common'][66];
                              break;

                           case "view" :
                              $title .= $LANG['search'][11];
                              break;

                           default :
                              $title .= $searchopt[$itemtype][$p['field'][$key]]["name"];
                        }
                        $title .= " = ".$p['contains'][$key];
                     }
                  }
               }
               if ($_SESSION["glpisearchcount2"][$itemtype]>0 && count($p['contains2'])>0) {
                  for ($key=0 ; $key<$_SESSION["glpisearchcount2"][$itemtype] ; $key++) {
                     if (strlen($p['contains2'][$key])>0) {
                        if (isset($p['link2'][$key])) {
                           $title .= " ".$p['link2'][$key]." ";
                        }
                        $title .= $metanames[$p['itemtype2'][$key]]."/";
                        $title .= $searchopt[$p['itemtype2'][$key]][$p['field2'][$key]]["name"];
                        $title .= " = ".$p['contains2'][$key];
                     }
                  }
               }
            }

            // Display footer
            echo Search::showFooter($output_type,$title);

            // Delete selected item
            if ($output_type==HTML_OUTPUT) {
               if ($isadmin) {
                  openArrowMassive("massiveaction_form");
                  Dropdown::showForMassiveAction($itemtype,$p['is_deleted']);
                  closeArrowMassive();

                  // End form for delete item
                  echo "</form>\n";
               } else {
                  echo "<br>";
               }
            }
            if ($output_type==HTML_OUTPUT) { // In case of HTML display
               printPager($p['start'],$numrows,$target,$parameters);
            }
         } else {
            echo Search::showError($output_type);
         }
      } else {
         echo $DB->error();
      }
      // Clean selection
      $_SESSION['glpimassiveactionselected']=array();
   }


}
