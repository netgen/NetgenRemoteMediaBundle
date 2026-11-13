Netgen Remote Media Bundle upgrade instructions
===============================================

Upgrade from 3.1 to 4.0
-----------------------

### Code changes

* `ProviderInterface::generateLocationHtmlTag()` is now part of the interface. Custom providers which don't extend `AbstractProvider` must implement it. In 3.1 they triggered a deprecation and the TWIG runtime applied the location overrides itself.

Upgrade from 3.0.3 to 3.1.0
---------------------------

### Database

* Version 3.1.0 ships the `Version030100` migration. Run it with:

    ```
    php bin/console doctrine:migrations:migrate --configuration=vendor/netgen/remote-media-bundle/migrations/doctrine.yaml
    ```

* The migration adds the nullable `alt_text_override` and `caption_override` columns to `ngrm_remote_resource_location`. Existing locations have no overrides, so they keep using the alt text and caption stored on the cloud.
* If the columns already exist (for example, added with `doctrine:schema:update` while testing an untagged version), the migration fails. Mark it as executed instead:

    ```
    php bin/console doctrine:migrations:version 'Netgen\RemoteMedia\Migrations\Doctrine\Version030100' --add --configuration=vendor/netgen/remote-media-bundle/migrations/doctrine.yaml
    ```

* The `down()` method drops both columns together with the stored overrides.
* If the Doctrine metadata cache is stored outside of the Symfony cache directory (for example in Redis), clear it after the upgrade with `php bin/console doctrine:cache:clear-metadata`. Otherwise Doctrine keeps using the old mapping and loading a location fails with `Typed property Netgen\RemoteMedia\API\Values\RemoteResourceLocation::$altTextOverride must not be accessed before initialization`.

### Code changes

* `ProviderInterface` declares the new method `generateLocationHtmlTag()` with `@method`, it will be added to the interface in 4.0. `AbstractProvider` implements it. Custom providers which don't extend `AbstractProvider` keep working, but `ngrm_remote_resource_location_html_tag` triggers a deprecation for them. Implement the method before upgrading to 4.0.
* The overrides are used only where a location is rendered through `ngrm_remote_resource_location_html_tag`, `ngrm_remote_resource_variation_html_tag`, `location.altText` or `location.caption`. Templates which read the values from `location.remoteResource` keep showing the values from the cloud, see [form documentation](FORM.md#alt-text-and-caption-overrides).
* Projects which build the form data of a remote media field manually (outside of the `RemoteMediaType` form, for example in import scripts) must send the current `altTextOverride` and `captionOverride` of the location. A missing key is saved as an empty override, as for the other location fields.

Upgrade from 3.0.1 to 3.0.2
---------------------------

### Database

* Version 3.0.2 ships the `Version030002` migration. Run it with:

    ```
    php bin/console doctrine:migrations:migrate --configuration=vendor/netgen/remote-media-bundle/migrations/doctrine.yaml
    ```

* The migration creates the `ngrm_remote_media_content_link` table used by `netgen/remote-media-ibexa` and the `nglayouts_remote_media_item` table used by `netgen/layouts-remote-media`. The tables are created also if those packages are not installed, they stay empty and unused then.
* Tables which already exist (for example created with `doctrine:schema:update`) are kept with their data. The migration only widens `nglayouts_remote_media_item.value` to `varchar(768)` with the `utf8mb4_bin` collation.
* `netgen/remote-media-ibexa` and `netgen/layouts-remote-media` ship no migrations of their own, the command above is the only one to run.
* All Netgen Remote Media packages share the migrations of this bundle and the version table `ngrm_migration_versions`. One command then updates the database of every installed package, and the tables of the integration packages are always created after `ngrm_remote_resource_location`, which they reference.
* The `down()` method drops both tables together with their data.
* If your project runs `doctrine:migrations:diff` without the integration packages, the diff proposes to drop the two tables. Hide them with the `schema_filter` of the Doctrine DBAL connection:

    ```yaml
    doctrine:
        dbal:
            schema_filter: '~^(?!(ngrm_remote_media_content_link|nglayouts_remote_media_item)$)~'
    ```

* Do not hide the `ngrm_migration_versions` table (for example with `~^(?!ngrm_)~`). The migrations of this bundle do not find the table then and the second run of the command fails with `TableExistsException`.
* The diff also proposes to drop `ngrm_migration_versions`, since it is the version table of this bundle and not of the project. This was already the case in 3.0.1. To exclude it, pass a filter to the diff command, which replaces `schema_filter` for that run, so repeat the project filters in it:

    ```
    php bin/console doctrine:migrations:diff --filter-expression='~^(?!(ngrm_migration_versions|ngrm_remote_media_content_link|nglayouts_remote_media_item)$)~'
    ```

Upgrade from 3.0.0 to 3.0.1
---------------------------

### Database

* Version 3.0.1 ships the `Version030001` migration. Run it with:

    ```
    php bin/console doctrine:migrations:migrate --configuration=vendor/netgen/remote-media-bundle/migrations/doctrine.yaml
    ```

* The migration rebuilds the `ngrm_remote_resource` and `ngrm_remote_resource_location` tables, which took about 10 s on a database with ~164,000 resources and ~171,000 locations (MySQL 8.4).
* The `down()` method fails if longer values than the old column limits already exist.

Upgrade from 2.0 to 3.0
-----------------------

Version 3.0 is a major release. The bundle is decoupled from eZ / Ibexa and contains only the core features, so there are a lot of breaking changes. See [CHANGELOG](CHANGELOG.md) for the full list of changes.

### Upgrade `composer.json`

* Require `netgen/remote-media-bundle` `^3.0` and run `composer update`. The bundle now requires `doctrine/doctrine-migrations-bundle`.
* If you use Ibexa CMS, also require [netgen/remote-media-ibexa](https://github.com/netgen/remote-media-ibexa), which provides the field type, the admin UI integration and the rich text integration that were removed from this bundle.
* PHP 8.1+, Symfony 5.4 or 6.2+ (Symfony 7 is not supported), `doctrine/orm` 2.10+, `doctrine/doctrine-bundle` 2.4+ and `cloudinary/cloudinary_php` 2.9+ are required.
* `netgen/open-graph-bundle` is no longer supported and the Open Graph handler is removed.
* The Composer package type is now `symfony-bundle`.

### Database

* The `ngremotemedia_field_link` table is no longer used.
* The bundle maps two new entities to the `ngrm_remote_resource` and `ngrm_remote_resource_location` tables with Doctrine XML mappings.
* The tables are created by the Doctrine migration shipped with the bundle (own configuration `migrations/doctrine.yaml`, version table `ngrm_migration_versions`). Run it with:

    ```
    php bin/console doctrine:migrations:migrate --configuration=vendor/netgen/remote-media-bundle/migrations/doctrine.yaml
    ```

* If the tables already exist (for example, created with `doctrine:schema:update` on an untagged 3.0), the migration fails. Create the version table, mark the migration as executed and run the migrations again:

    ```
    php bin/console doctrine:migrations:sync-metadata-storage --configuration=vendor/netgen/remote-media-bundle/migrations/doctrine.yaml
    php bin/console doctrine:migrations:version 'Netgen\RemoteMedia\Migrations\Doctrine\Version030000' --add --configuration=vendor/netgen/remote-media-bundle/migrations/doctrine.yaml
    php bin/console doctrine:migrations:migrate --configuration=vendor/netgen/remote-media-bundle/migrations/doctrine.yaml
    ```
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
