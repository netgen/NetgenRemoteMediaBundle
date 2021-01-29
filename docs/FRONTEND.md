# Frontend development

- [Introduction](#introduction)
- [Setup](#setup)
- [Development](#development)
- [Building the project](#building-the-project)
- [Development build](#development-build)
- [Project structure](#project-structure)

## Introduction

Fontend is developed in [Vue.js](https://vuejs.org/v2/guide/) with [runtime compiler](https://vuejs.org/v2/guide/installation.html#Runtime-Compiler-vs-Runtime-only) included. Vue instance is mounted to DOM element in which the admin form for remote media field is rendered. Other views are rendered as [Vue components](https://vuejs.org/v2/guide/components.html), more specifically as [single file components](https://vuejs.org/v2/guide/single-file-components.html).

## Setup

All source code is in frontend directory in the root of the repo:

```bash
$ cd frontend
```

JS dependencies must be installed:

```bash
$ npm install
```

## Development

For development, it is best to use webpack dev server, which can be started with:

```bash
$ npm run serve
```

Template for dev server is in [frontend/public/index.html](frontend/public/index.html), where it could be changed when needed. If it is changed, don't forget to change admin templates, also:
[bundle/Resources/views/ezadminui/field/edit/ngremotemedia.html.twig](bundle/Resources/views/ezadminui/field/edit/ngremotemedia.html.twig)
[bundle/ezpublish_legacy/ngremotemedia/design/standard/templates/content/datatype/edit/ngremotemedia.tpl](bundle/ezpublish_legacy/ngremotemedia/design/standard/templates/content/datatype/edit/ngremotemedia.tpl)

When using dev server API calls are proxied to some media site, which is configured in [frontend/vue.config.js](frontend/vue.config.js). Because API is not on a public URL, developer needs to login to selected media site instance and copy eZSESSID cookie to proxy configuration file.

## Building the project

Built production assets are commited to repo.

You need to be positioned in frontend directory:

```bash
$ cd frontend
```

Assets are built with:

```bash
$ npm run build
```

Bundled JS and CSS files are in [frontend/dist](frontend/dist), but are also copied to two different directories for ngadminui and ez admin v2, respectively.

Files are copied with frontend/copyFiles.js node script which can be started independently with:

```bash
$ node copyFiles.js
```

## Development build

Assets can also be built in dev mode (non uglified and non minified). This is not normally needed, but is usefull in some legacy eZ applications where assets are handled with assetic and handling fails because uglifyjs cannot process production build.

You need to be positioned in frontend directory:

```bash
$ cd frontend
```

Assets are built with:

```bash
$ npm run dev
```

Bundled JS file is in [frontend/dist](frontend/dist), but is also copied to two different directories for ngadminui and ez admin v2, respectively.

File is copied with frontend/copyFilesDev.js node script which can be started independently with:

```bash
$ node copyFilesDev.js
```

This script fakes additional files (normally created with production build).


## Project structure

```bash
frontend
│   .browserslistrc   # supported browsers for babel
│   .eslintrc         # eslint configuration
│   babel.config.js   # babel configuration
│   copyFiles.js      # script for copying built assets to bundle
│   package.json      # defines npm package
│   postcss.config.js # postcss config
│   vue.config.js     # vue config
│
└───src               # project source
│   └───components    # one-file vue components
│   │   │   ...
│   │
│   └───constants     # files that define various constants
│   │   │   ...
│   │
│   └───scss          # global SCSS rules
│   │   │   ...
│   │
│   └───utility       # various utility functions
│   │   │   ...
│   │
│   │   main.js       # App entry point that create vue instance
│                     # and mounts it to the DOM element.
│                     # Also represents top level component 
│                     # which mounts to DOM template.
└───public
│   │   index.html    # defines DOM template used with webpack
│                     # dev server
│
└───dist              # built assets

```
