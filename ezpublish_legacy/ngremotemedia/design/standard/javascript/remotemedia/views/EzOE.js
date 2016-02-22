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
            this.model.get('media').set({id: this.editorAttributes.resourceId});
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

        attributes.coords = _.map(attributes.coords.split(','), function(n){ return parseInt(n, 10); });
        console.log('parse_custom_attributes', attributes);
        return attributes;
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

            var ea = this.editorAttributes;
            var editorToScale = ea.coords ? [{
                name: ea.version,
                coords: ea.coords
            }] : [];

            console.log("88888888888888", editorToScale, ea);

            this.model.set({
                toScale: editorToScale,
                available_versions: media.get('available_versions')
            });

            scaler_view = new RemoteMedia.views.Scaler({
                el: modal.show().contentEl,
                model : this.model,
                trueSize : media.get('true_size'),
                className : 'remotemedia-scaler',
                singleVersion : true,
                editorAttributes : this.editorAttributes,
                selectedVersion : this.editorAttributes.version
            }).render();

        }.bind(this));


        modal.on('close', function(){
            scaler_view.trigger('destruct');
            scaler_view.trigger('stack.popped');
            // 
            // this.model.trigger('version.create', [], this.model.get('available_versions')[0] ); //emulate
        }.bind(this));        

    },


    updateEditor: function(versions, data) {
        var media = this.model.get('media');

        var attributes = {
            resourceId: media.id,
            version: data.name,
            alttext: this.editorAttributes.alttext,
            cssclass: this.editorAttributes.cssclass,
            coords: data.coords.join(','),
            image_url: media.get('generated_url')
        };
    
        this.updateTinyMCE({
            src: attributes.image_url,
            customattributes: this.serialize_custom_attributes(attributes)
        });
    },


    serialize_custom_attributes: function(attributes){
        return _(attributes).map(function(value, key) {
            return key + '|' + value;
        }).join('attribute_separation');
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