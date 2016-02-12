define(['remotemedia/view', './scaled_version', 'jquery-safe', 'jcrop'],
    function(View, ScaledVersion, $) {
        return View.extend({
            // size of cropping media
            SIZE: {
                w: 830,
                h: 580
            },

            // Holds reference to current selected scale li
            current: null,

            // Will hold the Jcrop API
            cropper: null,

            trueSize: [],

            singleVersion: false,

            selectedVersion: null,

            hasSelection: false,

            editorAttributes: false,
            className: 'remotemedia-scaler',

            versionSaved: null,
            poppedFromStack: null,

            initialize: function(options) {
                options = (options || {});
                _.bindAll(this);
                _.extend(this, _.pick(options, ['trueSize', 'singleVersion', 'editorAttributes', 'selectedVersion']));
                this.versionSaved = null;
                this.poppedFromStack = null;

                this.versions = this.model.combined_versions();

                // Model is an instance of Attribute
                this.model.on('scale', this.render, this);

                // When I get popped from stack
                // i save my current scale
                this.on('destruct', this.saveCrop, this);
                this.on('stack.popped', this.stackPopped, this);
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
                    media: media.thumb(this.SIZE.w, this.SIZE.h, 'jpg')
                });
                this.$el.append(content);


                var classes = this.model.get('classList');
                var viewModes = this.model.get('viewModes');

                if (classes || viewModes) {
                    var selectedClass = false;
                    var selectedView = false;
                    var viewsObj = false;
                    var alttext = '';
                    var tmpArr = [];

                    if (this.editorAttributes) {
                        alttext = (this.editorAttributes.alttext || '');
                        selectedClass = (this.editorAttributes.cssclass || false);
                        selectedView = (this.editorAttributes.viewmode || false);
                    }

                    if (classes) {
                        classes = _(classes).map(function(value) {
                            tmpArr = value.split('|');
                            return {
                                value: tmpArr[0],
                                name: _(tmpArr).last(),
                                selected: (tmpArr[0] == selectedClass)
                            };
                        });
                    }
                    if (viewModes) {
                        viewsObj = _(viewModes).map(function(value) {
                            tmpArr = value.split('|');
                            return {
                                value: tmpArr[0],
                                name: _(tmpArr).last(),
                                selected: (tmpArr[0] == selectedView)
                            };
                        });
                    }

                    this.$('.customattributes').html(
                        JST.scalerattributes({
                            classes: classes,
                            viewmodes: viewsObj,
                            alttext: alttext
                        })
                    );
                }

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



            storeVersion: function(selection, scale) {
                // Must store scale coords back onto object
                var coords = [selection.x, selection.y, selection.x2, selection.y2];
                
                if(!scale.size){alert("bum4");} //TODO: inspect when we don't have scale.size
                var size = scale.size; // || ([selection.w, selection.h]);

                this.trigger('save');
                this.model.addVanityUrl(scale.name, coords, size).success(this.versionCreated);
            },

            versionCreated: function(data) {
                data.content && (data = data.content);

                var current_version = _.find(this.versions, function(v) { return v.name  === data.name; });
                current_version && (current_version.coords = data.coords);

                this.versionSaved = data;
                if (this.singleVersion)
                    this.finishScaler();
                else {
                    this.model.trigger('version.create', this.versions, this.versionSaved);
                    this.trigger('saved');
                }
            },

            saveCrop: function() {
                if (!this.current)
                    return;
                /**
                 * Set editor attribute values if any
                 */
                if (this.editorAttributes) {
                    var _this = this;
                    var inputEl = this.$('.customattributes :input');
                    inputEl.each(function() {
                        var el = $(this);
                        _this.editorAttributes[el.attr('name')] = el.val();
                    });
                }
                var scale = this.current.data('scale');

                if (this.cropper && scale) {
                    var selection = this.cropper.tellSelect();

                    if (!this.hasSelection) {
                        alert("BUM 2");
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

            changeScale: function(e) {
                e && e.preventDefault();
                this.cropper && this.cropper.destroy();

                if (this.current) {
                    if (!this.singleVersion){ this.saveCrop();}
                    if (e && this.current.get(0) == e.currentTarget){ return; }
                    this.current.removeClass('active');
                }

                // If method is triggered without click we
                // should return after saving the current scale
                if (!e){ alert("BUM3"); return;}

                this.current = $(e.currentTarget).addClass('active');
                var scale = this.current.data('scale');

                if (typeof scale === 'undefined'){return this;}
                if (scale.toSmall) { return this; }

                var w = this.SIZE.w,
                    h = this.SIZE.h,
                    x, y, x2, y2;

                // Find initial placement of crop
                // x,y,x2,y2
                if (scale && scale.coords) {
                    x = scale.coords[0];
                    y = scale.coords[1];
                    x2 = scale.coords[2];
                    y2 = scale.coords[3];
                } else {
                    //This happens on fresh upload
                    x = 0;
                    y = 0;
                    x2 = w / 2;
                    y2 = h / 2;
                }

                var ratio = (scale.size[0] / scale.size[1]);
                var context = this;

                // If an API exists we dont need to build Jcrop
                // but can just change crop
                var cropperOptions = {
                    setSelect: [x, y, x2, y2],
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

            /**
             * Checks if both stack animation is finished and version saved to server before
             * adding to tinyMCE
             */
            finishScaler: function() {
                if (this.versionSaved && this.poppedFromStack) {
                    var _this = this;
                    /**
                     * Must be wrapped in an timeout function to prevent FireFox from
                     * replacing all content instead of addding image to selected content
                     */
                    _.delay(function() {
                        _this.model.trigger('version.create', _this.versions, _this.versionSaved);
                        _this.trigger('saved');
                    }, 0);
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
        });
    });