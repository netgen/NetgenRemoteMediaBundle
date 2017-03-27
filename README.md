# Netgen Remote Media Bundle #

Netgen Remote Media Bundle is an eZ Publish bundle providing field type which supports remote resource providers, such as [Cloudinary](http://cloudinary.com/).

This repository contains field type (and legacy data type) implementation, and it provides the interface for legacy administration. 


## Features ##

* field type support for remote resources (only Cloudinary supported at the moment)
* support for images, videos and documents upload
* images cropping editor


## Licence and installation instructions ##

[Licence](LICENCE)

[Installation instructions](Resources/docs/INSTALL.md)


## Documentation ##

For usage documentation see [USAGE.md](Resources/docs/USAGE.md)


## What's next ## 
Here's the plan for features that should be in place before 1.0 release:
- [x] move image variation defintions to yaml files
- [x] add option to pass any option to cloudinary directly from template
- [ ] add option to select folder when uploading the image
- [ ] support both eZ 5.4.*, and eZPlatform with Legacy Bridge
- [ ] add support for Netgen Content Browser(*) - on roadmap for 1.x
- [ ] add support for Netgen Layouts (*) - on roadmap for 1.x

(*) Netgen Content Browser and Netgen Layouts are products of Netgen. This bundle will provide support for both if they are already installed and activated on the project.


## Copyright ## 

* Copyright (C) 2016 Keyteq. All rights reserved.
* Copyright (C) 2016 Netgen. All rights reserved.
