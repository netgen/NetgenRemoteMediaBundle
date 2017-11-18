(function(){
    var Attribute = NgRemoteMedia.models.Attribute,
        BrowserView = NgRemoteMedia.views.Browser,
        ScalerView = NgRemoteMedia.views.Scaler;

    NgRemoteMedia.views.EzOE = Backbone.View.extend({


        tinymceEditor: null,
        bookmark: null,
        selectedContent: null,

        initialize: function(options) {
            options = (options || {});

            if (_(options).has('tinymceEditor')) {
                this.tinymceEditor = options.tinymceEditor;
                this.bookmark = this.tinymceEditor.selection.getBookmark();
                this.selectedContent = $(this.tinymceEditor.selection.getContent());
            }

            _.bindAll(this);

            var attribute_data =  this.$el.closest('.attribute').data();
            var id = NgRemoteMediaShared.config().is_admin ? this.$('[name="ContentObjectAttribute_id[]"]').val() : attribute_data.id;
            var version = NgRemoteMediaShared.config().is_admin ? RemoteMediaSettings.ez_contentobject_version : attribute_data.version;

            var media_attributes, custom_attributes;

            if(this.is_remotemedia_selected()){
                custom_attributes = this.parse_custom_attributes(this.selectedContent.attr('customattributes'));
                media_attributes = { media: {
                        custom_attributes: custom_attributes
                    }
                }
            }

            this.model = new Attribute($.extend({id: id, version: version, ezoe: true }, media_attributes), {parse: true});
            this.listenTo(this.model.get('media'), 'generated', this.updateEditor);

            // Preselected image. Show scaler with selected crop
            if (media_attributes) {
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
            var modal = new NgRemoteMedia.views.Modal().insert().render();

            this.view = new NgRemoteMedia.views.Browser({
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
            var media = this.model.get('media');

            var view_options = {
                model : media,
                className : 'ngremotemedia-scaler',
                singleVersion : true
            };

            media.fetch({
                transform: false,
                data: {
                    resourceId: media.id
                }
            }).done(function(){
                this.render_scaler_view_in_modal(view_options);
            }.bind(this));

        },


        render_scaler_view_in_modal: function(options){

            var modal = this.modal = new NgRemoteMedia.views.Modal().insert().render();
            _.extend(options, {el:modal.show().contentEl });

            var scaler_view = new ScalerView(options).render();
            scaler_view.modal = modal;

            modal.on('close', function(){
                scaler_view.close();
            });
        },



        updateEditor: function(variation) {
            var media = this.model.get('media');

            var attributes = {
                resourceId: media.id,
                version: variation.get('name'),
                caption: media.get('file').caption,
                alttext: media.alt_text(),
                cssclass: media.css_class(),
                coords: variation.ezoe_coords(),
                image_url: variation.get('generated_url')
            };

            this.updateTinyMCE({
                src: attributes.image_url,
                customattributes: this.serialize_custom_attributes(attributes)
            });

            this.modal.close();
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

    });




})();
