# Named objects

This feature provides a way to configure remote resources and locations through YAML configuration, which can then be easily accessible through both PHP and TWIG. This is useful for situations where you need to use some "hardcoded" resources in the code (eg. in templates, controllers etc.).

## Configuration

### Remote resource

It's possible to define just named remote resources, without location. Configuration consists of simple key, value pairs, where key is the desired name (eg `my_resource`) and value is the remote ID on the cloud.

Example:

```yaml
netgen_remote_media:
    named_objects:
        remote_resource:
            my_resource: 'upload|image|folder/my_resource'
```

### Remote resource location

If you also need a location for that resource, it's possible by using configuration for locations. Configuration consists of key, value pairs, where key is the desired name (eg. `my_resource_location`) and value is another array which accepts three keys:

- `resource_remote_id` - remote ID of the resource on the cloud
- `source` (optional) - text that will be used for the location source (eg. `named_my_resource_location`)
- `watermark_text` (optional) - watermark text which can be used if a watermaked variation gets built from that location

Example:

```yaml
netgen_remote_media:
    named_objects:
        remote_resource_location:
            my_resource_location:
                resource_remote_id: 'upload|image|folder/my_resource'
                source: 'named_my_resource_location'
                watermark_text: 'Netgen.io'
```

## Usage

You can easily fetch configured resources and locations both through PHP and TWIG. It will automatically create resource (and location) for you in the database on first fetch.

**Warning**: If you remove the named resource or location from configuration, it won't delete the entities in the database!

### Usage in PHP

There are two methods that you can use in the main provider interface, which will return corresponding object (resource or location):

```php
/**
 * @throws \Netgen\RemoteMedia\Exception\NamedRemoteResourceNotFoundException
 * @throws \Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException
 */
public function loadNamedRemoteResource(string $name): RemoteResource;

/**
 * @throws \Netgen\RemoteMedia\Exception\NamedRemoteResourceLocationNotFoundException
 * @throws \Netgen\RemoteMedia\Exception\RemoteResourceNotFoundException
 */
public function loadNamedRemoteResourceLocation(string $name): RemoteResourceLocation;
```

Then you can use them as any other resources and locations:

```php
$resource = $provider->loadNamedRemoteResource('my_authenticaed_resource');
$authenticatedResource = $provider->authenticateRemoteResource($resource, AuthToken::fromDuration(300));

$logoLocation = $provider->loadNamedRemoteResourceLocation('logo_image');
$variation = $provider->buildVariation($logoLocation, 'default', 'logo');
```

### Usage in TWIG

There are two TWIG methods that encapsulate previously mentioned PHP methods. They both accept only name, and they return either corresponding object (resource or location) or null, in case of exception:

```html
{% set resource = ngrm_named_remote_resource('my_authenticated_resource') %}
{% set authenticated_resource = ngrm_authenticate_remote_resource(resource, 300) %}

<a href="{{ authenticated_resource.url }}">Download</a>

{% set location = ngrm_named_remote_resource_location('my_image') %}
{{ ngrm_remote_resource_variation_html_tag(location, 'default', 'wide') }}
```
