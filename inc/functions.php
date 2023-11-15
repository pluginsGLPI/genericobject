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

/*
 * return the name of a dropdown type
 * This shared by the following classes :
 *    - PluginGenericobjectCommonDropdown
 *    - PluginGenericobjectCommonTreeDropdown
 */
function dropdown_getTypeName($class, $nb = 0)
{
    $fk = getForeignKeyFieldForTable(getTableForItemType($class));
    $instance = new $class();
    $options = PluginGenericobjectField::getFieldOptions($fk, $instance->linked_itemtype);
    $dropdown_type = isset($options['dropdown_type'])
      ? $options['dropdown_type']
      : null;
    $label = $options['name'] ?? "no-name";
    if (!is_null($dropdown_type) and $dropdown_type === 'isolated') {
        $linked_itemtype_object = new $instance->linked_itemtype();
        $label .= " (" . __($linked_itemtype_object::getTypeName(), 'genericobject') . ")";
    }
    if ($label != '') {
        return $label;
    } else {
        return $class;
    }
}
/** @var array $LOG_FILTER */
global $LOG_FILTER;
$LOG_FILTER = [];
/*
 * a simple logger function
 * You can disable logging by using the global $LOG_FILTER
 * in setup.php after including this file
 */
function _log()
{
    /** @var array $LOG_FILTER */
    global $LOG_FILTER;
    $trace = debug_backtrace();
    $filename = "";
    if (count($trace) > 0) {
        $glpi_root = str_replace("\\", "/", GLPI_ROOT);
        $trace_file = str_replace("\\", "/", $trace[0]['file']);
        $filename = preg_replace("|^" . $glpi_root . "/" . Plugin::getPhpDir('genericobject', false) . "/|", "", $trace_file);
    }
    if (count($trace) > 1) {
        $caller = $trace[1];
    } else {
        $caller = null;
    }
    $msg = _format_trace($trace, func_get_args());
    $msg .= "\n";
    $show_log = false;
    if (
        !is_null($caller) and
        isset($caller['class']) and
        in_array($caller['class'], $LOG_FILTER)
    ) {
        $callee = array_shift($trace);
        $show_log = true;
    }
    if (in_array($filename, $LOG_FILTER)) {
        $show_log = true;
    }
    if ($show_log) {
        call_user_func_array("Toolbox::logInFile", ['generic-object', $msg, true]);
    }
}

function _format_trace($bt, $args)
{
    static $tps = 0;
    $msg = "";
    $msg = "From \n";
    if (count($bt) > 0) {
        foreach (array_reverse($bt) as $idx => $trace) {
            $msg .= sprintf("  [%d] ", $idx);
            if (isset($trace['class'])) {
                $msg .= $trace['class'] . '::';
            }
            $msg .= $trace['function'] . '()';
            if (isset($trace['file'])) {
                $msg .= ' called in ' . $trace['file'] . ', line ' . $trace['line'];
            }
            $msg .= "\n";
        }
    }

    if ($tps && function_exists('memory_get_usage')) {
        $msg .= ' (' . number_format(microtime(true) - $tps, 3) . '", ' .
         number_format(memory_get_usage() / 1024 / 1024, 2) . 'Mio)';
    }
    $msg .= "\n  ";
    foreach ($args as $arg) {
        if (is_array($arg) || is_object($arg)) {
            $msg .= " " . str_replace("\n", "\n  ", print_r($arg, true));
        } else if (is_null($arg)) {
            $msg .= 'NULL ';
        } else if (is_bool($arg)) {
            $msg .= ($arg ? 'true' : 'false') . ' ';
        } else {
            $msg .= $arg . ' ';
        }
    }
    $msg .= "\n";
    return $msg;
}
