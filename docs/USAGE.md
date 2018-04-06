# Usage instructions for Netgen Remote Media Bundle #

## Content type definition ##
You can add the remote media content field to your content type. There is no additional configuration needed.

## Managing your media ##
You can do simple management of your media files while editing the content, directly from the administration interface, both on the legacy administration and Netgen Admin UI. 

### Uploading ###
With a simple press of a button, you can either browse the existing media (separated into images and videos) or upload the media from your own computer.
When uploading, you can select which folder to upload to*, and when browsing and searching, you can also limit your view per folder.

(*) **Note**: for automatic creating of folders when uploading, please remember to activate "Auto-create folders" option on your Cloudinary account.

### Cropping the images ###
The editors have the ability to crop the images immediately when editing the content object. As long as the variations in the `crop` transformation are defined, editors are able to choose which part of the image they want to show for each use case.
One example of this would be if one would use the `<picture>` tag with different formats for desktop and mobile. In this case, editors can upload a single image and choose different cropping for each resolution.

## Image variation definitions ##
Image variations are defined through the yaml configuration in the similar way as they are defined in eZ Platform. The configuration is siteaccess aware. Furthermore, you can define variations per content type, meaning you can have two variations that are named the same but use different transformations depending on the content type where they are used.
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

## In templates ##
If you have added the field to your content class, you can now use it with the normal `ez_render_field` function:
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

If you just need to get the URL of the image, you can get the `Variation` object by using the Twig function `netgen_remote_variation`:
```php
{% set variation = netgen_remote_variation(content, 'image', 'full') %}
```
