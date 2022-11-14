# Installation instructions for Netgen Remote Media Bundle

## Installation steps
  
### Configure the bundle

In `config.yml` add basic configuration:

```yaml
netgen_remote_media:
    provider: cloudinary
    account_name: [your_cloud_name]
    account_key: [your_key]
    account_secret: [your_secret]
```

\* Currently `cloudinary` is the only supported provider.

#### Cache configuration

This bundle has a PSR6 compatible remote media provider for Cloudinary which caches all requests towards Cloudinary to prevent breaking the API rate limit and to improve performance. You can manually configure cache pool as well as desired TTOL:


```yaml
netgen_remote_media:
    cache:
        pool: cache.app
        ttl: 7200
```

Above shown are the default used parameters. For more information about creating and configuring cache pools, see https://symfony.com/doc/current/cache.html.

**Warning:** the provider uses tagging functionality to be able to invalidate cache on eg. resource upload, edit or delete. In order to support cache tagging, a corresponding tag-aware pool has to be used. If you use a non-tag-aware pool, tagging will be disabled which means that you will experience some issues while using the bundle. Eg. newly uploaded resource might not be visible immediatelly (until the cache doesn't expire) in the browser or search.

### Require the bundle

Run the following from your website root folder:

```
$ composer require netgen/remote-media-bundle:^3.0
```

### Activate the bundle

Activate the bundle in `config/bundles.php` file by adding it to the array:

```php
return [
    ...

    Netgen\Bundle\RemoteMediaBundle\NetgenRemoteMediaBundle(),
];
```

### Add routing

This bundle has some internal Symfony routes. In order for them to work, include them in your main `config/routes.yaml`:

```yaml
netgen_remote_media:
    resource: "@NetgenRemoteMediaBundle/Resources/config/routing.yml"
```

### Configure Cloudinary webhook notifications (optional)

If you want to be able to manage resources through Cloudinary interface as well, you might want to configure the [Cloudinary webhook notifications](Cloudinary/WEBHOOK_NOTIFICATIONS.md). Read more on the link.

### Clear the caches

Run the following command:

```
$ php bin/console cache:clear
```
