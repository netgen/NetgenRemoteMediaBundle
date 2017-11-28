# Netgen Remote Media Bundle changelog

##1.0

##1.0.11-alpha
###Fixed
* fixed `load more` functionality when searching for remote image

##1.0.10-alpha
###Added
* add `toString` and `fromString` methods to legacy datatype implementation

##1.0.9-alpha
###Added
* option to use subdomains on Cloudinary

##1.0.8-alpha
###Fixed
* fix listing of images when browsing in administration
* bugfix on videos in legacy
###Changed
* require latest 1.x version of Cloudinary API
* better configuration for embedded images

##1.0.7-alpha
###Fixed
* field template configuration

##1.0.6-alpha
###Changed
* wrap inline image together with caption for easier styling

##1.0.5-alpha
###Added
* add caption to images embedded in ezxml text

##1.0.4-alpha
###Fixed
* make sure configuration is merged correctly, and the values are not overwritten

##1.0.3-alpha
###Added
* add migration command
* test for existance of array offset before testing it's value

##1.0.2-alpha
###Fixed
* enable overwritting of the media on cloudinary.

##1.0.1-alpha
###Added
* CS fixer and travis configuration
* support for 'invalidate' option on media upload

###Fixed
* bugfix when no default configuration has been set

##1.0-alpha
* Initial release
