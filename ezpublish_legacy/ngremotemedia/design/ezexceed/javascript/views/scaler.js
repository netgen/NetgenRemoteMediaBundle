define(['remotemedia/view', './scaled_version', 'jquery-safe', 'remotemedia/templates/scaler', 'remotemedia/templates/scalerattributes', 'jcrop'],
    function(View, ScaledVersion, $, Scaler, Scalerattributes) {
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
                console.log("scaler:initialize", this, options);
            },

            events: {
                'click .nav li': 'changeScale',
                'mouseenter .nav li.cropped': 'overlay'
            },

            overlay: function(e) {
                var node = this.$(e.currentTarget);
                var overlay = node.find('div');
                if (node !== this.current) {
                    this.createOverlay(overlay, node.data('scale'));
                }
            },

            // render the overlay div for a scaled versions
            // menu item. Happens on mouseenter on the li or after saving
            // new crop information
            createOverlay: function(node, data) {
                if (this.cropper && 'coords' in data && data.coords.length === 4) {

                    var scale = this.cropper.getScaleFactor();
                    var coords = data.coords;

                    var x = parseInt(coords[0] / scale[0], 10),
                        y = parseInt(coords[1] / scale[1], 10),
                        x2 = parseInt(coords[2] / scale[0], 10),
                        y2 = parseInt(coords[3] / scale[1], 10);

                    node.css({
                        'position': 'absolute',
                        'top': y + node.parent().outerHeight(true),
                        left: x,
                        width: parseInt(x2 - x, 10),
                        height: parseInt(y2 - y, 10)
                    });
                }
            },



            fitTo: function(w, h, maxWidth, maxHeight) {
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

                var content = Scaler({
                    media: media.thumb(this.SIZE.w, this.SIZE.h, 'jpg')
                });
                this.$el.append(content);

                console.log('scaler:render', this.versions);

                var outerBounds = this.outerBounds(this.versions, 4, 40);

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
                        Scalerattributes({
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
                        outerBounds: outerBounds,
                        className: (version.coords ? 'cropped' : 'uncropped')
                    }).render().el;
                });
                this.$('ul.nav').html(versionElements);

                if (this.selectedVersion) {
                    var scale;
                    var selectedEl = _.filter(this.$('ul.nav li'), function(el) {
                        scale = this.$(el).data('scale');
                        if (scale && _(scale).has('name') && scale.name == this.selectedVersion)
                            return true;
                        return false;
                    }, this);

                    this.$(selectedEl).find('a').click();
                } else {
                    // Enable the first scaling by simulating a click
                    this.$('ul.nav li:first-child a').click();
                }

                return this;
            },

            // Calculate outer bounds for preview boxes
            outerBounds: function(versions, gt, lt) {

                var media = this.model.get('media');

                var min = {
                    w: 0,
                    h: 0
                };
                var max = {
                    w: 0,
                    h: 0
                };
                var i, w, h;

                for (i = 0; i < versions.length; i++) {
                    if (_(versions[i]).has('size') && _(versions[i].size).isArray()) {
                        w = parseInt(versions[i].size[0], 10);
                        h = parseInt(versions[i].size[1], 10);
                    } else {
                        console.error("BUM");
                        w = parseInt(media.get('width'), 10);
                        h = parseInt(media.get('height'), 10);
                    }

                    if (w > max.w) max.w = w;
                    if (h > max.h) max.h = h;

                    if (min.w === 0 ||  w < min.w) min.w = w;
                    if (min.h === 0 ||  h < min.h) min.h = h;
                }

                return {
                    max: max,
                    min: min
                };
            },

            scaledId: function(item) {
                return 'scaled-' + item.name;
            },

            storeVersion: function(selection, scale) {
                // Must store scale coords back onto object
                var coords = [selection.x, selection.y, selection.x2, selection.y2];
                var size = scale.size || ([selection.w, selection.h]);

                this.trigger('save');
                this.model.addVanityUrl(scale.name, coords, size).success(this.versionCreated);
            },

            versionCreated: function(data) {
                if ('content' in data && data.content) data = data.content;
                /**
                 * HACK. Prevent old coords to be used when scaling again
                 * Store new coords in scale button
                 */
                var name = data.name;
                var coords = data.coords;

                _.each(this.versions, function(version, key) {
                    if (version.name === name) {
                        this.versions[key].coords = coords;
                    }
                }, this);

                console.log('versionCreated', this.versions);

                var menuElement = this.$('#eze-remotemedia-scale-version-' + data.name.toLowerCase());
                menuElement.data('versions', this.versions);
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
                console.log('saveCrop scale', scale);

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
                    } else {
                        var tellScaled = this.cropper.tellScaled();
                        var ratio = (tellScaled.w / tellScaled.h);
                        if (!parseInt(scale.size[0], 10))
                            scale.size[0] = Math.ceil(scale.size[1] * ratio);
                        else if (!parseInt(scale.size[1], 10))
                            scale.size[1] = Math.ceil(scale.size[0] / ratio);
                    }

                    this.storeVersion(selection, scale);
                    this.current.removeClass('uncropped').addClass('cropped');
                }
            },

            changeScale: function(e) {
                if (e) e.preventDefault();

                if (this.current !== null) {
                    if (!this.singleVersion)
                        this.saveCrop();
                    if (e && this.current.get(0) == this.$(e.currentTarget).get(0))
                        return;

                    this.current.removeClass('active');
                }

                // If method is triggered without click we
                // should return after saving the current scale
                if (!e) return;

                this.current = this.$(e.currentTarget);
                this.current.addClass('active');

                var scale = this.current.data('scale');

                console.log(scale);

                if (typeof scale === 'undefined')
                    return this;

                // If image is to small for the version, show an overlay with error message
                var mediaFile = this.model.get('media').get('file');
                var width = parseInt(mediaFile.width, 10);
                var height = parseInt(mediaFile.height, 10);
                if (scale.size[0] > width || scale.size[1] > height) {
                    if (this.cropper) {
                        this.cropper.destroy();
                        this.cropper = null;
                        this.$('.image-wrap>img').css('visibility', 'visible');
                    }
                    this.showAlert();
                    return this;
                }
                /**
                 * Hide the alert
                 */
                this.showAlert(true);

                var w = this.SIZE.w;
                var h = this.SIZE.h;
                var x, y, x2, y2;

                // Find initial placement of crop
                // x,y,x2,y2
                if (scale && 'coords' in scale) {
                    x = scale.coords[0] - 0;
                    y = scale.coords[1] - 0;
                    x2 = scale.coords[2] - 0;
                    y2 = scale.coords[3] - 0;
                } else {
                    x = parseInt((this.trueSize[0] - w) / 2, 10);
                    y = parseInt((this.trueSize[1] - h) / 2, 10);
                    x2 = parseInt((this.trueSize[0] + w) / 2, 10);
                    y2 = parseInt((this.trueSize[1] + h) / 2, 10);
                }
                var select = [x, y, x2, y2];

                var ratio = null,
                    minSize = null;

                if (scale && scale.size) {
                    if (parseInt(scale.size[0], 10) && parseInt(scale.size[1], 10))
                        ratio = (scale.size[0] / scale.size[1]);
                    minSize = scale.size;
                }

                // If an API exists we dont need to build Jcrop
                // but can just change crop
                var cropperOptions = {
                    setSelect: select
                };

                if (ratio) {
                    cropperOptions.aspectRatio = ratio;
                } else {
                    cropperOptions.aspectRatio = 0;
                }
                cropperOptions.minSize = minSize;

                /**
                 * Make sure user can't remove selection if width and height has bounded dimension
                 */
                cropperOptions.allowSelect = (parseInt(minSize[0], 10) && parseInt(minSize[1], 10)) ? false : true;

                if (this.cropper) {
                    // Change selection to new selection
                    this.cropper.setOptions(cropperOptions);
                } else {
                    var context = this;
                    this.$('.image-wrap>img').Jcrop({
                        trueSize: this.trueSize,
                        onSelect: function() {
                            context.hasSelection = true;
                        },
                        onRelease: function() {
                            context.hasSelection = false;
                        }
                    }, function(a) {
                        // Store reference to API
                        context.cropper = this;
                        // Set true size of media
                        this.setOptions(cropperOptions);
                    });
                }
            },

            showAlert: function(hide) {
                if (hide) {
                    this.$('.image-wrap p').remove();
                    return;
                }
                if (this.$('.image-wrap p').length)
                    return;

                var wrapper = this.$('.image-wrap');
                var img = wrapper.find('img');
                var size = this.imageViewedSize();
                wrapper.css({
                    width: size.w + 2,
                    height: size.h + 2
                });
                wrapper.append(this.template('alert'));
            },

            /**
             * Calculates the rendered image sizes
             *
             * @return {Object}
             */
            imageViewedSize: function() {
                var media = this.model.get('media');
                var file = media.get('file');
                var data = {
                    w: this.SIZE.w,
                    h: this.SIZE.h
                };
                if (this.SIZE.w > file.width)
                    data.w = file.width;

                if (this.SIZE.h > file.height)
                    data.h = file.height;

                if (file.ratio > 1)
                    data.h = Math.round(data.w / file.ratio);
                else
                    data.w = Math.round(data.h * file.ratio);
                return data;
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