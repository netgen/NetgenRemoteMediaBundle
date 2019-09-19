# Frontend development

- [Introduction](#introduction)
- [Setup](#setup)
- [Building the project](#building-the-project)
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

For development, it is best to use webpack dev server, which can be started with:

```bash
$ npm run serve
```

Template for dev server is in **_frontend/public/index.html_**, where it could be changed when needed.

## Building the project

You need to be positioned in frontend directory:

```bash
$ cd frontend
```

Assets are built with:

```bash
$ npm run build
```

Bundled JS and CSS files are in **_frontend/dist_**, but are also copied to two different directories for ngadminui and ez admin v2, respectively.

Files are copied with frontend/copyFiles.js node script which can be started like:

```bash
$ node copyFiles.js
```

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
│   │   main.js       # app entry point that create vue instance
│                     # and mounts it to the DOM element
└───public
│   │   index.html    # defines DOM template used with webpack
│                     # dev server
│
└───dist              # built assets

```
