Table of supported transformations from Cloudinary.

| Transformation name | Transformation alias | Description |
|:-------------------:|:--------------------:|:-----------:|
| Crop | crop | Crops the image if it has been cropped in the administration interface |
| Effect | effect | Applies the effect to change the visual appearance |
| Fill | fill | Exact given width and height while retaining the original aspect ratio, using only part of the image that fills the given dimensions if necessary |
| Fit | fit | The image is resized so that it takes up as much space as possible within a bounding box defined by the given width and height parameters. The original aspect ratio is retained and all of the original image is visible. |
| Format | format | Defines in format should the media be delivered. |
| Lfill | lfill | Same as the fill mode but only if the original image is larger than the given limit (width and height). This mode doesn't scale up the image if your requested dimensions are bigger than the original image's |
| Limit | limit | Same as the fit mode but only if the original image is larger than the given limit (width and height), in which case the image is scaled down. This mode doesn't scale up the image if your requested dimensions are larger than the original image's. |
| Lpad | lpad | Same as the pad mode but only if the original image is larger than the given limit (width and height), in which case the image is scaled down to fill the given width and height while retaining the original aspect ratio. If the proportions of the original image do not match the given width and height, padding is added to the image to reach the required size.  |
| Mfit | mfit | Same as the fit mode but only if the original image is smaller than the given minimum (width and height), in which case the image is scaled up. All of the original image is visible. |
| Mpad | mpad | Same as the pad mode but only if the original image is smaller than the given minimum (width and height), in which case the image is scaled up to fill the given width and height while retaining the original aspect ratio and with all of the original image visible. You can also specify the color of the background in the case that padding is added. |
| Named transformation | transformation | A named transformation is a set of image transformations that has been given a custom name for easy reference. |
| Pad | pad | Resize the image to fill the given width and height while retaining the original aspect ratio and with all of the original image visible. If the proportions of the original image do not match the given width and height, padding is added to the image to reach the required size. You can also specify the color of the background in the case that padding is added. |
| Quality | quality |  Set compression level to apply to an image as a value between 1 (smallest file size possible) and 100 (best visual quality). Automatic quality selection is also available |
| Resize | resize | Change the size of the image |
| Scale | scale | Change the size of the image exactly to the given width and height without necessarily retaining the original aspect ratio: all original image parts are visible but might be stretched or shrunk. |


For additional details on each of the transformation, consult the Cloudinary [documentation](http://cloudinary.com/documentation/image_transformations) 
