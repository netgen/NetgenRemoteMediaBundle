# Netgen Remote Media Bundle changelog

## 3.0.0

### Added

* `cloudinary.use_asset_folder_as_public_id_prefix` option which prefixes the public ID with the asset folder in `dynamic` folder mode, so the same file can be uploaded to different folders
* upload error for an existing resource now names the folder when the conflict is limited to that folder
* CKEditor 5 plugin `format` option to replace how the remote media element is stored in editor data (default `htmlFormat` keeps the existing `div.ngremotemedia-cke5` markup)
* CKEditor 5 plugin `fieldId` option accepts a `(editor) => string` function

### Fixed

* CKEditor 5 plugin is a regular function, so it loads without transpilation (CKEditor instantiates function plugins with `new`, which fails for arrow functions)
* CKEditor 5 plugin field ids are unique per inserted element (previously a count-based suffix could repeat after removing an element)
* CKEditor 5 plugin `config` option is deep merged over the defaults, so partial `paths`/`translations`/`inputFields` no longer drop the remaining keys
* CKEditor 5 plugin no longer requests the selected image for elements stored without a location
* CKEditor 5 plugin restores element alignment when loading stored data, and stores it as valid `text-align` CSS
* CKEditor 5 plugin ignores invalid stored alignment values (earlier versions stored `"undefined"`), so saving unchanged content no longer adds `text-align:undefined;`
* CKEditor 5 plugin keeps the default selected image when the selected image request fails, instead of storing the error response

### Changed

* minimum version of `cloudinary/cloudinary_php` is now 2.9 (required for `asset_folder` and `use_asset_folder_as_public_id_prefix` upload parameters)

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
