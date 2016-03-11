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
            poppedFromStack: null,

            initialize: function(options) {
                options = (options || {});
                _.bindAll(this);
                _.extend(this, _.pick(options, ['singleVersion', 'editorAttributes', 'selectedVersion']));
                this.versionSaved = null;
                this.poppedFromStack = null;
                
                this.trueSize = this.model.get('media').get('true_size');

                // Model is an instance of Attribute
                this.model.on('scale', this.render, this);


                // When I get popped from stack
                // i save my current scale
                this.on('destruct', this.saveCrop, this);
                this.on('stack.popped', this.stackPopped, this);

                this.versions = this.model.combined_versions();
            },

            events: {
                'click .nav li': 'changeScale',
            },


            fitTo: function(w, h, maxWidth, maxHeight) {
                if(w<maxWidth && h < maxHeight){return {w: w, h: h};}
                var ratio = Math.min(maxWidth / w, maxHeight / h);
                return { w: Math.floor(w*ratio), h: Math.floor(h*ratio) };
            },

            updateScalerSize: function(media){
                this.SIZE = this.fitTo(media.get('file').width, media.get('file').height, this.$el.width(), this.$el.height() - 100);
                return this;
            },

            render: function() {
                var media = this.model.get('media');

                this.updateScalerSize(media);

                var content = JST.scaler({
                    media: media.thumb(this.SIZE.w, this.SIZE.h)
                });
                this.$el.append(content);


                this.render_editor_elements();

                var versionElements = _(this.versions).map(function(version) {
                    return new ScaledVersion({
                        model: version,
                        media: media,
                        className: (version.coords ? 'cropped' : 'uncropped')
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

                var classes = this.model.get('media').get('class_list'),
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



            storeVersion: function(selection, scale) {
                // Must store scale coords back onto object
                var coords = [selection.x, selection.y, selection.x2, selection.y2];

                this.trigger('save');

                var method = this.singleVersion ? 'generate' : 'save_version';
                this.model[method](scale.name, coords).success(this.versionCreated);
            },

            versionCreated: function(data) {
                data.content && (data = data.content);

                this.model.get('media').set('generated_url', data.url);

                var current_version = _.find(this.versions, function(v) { return v.name  === data.name; });
                current_version && (current_version.coords = data.coords);

                this.versionSaved = data;

                if (this.singleVersion){ //For online editor
                    this.finishScaler();
                }else {
                    this.model.trigger('version.create', this.versions, this.versionSaved);
                    this.trigger('saved');
                }
            },

            saveCrop: function() {
                if (!this.current){return;}

                this.set_editor_attributes();

                var scale = this.current.data('scale');

                if (this.cropper && scale) {
                    var selection = this.cropper.tellSelect();

                    if (!this.hasSelection) {
                        selection.x = 0;
                        selection.y = 0;
                        selection.x2 = this.trueSize[0];
                        selection.y2 = this.trueSize[1];
                        if (!parseInt(scale.size[0], 10))
                            scale.size[0] = this.trueSize[0];
                        if (!parseInt(scale.size[1], 10))
                            scale.size[1] = this.trueSize[0];
                    }
                    

                    this.storeVersion(selection, scale);
                    this.current.removeClass('uncropped').addClass('cropped');
                }
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
                    if (!this.singleVersion){ this.saveCrop();}
                    this.current.removeClass('active');
                }

                this.cropper && this.cropper.destroy();

                this.current = $(e.currentTarget).addClass('active');
                var scale = this.current.data('scale');

                if (typeof scale === 'undefined' || scale.toSmall){return this;}

                var coords = scale.coords || [0, 0, this.SIZE.w/2, this.SIZE.h/2], //Defaults are for fresh upload
                    ratio = (scale.size[0] / scale.size[1]),
                    context = this;

                // If an API exists we dont need to build Jcrop
                // but can just change crop
                var cropperOptions = {
                    setSelect: coords,
                    aspectRatio: scale.unbounded ? null : ratio,
                    minSize: scale.size,
                    // Make sure user can't remove selection if width and height has bounded dimension
                    // if it has ratio than it has bounded dimension
                    allowSelect: scale.unbounded,
                    trueSize: this.trueSize,
                    onSelect:  function() { context.hasSelection = true; },
                    onRelease: function() { context.hasSelection = false; }                    
                };

                this.$('.image-wrap > img').Jcrop(cropperOptions, function(){
                    context.cropper = this;
                });

            },


            stackPopped: function() {
                this.poppedFromStack = true;
                this.finishScaler();
            },

            
            // Checks if both stack animation is finished and version saved to server before adding to tinyMCE            
            finishScaler: function() {
                if (this.versionSaved && this.poppedFromStack) {
                    /**
                     * Must be wrapped in an timeout function to prevent FireFox from
                     * replacing all content instead of addding image to selected content
                     */
                    _.delay(function() {
                        this.model.trigger('version.create', this.versions, this.versionSaved);
                        this.trigger('saved');
                    }.bind(this), 0);
                }
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
        
  };
};