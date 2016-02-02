define(['remotemedia/view', 'remotemedia/models', './tagger', './upload', 'brightcove'], function(View, Models, TaggerView, UploadView) {

    function convert_versions(versions){
        if(_.isArray(versions)){return versions;}
        return _.map(versions, function(size, name) {
            return {
                size: size.split ? size.split('x') : size,
                name: name
            };
        });
    }

    return View.extend({
        media: null,
        versions: null,
        version: null,

        initialize: function(options) {
            options = (options || {});
            _.bindAll(this);
            _.extend(this, _.pick(options, ['id', 'version']));

            var data = this.$el.data();
            _.extend(data, this.$('.attribute-base').data());

            var urlRoot = '/';
            if (data.urlRoot !== '/') urlRoot = data.urlRoot + urlRoot;

            this.model = new Models.attribute({
                id: data.id,
                version: this.version,
                media: data.bootstrap
            }, {
                parse: true
            });
            console.log("Data:", data);
            this.model.urlRoot = urlRoot;
            this.model
                .on('change', this.render)
                .on('version.create', this.versionCreated);

            this.collection = new Models.collection();
            this.collection.urlRoot = urlRoot;
            this.collection.id = data.id;
            this.collection.version = this.version;

            this.on('saved', this.update, this);

            this.loadCSS();
        },

        loadCSS: function() {
            var headEl = document.getElementsByTagName('head')[0];
            var files = ['jquery.jcrop', 'ngremotemedia'];
            _.each(files, function(name) {
                var css = document.createElement('link');
                css.href = '/extension/ngremotemedia/design/ezexceed/stylesheets/' + name + '.css';
                css.type = 'text/css';
                css.rel = 'stylesheet';
                headEl.appendChild(css);
            });
        },

        events: {
            'click button.from-remotemedia': function(e) {
                e.preventDefault();
                require(['remotemedia/views/browser'], this.browse);
            },
            'click button.scale': function(e) {
                e.preventDefault();
                require(['remotemedia/views/scaler'], this.scale);
            },
            'click .remove': 'removeMedia'
        },

        removeMedia: function(e) {
            e.preventDefault();
            var data = this.$('.data').val('').serializeArray();

            data.push({
                name: 'mediaRemove',
                value: 1
            });

            this.trigger('save', this.model.id, data);
            this.hide(this.$('.eze-image'));
        },

        browse: function(BrowseView) {
            var options = {
                model: this.model,
                version: this.version,
                collection: this.collection
            };

            var context = {
                icon: '/extension/ezexceed/design/ezexceed/images/kp/32x32/Pictures.png',
                heading: 'Select media',
                render: true
            };
            eZExceed.stack.push(
                BrowseView,
                options,
                context
            ).on('destruct', this.changeMedia);
            this.collection.search('');
        },

        // Start render of scaler sub-view
        scale: function(ScaleView) {
            var data = this.$scale.data();
            var options = {
                model: this.model,
                trueSize: data.truesize
            };

            console.log("main:scale", options);

            var context = {
                icon: '/extension/ezexceed/design/ezexceed/images/kp/32x32/Pictures-alt-2b.png',
                className: 'dark',
                heading: 'Select crops',
                render: true
            };

            this.model.fetch({
                transform: false
            }).success(function(){
                console.log("fetch", this, arguments);

                eZExceed.stack.push(
                    ScaleView,
                    options,
                    context
                );
            });
            return this;
        },

        changeMedia: function(data) {
            this.$('.media-id').val(data.id);
            this.$('.media-host').val(data.host);
            this.$('.media-type').val(data.type);
            this.$('.media-ending').val(data.ending);

            data = this.$(':input').serializeArray();
            data.push({
                name: 'changeMedia',
                value: 1
            });

            this.trigger('save', this.model.id, data);
        },

        loader: function() {
            var data = {
                size: 32,
                className: 'icon-32',
                statusText: 'Uploading…'
            };
            this.$('.eze-image .thumbnail').html(this._loader(data));
            this.$('.upload-from-disk').attr("disabled", "disabled");
        },

        update: function() {
            this.model.fetch({
                transform: false
            });
        },

        render: function() {
            window.main_view = this;
            var content = this.model.get('content');
            var media = this.model.get('media');
            // var file = media.get('file');
            var file = media.get('metaData');

            if (content) {
                this.$('.attribute-base').html(content);
            }

            if (file) {
                this.$scale = this.$("button.scale");

                var available_versions = convert_versions(this.$scale.data('versions'));
                this.model.set('available_versions', available_versions);

                var imgWidth = file.width;
                var imgHeight = file.height;

                var imageFitsAll = !_(available_versions).some(function(version) {
                    var width = parseInt(version.size[0], 10);
                    var height = parseInt(version.size[1], 10);
                    return width > imgWidth || height > imgHeight;
                });

                var img = this.$('.edit-image img');
                if (!imageFitsAll)
                    this.show(img);
                else
                    this.hide(img);

                if (file && 'type' in file && file.type.match(/video/)) {
                    if (typeof brightcove !== 'undefined'){
                        brightcove.createExperiences();
                    }
                }
            }

            this.renderUpload().renderTags();

            return this;
        },

        renderTags: function() {
            this.taggerView = new TaggerView({
                el: this.$('.remotemedia-tags'),
                model: this.model.get('media')
            }).render();
            return this;
        },

        renderUpload: function() {
            this.upload = new UploadView({
                model: this.model,
                uploaded: this.changeMedia,
                el: this.$el,
                version: this.version
            }).render();
            this.listenTo(this.upload, 'uploading', this.loader);
            return this;
        },

        versionCreated: function(versions) {
            this.model.trigger('autosave.saved');
            this.trigger('save', 'triggerVersionUpdate', {
                'triggerVersionUpdate': 1
            });
            this.$scale.data('versions', versions);
        }
    });
});