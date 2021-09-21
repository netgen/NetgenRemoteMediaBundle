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

This bundle has a PSR6 compatible remote media provider for Cloudinary which caches all requests towards Cloudinary to prevent breaking the API rate limit and to improve performance. You can manually configure cache adapter service as well as provider (which can be a name of provider service or an URI, in case of eg. Redis or Memcached):


```yaml
netgen_remote_media:
    cache:
        provider: cache.adapter.redis
        provider: 'redis://localhost'
```

By default, `cache.adapter.filesystem` is being used which is supported in Symfony v3, v4 and v5. You can use any available adapter from Symfony (see https://symfony.com/doc/current/cache.html) or implement your own adapter.

**Warning:** the provider uses tagging functionality to be able to invalidate cache on eg. resource upload, edit or delete. In order to support cache tagging, a corresponding tag-aware adapter has to be used. If you use a non-tag-aware adapter, tagging will be disabled which means that you will experience some issues while using the bundle. Eg. newly uploaded resource might not be visible immediatelly (until the cache doesn't expire) in the browser or search.

**Note:** default adapter `cache.adapter.filesystem` **does not** support tagging!

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

In case of Symfony v3, activate the bundle in `app/AppKernel.php` file by adding it to the `$bundles` array in `registerBundles` method:

```php
public function registerBundles()
{
    ...

    $bundles[] = new Netgen\Bundle\RemoteMediaBundle\NetgenRemoteMediaBundle();

    return $bundles;
}
```

### Clear the caches

Run the following command:

```
$ php bin/console cache:clear
```
