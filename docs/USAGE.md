# Usage instructions for Netgen Remote Media Bundle #

## Managing your media ##
You can do simple management of your media files with the provided Vue.js application.

### Uploading ###
With a simple press of a button, you can either browse the existing media (separated into images and videos) or upload the media from your own computer.
When uploading, you can select which folder to upload to*, and when browsing and searching, you can also limit your view per folder.

(*) **Note**: for automatic creating of folders when uploading, please remember to activate "Auto-create folders" option on your Cloudinary account.

### Cropping the images ###
The editors have the ability to crop the images immediately when editing the content object. As long as the variations in the `crop` transformation are defined, editors are able to choose which part of the image they want to show for each use case.
One example of this would be if one would use the `<picture>` tag with different formats for desktop and mobile. In this case, editors can upload a single image and choose different cropping for each resolution.

## Image variation definitions ##
Image variations are defined through the YAML configuration. The configuration is siteaccess aware. Furthermore, you can group variations, meaning you can have two variations that are named the same but use different transformations depending on the place where they are used (eg. different variations for different types).

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
