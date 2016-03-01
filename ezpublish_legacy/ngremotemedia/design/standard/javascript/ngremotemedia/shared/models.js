/*global RemoteMediaSettings, RemoteMediaShared*/
window.RemoteMediaShared || (window.RemoteMediaShared = {});

window.RemoteMediaShared.config = function() {
    
    var is_admin = this.is_admin();

    return {
        is_admin: is_admin,
        plupload_swf: is_admin ? '/extension/ngremotemedia/design/standard/javascript/libs/plupload/Moxie.swf' : eZExceed.config.plupload.flash_swf_url,
        currentObjectId: is_admin ? RemoteMediaSettings.ez_contentobject_id : eZExceed.config.currentObjectId,
        version: is_admin ? RemoteMediaSettings.ez_contentobject_version : $('[data-version]').data('version')
    };
};

window.RemoteMediaShared.is_admin = function(){
    return typeof RemoteMediaSettings !== 'undefined';
};


window.RemoteMediaShared.url = function(url){
    var prefix = this.is_admin() ?  RemoteMediaSettings.url_prefix : $('.ngremotemedia-type').data('urlRoot');
    var new_url = [prefix, url].join('/').replace(/\/+/g, '/');
    return new_url;
};

window.RemoteMediaShared.Models = function() {

    var Attribute = Backbone.Model.extend({
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
            return [RemoteMediaShared.url("/ngremotemedia/fetch"), RemoteMediaShared.config().currentObjectId, this.id, this.get('version')].join('/');
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
            var url = [RemoteMediaShared.url("/ngremotemedia/change"), RemoteMediaShared.config().currentObjectId, this.id, this.get('version')].join('/');

            return this.save({}, {
                url: url,
                method: 'POST',
                data: {
                    resource_id: id, 
                } 
            });
        },

        // Create a new vanity url for a version
        // name should be a string to put on the back of the object name
        // coords should be an array [x,y,x2,y2]
        save_version: function(name, coords) {
            var data = {
                name: name
            };

            _.extend(data, this.process_coords(coords));

            var url = [RemoteMediaShared.url("/ngremotemedia/save"), RemoteMediaShared.config().currentObjectId, this.id, this.get('version')].join('/');

            return Backbone.sync('create', this, {
                url: url,
                data: data,
                transform: false
            });
        },


        generate: function(name, coords){
            var data = {
                name: name,
                resourceId: this.get('media').id
            };

            _.extend(data, this.process_coords(coords));

            return Backbone.sync('create', this, {
                url: RemoteMediaShared.url('/ngremotemedia/generate'),
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

        initialize: function() {
            _.bindAll(this);
        },

        parse: function(data) {
            if(data.media){
                var new_data = data.media;
                new_data.available_versions = data.available_versions;
                new_data.class_list = data.class_list;
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
            return [RemoteMediaShared.url('/ngremotemedia/simple_fetch')].join('/');
        },


        tags_url: function(method) {
            var attr = this.get('attr');
            method || (method = "");
            return [RemoteMediaShared.url('/ngremotemedia/tags'+method), RemoteMediaShared.config().currentObjectId, attr.id, attr.get('version')].join('/');
        },


        add_tag: function(tag_name) {
            return Backbone.sync('create', this, {
                transform: false,
                url: this.tags_url(),
                data: {
                    id: this.get('resourceId'), 
                    tag: tag_name
                }
            });
        },


        remove_tag: function(tag_name) {
            return Backbone.sync('delete', this, {
                transform: false,
                method: 'POST',
                url: this.tags_url('_delete'),
                data: {
                    id: this.get('resourceId'),
                    tag: tag_name
                }
            });
        },


        // Generate thumb url for a given size
        thumb: function(width, height) {
            var url = this.get('url').split(/\/v\d+\//);
            return [url[0], 'w_' + width + ',h_' + height, url[1]].join("/");
        }
    });

    var MediaCollection = Backbone.Collection.extend({
        model: Media,

        attr: null,
        total: 0,
        q: '',
        limit: 25,
        xhr: null,

        initialize: function() {
            _.bindAll(this);
        },

        url: function() {
            return RemoteMediaShared.url('/ngremotemedia/browse');
        },

        transformUrl: false,


        search: function(q, data) {
            data = (data ||  {});
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
            this.total = data.count;
            return data.hits;
        },

        page: function() {
            if (this.length < this.total) {
                var data = {
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