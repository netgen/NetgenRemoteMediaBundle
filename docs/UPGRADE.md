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
* All the exceptions have changed their namespace from `Netgen\Bundle\RemoteMediaBundle\Exception` to `Netgen\RemoteMedia\Exception`
* Main `ngremotemedia-type` CSS class has been renamed to `ngremotemedia-container`
* Configuration for variations is now done organized in `variation groups` instead of `content types` so a few methods have changed their signature:
    * `Netgen\RemoteMedia\Core\VariationResolver::getVariationsForContentType($contentTypeIdentifier)` became `Netgen\RemoteMedia\Core\VariationResolver::getVariationsForGroup(string $contentTypeIdentifier): array`
* This core bundle doesn't support siteaccess-aware image variations configuration anymore.
* `Query` and `Result` objects related to search have been made more generic and changed their namespace from `Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search` to `Netgen\RemoteMedia\API\Search`.
* `Result` object now returns an array of `RemoteResource` objects instead of associative array directly from Cloudinary. The method has been changed from `getResults()` to `getResources()`.
* Most methods that were accepting `resourceId` and `resourceType` in the `RemoteMediaProvider` class are now accepting `RemoteResource` object instead. Those methods are:
    * `deleteResource(RemoteResource $resource)`
    * `addTagToResource(RemoteResource $resource, string $tag)`
    * `removeTagFromResource(RemoteResource $resource, string $tag)`
    * `removeAllTagsFromResource(RemoteResource $resource)`
    * `updateTags(RemoteResource $resource, array $tags)`
    * `updateResourceContext(RemoteResource $resource, array $context)`
 * Method `RemoteMediaProvider::updateTags()` now accepts an array of tags instead of string.
 * Method `RemoteMediaProvider::getRemoteResource()` doesn't return empty value, instead it throws a `Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException` exception. Empty value is not valid anymore.
 * `RemoteResource` object's constructor has been set to private; now it's possible to instantiate it through two available static methods and both methods are taking care that resource ID is not empty (as empty value is not valid anymore): they will throw an `InvalidArgumentException` if this parameter is missing:
    * `public static function createFromParameters(array $parameters): self`
    * `public static function createFromCloudinaryResponse(array $response): self`
