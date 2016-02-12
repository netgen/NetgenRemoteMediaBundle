#!/bin/sh
JSDIR=ezpublish_legacy/ngremotemedia/design/standard/javascript

node_modules/.bin/handlebars $JSDIR/templates \
--extension hbs \
--namespace window.JST \
--output $JSDIR/remotemedia/shared/templates.js
