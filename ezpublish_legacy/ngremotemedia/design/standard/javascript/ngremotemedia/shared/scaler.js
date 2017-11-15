window.NgRemoteMediaShared || (window.NgRemoteMediaShared = {});

window.NgRemoteMediaShared.scaler = function(ScaledVersion, $){
  return {
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

            selectedVersion: null,

            hasSelection: false,

            editorAttributes: false,
            className: 'ngremotemedia-scaler',

            versionSaved: null,

            initialize: function(options) {
                options = (options || {});
                _.bindAll(this);
                _.extend(this, _.pick(options, ['singleVersion', 'editorAttributes', 'selectedVersion']));
                this.versionSaved = null;


                // Model is an instance of Attribute
                this.model.on('scale', this.render, this);

                // this.versions = this.model.variations;
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

                var content = JST.scaler({
                    singleVersion: this.singleVersion,
                    media: this.model.thumb(this.SIZE.w, this.SIZE.h)
                });
                this.$el.append(content);


                this.render_editor_elements();

                var versionElements = this.model.variations.map(function(version, name) {
                    return new ScaledVersion({
                        model: version
                    }).render().el;
                });

                this.$('ul.nav').html(versionElements);


                if (this.selectedVersion) {
                    this.$('ul.nav li[version_name="'+this.selectedVersion.toLowerCase()+'"]').click();
                } else {
                    this.$('ul.nav li:first-child a').click();
                }

                return this;
            },


            render_editor_elements: function(){
                if(!this.editorAttributes){return;}

                var classes = this.model.get('class_list'),
                    selectedClass = this.editorAttributes.cssclass || false;

                if (classes) {
                    classes = _(classes).map(function(value) {
                        var s = value.split('|');
                        return {
                            value: s[0],
                            name: s[1],
                            selected: s[0] == selectedClass
                        };
                    });
                }

                this.$('.customattributes').html(
                    JST.scalerattributes({
                        classes: classes,
                        alttext: this.editorAttributes.alttext
                    })
                );
            },



            // storeVersion: function(selection, model) {
            //     // Must store scale coords back onto object
            //     // var coords = [selection.x, selection.y, selection.x2, selection.y2];

            //     this.trigger('save');
            //     model.save(selection);

            //     // var method = this.singleVersion ? 'generate' : 'save_variation';
            //     // this.model[method](scale.name, coords).success(this.versionCreated);
            // },

            // versionCreated: function(data) {
            //     data.content && (data = data.content);

            //     this.model.get('media').set('generated_url', data.url);

            //     var current_version = _.find(this.versions, function(v) { return v.name  === data.name; });
            //     current_version && (current_version.coords = data.coords);

            //     this.versionSaved = data;

            //     if (this.singleVersion){ //For online editor
            //         this.finishScaler();
            //     }else {
            //         this.model.trigger('version.create', this.versions, this.versionSaved);
            //         this.trigger('saved');
            //     }
            // },

            saveAll: function(){
                return this.model.save_variations().done(function(){
                    this.trigger('saved');
                }.bind(this));
            },

            generate: function(){
                var variation = this.current.data('model');
                return variation.generate_image().done(function(data){
                    console.log(data);
                    console.log(arguments);
                    console.log(variation);
                    this.model.set('generated_url', data.url);
                    this.model.trigger('generated');
                    this.trigger('saved');
                }.bind(this));
            },

            set_editor_attributes: function(){
                if (!this.editorAttributes){ return; }
                var self = this;
                this.$('.customattributes :input').each(function(){
                    self.editorAttributes[this.name] = $(this).val();
                });
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

                var not_initial = false;

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
                        not_initial = model.set($.extend({crop_changed: true}, selection));
                        not_initial = true;
                    }, 75)
                };

                this.$('.image-wrap > img').Jcrop(cropperOptions, function(){
                    context.cropper = this;
                });

            },


            // stackPopped: function() {
            //     this.finishScaler();
            // },


            // // Checks if both stack animation is finished and version saved to server before adding to tinyMCE
            // finishScaler: function() {
            //     if (this.versionSaved) {
            //          // Must be wrapped in an timeout function to prevent FireFox from replacing all content instead of addding image to selected content
            //         _.delay(function() {
            //             this.model.trigger('version.create', this.versions, this.versionSaved);
            //             this.trigger('saved');
            //         }.bind(this), 0);
            //     }
            // },

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

  };
};
