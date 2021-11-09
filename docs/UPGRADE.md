Netgen Remote Media Bundle upgrade instructions
===============================================

Upgrade from 1.0 to 2.0
-----------------------

Version 2.0 is a major release which brings many improvements and new features, as well as code cleanup but it contains some breaking changes as well as removed supports.

### Changed requiremets
* This bundle now supports only Symfony v3, the support for Symfony v2.8 (or lower) has been dropped
* This bundle now supports only PHP 7.2 - 7.4 versions
* This bundle now supports only eZ Platform v2 (from 2.4, or kernel 7.4)

### Data structure changes

The data structure for eZ field value as well as for eZ XML field custom tag, which is being stored in the database, has been changed. There's a migration command which will iterate through all fields (both NGRM and eZ XML fields) in the database and fix their data to support the new version.

**WARNING:** the command works directly with the database, bypassing eZ's API. This means that it will fix all the data (including previous versions, drafts etc.) but it also means that **database backup is mandatory** prior to executing the command.

#### Command

```console
php bin/console netgen:ngremotemedia:refresh:ez_fields  
```

#### Options

* `--dry-run` - this will only display the actions that will be performed
* `--force` - this will use the first found resource and empty fields with non-existing resources
* `--content-ids` - list of content IDs to process (default: all)
* `--chunk-size` - the size of the chunk of attributes to fetch (and size of chunk of resource to get from remote media in one API request)
* `--rate-limit-treshold` - Percentage of remaining API rate limit below which the command will exit to prevent crashing the media on frontend (default 50 (%)).

#### Possible rate-limit issues

As you may know, cloud providers (eg. Cloudinary) mostly have the limit of requests that can be executed towards their API in specific period (one hour for Cloudinary). Since the command iterates through all fields and asks the API for each of these resources, it might break the rate limit which will make remote media resources on the live site unavailable.

In order to prevent this, the command will perform API fetches in chunks: it will first generate the list of all resources that need to be fetched and then fetch them in one request. You can control the size of the chunk with `--chunk-size` parameter. You can also set the `--rate-limit-treshold` which will stop the execution if the command consumes more than a set percentage of the rate limit.

### Code changes

#### Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider

Due to dropping support for PHP versions lower than 7.2, the signature for some methods has been changed to include parameter type and return type declaration:

```diff
-public function buildVariation(Value $value, $contentTypeIdentifier, $variationName, $secure = true)
+public function buildVariation(Value $value, string $contentTypeIdentifier, $variationName, ?bool $secure = true): Variation
```

```diff
-public function getRemoteResource($resourceId, $resourceType = 'image')
+public function getRemoteResource(string $resourceId, string $resourceType = 'image'): Value
```

```diff
-protected function prepareUploadOptions($fileName, $fileUri, $options = array())
+protected function prepareUploadOptions(UploadFile $uploadFile, $options = []): array
```

This, of course, also affects the Cloudinary implementation: `Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary`.

#### Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway

Search function now receives `Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Query` object and returns `Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Result` object:

```diff
-abstract public function search($query, $options = array(), $limit = 10, $offset = 0)
+abstract public function search(Query $query): Result;
```

Since Cloudinary requires type, resource type and resource public ID to uniquely identify a resource (eg. both image and video can exist with the same public ID), resource type parameter has been added to many methods to be more precise at determining the resource that we want to work with. The type parameter has been skipped for now since we mostly use `upload` type. This will be implemented in the next major release.

```diff
-abstract public function addTag($id, $tag)
+abstract public function addTag($id, $type, $tag);
```

```diff
-abstract public function removeTag($id, $tag)
+abstract public function removeTag($id, $type, $tag);
```

```diff
-abstract public function update($id, $options)
+abstract public function update($id, $type, $options)
```

```diff
-abstract public function getDownloadLink($id, $options)
+abstract public function getDownloadLink($id, $type, $options);
```

```diff
-abstract public function delete($id)
+abstract public function delete($id, $type)
```

### New methods

#### Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider

```php
public function usage(): array
public function searchResourcesCount(Query $query): int
public function listSubFolders(string $parentFolder): array
public function createFolder(string $path): void
public function listTags(): array
public function removeAllTagsFromResource($id, $type)
```

#### Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Gateway

```php
public function usage()
public function searchCount(Query $query)
public function listSubFolders(string $parentFolder)
public function createFolder(string $path)
public function listTags()
public function removeAllTags($id, $type)
```
