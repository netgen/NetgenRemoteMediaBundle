#!/bin/sh
JSDIR=ezpublish_legacy/ngremotemedia/design/standard/javascript

node_modules/.bin/handlebars $JSDIR/templates \
--extension hbs \
--namespace window.NgRemoteMedia.JST \
--output $JSDIR/ngremotemedia/templates.js
