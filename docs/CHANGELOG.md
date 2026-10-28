# Netgen Remote Media Bundle changelog

## 3.0.0

### BC breaks

* the bundle is decoupled from eZ / Ibexa: the field type, legacy bridge, admin UI integration, XmlText / RichText converters and eZ commands are removed, use [netgen/remote-media-ibexa](https://github.com/netgen/remote-media-ibexa) for the Ibexa integration
* `netgen/open-graph-bundle` is no longer supported and the Open Graph handler is removed
* requires PHP 8.1+, Symfony 5.4 or 6.2+ (Symfony 7 is excluded by the Composer constraints), `doctrine/orm` 2.10+, `doctrine/doctrine-bundle` 2.4+ and `cloudinary/cloudinary_php` 2.9+ (Cloudinary SDK v1 is no longer supported)
* Composer package type is now `symfony-bundle` (was `ezplatform-bundle`) and all non-bundle code lives in the new `Netgen\RemoteMedia\` namespace (`lib/`)
* the `ngremotemedia_field_link` table and the `RemoteMediaFieldLink` entity are removed, resources are stored in the new `ngrm_remote_resource` and `ngrm_remote_resource_location` tables (Doctrine XML mappings), no Doctrine migrations are shipped and v2 data is not migrated automatically
* `Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value` is replaced by `Netgen\RemoteMedia\API\Values\RemoteResource` (identified by `remoteId`) and `RemoteResourceLocation` (crop settings, watermark text, source), `Variation` is replaced by `RemoteResourceVariation`
* `Provider\Cloudinary\Search\Query` and `Result` are replaced by `Netgen\RemoteMedia\API\Search\Query` and `Result` (`getResults()` is now `getResources()` and returns `RemoteResource` objects)
* `RemoteMediaProvider` is replaced by `Netgen\RemoteMedia\API\ProviderInterface` (service `netgen_remote_media.provider`), its methods take `RemoteResource` / `RemoteResourceLocation` objects
* `VariationResolver` is replaced by `Netgen\RemoteMedia\Core\Resolver\Variation`, `getVariationsForContentType()` is replaced by `getAvailableVariations(?string $group)`
* configuration is no longer siteaccess aware, `system.<siteaccess>.image_variations.<content type>` is now `image_variations.<variation group>` at the top level of `netgen_remote_media`
* `cloudinary.folder_mode` is a new option which defaults to `dynamic`, 2.x had no such option and worked as `fixed`, so set it to `fixed` if your Cloudinary account uses fixed folder mode
* the `netgen_remote_media.cloudinary.cache_ttl` parameter and the `ezpublish.cache_pool` service are replaced by the `cache.pool` (default `cache.app`, use a tag-aware pool) and `cache.ttl` (default `7200`) options
* Twig functions `netgen_remote_*` are replaced by `ngrm_*` functions (`ngrm_remote_resource`, `ngrm_remote_resource_variation`, `ngrm_remote_resource_html_tag`, `ngrm_video_thumbnail`, `ngrm_remote_resource_download_url`, ...) and the `scaling_format` filter is now `ngrm_scaling_format`
* console command `netgen:ngremotemedia:usage` is now `netgen:remote_media:usage`, the other `netgen:ngremotemedia:*` commands are removed
* eZ admin routes are removed, routing now imports `@NetgenRemoteMediaBundle/Resources/config/routing.yml` (ajax routes `netgen_remote_media_ajax_*` and the Cloudinary notify callback)
* `TransformationHandlersCompilerPass` is renamed to `TransformationHandlersPass` and most classes are now `final`, use decoration instead of extension
* frontend assets are rebuilt (run `assets:install` again) and the main CSS class `ngremotemedia-type` is renamed to `ngremotemedia-container`

### Added

* `cloudinary.folder_mode` option (`dynamic` / `fixed`) with support for both Cloudinary folder modes
* `cloudinary.use_asset_folder_as_public_id_prefix` option which prefixes the public ID with the asset folder in `dynamic` folder mode, so the same file can be uploaded to different folders
* `cloudinary.append_extension`, `cloudinary.unique_filenames`, `cloudinary.cache_requests`, `cloudinary.log_requests` and `cloudinary.encryption_key` options
* `named_objects` configuration with `ngrm_named_remote_resource` and `ngrm_named_remote_resource_location` Twig functions
* `templates.view_resource` configuration
* `RemoteMediaType` and `RemoteMediaFolderType` Symfony form types (options like `variation_group`, `allowed_types`, `parent_folder`, `disable_upload`, `hide_filename` and `upload_context`)
* CKEditor 5 plugin for inserting remote media into rich text
* protected resources, with `ngrm_authenticate_remote_resource` and `ngrm_authenticate_remote_resource_location` Twig functions
* watermark text on resource locations, uploading from an external URL and moving resources between folders
* `netgen:remote_media:refresh` command for refreshing stored resources
* `NotificationReceivedEvent` dispatched on every Cloudinary webhook notification
* the Cloudinary notify callback handles resource move, display name change and asset folder move / rename notifications (move and display name only in `dynamic` folder mode)
* transformation handlers `flags`, `gravity`, `opacity` and `watermark_text`
* `audio` and `document` resource types
* `ngrm-change` browser event (`detail`: `inputFields`, `selectedImage`, `fieldId`, `changedField`) dispatched by the Vue app so custom listeners can react to a selection change, each field instance has its own modal portal
* Croatian translations

### Changed

* minimum version of `cloudinary/cloudinary_php` is now 2.9 (required for `asset_folder` and `use_asset_folder_as_public_id_prefix` upload parameters)
* resource name is resolved from the Cloudinary `display_name` (Cloudinary resolves public ID and display name from the original filename)
* password-protected PDFs are uploaded as `raw` resources
* upload error for an existing resource now names the folder when the conflict is limited to that folder
* the Vue app is reworked and the assets are rebuilt

### Fixed

* Doctrine XML mappings use the correct `http://` namespaces, so they pass Doctrine XML validation (`validate_xml_mapping`)
* `symfony/serializer` and `symfony/translation` are now required (the bundle depends on both but did not declare them)

## 1.1.11

### Fixed

* cache keys for resource listings and fetching of a single resource
  are now generated with having 'type' in mind (image/video)

## 1.1.10

### Fixed

* remove slashes from cache key generation (fixes folders listing issue)

## 1.1.9

### Added

* cache TTL is exposed through the parameter `netgen_remote_media.cloudinary.cache_ttl`

### Changed

* non-media files now get uploaded with original extension

## 1.1.8

### Added

* support caching in both 5.4 and 6 version of eZ (stash PSR-6 support change in 0.14)
* cache getting resource by id from Cloudinary
* invalidate cache of the resource when updating

## 1.1.7

### Fixed

* enable caching for listings

##  1.1.6

### Removed

* removed controls on the video by default, can be added manually by user

##  1.1.5

### Fixed

* Cloudinary provider now returns empty `Value` when empty resource id is provided

## 1.1.4

### Fixed

* undefined offset error when requesting non-existent resource id
* validation for required field

## 1.1.3

### Fixed

* ezjscpacker does not have an issue anymore with handlebars

## 1.1.2

### Fixed

* cropping is now always correctly saved when switching between formats

## 1.1.1

### Removed

* Netgen Admin UI menu plugin is now disabled as it is not used, and caused double legacy aside template to show up

## 1.1

### Added

* introduced cached gateway
* folder support (browsing/searching and uploading)
* tests
* opengraph handler for [NetgenOpenGraphBundle](https://github.com/netgen/NetgenOpenGraphBundle)
* persist connection between remote media and content for embedded media
* preview formats in the configuration are made configurable

### Fixed

* several bugfixes
* administration interface has been cleaned up and improved

### Changed

* browse and search is now separated for images/documents and videos/audio files
* improved cropping interface
* search by name prefix and search by tag have been separated

### Removed

* support for eZExceed

## 1.0.11-alpha

### Fixed

* fixed `load more` functionality when searching for remote image

## 1.0.10-alpha

### Added

* add `toString` and `fromString` methods to legacy datatype implementation

## 1.0.9-alpha

### Added

* option to use subdomains on Cloudinary

## 1.0.8-alpha

### Fixed

* fix listing of images when browsing in administration
* bugfix on videos in legacy
### Changed

* require latest 1.x version of Cloudinary API
* better configuration for embedded images

## 1.0.7-alpha

### Fixed

* field template configuration

## 1.0.6-alpha

### Changed

* wrap inline image together with caption for easier styling

## 1.0.5-alpha

### Added

* add caption to images embedded in ezxml text

## 1.0.4-alpha

### Fixed

* make sure configuration is merged correctly, and the values are not overwritten

## 1.0.3-alpha

### Added

* add migration command
* test for existence of array offset before testing it's value

## 1.0.2-alpha

### Fixed

* enable overwriting of the media on Cloudinary.

## 1.0.1-alpha

### Added

* CS fixer and travis configuration
* support for 'invalidate' option on media upload

### Fixed

* bugfix when no default configuration has been set

## 1.0-alpha

* Initial release
