define(['backbone', 'jquery-safe'], function(Backbone, $)
{
    var url = function() {
        var args = ['remotemedia', 'media', this.id, this.get('version')];
        if (arguments.length > 0) {
            args = ['remotemedia'].concat(_.toArray(arguments));
        }
        return args.join('/');
    };

    var Attribute = Backbone.Model.extend({
        urlRoot : null,
        medias : null,

        initialize : function(options)
        {
            _.bindAll(this);
            this.medias = new MediaCollection();
        },

        defaults : function()
        {
            return {
                content : false,
                media : new Media()
            };
        },

        url : url,

        parse : function(data)
        {
            if ('media' in data) {
                data.media = new Media(data.media, {parse: true});
                data.media.set('attr', this);
            }
            if ('content' in data) data.content = data.content;
            if ('toScale' in data) data.toScale = data.toScale;
            return data;
        },

        fetch : function(options)
        {
            options = options || ({});
            options.success = this.fetched;
            return Backbone.sync('read', this, options);
        },

        fetched : function(response)
        {
            this.set(this.parse(response));
            this.trigger('fetched');
        },

        onScale : function(response) {
            this.trigger('scale', response);
        },

        // Create a new vanity url for a version
        // name should be a string to put on the back of the object name
        // coords should be an array [x,y,x2,y2]
        // size shoudl be an array [width,height]
        addVanityUrl : function(name, coords, size, options)
        {
            options = (options || {});
            var data = {
                name : name,
                size : size
            };

            if (coords)
                data.coords = coords;

            if (_(options).has('media')) {
                data.mediaId = options.media.id;
                data.remotemediaId = options.media.get('remotemediaId');
            }
            else {
                var media = this.get('media');
                data.mediaId = media.id;
                data.remotemediaId = media.get('remotemediaId');
            }

            var id = this.id !== "ezoe" ? this.id : this.get('attributeId');
            var url = this.url('saveVersion', id, this.get('version'));

            return Backbone.sync('create', {url: url}, {data: data});
        }
    });


    var Media = Backbone.Model.extend({
        urlRoot : '',

        initialize : function(options)
        {
            options = (options || {});
            _.bindAll(this);
            if ('urlRoot' in options) {
                this.urlRoot = options.urlRoot;
                delete options.urlRoot;
            }
        },

        parse : function(data)
        {
            data.tags = new Backbone.Collection(_.map(data.tags, function(tag) {
                return {
                    id : tag,
                    tag : tag
                };
            }));
            return data;
        },

        domain : function()
        {
            return 'http://' + this.get('host');
        },

        url : false,

        save : function()
        {
            var attr = this.get('attr');
            var url = attr.url('tag', attr.id, attr.get('version'), 'tag');

            var data = {
                id : this.id,
                tags : this.get('tags').pluck('tag')
            };

            return Backbone.sync('create', {url: url}, {data: data});
        },

        // Generate thumb url for a given size
        thumb : function(width, height, filetype)
        {
            filetype = (filetype || 'jpg');
            return this.domain() + '/' + width + 'x' + height + '/' + this.id + '.' + filetype;
        }
    });

    var MediaCollection = Backbone.Collection.extend({
        model : Media,

        // Must end in trailing slash
        urlRoot : '/',

        attr : null,

        total : 0,

        q : '',

        limit : 25,

        remotemediaId : null,

        xhr : null,

        initialize : function(options)
        {
            _.bindAll(this);
        },

        url : function()
        {
            return ['remotemedia', 'browse', this.id, this.version].join('/');
        },

        search : function(q, data)
        {
            var data = (data || {});
            if (typeof q === 'string') {
                this.q = q;
                data.q = q;
            }
            data.limit = this.limit;
            if (this.xhr && typeof this.xhr.abort === 'function') {
                this.xhr.abort();
            }
            this.xhr = this.fetch({data : data, reset: true});
            return this.xhr;
        },

        fetched : function()
        {
            this.trigger('fetched');
            this.xhr = null;
        },

        parse : function(data)
        {
            if ('remotemediaId' in data) {
                this.remotemediaId = data.remotemediaId;
            }
            if ('results' in data) {
                this.total = data.results.total;
                data = data.results.hits;
            }
            return data;
        },

        page : function(q)
        {
            if (this.length < this.total) {
                var data = {
                    limit : this.limit,
                    offset : this.length,
                    q : this.q
                };

                return Backbone.sync('read', {url: this.url()}, {data: data}).done(this.paged);
            }
            return false;
        },

        paged: function(data)
        {
            this.add(this.parse(data));
        }
    });

    return {
        media : Media,
        attribute : Attribute,
        collection : MediaCollection
    };
});
