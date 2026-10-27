Netgen Remote Media Bundle upgrade instructions
===============================================

Upgrade to 3.0 (database migrations)
------------------------------------

### Database migrations

The bundle now ships Doctrine migrations for its database tables. Run them with:

```
php bin/console doctrine:migrations:migrate --configuration=vendor/netgen/remote-media-bundle/migrations/doctrine.yaml
```

If the tables already exist (for example, created with `doctrine:schema:update`), the initial migration fails. Mark it as executed and run the migrations again to execute the remaining ones:

```
php bin/console doctrine:migrations:version 'Netgen\RemoteMedia\Migrations\Doctrine\Version030000' --add --configuration=vendor/netgen/remote-media-bundle/migrations/doctrine.yaml
php bin/console doctrine:migrations:migrate --configuration=vendor/netgen/remote-media-bundle/migrations/doctrine.yaml
```

Upgrade from 2.0 to 3.0
-----------------------

Version 3.0 is a major release. The bundle is decoupled from eZ / Ibexa and contains only the core features, so there are a lot of breaking changes. See [CHANGELOG](CHANGELOG.md) for the full list of changes.

### Upgrade `composer.json`

* Require `netgen/remote-media-bundle` `^3.0` and run `composer update`.
* If you use Ibexa CMS, also require [netgen/remote-media-ibexa](https://github.com/netgen/remote-media-ibexa), which provides the field type, the admin UI integration and the rich text integration that were removed from this bundle.
* PHP 8.1+, Symfony 5.4 or 6.2+ (Symfony 7 is not supported), `doctrine/orm` 2.10+, `doctrine/doctrine-bundle` 2.4+ and `cloudinary/cloudinary_php` 2.9+ are required.
* `netgen/open-graph-bundle` is no longer supported and the Open Graph handler is removed.
* The Composer package type is now `symfony-bundle`.

### Database

* The `ngremotemedia_field_link` table is no longer used.
* The bundle maps two new entities to the `ngrm_remote_resource` and `ngrm_remote_resource_location` tables with Doctrine XML mappings.
* This bundle does not ship Doctrine migrations, create the tables with the schema tooling of your project (for example a migration generated with `doctrine:migrations:diff`).
* Data migration from the v2 data to the new tables is not part of this bundle yet, v2 data is not migrated automatically.

### Configuration

* Siteaccess aware configuration is removed. Move `netgen_remote_media.system.<siteaccess>.image_variations.<content type>.<variation>` to `netgen_remote_media.image_variations.<variation group>.<variation>`, content types are replaced by variation groups.
* The `netgen_remote_media.cloudinary.cache_ttl` parameter has been removed. Use `netgen_remote_media.cache.ttl` (default `7200`) instead.
* The `ezpublish.cache_pool` service is no longer used. Use `netgen_remote_media.cache.pool` (default `cache.app`) and make sure the pool is tag-aware. See [Install instructions](INSTALL.md) for more info.
* The new `netgen_remote_media.cloudinary.folder_mode` option defaults to `dynamic`. Version 2 had no such option and worked as `fixed`, so set it to `fixed` if your Cloudinary account uses fixed folder mode.
* Import the new routing file, the eZ admin routes are removed:

```yaml
netgen_remote_media:
    resource: "@NetgenRemoteMediaBundle/Resources/config/routing.yml"
```

* Run `php bin/console assets:install` again, the frontend assets are rebuilt.
* The main `ngremotemedia-type` CSS class has been renamed to `ngremotemedia-container`, update your custom styles.

### Breaking changes

* `Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value` has been removed. Use `Netgen\RemoteMedia\API\Values\RemoteResource`.
* `Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Variation` has been removed. Use `Netgen\RemoteMedia\API\Values\RemoteResourceVariation`.
* `Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Query` has been removed. Use `Netgen\RemoteMedia\API\Search\Query`.
* `Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Result` has been removed. Use `Netgen\RemoteMedia\API\Search\Result`.
* `RemoteMediaProvider` has been removed. Use `Netgen\RemoteMedia\API\ProviderInterface` (service `netgen_remote_media.provider`).
* `VariationResolver` has been removed. Use `Netgen\RemoteMedia\Core\Resolver\Variation`.
* `VariationResolver::getVariationsForContentType()` has been removed. Use `Variation::getAvailableVariations(?string $group = null)`.
* `Result::getResults()` has been removed. Use `Result::getResources()`, which returns an array of `RemoteResource` objects instead of the raw Cloudinary response.
* `TransformationHandlersCompilerPass` has been renamed to `TransformationHandlersPass`.
* Exceptions have moved from `Netgen\Bundle\RemoteMediaBundle\Exception` to `Netgen\RemoteMedia\Exception`.
* Twig functions `netgen_remote_variation`, `netgen_remote_variation_embed`, `netgen_remote_video`, `netgen_remote_video_embed`, `netgen_remote_video_thumbnail`, `netgen_remote_download`, `netgen_remote_media` and `netgen_remote_media_embed` have been removed. Use the `ngrm_*` functions, for example `ngrm_remote_resource_variation`, `ngrm_remote_resource_html_tag`, `ngrm_video_thumbnail` and `ngrm_remote_resource_download_url`.
* The `scaling_format` Twig filter has been renamed to `ngrm_scaling_format`.
* Command `netgen:ngremotemedia:usage` has been renamed to `netgen:remote_media:usage`.
* Commands `netgen:ngremotemedia:add:data`, `netgen:ngremotemedia:add:field`, `netgen:ngremotemedia:migrate:ezimage` and `netgen:ngremotemedia:refresh:ez_fields` have been removed. Use `netgen:remote_media:refresh` to refresh stored resources.
* Most classes are now `final`. Refactor your overrides to use the decorator pattern instead.

### Changes in `RemoteResource`

* It does not extend the eZ field type value anymore, it is a standalone value object with private properties and getters.
* It has an `id` (the ID of the stored resource, `null` if the resource is fetched from remote) and a `remoteId` which uniquely identifies the resource on the cloud.
* The provider merges the parameters needed to identify the resource (for Cloudinary `type`, resource type and public ID) into the single `remoteId` string.
* A single `type` replaces `mediaType`, `audio` and `document` are new types.
* It has a single `url` instead of both URL and secure URL.
* `altText`, `caption` and `tags` are separate properties instead of being part of the metadata array.
* `variations` are removed, crop settings are now stored on the `RemoteResourceLocation` object.
* Metadata is a plain array available through `getMetadata()`.
* Tags can be manipulated with `hasTag()`, `addTag()` and `removeTag()`.

### Changes in `Query` and `Result`

* `Query` receives arrays of types, folders, tags and remote IDs instead of single values.
* `Result` has no static `fromResponse()` constructor anymore, it is provider independent.

### Deprecations

There are no deprecations in Netgen Remote Media 3.0.
