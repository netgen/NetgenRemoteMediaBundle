window.RemoteMediaShared || (window.RemoteMediaShared = {});

window.RemoteMediaShared.ezoe = function($, Attribute, BrowserView, ScalerView) {
  return {

    tinymceEditor: null,
    bookmark: null,
    selectedContent: null,
    editorAttributes: {},

    initialize: function(options) {
        options = (options || {});

        if (_(options).has('tinymceEditor')) {
            this.tinymceEditor = options.tinymceEditor;
            this.bookmark = this.tinymceEditor.selection.getBookmark();
            this.selectedContent = $(this.tinymceEditor.selection.getContent());
        }

        _.bindAll(this);

        var id = RemoteMediaShared.config().is_admin ? this.$('[name="ContentObjectAttribute_id[]"]').val() : this.$el.closest('.attribute').data('id');

        this.model = new Attribute({
            id: id,
            version: RemoteMediaShared.config().version,
            ezoe: true
        });

        this.listenTo(this.model, 'version.create', this.updateEditor);

        // Preselected image. Show scaler with selected crop
        if (this.is_remotemedia_selected()) {
            this.editorAttributes = this.parse_custom_attributes(this.selectedContent.attr('customattributes'));
            this.model.get('media').set({id: this.editorAttributes.resourceId});
            this.scaler();
        } else {
            this.browser();
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
        return attributes;
    },


   browser: function() {
        var options = {
            model: this.model,
            collection: this.model.medias
        };

        var context = {
            icon: '/extension/ezexceed/design/ezexceed/images/kp/32x32/Pictures.png',
            heading: 'Select media',
            render: true
        };
        eZExceed.stack.push(
            BrowserView,
            options,
            context
        ).on('destruct', this.changeMedia);
        this.model.medias.search(''); //Fetch
    },


    changeMedia: function(new_media){
        var media = this.model.get('media');
        if(new_media.new_image_selected){
            media.set({id: new_media.id}); //Update id
            this.scaler();
        }
        return this;
    },    

    is_remotemedia_selected: function(){
      return this.selectedContent && this.selectedContent.is('img') && this.selectedContent.hasClass('ngremotemedia');
    },
 


    // Open a scaling gui
    scaler: function() {
        var media = this.model.get('media'),
            ea = this.editorAttributes,
            editorToScale = ea.coords ? [{
                name: ea.version,
                coords: ea.coords
            }] : [];           

        var view_options = {
            model : this.model,
            className : 'ngremotemedia-scaler',
            singleVersion : true,
            editorAttributes : ea,
            selectedVersion : ea.version
        };


        media.fetch({
            transform: false,
            data: {
                resourceId: media.id
            }
        }).done(function(){
            this.editorAttributes.alttext = this.editorAttributes.alttext || media.get('file').alt_text;
            this.model.set({
                toScale: editorToScale,
                available_versions: media.get('available_versions')
            });
            
            this.render_scaler_view(view_options);

        }.bind(this));

    },


    render_scaler_view: function(options){
      this.render_scaler_view_in_stack(options);
    },


    render_scaler_view_in_modal: function(options){
        var modal = new NgRemoteMedia.views.Modal().insert().render();
        _.extend(options, {el:modal.show().contentEl });
        var scaler_view = new ScalerView(options).render();

        modal.on('close', function(){
            scaler_view.trigger('destruct');
            scaler_view.trigger('stack.popped');
        }); 
    },


    render_scaler_view_in_stack: function(options){
        var context = {
            icon: '/extension/ezexceed/design/ezexceed/images/kp/32x32/Pictures-alt-2b.png',
            className: 'dark',
            heading: 'Select crops',
            render: true
        };
        
        eZExceed.stack.push(
            ScalerView,
            options,
            context
        );    
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
            el = ed.selection.getNode(),
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
        args['class'] += ' ezoeItemCustomTag ngremotemedia';

        // Fixes crash in Safari
        tinymce.isWebKit && ed.getWin().focus();
        this.bookmark && ed.selection.moveToBookmark(this.bookmark);

        if (el && el.nodeName == 'IMG'){
            ed.dom.setAttribs(el, args);
        }else {
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

  };
};