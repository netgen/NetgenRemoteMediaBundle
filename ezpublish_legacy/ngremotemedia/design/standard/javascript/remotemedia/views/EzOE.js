RemoteMedia.views.EzOE = Backbone.View.extend({
    tinymceEditor: null,
    bookmark: null,
    selectedContent: null,
    editorAttributes: {},

    initialize: function(options) {
        options = (options || {});

        this.attribute_id = this.$('[name="ContentObjectAttribute_id[]"]').val(),
        this.version = RemoteMediaShared.config().version;

        console.log(this.attribute_id, this.version);

        if (_(options).has('tinymceEditor')) {
            this.tinymceEditor = options.tinymceEditor;
            this.bookmark = this.tinymceEditor.selection.getBookmark();
            this.selectedContent = $(this.tinymceEditor.selection.getContent());
        }

        _.bindAll(this);

        // var prefix = (eZExceed && _(eZExceed).has('urlPrefix')) ? '/' + eZExceed.urlPrefix : '';
        // prefix = prefix + '/ezjscore/call';


        this.model = new RemoteMedia.models.Attribute({
            id: 'bla',
            version: this.version
        });

        this.listenTo(this.model, 'version.create', this.updateEditor);

        // Preselected image. Show scaler with selected crop
        if (this.is_remotemedia_selected()) {
            this.editorAttributes = this.parse_custom_attributes(this.selectedContent.attr('customattributes'));
            this.model.get('media').set({id: this.editorAttributes.media_id});
            this.scaler();
        } else {
            this.setup_admin_browser();
        }

        return this;
    },

    parse_custom_attributes: function(customAttributes){
        var attributes = {}, tmpArr;
        _(customAttributes.split('attribute_separation')).each(function(value) {
            tmpArr = value.split('|');
            attributes[tmpArr[0]] = tmpArr[1];
        });
        return attributes;
    },


    setup_browser: function(){
        var options = {
            model: this.model,
            collection: this.model.medias,
            onSelect: this.changeMedia
        };

        // var headingOptions = {
        //     icon: '/extension/ezexceed/design/ezexceed/images/kp/32x32/Pictures.png',
        //     name: 'Select media',
        //     quotes: true
        // };


        // this.browser = eZExceed.stack.push(RemoteMedia.views.Browser, options, {
        //     headingOptions: headingOptions
        // });

        this.browser = new RemoteMedia.views.Browser(options);
        this.browser.on('destruct', this.showScaler);


        
        this.model.medias.search('');
        return this;
    },


    setup_admin_browser: function() {
        console.log('setup_admin_browser');
        var modal = new RemoteMedia.views.Modal().insert().render();

        this.view = new RemoteMedia.views.Browser({
            model: this.model,
            collection: this.model.medias,
            onSelect: function(model){ //also used just for close on upload
                modal.close();
                model && this.changeMedia(model);
            }.bind(this),
            el: modal.show().contentEl
        }).render();

        this.model.medias.search(''); //Fetch

    },    


    is_remotemedia_selected: function(){
      return this.selectedContent && this.selectedContent.is('img') && this.selectedContent.hasClass('remotemedia');
    },

    // changeMedia: function(params) {
    //     this.media = params;
    //     eZExceed.stack.pop();
    // },


    //Admin
    changeMedia: function(new_media){
        this.model.get('media').set({id: new_media.id}); //Update id
        this.scaler();
        return this;
    },    

    showScaler: function() {
        var media = this.model.get('media');
        var model = this.model;
        var editorAttributes = this.editorAttributes;
        console.log('editorAttributes', editorAttributes);
        var self = this;
        // Show the editor

        

        var versions = model.get('toScale');

        // var options = {
        //     model: model,
        //     media: media,
        //     trueSize: [media.get('file').width, media.get('file').height],
        //     className: 'remotemedia-scaler',
        //     singleVersion: true,
        //     editorAttributes: editorAttributes
        // };


        var options = {
            model : this.model,
            trueSize : [media.get('file').width, media.get('file').height],
            className : 'remotemedia-scaler',
            singleVersion : true,
            editorAttributes : editorAttributes,
            selectedVersion : (_(this.editorAttributes).has('version')) ? this.editorAttributes.version : null
        };

        if (editorAttributes && editorAttributes.version) {

                var currentVersion = _(versions).find(function(value) {
                    return value.name == editorAttributes.version;
                });

                if (currentVersion) {
                    options.selectedVersion = editorAttributes.version;

                    if (editorAttributes.x1 && editorAttributes.y1 && editorAttributes.x2 && editorAttributes.y2) {
                        currentVersion.coords = [editorAttributes.x1, editorAttributes.y1, editorAttributes.x2, editorAttributes.y2];
                    }
                }

        }

        // var headingOptions = {
        //     name: 'Select crop',
        //     icon: '/extension/ezexceed/design/ezexceed/images/kp/32x32/Pictures-alt-2b.png',
        //     quotes: true
        // };

        // eZExceed.stack.push(RemoteMedia.views.Scaler, options, {
        //     headingOptions: headingOptions
        // });

        // new RemoteMedia.views.Scaler(options);

        self.scaler(options);

    




        // this.model.media(this.media.model, ['ezoe', this.media.id]);
    },


    // Open a scaling gui
    scaler: function() {
        var modal = new RemoteMedia.views.Modal().insert().render(),
            scaler_view,
            media = this.model.get('media');

        media.fetch({
            transform: false,
            data: {
                user_id: RemoteMediaShared.config().user_id
            }
        }).done(function(){
            this.model.set({available_versions: media.get('available_versions')});

            scaler_view = new RemoteMedia.views.Scaler({
                el: modal.show().contentEl,
                model : this.model,
                trueSize : media.get('true_size'),
                className : 'remotemedia-scaler',
                singleVersion : true,
                editorAttributes : this.editorAttributes,
                selectedVersion : (_(this.editorAttributes).has('version')) ? this.editorAttributes.version : null
            }).render();

        }.bind(this));


        modal.on('close', function(){
            // scaler_view.trigger('destruct');
            // scaler_view.trigger('stack.popped');
            // 
            this.model.trigger('version.create', [], this.model.get('available_versions')[0] ); //emulate
        }.bind(this));        

    },


    updateEditor: function(versions, data) {
        var media = this.model.get('media');
        console.log(media);
        var values = this.editorAttributes;

        values = _(values).extend({
            media_id: media.id,
            remotemedia_id: media.get('id'),
            version: data.name,
            image_width: data.size[0],
            image_height: data.size[1],
            image_url: media.get('url')
        });
        if (data.coords) {
            values.x1 = data.coords[0];
            values.y1 = data.coords[1];
            values.x2 = data.coords[2];
            values.y2 = data.coords[3];
        }
        var customAttributes = _(values).map(function(value, key) {
            return key + '|' + value;
        });
        var customAttributesString = customAttributes.join('attribute_separation');

        var imgAttribute = {
            src: values.image_url,
            customattributes: customAttributesString
        };
        this.updateTinyMCE(imgAttribute);
    },

    updateTinyMCE: function(attributes) {
        var ed = this.tinymceEditor,
            args = {
                src: '',
                alt: '',
                style: '',
                'class': '',
                width: '',
                height: '',
                onmouseover: '',
                onmouseout: '',
                type: 'custom'
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
        else {
            ed.execCommand('mceInsertContent', false, '<img id="__mce_tmp" />', {
                skip_undo: 1
            });
            ed.dom.setAttribs('__mce_tmp', args);
            ed.dom.setAttrib('__mce_tmp', 'id', '');
            ed.undoManager.add();
        }
        
        // Trigger eZExceed autosave
        ed.execCommand('mceRepaint');
        ed.save();
        $(ed.getElement()).trigger('focusout');
        ed.getWin().focus();
    }
});