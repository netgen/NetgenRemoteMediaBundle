# Netgen Remote Media Bundle #

Netgen Remote Media Bundle is an eZ Publish bundle providing field type which supports remote resource providers, such as [Cloudinary](http://cloudinary.com/).

This repository contains field type (and legacy data type) implementation, and it provides the interface for legacy administration. 


## Features ##

* field type support for remote resources (only Cloudinary supported at the moment)
* support for images, videos and documents upload
* images cropping editor


## Installation instructions ##

**Requirements**
* eZ Publish 5.4.*


TODO: move to install.md

**Installation steps**

* Run the following from your website root folder:
	`$ composer require netgen/remote-media-bundle:^1.0@alpha`
    
* Configure the bundle:
    * in `config.yml` add basic configuration:
    ```
    netgen_remote_media:
        provider: cloudinary
        account_name: [your_cloud_name]
        account_key: [your_key]
        account_secret: [your_secret]
    ```
    
* Configure legacy settings:
    * add the following to `ezoe.ini.append.php` (create one if it does not exist)
    ```
    [EditorSettings]
    Plugins[]=ngremotemedia
    
    [EditorLayout]
    Buttons[]=ngremotemedia
    ```
    
* Activate the bundle:
    ```
    public function registerBundles()
    {
        ...
    
        $bundles[] = new Netgen\Bundle\RemoteMediaBundle\NetgenRemoteMediaBundle();
    
        return $bundles;
    }
    ```
    
* Update the database with a custom table:
	* `$ mysql -u<user> -p<password> -h<host> <db_name> < vendor/netgen/remote-media-bundle/Netgen/Bundle/RemoteMediaBundle/Resources/sql/mysql/schema.sql`
    * **OR** run `php ezpublish/console doctrine:schema:update --force` (or run with `--dump-sql` to get the sql needed for creating the table)

* Clear the caches
    * run the following command:
    ```
    $ php ezpublish/console cache:clear
    ```
TODO: move to usage.md
* Now you can add the field to your content class and use it:
    * in twig template, you can use the `ez_render_field` function:
    ```php
    {{ ez_render_field(
        content,
        'remote_image',
        {
            'parameters':
            {
                'format': 'Medium'
            }
        }
    ) }}

    ```


## What's next ## 
Here's the plan for features that should be in place before 1.0 release:
- [x] drop eZExceed support
- [x] move image variation defintions to yaml files
- [ ] add option to select folder when uploading the image
- [ ] support both eZ 5.4.*, and eZPlatform with Legacy Bridge
- [ ]add support for Netgen Content Browser(*) - on roadmap for 1.x
- [ ]add support for Netgen Layouts (*) - on roadmap for 1.x

(*) Netgen Content Browser and Netgen Layouts are products of Netgen. This bundle will provide support for both if they are already installed and activated on the project.


## Copyright ## 

* Copyright (C) 2016 Keyteq. All rights reserved.
* Copyright (C) 2016 Netgen. All rights reserved.


## License ##
TODO: add explicit licence
* http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
