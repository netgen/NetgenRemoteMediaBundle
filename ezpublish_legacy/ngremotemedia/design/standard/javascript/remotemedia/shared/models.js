/*global eZOeGlobalSettings, RemoteMediaShared*/
window.RemoteMediaShared || (window.RemoteMediaShared = {});

window.RemoteMediaShared.config = function() {

    var is_admin = typeof eZOeGlobalSettings !== 'undefined';
    this.user_id || ( this.user_id = $('[data-user-id]').data('user-id') );

    return {
        is_admin: is_admin,
        user_id: this.user_id,
        plupload_swf: is_admin ? '/extension/remotemedia/design/standard/javascript/libs/plupload/Moxie.swf' : eZExceed.config.plupload.flash_swf_url,
        currentObjectId: is_admin ? eZOeGlobalSettings.ez_contentobject_id : eZExceed.config.currentObjectId,
        version: is_admin ? eZOeGlobalSettings.ez_contentobject_version : $('[data-version]').data('version')
    };
};


window.RemoteMediaShared.Models = function() {

    var Attribute = Backbone.Model.extend({
        urlRoot: null,
        medias: null,

        initialize: function() {
            _.bindAll(this);
            this.medias = new MediaCollection();
        },

        toScaleIndexed: function() {
            return _.reduce(this.get('toScale') || [], function(h, v) {
                h[v.name.toLowerCase()] = v;
                return h;
            }, {});
        },

        defaults: function() {
            return {
                content: false,
                media: new Media()
            };
        },

        url: function() {
            console.log(this);
            return ["/ezexceed/ngremotemedia/fetch", RemoteMediaShared.config().currentObjectId, this.id, this.get('version')].join('/');
        },


        parse: function(data) {
            if ('media' in data) {
                data.media.attr = this;
                data.media = new Media(data.media, {
                    parse: true
                });
            }
            return data;
        },


        combined_versions: function() {
            var indexed = this.toScaleIndexed();
            return _.map(this.get('available_versions'), function(v) {
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


        change_media: function(id){
            var url = ["/standard/ngremotemedia/change", RemoteMediaShared.config().currentObjectId, this.id, this.get('version')].join('/');

            return this.save({}, {
                url: url,
                method: 'POST',
                data: {
                    resource_id: id, 
                    user_id: RemoteMediaShared.config().user_id
                } 
            });
        },

        // Create a new vanity url for a version
        // name should be a string to put on the back of the object name
        // coords should be an array [x,y,x2,y2]
        save_version: function(name, coords) {
            var data = {
                name: name,
                mediaId: this.get('media').id,
                user_id: RemoteMediaShared.config().user_id
            };

            _.extend(data, this.process_coords(coords));

            var url = ["/ezexceed/ngremotemedia/save", RemoteMediaShared.config().currentObjectId, this.id, this.get('version')].join('/');

            return Backbone.sync('create', this, {
                url: url,
                data: data,
                transform: false
            });
        },


        generate: function(name, coords){
            var data = {
                name: name,
                resourceId: this.get('media').id,
                user_id: RemoteMediaShared.config().user_id
            };

            _.extend(data, this.process_coords(coords));

            return Backbone.sync('create', this, {
                url: '/ezexceed/ngremotemedia/generate',
                data: data,
                transform: false
            });
        },


        process_coords: function(coords){
            var data = {};
            data.crop_x = Math.round(coords[0]);
            data.crop_y = Math.round(coords[1]);
            data.crop_w = Math.round(coords[2] - coords[0]);
            data.crop_h = Math.round(coords[3] - coords[1]);
            return data;
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
            if(data.media){
                var new_data = data.media;
                new_data.available_versions = data.available_versions;
                new_data.metaData = data.media;
                new_data.resourceId = data.media.public_id;
                data = new_data;
            }
            data.id = data.resourceId;
            data.file = _.extend({}, data.metaData); //Create alias for metaData            
            delete(data.metaData);
            data.file.type = data.file.resource_type;
            data.true_size = [data.file.width, data.file.height];
            data.tags = new Backbone.Collection(_.map(data.file.tags, function(tag) {
                return {
                    id: tag,
                    tag: tag
                };
            }));
            return data;
        },

        url: function(){
            return ['/ezexceed/ngremotemedia/simple_fetch', this.id].join('/');
        },


        tags_url: function() {
            var attr = this.get('attr');
            return ['/ezexceed/ngremotemedia/tags', RemoteMediaShared.config().currentObjectId, attr.id, attr.get('version')].join('/');
        },


        add_tag: function(tag_name) {
            return Backbone.sync('create', this, {
                transform: false,
                url: this.tags_url(),
                data: {
                    user_id: RemoteMediaShared.config().user_id,
                    id: this.get('resourceId'), 
                    tag: tag_name
                }
            });
        },


        remove_tag: function(tag_name) {
            return Backbone.sync('delete', this, {
                transform: false,
                method: 'DELETE',
                url: this.tags_url(),
                data: {
                    user_id: RemoteMediaShared.config().user_id,
                    id: this.get('resourceId'),
                    tag: tag_name
                }
            });
        },


        // Generate thumb url for a given size
        thumb: function(width, height, filetype) {
            filetype = (filetype || 'jpg');
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

        initialize: function() {
            _.bindAll(this);
        },

        url: function() {
            return '/ezexceed/ngremotemedia/browse';
        },

        transformUrl: false,


        search: function(q, data) {
            data = (data ||  {});
            if (typeof q === 'string') {
                this.q = q;
                data.q = q;
            }
            data.limit = this.limit;
            data.user_id = RemoteMediaShared.config().user_id;
            
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

        page: function() {
            if (this.length < this.total) {
                var data = {
                    user_id: RemoteMediaShared.config().user_id,
                    limit: this.limit,
                    offset: this.length,
                    q: this.q
                };

                return Backbone.sync('read', this, {
                    url: this.url(),
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
        Media: Media,
        Attribute: Attribute,
        MediaCollection: MediaCollection
    };

};