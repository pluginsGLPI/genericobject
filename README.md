# Genericobject GLPI plugin

[![License](https://img.shields.io/github/license/pluginsGLPI/genericobject.svg?&label=License)](https://github.com/pluginsGLPI/genericobject/blob/develop/LICENSE)
[![Follow twitter](https://img.shields.io/twitter/follow/Teclib.svg?style=social&label=Twitter&style=flat-square)](https://twitter.com/teclib)
[![Telegram Group](https://img.shields.io/badge/Telegram-Group-blue.svg)](https://t.me/glpien)
[![Project Status: Active](http://www.repostatus.org/badges/latest/active.svg)](http://www.repostatus.org/#active)
[![GitHub release](https://img.shields.io/github/release/pluginsGLPI/genericobject.svg)](https://github.com/pluginsGLPI/genericobject/releases)
[![GitHub build](https://travis-ci.org/pluginsGLPI/genericobject.svg?)](https://travis-ci.org/pluginsGLPI/genericobject/)

## ⚠️ IMPORTANT NOTICE - END OF LIFE

**GenericObject 3.0.0 is a migration-only plugin designed exclusively for GLPI 11.0+**

Plugin marking the end of life of `genericobject`.
It updates database tables and data to the final version, required before the automatic migration of forms into GLPI 11 core.
⚠️ This plugin must be installed in production only to perform the update, and then uninstalled once the operation is complete.

### Purpose of this version

This version serves as a migration facilitator. Its main objectives are:
Update database tables and data to prepare their final state for GLPI 11.
Ensure the necessary compatibility for the automatic migration of objects and forms to the GLPI 11 core.

Usage Instructions:
--------------------------
1. After migrating to GLPI 11, install this final version (3.0.0).
2. Run the data migration operation using the following command:
    - "`php bin/console migration:genericobject_plugin_to_core`".
3. Warning: if you have added extra fields to your `GenericObject` items using the `Fields` plugin, you must update it to the version compatible with GLPI 11 (1.22.0).
This version indeed includes a dedicated migration method to correctly associate these fields with GLPI's `CustomAsset` system, which they previously relied on.
4. Uninstall the `GenericObject` plugin once all data has been integrated and verified within the core of GLPI 11.

This version provides support for migration only. For features related to custom assets, use GLPI 11's native custom assets.


## Documentation

We maintain a detailed documentation here -> [Documentation](http://glpi-plugins.readthedocs.io/en/latest/genericobject/index.html)

## Contact

For notices about major changes and general discussion of genericobject, subscribe to the [/r/glpi](https://www.reddit.com/r/glpi/) subreddit.
You can also chat with us via [@glpi on Telegram](https://t.me/glpien).

## Professional Services

![GLPI Network](./glpi_network.png "GLPI network")

The GLPI Network services are available through our [Partner's Network](http://www.teclib-edition.com/en/partners/). We provide special training, bug fixes with editor subscription, contributions for new features, and more.

Obtain a personalized service experience, associated with benefits and opportunities.

## Contributing

* Open a ticket for each bug/feature so it can be discussed
* Follow [development guidelines](http://glpi-developer-documentation.readthedocs.io/en/latest/plugins/index.html)
* Refer to [GitFlow](http://git-flow.readthedocs.io/) process for branching
* Work on a new branch on your own fork
* Open a PR that will be reviewed by a developer

## Copying

* **Code**: you can redistribute it and/or modify
