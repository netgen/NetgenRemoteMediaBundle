# Netgen Remote Media Bundle

[![Build Status](https://img.shields.io/travis/netgen/NetgenRemoteMediaBundle.svg?style=flat-square)](https://travis-ci.org/netgen/NetgenRemoteMediaBundle)
[![Code Coverage](https://img.shields.io/codecov/c/github/netgen/NetgenRemoteMediaBundle.svg?style=flat-square)](https://codecov.io/gh/netgen/NetgenRemoteMediaBundle)
[![Downloads](https://img.shields.io/packagist/dt/netgen/remote-media-bundle.svg?style=flat-square)](https://packagist.org/packages/netgen/remote-media-bundle)
[![Latest stable](https://img.shields.io/packagist/v/netgen/remote-media-bundle.svg?style=flat-square)](https://packagist.org/packages/netgen/remote-media-bundle)
[![License](https://img.shields.io/github/license/netgen/NetgenRemoteMediaBundle.svg?style=flat-square)](LICENCE)

Netgen Remote Media Bundle is a bundle providing support for remote resource providers, primarily [Cloudinary](http://cloudinary.com/).

This repository contains an API for remote resource providers and currently an implementation for Cloudinary (a wrapper above Cloudinary PHP library). It also contains a Vue.js app for managing resources. 

## Features

- API for remote resources (only Cloudinary supported at the moment)
- support for images, videos, and documents upload
- images cropping editor
- Vue.js app for managing resources

## Licence and installation instructions

[Licence](LICENCE)

[Installation instructions](docs/INSTALL.md)

[Upgrade instructions](docs/UPGRADE.md)

## Documentation

For usage documentation see [USAGE.md](docs/USAGE.md)

## Contributing

For frontend development see [FRONTEND.md](docs/frontend.md)

### Unit tests

Run the unit tests by calling `composer test` from the repo root:

```
$ composer test
```

### Coding standards

This repo uses PHP CS Fixer and rules defined in `.php-cs-fixer.php` file to enforce coding
standards. Please check the code for any CS violations before submitting patches:

```
$ php-cs-fixer fix
```

## Copyright

- Copyright (C) 2016 Keyteq. All rights reserved.
- Copyright (C) 2016 Netgen. All rights reserved.
