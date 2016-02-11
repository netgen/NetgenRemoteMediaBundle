RemoteMedia.views.Scaler = Backbone.View.extend({
    // Holds reference to current selected scale li
    current: null,

    // Will hold the Jcrop API
    cropper: null,

    trueSize: [],

    // size of cropping media
    size: {
        w: 830,
        h: 580
    },

    tagName: 'div',
    className: 'scaler',

    initialize: function(options) {
        _.bindAll(this, 'render', 'changeScale', 'versionCreated', 'createOverlay');

        this.media = new RemoteMedia.models.Media({
            id: options.mediaId,
            host: options.host,
            type: options.type
        });

        this.versions = this.model.combined_versions();
        this.trueSize = options.trueSize;
        this.boxSize = options.size;

        this.model.bind('scale', this.render);
        this.model.bind('version.create', this.versionCreated);

        return this;
    },

    events: {
        'click .header li': 'changeScale',
        'mouseenter .header li': 'overlay'
    },

    overlay: function(e) {
        var node = $(e.currentTarget),
            overlay = node.find('div');

        if (node !== this.current)
            this.createOverlay(node.find('div'), node.data('scale'));
    },

    createOverlay: function(node, data) {
        if (this.cropper) {
            if (!('coords' in data) || data.coords.length !== 4)
                return false;
            var scale = this.cropper.getScaleFactor();
            var coords = data.coords,
                x = parseInt(coords[0] / scale[0], 10),
                y = parseInt(coords[1] / scale[1], 10),
                x2 = parseInt(coords[2] / scale[0], 10),
                y2 = parseInt(coords[3] / scale[1], 10),
                offset = this.container.position();
            var css = {
                'top': parseInt((offset.top - 0) + y, 10),
                left: parseInt((offset.left - 0) + x, 10),
                width: parseInt(x2 - x, 10),
                height: parseInt(y2 - y, 10)
            };
            node.css(css);
        }
    },

    render: function(response) {
        if (response.content.hasOwnProperty('skeleton')) {
            this.$el.html(response.content.skeleton);
        }

        this.$('img').attr({
            src: this.media.thumb(this.size.w, this.size.h, 'jpg') + '?original=1'
        });
        this.container = this.$('#remotemedia-scaler-image');

        var i, scale = $(response.content.scale),
            item, r, name;
        var ul = this.$('.header ul'),
            box;
        var outerBounds = this.outerBounds(this.versions, 4, 40);
        for (i = 0; i < this.versions.length; i++) {
            r = this.versions[i];
            item = scale.clone();
            item.attr('id', this.scaledId(r));
            item.find('h2').text(r.name);
            item.find('span').text(r.size.join('x'));
            item.data('scale', r);
            if ('url' in r)
                item.addClass('cropped');
            else
                item.addClass('uncropped');

            ul.append(item);

            box = new RemoteMedia.views.Scalebox({
                el: item.find('p'),
                model: r.size,
                outer: outerBounds
            }).render();
        }

        // Enable the first scaling by simulating a click
        this.$('.header ul').find('a').first().click();

        return this;
    },

    // Calculate outer bounds for preview boxes
    outerBounds: function(versions, gt, lt) {
        var i, w, h, min = {
                w: 0,
                h: 0
            },
            max = {
                w: 0,
                h: 0
            };
        for (i = 0; i < versions.length; i++) {
            w = parseInt(versions[i].size[0], 10);
            h = parseInt(versions[i].size[1], 10);

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
        var vanityName = scale.name,
            size = scale.size;

        // Must store scale coords back onto object
        scale.coords = [selection.x, selection.y, selection.x2, selection.y2];

        return this.model.addVanityUrl(vanityName, scale.coords, size);
    },

    versionCreated: function(data) {
        var menuElement = this.$('#scaled-' + data.name.toLowerCase());
        menuElement.data('scale', data);
        this.createOverlay(menuElement.find('.overlay'), data);
    },

    changeScale: function(e) {
        e.preventDefault();
        var scale;

        if (this.current !== null) {
            this.current.removeClass('active');

            if (this.cropper !== null) {
                // If a previous crop exists, save the coordinates as a new vanity url
                scale = this.current.data('scale');
                if (this.cropper && scale) {
                    this.storeVersion(this.cropper.tellSelect(), scale);
                    this.current.removeClass('uncropped').addClass('cropped');
                }
            }
        }

        this.current = $(e.currentTarget);
        this.current.addClass('active');
        scale = this.current.data('scale');

        var w = this.size.w,
            h = this.size.h,
            x, y, x2, y2;

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

        var ratio = (scale.size[0] / scale.size[1]);

        // If an API exists we dont need to build Jcrop
        // but can just change crop
        var context = this,
            size = this.trueSize;
        if (this.cropper) {
            // Change selection to new selection
            this.cropper.setOptions({
                setSelect: select,
                aspectRatio: ratio,
                minSize: scale.size
            });
        } else {
            this.$('#remotemedia-scaler-crop').Jcrop({
                trueSize: size
            }, function(a) {
                // Store reference to API
                context.cropper = this;
                // Set true size of media
                this.setOptions({
                    aspectRatio: ratio,
                    setSelect: select,
                    minSize: scale.size
                });
            });
        }
    },

    close: function() {
        if (this.cropper) {
            this.cropper.destroy();
            this.cropper = null;
            this.current = null;
            this.delegateEvents([]);
            this.model.unbind('scale');
            this.model.unbind('version.create');
            this.$el.html('');
        }
    }
});