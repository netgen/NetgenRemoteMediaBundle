Netgen Remote Media Bundle upgrade instructions
===============================================

Upgrade from 2.0 to 3.0
-----------------------

Version 3.0 is a major release where the whole bundle has been decoupled from eZ and now contains only core features and doesn't depend on eZ anymore. In order to retain all the eZ functionalities (field type, NG admin UI and eZ admin UI), you have to install the new bundle which serves as a connector between Remote Media and eZ (currently WIP).

Also, there were a lot of changes during the decoupling from eZ to make things cleaner so there is a lot of breaking changes.

* This bundle doesn't depend on eZ anymore and does not contain any eZ related integration
* This bundle doesn't depend on Netgen Open Graph bundle anymore and does not contain any related integration
* Minimum supported version of PHP is now PHP 7.4
* Most of the classes in the codebase are now `final` - if you have any overrides in your project, refactor them to use the Decorator pattern instead
* The main value object `Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value` which was extending eZ field type value has now been renamed to `Netgen\RemoteMedia\API\Values\RemoteResource` and all methods use or return this new one; methods and properties remained the same; now it's a standalone value object
* The variation object `Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation` has been renamed to `Netgen\RemoteMedia\API\Values\Variation` and all methods use or return this new one; methods and properties remained the same
* All the core classes and interfaces have changed their namespace from `Netgen\Bundle\RemoteMediaBundle\RemoteMedia` to `Netgen\RemoteMedia\Core` so those have to be updated accordingly
