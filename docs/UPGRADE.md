Netgen Remote Media Bundle upgrade instructions
===============================================

Upgrade from 3.1 to 4.0
-----------------------

### Upgrade `composer.json`

* Require `netgen/remote-media-bundle` `^4.0` and run `composer update`.
* The integration packages, [netgen/remote-media-ibexa](https://github.com/netgen/remote-media-ibexa) and [netgen/layouts-remote-media](https://github.com/netgen-layouts/layouts-remote-media), need a release which allows `netgen/remote-media-bundle` `^4.0`. Upgrade them together with the bundle.

### Assets

* The frontend assets are rebuilt. Run `php bin/console assets:install` again.

### Database

* Version 4.0 ships the `Version040000` migration. Run it with:

    ```
    php bin/console doctrine:migrations:migrate --configuration=vendor/netgen/remote-media-bundle/migrations/doctrine.yaml
    ```

* The migration adds the nullable `position` column to `ngrm_remote_resource_location`. The form stores the order of a field's resources in it. Existing locations have no position.
* Run it before you generate the migration of a `OneToOne` relation (see below) with `doctrine:migrations:diff`. Otherwise the diff also adds `position`, and `Version040000` then fails with "Duplicate column name 'position'". The other way round, the `SET l.position = 0` of the example fails if `Version040000` didn't run.
* Pass the diff the `--filter-expression` from [Upgrade from 3.0.1 to 3.0.2](#upgrade-from-301-to-302), so it doesn't drop `ngrm_migration_versions`.
* The `down()` method drops the column together with the stored order.
* If the Doctrine metadata cache is stored outside of the Symfony cache directory (for example in Redis), clear it after the upgrade with `php bin/console doctrine:cache:clear-metadata`.

### Code changes

* `ProviderInterface::generateLocationHtmlTag()` is now part of the interface. Custom providers which don't extend `AbstractProvider` must implement it. In 3.1 they triggered a deprecation and the TWIG runtime applied the location overrides itself.
* The data of `RemoteMediaType` is now always a Doctrine `ArrayCollection` of `RemoteResourceLocation` objects. This is also the case with `'upload_limit' => 1`, then the collection has 0 or 1 items. The new `upload_limit` option defaults to `null` (unlimited). Add `'upload_limit' => 1` to fields which should keep accepting one resource:

    ```php
    $builder->add('images', RemoteMediaType::class, [
        'upload_limit' => 1,
    ]);
    ```

* Every entity property used by a `RemoteMediaType` field must be a to-many relation. A field bound to a single `RemoteResourceLocation` fails with a `TransformationFailedException`. See below how to migrate a `OneToOne` relation.
* `RemoteMediaType` has only one child now, the hidden JSON field `collectionPayload`. The children `locationId`, `remoteId`, `type`, `altText`, `caption`, `altTextOverride`, `captionOverride`, `watermarkText`, `tags`, `cropSettings` and `source` are removed. Custom form themes which render them, and code which submits them (tests, import scripts), must use `collectionPayload` instead. Its format is described in [FORM.md](FORM.md#how-the-field-is-submitted).
* A submission without `collectionPayload` keeps the field's value. Send an empty list, `[]`, to clear the field.
* If a resource no longer exists on the remote, the form and the CKEditor `location/create` and `location/update` endpoints keep it. The change is stored locally and a warning with the remote ID is logged. In 3.x the resource was deleted together with all its locations. `RemoteResourceService::handleRemoteUpdate()` returns `false` in that case. The Cloudinary notification callback and `netgen:remote_media:refresh --delete` still delete such resources.
* Constructor changes, if you create these services yourself:
    * `RemoteMediaType` takes the `RemoteResourceService` as the 3rd argument.
    * `RemoteResourceService` takes an optional logger as the 2nd argument.
    * The `Configuration` controller takes `VueTranslations` instead of `TranslatorInterface` as the 2nd argument.

### Migrate a `OneToOne` relation to a collection

Change the relation of the entity as shown in [FORM.md](FORM.md#add-relation-to-your-entity): a `ManyToMany` relation with a unique inverse join column, `#[ORM\OrderBy(['position' => 'ASC'])]`, `addX()` and `removeX()` methods, and no setter which replaces the collection.

Then move the data in a Doctrine migration of your project. Run `Version040000` first (see [Database](#database)), then generate the migration with `php bin/console doctrine:migrations:diff` and the `--filter-expression` from [Upgrade from 3.0.1 to 3.0.2](#upgrade-from-301-to-302), then add the `INSERT` and `UPDATE` statements between the creation of the join table and the drop of the old column. Without them the existing relations are lost. Run it with `php bin/console doctrine:migrations:migrate`.

For an entity table `my_entity` with the column `remote_resource_location_id`, mapped as in [FORM.md](FORM.md#add-relation-to-your-entity), the whole migration is (MySQL):

```php
<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261114000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Move my_entity.remote_resource_location_id to the my_entity_image join table';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on MySQL.');

        $this->addSql('CREATE TABLE my_entity_image (my_entity_id INT NOT NULL, remote_resource_location_id INT NOT NULL, INDEX IDX_113C6D80677473C9 (my_entity_id), UNIQUE INDEX UNIQ_113C6D80B29166B0 (remote_resource_location_id), PRIMARY KEY(my_entity_id, remote_resource_location_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE my_entity_image ADD CONSTRAINT FK_113C6D80677473C9 FOREIGN KEY (my_entity_id) REFERENCES my_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE my_entity_image ADD CONSTRAINT FK_113C6D80B29166B0 FOREIGN KEY (remote_resource_location_id) REFERENCES ngrm_remote_resource_location (id) ON DELETE CASCADE');

        // Added by hand: move the existing relations and give them a position
        $this->addSql('INSERT INTO my_entity_image (my_entity_id, remote_resource_location_id) SELECT id, remote_resource_location_id FROM my_entity WHERE remote_resource_location_id IS NOT NULL');
        $this->addSql('UPDATE ngrm_remote_resource_location l INNER JOIN my_entity_image i ON i.remote_resource_location_id = l.id SET l.position = 0');

        // The name of the old foreign key is in your generated migration, or in SHOW CREATE TABLE my_entity
        $this->addSql('ALTER TABLE my_entity DROP FOREIGN KEY FK_OLD_FOREIGN_KEY_NAME');
        $this->addSql('ALTER TABLE my_entity DROP remote_resource_location_id');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on MySQL.');

        $this->addSql('ALTER TABLE my_entity ADD remote_resource_location_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE my_entity ADD CONSTRAINT FK_OLD_FOREIGN_KEY_NAME FOREIGN KEY (remote_resource_location_id) REFERENCES ngrm_remote_resource_location (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_OLD_INDEX_NAME ON my_entity (remote_resource_location_id)');

        // Keeps the first resource of each collection, the others are dropped from the entity
        $this->addSql('UPDATE my_entity e SET e.remote_resource_location_id = (SELECT i.remote_resource_location_id FROM my_entity_image i INNER JOIN ngrm_remote_resource_location l ON l.id = i.remote_resource_location_id WHERE i.my_entity_id = e.id ORDER BY l.position, l.id LIMIT 1)');

        $this->addSql('DROP TABLE my_entity_image');
    }
}
```

The index and foreign key names above are the ones Doctrine generates for these table and column names. Take the names from your generated migration. The locations themselves stay in `ngrm_remote_resource_location`, only the link moves to the join table.

### Templates

* `@NetgenRemoteMedia/form/remote_media_type.html.twig` renders the `collectionPayload` field instead of the `locationId` and `source` fields.
* `@NetgenRemoteMedia/app/interactions.html.twig` takes three new parameters: `locations` (a list of `RemoteResourceLocation` objects), `upload_limit` and `location_source`. `upload_limit` defaults to `1`. The `location_id` parameter is removed. Pass `locations`, and `collectionPayload` in `input_fields`, like `remote_media_type.html.twig` does. The `variation_settings_json` parameter is removed, crop settings are read from the locations.
* Both `interactions.html.twig` and `select_folder.html.twig` get their translations from the new `ngrm_vue_translations()` TWIG function. Templates which override them should do the same.
* The `ngremotemedia.nb.yaml` translations are removed. Symfony falls back from `nb` to `no`.

### `/configuration` endpoint

* The `translations` of the `/configuration` response (used by the CKEditor plugin) are now the same list as in the form widget. The legacy keys (`Search for media`, `Cancel`, `next`, `prev` etc.) and the keys of removed labels are gone, and the keys of the upload panel, the media picker and reordering are added.

### `ngrm-change` event

* The `detail` of the `ngrm-change` browser event now also has `config`, the configuration of the widget.
* `detail.selectedImage.uploadedResources` holds all resources of the field, in order. The other fields of `selectedImage` still describe the first resource.
* In form fields the widget posts only the `collectionPayload` input, named `inputFields.collectionPayload` in `detail`. The named inputs of 3.x (`remoteId`, `locationId` etc.) are not rendered anymore. Listeners which read them must read `collectionPayload` or `selectedImage.uploadedResources`. In CKEditor (`mode: 'embed'`) the named inputs stay.

### Blocked form submit

* While files in the upload panel are waiting to be uploaded or uploading, the widget blocks the submit of the form it is in. It cancels both the click on a submit button and the `submit` event, in the capture phase on `document`, and shows a message above the panel. If the field is not visible, for example in an inactive tab, the message is also shown on top of the page.
* Submit buttons with the `formnovalidate` or the `data-ngrm-skip-submit-block` attribute are never blocked, so Cancel or Delete draft buttons keep working. Add `data-ngrm-skip-submit-block` to other buttons which leave the form without saving.
* Hosts which react to the submit button themselves (for example the loading state of a Sylius `loadable` form) see neither event. If they change their state in some other way before the submit, they can undo it on the bubbling `ngrm-submit-blocked` event of the widget. Its `detail` has `form` (the blocked form) and `message` (the message shown to the editor).

### Upload endpoint

* The upload endpoint answers a missing file, an unsupported visibility and a failed file upload with `400 Bad Request` and the translated message in `detail` of a JSON body. In 3.x it threw an `InvalidArgumentException`, which ended in a `500` response.

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
