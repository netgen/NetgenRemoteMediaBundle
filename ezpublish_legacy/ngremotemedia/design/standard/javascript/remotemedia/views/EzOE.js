RemoteMedia.views.EzOE = Backbone.View.extend({
    attributeEl : null,
    tinymceEditor : null,
    bookmark : null,
    selectedContent : null,
    editorAttributes : {},

    initialize : function(options)
    {
        options = (options || {});

        if (_(options).has('textEl'))
            this.attributeEl = $(options.textEl).closest('.attribute');
        if (_(options).has('tinymceEditor')) {
            this.tinymceEditor = options.tinymceEditor;
            this.bookmark = this.tinymceEditor.selection.getBookmark();
            this.selectedContent = $(this.tinymceEditor.selection.getContent());
        }

        _.bindAll(this);

        var prefix = (eZExceed && _(eZExceed).has('urlPrefix')) ? '/' + eZExceed.urlPrefix : '';
        prefix = prefix + '/ezjscore/call';

        /**
         * TODO: The attributeEl is eZExceed spesific so this won't work in vanilla version
         */
        this.model = new RemoteMedia.models.Attribute({
            id : this.attributeEl.data('id'),
            version : this.attributeEl.data('version'),
            prefix : prefix
        });
        this.model.bind('version.create', this.updateEditor);

        if (this.selectedContent && this.selectedContent.is('img') && this.selectedContent.hasClass('remotemedia'))
        {
            /**
             * Preselected image. Show scaler with selected crop
             */
            var customAttributes = this.selectedContent.attr('customattributes');
            var attributes = {},
                tmpArr;
            _(customAttributes.split('attribute_separation')).each(function(value){
                tmpArr = value.split('|');
                attributes[tmpArr[0]] = tmpArr[1];
            });
            this.editorAttributes = attributes;
            var options = {
                id : attributes.mediaId,
                remotemediaId : attributes.remotemediaId,
                model : new RemoteMedia.models.Media()
            };
            this.media = options;
            this.showScaler();
        }
        else
        {
            var options = {
                model : this.model,
                collection : this.model.medias,
                onSelect : this.changeMedia
            };
            var headingOptions =
            {
                icon : '/extension/ezexceed/design/ezexceed/images/kp/32x32/Pictures.png',
                name : 'Select media',
                quotes : true
            };
            this.model.medias.search('');

            this.browser = eZExceed.stack.push(RemoteMedia.views.Browser, options, {headingOptions : headingOptions});
            this.browser.on('destruct', this.showScaler);
        }

        return this;
    },

    changeMedia : function(params)
    {
        this.media = params;
        eZExceed.stack.pop();
    },

    showScaler : function()
    {
        if (!this.media)
            return;
        /**
         * Show the editor
         */
        var _this = this;
        this.media.model.on('change', function(response)
        {
            /**
             * Remove the change event
             */
            _this.media.model.off('change', this);
            var media = _this.media.model;
            var versions = _this.model.get('toScale');

            var options = {
                model : _this.model,
                media : media,
                versions : versions,
                trueSize : [media.get('file').width, media.get('file').height],
                className : 'remotemedia-scaler',
                singleVersion : true,
                editorAttributes : _this.editorAttributes
            };

            if (_this.editorAttributes)
            {
                var attr = _this.editorAttributes;

                if (_(attr).has('version'))
                {
                    var currentVersion = _(versions).find(function(value){
                        return value.name == attr.version;
                    });

                    if (currentVersion) {
                        options.selectedVersion = attr.version;
                        var attrUnderscore = _(attr);

                        if (attrUnderscore.has('x1') && attrUnderscore.has('y1')
                            && attrUnderscore.has('x2') && attrUnderscore.has('y2'))
                        {
                            currentVersion.coords = [attr.x1, attr.y1, attr.x2, attr.y2];
                        }
                    }
                }
            }

            var headingOptions =
            {
                name : 'Select crop',
                icon : '/extension/ezexceed/design/ezexceed/images/kp/32x32/Pictures-alt-2b.png',
                quotes : true
            };

            eZExceed.stack.push(RemoteMedia.views.Scaler, options, {headingOptions : headingOptions});
        });

        this.model.media(this.media.model, ['ezoe', this.media.id]);
    },

    updateEditor : function(data)
    {
        var media = this.media.model;
        var values = this.editorAttributes;

        values = _(values).extend({
            media_id : media.id,
            remotemedia_id : media.get('remotemediaId'),
            version : data.name,
            image_width : data.size[0],
            image_height : data.size[1],
            image_url : '//' + media.get('host') + data.url + '.' + media.get('scalesTo').ending
        });
        if (data.coords)
        {
            values.x1 = data.coords[0];
            values.y1 = data.coords[1];
            values.x2 = data.coords[2];
            values.y2 = data.coords[3];
        }
        var customAttributes = _(values).map(function(value, key){
            return key + '|' + value;
        });
        var customAttributesString = customAttributes.join('attribute_separation');

        var imgAttribute = {
            src : values.image_url,
            customattributes : customAttributesString
        }
        this.updateTinyMCE(imgAttribute);
    },

    updateTinyMCE : function(attributes)
    {
        var ed = this.tinymceEditor,
            args = {
                src : '',
                alt : '',
                style : '',
                'class' : '',
                width : '',
                height : '',
                onmouseover : '',
                onmouseout : '',
                type : 'custom'
            };

        _(args).extend(attributes);

        if (args.class.length)
            args.class += ' ';
        args.class = args.class.concat('ezoeItemCustomTag remotemedia');

        // Fixes crash in Safari
        if (tinymce.isWebKit)
            ed.getWin().focus();

        if (this.bookmark)
            ed.selection.moveToBookmark(this.bookmark);

        var el = ed.selection.getNode();

        if (el && el.nodeName == 'IMG')
            ed.dom.setAttribs(el, args);
        else
        {
            ed.execCommand('mceInsertContent', false, '<img id="__mce_tmp" />', {skip_undo : 1});
            ed.dom.setAttribs('__mce_tmp', args);
            ed.dom.setAttrib('__mce_tmp', 'id', '');
            ed.undoManager.add();
        }
        /**
         * Trigger eZExceed autosave
         */
        ed.execCommand('mceRepaint');
        ed.save();
        $(ed.getElement()).trigger('focusout');
        ed.getWin().focus();
    }
});

