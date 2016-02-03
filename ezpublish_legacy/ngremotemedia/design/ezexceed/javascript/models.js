define(['backbone', 'jquery-safe'], function(Backbone, $) {
    var url = function() {
        // var args = ['remotemedia', 'media', this.id, this.get('version')];
        // if (arguments.length > 0) {
        //     args = ['remotemedia'].concat(_.toArray(arguments));
        // }
        console.warn(arguments);
        return ["/ezexceed/ngremotemedia/fetch", this.id, this.get('version')].join('/') // /90430/5
            // return args.join('/');
    };


    var find_version = function(versions, version_name){
        return _.find(versions, function(v){ v.name.toLowerCase() === version_name.toLowerCase(); });
    };

    var Attribute = Backbone.Model.extend({
        urlRoot: null,
        medias: null,

        initialize: function() {
            _.bindAll(this);
            this.medias = new MediaCollection();
        },

        toScaleIndexed: function(){
            return _.reduce(this.get('toScale') || [], function(h,v){
                h[v.name.toLowerCase()] = v; return h;
            }, {});
        },

        defaults: function() {
            return {
                content: false,
                media: new Media()
            };
        },

        url: function() {
            return ["/ezexceed/ngremotemedia/fetch", this.id, this.get('version')].join('/'); // /90430/5
        },


        parse: function(data) {
            console.log(data);
            if ('media' in data) {
                data.media.attr = this;
                data.media = new Media(data.media, {
                    parse: true
                });
            }
            return data;
        },


        combined_versions: function(){
            var indexed = this.toScaleIndexed();
            return _.map(this.get('available_versions'), function(v){
                v = $.extend({}, v);
                var exact_version = indexed[v.name.toLowerCase()];
                exact_version && (v.coords = exact_version.coords);
                return v;
            });
        },

        fetch: function(options) {
            options = options || ({});
            options.success = this.fetched;
            options.transform = false;
            return Backbone.sync('read', this, options);
        },

        fetched: function(response) {
            this.set(this.parse(response));
            this.trigger('fetched');
        },

        onScale: function(response) {
            this.trigger('scale', response);
        },

        // Create a new vanity url for a version
        // name should be a string to put on the back of the object name
        // coords should be an array [x,y,x2,y2]
        // size shoudl be an array [width,height]
        addVanityUrl: function(name, coords, size, options) {
            options = (options || {});
            var data = {
                name: name
            };

            if (coords) {
                data.crop_x = Math.round(coords[0]);
                data.crop_y = Math.round(coords[1]);
                data.crop_w = Math.round(coords[2] - coords[0]);
                data.crop_h = Math.round(coords[3] - coords[1]);
            }

            if (_(options).has('media')) {
                data.mediaId = options.media.id;
                data.remotemediaId = options.media.get('remotemediaId');
            } else {
                var media = this.get('media');
                data.mediaId = media.id;
                data.remotemediaId = media.get('remotemediaId');
            }

            var id = this.id !== "ezoe" ? this.id : this.get('attributeId');
            // var url = this.url('saveVersion', id, this.get('version'));

            var url = ["/ezexceed/ngremotemedia/save", eZExceed.config.currentObjectId, this.id, this.get('version')].join('/'); // /90430/5

            return Backbone.sync('create', { url: url }, {
                data: data,
                transform: false
            });
        }
    });


    var Media = Backbone.Model.extend({
        urlRoot: '',

        initialize: function(options) {
            options = (options || {});
            _.bindAll(this);
            if ('urlRoot' in options) {
                this.urlRoot = options.urlRoot;
                delete options.urlRoot;
            }
        },

        parse: function(data) {
            data.file = data.metaData; //Create alias for metaData            
            delete(data.metaData); //TODO: rename on server
            data.tags = new Backbone.Collection(_.map(data.tags, function(tag) {
                return {
                    id: tag,
                    tag: tag
                };
            }));
            return data;
        },

        domain: function() {
            return 'http://' + this.get('host');
        },

        url: false,

        save: function() {
            var attr = this.get('attr');
            var url = ['/ezexceed/ngremotemedia/tag/add', attr.id, attr.get('version') ].join('/');

            var data = {
                id: attr.get('media').get('resourceId'),
                tags: this.get('tags').pluck('tag')
            };

            return Backbone.sync('create', { url: url }, {
                data: data,
                transform: false
            });
        },


        // Generate thumb url for a given size
        thumb: function(width, height, filetype) {
            filetype = (filetype || 'jpg');
            // return this.domain() + '/' + width + 'x' + height + '/' + this.id + '.' + filetype;
            // http://res.cloudinary.com/netgentest/image/upload/v1454075723/phpb3r2JI/5.jpg
            var url = this.get('url').split(/\/v\d+\//);
            return [url[0], 'w_' + width + ',h_' + height, url[1]].join("/");
        }
    });

    var MediaCollection = Backbone.Collection.extend({
        model: Media,

        // Must end in trailing slash
        urlRoot: '/',
        attr: null,
        total: 0,
        q: '',
        limit: 25,
        remotemediaId: null,
        xhr: null,

        initialize: function(options) {
            _.bindAll(this);
        },

        url: function() {
            // return '/ezexceed/ngremotemedia/browse/{attributeId}/{contentVersionId}/{query}';
            return ['/ezexceed/ngremotemedia/browse', this.id, this.version].join('/');
        },

        transformUrl: false,


        search: function(q, data) {
            var data = (data ||  {});
            if (typeof q === 'string') {
                this.q = q;
                data.q = q;
            }
            data.limit = this.limit;
            if (this.xhr && typeof this.xhr.abort === 'function') {
                this.xhr.abort();
            }
            this.xhr = this.fetch({
                data: data,
                reset: true
            });
            return this.xhr;
        },

        fetched: function() {
            this.trigger('fetched');
            this.xhr = null;
        },

        parse: function(data) {
            if ('remotemediaId' in data) {
                this.remotemediaId = data.remotemediaId;
            }
            if ('results' in data) {
                this.total = data.results.total;
                data = data.results.hits;
            }
            return data;
        },

        page: function(q) {
            if (this.length < this.total) {
                var data = {
                    limit: this.limit,
                    offset: this.length,
                    q: this.q
                };

                return Backbone.sync('read', {
                    url: this.url()
                }, {
                    data: data,
                    transform: false
                }).done(this.paged);
            }
            return false;
        },

        paged: function(data) {
            this.add(this.parse(data));
        }
    });

    return {
        media: Media,
        attribute: Attribute,
        collection: MediaCollection
    };
});