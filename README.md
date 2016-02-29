# Netgen Remote Media Bundle #

Netgen Remote Media Bundle is an eZ Publish/eZ Platform bundle providing field type which supports remote resource providers, such as [Cloudinary](http://cloudinary.com/). 

This repository contains field type (and legacy data type) implementation, and it provides the interface for legacy administration, and plugin for image cropping through [eZExceed](http://www.ezexceed.com/) user interface. 


## Features ##

* field type support for remote resources
* support for images, videos and documents upload
* images cropping editor


## Installation instructions ##

**Requirements**
* eZ Publish 5.4.*

**Optional requirements**
* eZExceed

**Installation steps**
* Use composer:
    * add `"netgen/remote-media-bundle": "~0.*"` to your `composer.json`
    * add `git@bitbucket.org:netgen/netgenremotemedia.git` as a composer repository
    * run `composer update netgen/remote-media-bundle`
    
* Configure the bundle:
    * in `config.yml` add basic configuration:
    ```yaml
    netgen_remote_media:
        provider: cloudinary
        account_name: [your_cloud_name]
        account_key: [your_key]
        account_secret: [your_secret]
    ```
    Complete configuration options are available at `Resources/config/config.yml.example`
    * put the following in your `ezpublish/config/routing.yml`
    ```yml
    _netgen_remote_media:
        resource: "@NetgenRemoteMediaBundle/Resources/config/routing.yml"
    ```
    
* Configure legacy settings:
    * add the following to `ezoe.ini.append.php` (create one if it does not exist)
    ```ini
    [EditorSettings]
    Plugins[]=remotemedia
    
    [EditorLayout]
    Buttons[]=remotemedia
    ```
    
* Activate the bundle:
    ```php
    public function registerBundles()
    {
        ...
    
        $bundles[] = new Netgen\Bundle\RemoteMediaBundle\NetgenRemoteMediaBundle();
    
        return $bundles;
    }
    ```
    
* Update the database:
    * run `php ezpublish/console doctrine:schema:update --force` (or run with `--dump-sql` to get the sql needed for creating the table)
     
* Clear the caches
    * run the following command:
    ```bash
    $ php ezpublish/console cache:clear
    ```
    
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


## Copyright ## 

* Copyright (C) 2016 Keyteq. All rights reserved.
* Copyright (C) 2016 Netgen. All rights reserved.

## License ##

* http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
