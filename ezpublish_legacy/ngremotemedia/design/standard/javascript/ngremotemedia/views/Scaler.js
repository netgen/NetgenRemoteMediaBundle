(function(){
    var ScaledVersion = NgRemoteMedia.views.ScaledVersion;
    var template = NgRemoteMedia.template;

    NgRemoteMedia.views.Scaler = Backbone.View.extend({
        // size of cropping media
        SIZE: {
            w: 830,
            h: 580
        },

        // Holds reference to current selected scale li
        current: null,

        // Will hold the Jcrop API
        cropper: null,

        singleVersion: false,

        hasSelection: false,

        setup_class_name: function(){
            var css_class = this.singleVersion ? 'ngrm-single' : 'ngrm-multiple';
            var classes = ['ngremotemedia-scaler', css_class]
            this.$el.addClass(classes.join(' '));
            return this;
        },

        initialize: function(options) {
            this.singleVersion = options.singleVersion;
            this.model.on('scale', this.render, this);
            this.setup_class_name();
        },

        events: {
            'click .js-save': 'saveAll',
            'click .js-generate': 'generate',
            'click .nav li': 'changeScale',
        },


        fitTo: function(w, h, maxWidth, maxHeight) {
            if(w<maxWidth && h < maxHeight){return {w: w, h: h};}
            var ratio = Math.min(maxWidth / w, maxHeight / h);
            return { w: Math.floor(w*ratio), h: Math.floor(h*ratio) };
        },

        updateScalerSize: function(media){
            this.SIZE = this.fitTo(media.originalWidth(), media.originalHeight(), this.$el.width(), this.$el.height() - 100);
            return this;
        },

        render: function() {
            this.updateScalerSize(this.model);

            var content = template('scaler', {
                singleVersion: this.singleVersion,
                media: this.model.thumb(this.SIZE.w, this.SIZE.h)
            });
            this.$el.append(content);

            this.render_editor_elements();

            var versionElements = this.model.variations.map(function(variation, name) {
                return new ScaledVersion({ model: variation }).render().el;
            });

            this.$('ul.nav').html(versionElements);

            var selectedVersion = this.model.get('custom_attributes').version;

            if (selectedVersion) {
                this.$('ul.nav li[version_name="'+selectedVersion+'"]').click();
            } else {
                this.$('ul.nav li:first-child a').click();
            }

            return this;
        },


        render_editor_elements: function(){
            if(!this.singleVersion){return;}

            var class_list = this.model.get('class_list'),
                selectedClass = this.model.get('custom_attributes').cssclass,
                alttext = this.model.get('custom_attributes').alttext,
                css_class

            if (class_list) {
                css_class = _(class_list).find({value: selectedClass});
                css_class && (css_class.selected = true);
            }

            this.$('.customattributes').html(
                template('scalerattributes', {
                    classes: class_list,
                    alttext: alttext
                })
            );
        },


        saveAll: function(){
            return this.model.save_variations().done(function(){
                this.trigger('saved');
            }.bind(this));
        },


        generate: function(){
            var variation = this.current.data('model');

            this.update_custom_attributes();

            return variation.generate_image().done(function(data){
                variation.set('generated_url', data.url);
                this.model.trigger('generated', variation);
                this.trigger('saved');
            }.bind(this));
        },

        update_custom_attributes: function(){
            var custom_attributes = {};
            this.$('.customattributes :input').each(function(){
                custom_attributes[this.name] = $(this).val();
            });

            this.model.set({custom_attributes: custom_attributes})
        },

        changeScale: function(e) {
            e && e.preventDefault();

            if (this.current) {
                if (this.current.get(0) == e.currentTarget){ return; }
                this.current.removeClass('active');
            }

            this.cropper && this.cropper.destroy();
            this.current = $(e.currentTarget).addClass('active');

            var model = this.current.data('model');

            if (typeof model === 'undefined' || model.tooSmall()){return this;}

            var context = this;
            model.not_initial = false;
            model.set({crop_changed: false}, {silent: true});

            // If an API exists we dont need to build Jcrop but can just change crop
            var cropperOptions = {
                setSelect: model.coords(),
                aspectRatio: model.aspectRatio(),
                minSize: model.minSize(),
                // Make sure user can't remove selection if width and height has bounded dimension
                // if it has ratio than it has bounded dimension
                allowSelect: model.unbounded(),
                trueSize: model.originalSize(),
                onSelect:  function() { context.hasSelection = true; },
                onRelease: function() { context.hasSelection = false; },
                onChange: _.debounce(function(selection){
                    model.set(selection);
                    model.not_initial && model.set({crop_changed: true});
                    model.not_initial = true;
                }, 75)
            };

            this.$('.image-wrap > img').Jcrop(cropperOptions, function(){
                context.cropper = this;
            });

        },


        close: function() {
            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
                this.current = null;
                this.delegateEvents([]);
                this.model.off('scale version.create');
                this.$el.html('');
            }
        }


    });

})();
