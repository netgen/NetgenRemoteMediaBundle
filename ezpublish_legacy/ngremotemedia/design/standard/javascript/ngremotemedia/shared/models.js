/*global RemoteMediaSettings, NgRemoteMediaShared*/
window.NgRemoteMediaShared || (window.NgRemoteMediaShared = {});

window.NgRemoteMediaShared.config = function() {

    var is_admin = this.is_admin();

    return {
        is_admin: is_admin,
        plupload_swf: '/extension/ngremotemedia/design/standard/javascript/libs/plupload/Moxie.swf'
    };
};

window.NgRemoteMediaShared.is_admin = function(){
    return typeof RemoteMediaSettings !== 'undefined';
};


window.NgRemoteMediaShared.url = function(url){
    var prefix = this.is_admin() ?  RemoteMediaSettings.url_prefix : $('.ngremotemedia-type').data('urlRoot');
    var new_url = [prefix, url].join('/').replace(/\/+/g, '/');
    return new_url;
};


var loadCSS = function() {
    var headEl = document.getElementsByTagName('head')[0];
    var files = ['jquery.jcrop', 'ngremotemedia'];
    _.each(files, function(name) {
        var css = document.createElement('link');
        css.href = '/extension/ngremotemedia/design/standard/stylesheets/' + name + '.css';
        css.type = 'text/css';
        css.rel = 'stylesheet';
        headEl.appendChild(css);
    });
};

loadCSS();



window.NgRemoteMediaShared.Models = function() {
    var VariationCollection = NgRemoteMedia.models.VariationCollection;

    var Attribute = Backbone.Model.extend({
        klass: "Attribute",
        medias: null,

        initialize: function(attributes) {
            _.bindAll(this);
            this.medias = new MediaCollection();
            this.variations = new VariationCollection();
        },

        // toScaleIndexed: function() {
        //     return _.reduce(this.get('toScale') || [], function(h, v) {
        //         h[v.name.toLowerCase()] = v;
        //         return h;
        //     }, {});
        // },

        defaults: function() {
            return {
                content: false,
                media: new Media()
            };
        },

        url: function() {
            return [NgRemoteMediaShared.url("/ngremotemedia/fetch"), this.get('contentObjectId'), this.id, this.get('version')].join('/');
        },

        parse: function(data) {

            if ('media' in data) {
                data.media.attr = this;
                data.media = new Media(data.media, {
                    parse: true
                });
            }


            if ('variations' in data) {
                var x = _.map(data.variations, function(value, name){
                    return $.extend({name: name}, value);
                });

                // data.variations = new VariationCollection(x, {
                //     parse: true
                // });
                this.variations.set(x, {parse: true})
                this.variations.media = data.media;

                delete(data.variations);
            }


            return data;
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
            var url = [NgRemoteMediaShared.url("/ngremotemedia/change"), this.get('contentObjectId'), this.id, this.get('version')].join('/');

            return this.save({}, {
                url: url,
                method: 'POST',
                data: {
                    resource_id: id,
                }
            });
        },


        // save_variation: function(name, selection) {
        //     var variations = this.get('variations');
        //     console.log(variations, selection);
        //     variations[name] = {
        //         name: name,
        //         coords: this.parse_jcrop_selection_to_coords(selection),
        //     }
        //     this.set('variations', variations);
        //     return this;
        // },


        //NOTE: not used yet
        save_variations: function() {
            var url = [NgRemoteMediaShared.url("/ngremotemedia/save"), this.get('contentObjectId'), this.id, this.get('version')].join('/');

            var json = this.variations.toJSON();

            var out = {};
            this.variations.each(function(item){
                out[item.get('name')] = {
                    x: item.get('x'),
                    y: item.get('y'),
                    w: item.get('w'),
                    h: item.get('h')
                }
            });

            return Backbone.sync('create', this, {
                url: url,
                data: {variations: out},
                transform: false
            });
        },



        generate: function(name, selection){
            var data = {
                name: name,
                resourceId: this.get('media').id
            };

            _.extend(data, this.parse_jcrop_selection_to_coords(selection));

            return Backbone.sync('create', this, {
                url: NgRemoteMediaShared.url('/ngremotemedia/generate'),
                data: data,
                transform: false
            });
        },


        // var coords = [selection.x, selection.y, selection.x2, selection.y2];
        parse_jcrop_selection_to_coords: function(selection){
            var data = {};
            data.x = Math.round(selection.x);
            data.y = Math.round(selection.y);
            data.w = Math.round(selection.x2 - selection.x);
            data.h = Math.round(selection.y2 - selection.y);
            return data;
        }

    });





    /**
     * Model Media
     */

    var Media = Backbone.Model.extend({
        klass: "Media",
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
            return data;
        },

        url: function(){
            return [NgRemoteMediaShared.url('/ngremotemedia/simple_fetch')].join('/');
        },

        // Generate thumb url for a given size
        thumb: function(width, height) {
            var url = this.get('url').split(/\/v\d+\//);
            return [url[0], 'w_' + width + ',h_' + height, url[1]].join("/");
        }
    });


    var MediaCollection = Backbone.Collection.extend({
        klass: "MediaCollection",
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
            return NgRemoteMediaShared.url('/ngremotemedia/browse');
        },

        search: function(data) {
            data = (data ||  {});
            data.limit = this.limit;

            if (this.xhr && typeof this.xhr.abort === 'function') {
                this.xhr.abort();
            }
            this.xhr = this.fetch({
                data: data,
                transform: false,
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

                return this.fetch({
                    url: this.url(),
                    data: data,
                    remove: false,
                    transform: false
                });
            }
            return false;
        }
    });


    var Variation = Backbone.Model.extend({
        klass: "Variation",
        idAttribute: 'name',


        set: function(attributes, options){
          _.each(attributes, function(value, attr) {
            if(!_.contains(['name', 'cropped'], attr)){
              attributes[attr] = Math.round(value) || 0;
            }
          })
          return Backbone.Model.prototype.set.call(this, attributes, options)

        },

        file: function(){
          return this.collection.media.get('file');
        },

        originalWidth: function(){
          return this.file().width;
        },

        originalHeight: function(){
          return this.file().height;
        },

        tooSmall: function(){
          return this.get('w') > this.originalWidth() && this.get('h') > this.originalHeight();
        },

        unbounded: function(){
          return !this.get('w') || !this.get('h'); //if any dimension is 0
        },

        ratio: function(){
          return this.get('possibleWidth') / this.get('possibleHeight');
        },

        minSize: function () {
            return [this.get('possibleWidth'), this.get('possibleHeight')];
        },

        aspectRatio: function(){
          return this.unbounded() ? null : this.ratio()
        },

        // var coords = [selection.x, selection.y, selection.x2, selection.y2];
        // jCrop selection
        coords: function(){
            var c = this.attributes;
            if(this.is_cropped()){
                return [c.x, c.y, c.x + c.w, c.y + c.h];
            }else{
                return [0, 0, this.originalWidth() / 2, this.originalHeight() / 2];
            }
        },

        is_cropped: function(){
          return this.has('x');
        },

        // For template rendering
        data: function(){
          return $.extend({}, this.attributes, {
            tooSmall: this.tooSmall(),
            unbounded: this.unbounded(),
          });
        },

    })

    var VariationCollection = Backbone.Collection.extend({
        klass: "VariationCollection",
        model: Variation
    })


    return {
        Media: Media,
        Attribute: Attribute,
        MediaCollection: MediaCollection,
        Variation: Variation,
        VariationCollection: VariationCollection
    };

};
