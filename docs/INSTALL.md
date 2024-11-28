# Installation instructions for Netgen Remote Media Bundle

## Configure the bundle

In `config.yml` add basic configuration:

```yaml
netgen_remote_media:
    provider: cloudinary
    account_name: [your_cloud_name]
    account_key: [your_key]
    account_secret: [your_secret]
```

**Note:** Currently `cloudinary` is the only supported provider.

### Cache configuration

This bundle has a PSR6 compatible remote media provider for Cloudinary which caches all requests towards Cloudinary to prevent breaking the API rate limit and to improve performance. You can manually configure cache pool as well as desired TTL:

```yaml
netgen_remote_media:
    cache:
        pool: cache.app
        ttl: 7200
```

Above shown are the default used parameters. For more information about creating and configuring cache pools, see https://symfony.com/doc/current/cache.html.

**Warning:** the provider uses tagging functionality to be able to invalidate cache on eg. resource upload, edit or delete. In order to support cache tagging, a corresponding tag-aware pool has to be used. If you use a non-tag-aware pool, tagging will be disabled which means that you will experience some issues while using the bundle. Eg. newly uploaded resource might not be visible immediately (until the cache doesn't expire) in the browser or search.

### Cloudinary configuration

#### Folder mode

On June 4th, 2024, Cloudinary has introduced a setting called `folder_mode` which changes the connection between folders and resource public ID. This required some changes in how this bundle works. You can read more [here](https://cloudinary.com/documentation/folder_modes).

All new accounts are automatically set to `dynamic` mode and this can't be changed, while old accounts are still in `fixed` mode. This can be changed but once changed to `dynamic`, it can't be switched back.

So in order to support both modes, there's a parameter with the same name here, and it has to be properly configured. You can check your mode in your Cloudinary dashboard.

```yaml
netgen_remote_media:
    cloudinary:
        folder_mode: dynamic
```

Default value is `dynamic` and another available mode is `fixed` (for old accounts).

#### Caching and logging requests

There are three Cloudinary API gateways implemented:

 * API gateway to query Cloudinary API directly
 * PSR6 cached gateway which internally uses API and caches all requests
 * Monolog logger gateway which logs all queries towards API

It's possible to configure both caching and logging which will decide about gateways being used:

```yaml
netgen_remote_media:
    cloudinary:
        cache_requests: true
        log_requests: false
```

By default, caching is enabled and logging is disabled. If both caching and logging is enabled, gateways will be used with the following order:

```
Cached gateway -> Logger gateway -> API gateway
```

So that logger logs only direct requests towards the API.

**Note:** for caching to work, cache has to be configured (see [Cache configuration](#cache-configuration)) .

#### Auth token for protected resources (Cloudinary Premium)

This bundle also supports and implements Cloudinary's functionality to have protected resources, which are not publicly available by default, but you need to authenticate first and get a signed URL with a token that is valid for a specific amount of time. Read more about this on the Cloudinary site: [Media Access Control and Authentication](https://cloudinary.com/documentation/control_access_to_media).

For this to work, you need Cloudinary premium account since we use token based authentication for that which is a premium feature (read more: [Token ]()). For this you need an encryption key from Cloudinary (read more on the above link how to get it) which will automatically enable this feature. The key can be configured here:

```yaml
netgen_remote_media:
    cloudinary:
        encryption_key: [YOUR_CLOUDINARY_ENCRYPTION_KEY]
```

### Upload prefix

If you need to change Cloudinary API url (to use eg. GEO specific URLs), there's a parameter `upload_prefix` (set to `https://api.cloudinary.com` by default):

```yaml
netgen_remote_media:
    upload_prefix: 'https://api.cloudinary.com'
```

## Require the bundle

Run the following from your website root folder:

```
$ composer require netgen/remote-media-bundle:^3.0
```

## Activate the bundle

Activate the bundle in `config/bundles.php` file by adding it to the array:

```php
return [
    ...

    Netgen\Bundle\RemoteMediaBundle\NetgenRemoteMediaBundle(),
];
```

## Add routing

This bundle has some internal Symfony routes. In order for them to work, include them in your main `config/routes.yaml`:

```yaml
netgen_remote_media:
    resource: "@NetgenRemoteMediaBundle/Resources/config/routing.yml"
```

## Configure Cloudinary webhook notifications (optional)

If you want to be able to manage resources through Cloudinary interface as well, you might want to configure the [Cloudinary webhook notifications](Cloudinary/WEBHOOK_NOTIFICATIONS.md). Read more on the link.

## Clear the caches

Run the following command:

```
$ php bin/console cache:clear
```
