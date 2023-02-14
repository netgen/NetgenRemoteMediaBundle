# Installation instructions

## Requirements

* eZ Platform

**Suggested**

* this package works best with [Netgen Admin UI](https://github.com/netgen/NetgenAdminUIBundle)

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
    
### Activate legacy extension

Add the following in your central `site.ini.append.php` file (usually `ezpublish_legacy/settings/override/site.ini.append.php`):

```
[ExtensionSettings]
ActiveExtensions[]=ngremotemedia
```
    
### Activate the bundle

Add the following in your `app/AppKernel.php` file:

```php
public function registerBundles()
{
    ...

    $bundles[] = new Netgen\Bundle\RemoteMediaBundle\NetgenRemoteMediaBundle();

    return $bundles;
}
```

### Install the bundle via Composer

Run the following from your website root folder:

```
composer require netgen/remote-media-bundle:^2.0
```
  
### Add routing configuration

Add the following entry to your main `routing.yaml` file:

```yaml
netgen_remote_media:
    resource: "@NetgenRemoteMediaBundle/Resources/config/routing.yml"
```
    
### Update the database

This bundle has a custom table in the database which needs to be created:
    
```
mysql -u <user> -p<password> -h <host> <db_name> < vendor/netgen/remote-media-bundle/bundle/Resources/sql/schema.sql
```

Or you can also do it via Doctrine:

```
php app/console doctrine:schema:update --force
```

(or run with `--dump-sql` to get the sql needed for creating the table).

### Additional configuration

This bundle has a few parameters that can be overridden through YAML configuration in your own site, if needed.


#### Upoad prefix

If you need to change Cloudinary API url (to use eg. GEO specific URLs), there's a parameter `upload_prefix` (set to `https://api.cloudinary.com` by default):

```yaml
netgen_remote_media:
    upload_prefix: 'https://api.cloudinary.com'
```

#### Audio waveform image

If you set the following parameter `netgen_remote_media.default.parameters.audio.enable_waveform` to `true` (default: `false`), audio files will be rendered with `<video>` tag instead and their waveform image will be shown instead of video.

The parameter is siteaccess-aware, so you can replace `default` with desired siteaccess if you want to enable this only on some siteaccesses.

#### Remove unused resources

You can configure the bundle to automatically delete a resource which is not being used on the cloud any more. This is a part of the semantic configuration (default: `false`):

```yaml
netgen_remote_media:
    remove_unused: false
```

This functionality depends on the custom database table which keeps info about all the images that are being used in a NGRM field, with connection to that content and field itself. When you remove a resource from NGRM field, if this resource is not contained in that table, it will be deleted from the cloud.

**Warning:** this table is currently implemented only for the NGRM field itself. It's not implemented for the eZ XML and Richtext fields and for any other external integrations (eg. with Netgen Layouts integration). Also, if you use the same cloud account for multiple sites, it won't be aware of the usage on other sites so use this option with caution.

#### Cloudinary cache TTL

This bundle catches all the requests towards Cloudinary to prevent breaking API rate limits. The default is `7200` seconds but this can be changed with the parameter `netgen_remote_media.cloudinary.cache_ttl`.

#### Use subdomains

Cloudinary has a possibility to use multiple sub-domains to fetch/download resources to improve web browsing field. This is enabled by default but it can be disabled by using the parameter `netgen_remote_media.parameters.use_subdomains`.

### Clear caches

Run the following command:

```
php bin/console cache:clear
```
