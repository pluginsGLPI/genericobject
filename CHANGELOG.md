# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## [2.14.13] - 2025-04-22

### Fixed

- Reverted performance optimization for GenericObject (#417) due to issues with object registration and broken options.


## [2.14.12] - 2024-04-11

### Fixed

- Improve global performance when using many genericobject
- The `TCO` (Total Cost of Ownership) is now correctly updated based on the costs associated with an item.

## [2.14.11] - 2024-12-27

### Fixed

- Fix `Use Components` to display all components
- Hide `items` without proper user access rights in the family list.

## [2.14.10] - 2024-09-06

### Fixed

- Fix ```rightname``` computation
- Fix local override management

## [2.14.9] - 2024-04-02

### Added

- PHP 8.3 compatibilty (```get_parent_class()```).

### Fixed

- Fix input alignment to match GLPI style.
- Fix ```impact``` icon for generic object.
