# Usage instructions

## Content type definition

You can add the remote media content field to your content type. There is no additional configuration needed. You can add it either through eZ admin interface (both legacy (eg. [Netgen Admin UI](https://github.com/netgen/NetgenAdminUIBundle)) and new stack admin are supported), or via available console command:

```
php bin/console netgen:ngremotemedia:add:field
```

Needed arguments:

* `content_type_identifier` - the identifier of the content type where you want to add the field (eg. `article`)
* `field_identifier` - the identifier of the field that will be created (eg. `remote_image`)
* `field_name` - the name of the field that will be created (eg. `Remote image`)

Options:

* `--field_position` - the position of the field that will be created (default: 0)

## Managing your media

You can do simple management of your media files while editing the content, directly from the administration interface, both on the legacy administration and Netgen Admin UI. 

### Uploading

With a simple press of a button, you can either browse the existing media (separated into images (which include some documents like PDF) video and audio or RAW files) or upload the media from your own computer.

When uploading, you can select which folder to upload to*, and when browsing and searching, you can also limit your view per folder. This bundle supports multi-level folder structure.

(*) **Note**: for automatic creating of folders when uploading, please remember to activate "Auto-create folders" option on your Cloudinary account.

#### Uploading via console command

You can also upload and add a resource to a specific content using the available console command:

```
php bin/console netgen:ngremotemedia:add:data
```

Required arguments:

* `content_id` - the ID of the content on which you want to add the resource
* `field_identifier` - the identifier of the NGRM field where you want to add the resource
* `image_path` - the path to the resource on the filesystem, which you want to upload

Additional options:

* `alt_text` - alternative text for the resource
* `caption` - caption for the resource
* `language` - if this is a multi-language site, you can select for which language you want to add the image (it accepts the locale value eg. `en_EN`), otherwise it will use the main language by default

### Cropping the images

The editors have the ability to crop the images immediately when editing the content object. As long as the variations in the `crop` transformation are defined, editors are able to choose which part of the image they want to show for each use case.

One example of this would be if one would use the `<picture>` tag with different formats for desktop and mobile. In this case, editors can upload a single image and choose different cropping for each resolution.

**Note:** the interface won't let you define the cropping if the used image is smaller than the defined variation.

## Image variation definitions

Image variations are defined through the YAML configuration in the similar way as they are defined in eZ Platform. The configuration is siteaccess aware. Furthermore, you can define variations per content type, meaning you can have two variations that are named the same but use different transformations depending on the content type where they are used.

Example:

```yaml
netgen_remote_media:
    system:
        default:
            image_variations:
                default:
                    full:
                        transformations:
                            - { name: resize, params: [800, 600] }
                            - { name: quality, params: ['auto', 'best'] }
                            - { name: effect, params: ['art', 'sizzle'] }
                            - { name: format, params: ['auto'] }
                    formatted:
                        transformations:
                            - { name: transformation, params: ['namedTransformation'] }
                frontpage:
                    small:
                        transformations:
                            - { name: fit, params: [250,250] }
                    full:
                        transformations:
                            - { name: crop, params: [1600, 800] }
                            - { name: fill, params: [1600, 800] }
```
You can check the list of the available tranformations [here](Resources/docs/Transfromations.md). Further details on what each transformation does is available on [Cloudinary web](http://cloudinary.com/documentation/image_transformations).

### WYSIWYG editor variations

When inserting an image or video into eZ XML or Richtext WYSIWYG editor fields, it is possible to select a variation that will be inserted instead of original image (with cropping editor also available). The list of variations that will be available there is defined under `embed` group:

```yaml
netgen_remote_media:
    system:
        default:
            image_variations:
                embed:
                    full:
                        transformations:
                            - { name: crop, params: [1600, 800] }
                            - { name: fill, params: [1600, 800] }
```

## Usage in templates

### Rendering the eZ field

If you have added the field to your content class, you can now use it with the normal `ez_render_field` function, and you can define the variation with the `format` parameter:

```php
{{ ez_render_field(
    content,
    'remote_image',
    {
        'parameters':
        {
            'format': 'large'
        }
    }
) }}
```

In the above example, `remote_image` is the name of the field and `large` is the name of the variation.

In case you want to manually define which transformations to use from the Twig template, you can do that as well. Instead of the name of the format, pass an array with the manually defined options:

```php
{{ ez_render_field(
    content,
    'image', {
        'parameters': {
            'format': {
                'width': 240,
                'height': 240,
                'fetch_format': 'png'
            }
        }
    }
) }}
```

This example will produce an image which will have a defined dimensions of `240x240` and will be delivered in a `png` format.

The other parameters you can pass to the function are:
* `alt_text` - overriding the one from the image
* `title` - override the image caption and use this as title
* `link_href` - if not empty, wrap the image in `<a>` tag

Note that not all parameters will always be applicable; it depends on the type of the resource you are rendering.

### Manual rendering

If you just need to get the URL of the image, you can get it from the field value as a `url` (http) or `secure_url` (https) property:

```php
{% set image_url = ez_field_value(content, 'image').secure_url %}
```
 
Or, if you need a variation, you can get the `Variation` object by using the Twig function `netgen_remote_variation` which accepts eZ content, field name and variation name:

```php
{% set variation = netgen_remote_variation(content, 'image', 'full') %}
```

The variation object then contains the `url` property.

```php
{% set image_url = variation.url %}
```

## Usage in PHP

If you need to get a resource value, variation or simply an URL for the resource somewhere in a service, or controller, you can use the service `@netgen_remote_media.provider` which will automatically provide the corresponding provider based on configuration (currently only `cloudinary`). You can see available methods in the abstract provider class: `Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider`.

### Fetching a resource

In order to fetch a single resource, you need the `resourceId` and `resourceType`:

```php
$resource = $this->remoteMediaProvider->getRemoteResource('my_folder/my_image.jpg', 'image');
$url = $resource->secure_url;
```

### Fetching a variation

You can also build a variation for a resource from eZ content using the following function:

```php
$variation = $this->remoteMediaProvider->buildVariation($resource, 'my_content_type', 'my_variation');
$url = $variation->url;
```
