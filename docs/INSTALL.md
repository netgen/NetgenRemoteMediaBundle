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

### Install the bundle via Composer

Run the following from your website root folder:

```
composer require netgen/remote-media-bundle:^2.0
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
  
### Add routing configuration

Add the following entry to your main `routing.yaml` file:

```yaml
netgen_remote_media:
    resource: "@NetgenRemoteMediaBundle/Resources/config/routing.yml"
```
    
### Update the database

This bundle has a custom table in the database which needs to be created:
    
```
mysql -u<user> -p<password> -h<host> <db_name> < vendor/netgen/remote-media-bundle/bundle/Resources/sql/schema.sql`
```

Or you can also do it via Doctrine:

```
php app/console doctrine:schema:update --force
```

(or run with `--dump-sql` to get the sql needed for creating the table).

### Clear caches

Run the following command:

```
php bin/console cache:clear
```
