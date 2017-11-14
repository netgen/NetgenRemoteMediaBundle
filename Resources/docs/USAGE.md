# Usage instructions for Netgen Remote Media Bundle #

## Content class definition ##
You can add the remote media content field to your content type. There is no additional configuration needed.

## Image variation definitions ##
Image variations are defined through yaml configuration, in the similar way it is defined in eZ Platform. Configuration is siteaccess aware, and furthermore, you can define variations per content type, meaning you can have two variations that are named the same, but use different transformations depending on the content type where they are used.
Example:
```yaml
netgen_remote_media:
    system:
        default:
            image_variations:
                default:
                    full:
                        transformations:
                            - { name: crop, params: [2, 1] }
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
                    large:
                        transformations:
                            - { name: crop, params: [1600, 800] }
                            - { name: fill, params: [1600, 800] }
```
You can check the list of available tranformations [here](Resources/docs/Transfromations.md). Further details on what which transformation does is available on [Cloudinary web](http://cloudinary.com/documentation/image_transformations).

## In templates ##
If you have added the field to your content class, now you can use it with normal `ez_render_field` function:
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
In case you want to manually define the transformations to use from the twig template, you can do that as well, just instead of the name of the format, pass an array with manually defined options:
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
This example would produce the image which would have a defined dimensions of `240x240` and would be delivered `png` format.

Other parameters you can pass to the function are:
* `alt_text` - overriding the one from the image
* `title` - override the image caption, and use this as title
* `link_href` - if not empty, wrap the image in `<a>` tag

Note that not all parameters will be applicable always, it depends on the type of the resource you are rendering.

If you need to get just the url of the image, you can get the `Variation` object by using twig function `netgen_remote_variation`:
```php
{% set variation = netgen_remote_variation(content, 'image', 'full') %}
```
