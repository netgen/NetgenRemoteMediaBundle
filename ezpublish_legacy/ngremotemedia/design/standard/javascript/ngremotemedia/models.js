/*global NgRemoteMediaShared */
(function(){

    window.NgRemoteMediaShared || (window.NgRemoteMediaShared = {});

    Backbone.emulateJSON = true;
    Backbone.emulateHTTP = true;


    window.NgRemoteMediaShared.config = function() {

        return {
            plupload_swf: '/extension/ngremotemedia/design/standard/javascript/libs/plupload/Moxie.swf'
        };
    };



    window.NgRemoteMediaShared.url = function(url){
        return [RemoteMediaSettings.url_prefix, url].join('/').replace(/\/+/g, '/');
    };


    var Attribute = Backbone.Model.extend({
        klass: "Attribute",
        medias: null,

        initialize: function(attributes) {
            _.bindAll(this);
            this.medias = new MediaCollection();        },

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
            if (data.media) {
                data.media.attr = this;

                var media = this.get('media');
                if(media){
                    var parsed = media.parse(data.media);
                    data.media = media.set(parsed);
                }else{
                    data.media = new Media(data.media, {parse: true});
                }
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


        scale: function(media) {
            $.getJSON(this.url('scaler', [media]), this.onScale);
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
        }

    });



    /**
     * Model Media
     */
    var Media = Backbone.Model.extend({
        klass: "Media",
        initialize: function() {
            this.variations || (this.variations = new VariationCollection());
        },

        defaults: function() {
            return {
                custom_attributes: {}
            };
        },

        originalWidth: function(){
          return this.get('metaData').width;
        },

        originalHeight: function(){
          return this.get('metaData').height;
        },

        originalSize: function() {
            return [this.originalWidth(), this.originalHeight()];
        },

        alt_text: function(){
          return this.get('custom_attributes').alttext || this.get('metaData').alt_text;
        },

        caption: function(){
          return this.get('custom_attributes').caption || this.get('metaData').caption;
        },

        css_class: function(){
          return this.get('custom_attributes').cssclass;
        },


        is_image: function () {
            return this.get('mediaType') === 'image';
        },

        is_video: function () {
            return this.get('mediaType') === 'video';
        },


        is_other: function () {
            return this.get('mediaType') === 'other';
        },

        parse_coords: function(c) {
            return {
                x: c[0],
                y: c[1],
                w: c[2],
                h: c[3],
            }
        },

        parse: function(data) {
            this.variations || (this.variations = new VariationCollection());

            var media = this;

            data.id = data.resourceId;

            if(!_.isEmpty(data.available_variations)){
                data.available_variations = _.reduce(data.available_variations, function(memo, size, name){
                    memo[name] = {
                        possibleWidth: size[0],
                        possibleHeight: size[1],
                    };
                    return memo;
                }, {});
            }else{
                data.available_variations = this.get('available_variations') || {};
            }


            // Custom attributes are used only with ezoe (online editor)
            var custom_attributes = data.custom_attributes;
            data.variations || (data.variations = {})

            if(custom_attributes && custom_attributes.coords){

                data.id = data.resourceId = custom_attributes.resourceId;

                data.metaData = {
                    alt_text: custom_attributes.alttext,
                    caption: custom_attributes.caption
                }

                data.variations[custom_attributes.version] = this.parse_coords(custom_attributes.coords);
            }



            if ('variations' in data) {
                // Mark variations from server as cropped
                _.each(data.variations, function(value) { value.is_cropped = true; });

                var variations = $.extend({}, data.available_variations, data.variations);
                var x = _.map(variations, function(value, name){
                    return $.extend({name: name, media: media}, value);
                });

                this.variations.set(x, {parse: true})
                this.variations.media = this;

                delete(data.variations);
            }


            return data;
        },


        save_variations: function() {
            var attribute_model = this.get('attr');
            var url = [NgRemoteMediaShared.url("/ngremotemedia/save"), attribute_model.get('contentObjectId'), attribute_model.id, attribute_model.get('version')].join('/');

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


        url: function(){
            return [NgRemoteMediaShared.url('/ngremotemedia/fetch_ezoe')].join('/');
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
            if(_.contains(['x', 'y', 'w', 'h', 'x2', 'y2', 'possibleWidth', 'possibleHeight' ], attr)){
              attributes[attr] = Math.round(value) || 0;
            }
          })
          return Backbone.Model.prototype.set.call(this, attributes, options)

        },

        media: function(){
          return this.collection.media;
        },

        originalWidth: function(){
          return this.media().originalWidth();
        },

        originalHeight: function(){
          return this.media().originalHeight();
        },

        originalSize: function() {
            return [this.originalWidth(), this.originalHeight()];
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
                return [0, 0, this.get('possibleWidth') / 2, this.get('possibleHeight') / 2];
            }
        },

        ezoe_coords: function() {
            var c = this.attributes;
            return [c.x, c.y, c.w, c.h].join(',');
        },

        is_cropped: function(){
          return this.get('is_cropped');
        },


        generate_image: function(){
            var variation = {};
            variation[this.id] = _.pick(this.attributes, 'x', 'y', 'w', 'h');

            return Backbone.sync('create', this, {
                url: NgRemoteMediaShared.url('/ngremotemedia/generate'),
                data: {
                    resourceId: this.get('media').id,
                    variation: variation,
                },
                transform: false
            });
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



    // Expose models
    NgRemoteMedia.models = {
        Media: Media,
        Attribute: Attribute,
        MediaCollection: MediaCollection,
        Variation: Variation,
        VariationCollection: VariationCollection
    };


})(); //end
