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
    * add `"netgen/remote-media-bundle": "~0.1"` to your `composer.json`
    * add `git@bitbucket.org:netgen/netgenremotemedia.git` as a composer repository
    * run `composer update netgen/remote-media-bundle`
    
* Configure the bundle:
    * in `config.yml` add basic configuration:
    ```
    netgen_remote_media:
        provider: cloudinary
        account_name: [your_cloud_name]
        account_key: [your_key]
        account_secret: [your_secret]
    ```
    Complete configuration options are available at `Resources/config/config.yml.example`
    
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
    
* Update the database:
    * run `php ezpublish/console doctrine:schema:update --force` (or run with `--dump-sql` to get the sql needed for creating the table)

* Clear the caches
    * run the following command:
    ```
    $ php ezpublish/console cache:clear
    ```

* Grant proper permissions to editors.
    
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
* drop eZExceed support
* move image variation defintions to yaml files
* add option to select folder when uploading the image
* support both eZ 5.4.*, and eZPlatform with Legacy Bridge
* add support for Netgen Content Browser(*)
* add support for Netgen Layouts (*)

(*) Netgen Content Browser and Netgen Layouts are products of Netgen. This bundle will provide support for both if they are already installed and activated on the project.


## Copyright ## 

* Copyright (C) 2016 Keyteq. All rights reserved.
* Copyright (C) 2016 Netgen. All rights reserved.


## License ##

* http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
