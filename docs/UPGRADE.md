Netgen Remote Media Bundle upgrade instructions
===============================================

Upgrade from 2.0 to 3.0
-----------------------

Version 3.0 is a major release where the whole bundle has been decoupled from eZ and now contains only core features and doesn't depend on eZ anymore. In order to retain all the eZ functionalities (field type, NG admin UI and eZ admin UI), you have to install the [Netgen Remote Media & Ibexa CMS integration](https://github.com/netgen/remote-media-ibexa) bundle which serves as a connector between Remote Media and eZ.

Also, there were a lot of changes during the decoupling from eZ to make things cleaner so there is a lot of breaking changes.

* This bundle doesn't depend on eZ anymore and does not contain any eZ related integration
* This bundle doesn't depend on Netgen Open Graph bundle anymore and does not contain any related integration
* Minimum supported version of PHP is now PHP 8.1
* This bundle now uses Cloudinary PHP SDK v2 instead of v1
* Most of the classes in the codebase are now `final` - if you have any overrides in your project, refactor them to use the Decorator pattern instead
* The main value object `Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value` which was extending eZ field type value has now been renamed to `Netgen\RemoteMedia\API\Values\RemoteResource` and all methods use or return this new one; methods and properties remained the same; now it's a standalone value object
* The variation object `Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation` has been renamed to `Netgen\RemoteMedia\API\Values\Variation` and all methods use or return this new one; methods and properties remained the same
* All the core classes and interfaces have changed their namespace from `Netgen\Bundle\RemoteMediaBundle\RemoteMedia` to `Netgen\RemoteMedia\Core` so those have to be updated accordingly
* All the exceptions have changed their namespace from `Netgen\Bundle\RemoteMediaBundle\Exception` to `Netgen\RemoteMedia\Exception`
* Main `ngremotemedia-type` CSS class has been renamed to `ngremotemedia-container`
* Configuration for variations is now done organized in `variation groups` instead of `content types` so a few methods have changed their signature:
    * `Netgen\RemoteMedia\Core\VariationResolver::getVariationsForContentType($contentTypeIdentifier)` became `Netgen\RemoteMedia\Core\VariationResolver::getVariationsForGroup(string $contentTypeIdentifier): array`
* This core bundle doesn't support siteaccess-aware image variations configuration anymore.
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
* The bundle was using eZ's `ezpublish.cache_pool` cache pool for caching while it was depending on eZ. Now it requires cache pool to be provided in order to work. See [Install instructions](INSTALL.md) for more info.
* Parameter for cache TTL `netgen_remote_media.cloudinary.cache_ttl` has been removed in favour of semantic configuration for cache. See [Install instructions](INSTALL.md) for more info.

### Code changes

#### Value changed to RemoteResource

Old object `Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia` has been replaced with new object `Netgen\RemoteMedia\API\Values\RemoteResource` with the following changes:

* it doesn't extend eZ's field type value object anymore since this bundle is now detached from eZ
* all properties are now private and there are corresponding getters as well as some helper methods to manipulate with the object
* static constructors have been removed from the value object -> they've been replaced with factory
* now it contains `id` which represents the ID of the stored resource in the database (via Doctrine) -> can be `null` if the resource has been fetched from remote
* now it contains `remoteId` which is used to uniquely identify the resource on the cloud -> in case when cloud providers require multiple parameters to uniquely identify the resource (eg. Cloudinary requires `resourceId`, `resourceType` and `type`), each provider is responsible to merge this info into single string which will be used as `remoteId`
* `mediaType` property has been removed; now we have a single `type` and it's up to the provider to convert this to the appropriate type for cloud provider
* now it contains two new types: `audio` and `document`
* now it contains only single `url` property instead of both URL and secure URL - it's up to provider to decide whether it will provide secure or not secure URLs
* `altText`, `caption` and `tags` have been extracted from metadata array to separate properties
* there are useful methods for manipulating with tags, such as `hasTag()`, `addTag()` or `removeTag()`
* `variations` property has been removed -> it's now a part of the separate `RemoteResourceLocation` object as a `cropSettings` property
* metadata is now an ordinary array, without pre-defined form, it's goal is to keep inside all additional data that cloud provider might return but are not mandatory for the core functionality -> there are useful methods for manipulating the metadata array, such as `hasMetaDataProperty()` and `getMetaDataProperty()`

#### Search query and result objects

Old class `Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Result` has been replaced with new one `Netgen\RemoteMedia\API\Values\SearchResult` with the following changes:

* static constructor has been removed from it, making it provider independent; it has been replaced with a factory

Old class `Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Query` has been replaced with new one `Netgen\RemoteMedia\API\Values\Query` with the following changes:

* it now receives an array of types (the provider is responsible to convert this type to corresponding one for eg. Cloudinary)
* it now receives an array of folders instead of single one
* it now receives an array of tags instead of single one
* it now receives an array of remoteIds instead of resourceIds (the provider is responsible to convert this remoteId to corresponding parameters for eg. Cloudinary)
