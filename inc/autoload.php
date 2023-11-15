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

class PluginGenericobjectAutoloader
{
    protected $paths = [];

    public function __construct($options = null)
    {
        if (null !== $options) {
            $this->setOptions($options);
        }
    }

    public function setOptions($options)
    {
        if (!is_array($options) && !($options instanceof \Traversable)) {
            throw new \InvalidArgumentException();
        }

        foreach ($options as $path) {
            if (!in_array($path, $this->paths)) {
                $this->paths[] = $path;
            }
        }
        return $this;
    }

    public function processClassname($classname)
    {
        preg_match("/^Plugin([A-Z][a-z0-9]+)([A-Z]\w+)$/", $classname, $matches);

        if (count($matches) < 3) {
            return false;
        } else {
            return $matches;
        }
    }

    public function autoload($classname)
    {
        $matches = $this->processClassname($classname);

        if ($matches !== false) {
            $plugin_name = strtolower($matches[1]);
            $class_name = strtolower($matches[2]);

            if ($plugin_name !== "genericobject") {
                return false;
            }

            $filename = implode(".", [
                $class_name,
                "class",
                "php"
            ]);

            foreach ($this->paths as $path) {
                 $test = $path . DIRECTORY_SEPARATOR . $filename;
                if (file_exists($test)) {
                    return include_once($test);
                }
            }
        }
        return false;
    }

    public function register()
    {
        spl_autoload_register([$this, 'autoload']);
    }
}
