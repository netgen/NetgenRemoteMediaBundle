/*globals eZOeGlobalSettings*/
RemoteMedia.models.Media = Backbone.Model.extend({
    prefix: '',

    defaults: function() {
        return {
            id: '',
            host: '',
            type: ''
        };
    },

    initialize: function(options) {
        options = (options || {});
        _.bindAll(this, 'thumb', 'domain', 'removeTag', 'addTag', 'saveAttr');
        if ('prefix' in options)
            this.prefix = options.prefix;
    },

    // parse: function(data){
    //    data.file = _.extend({}, data.metaData); //Create alias for metaData            
    //    delete(data.metaData); //TODO: rename on server
    //    return data;
    // },

    // domain: function() {
    //     return '//' + this.get('host');
    // },

    // url: function(method, extra) {
    //     return ['/ezexceed/ngremotemedia/browse', eZOeGlobalSettings.ez_contentobject_id, this.id, this.version].join('/');
    //     // return this.prefix + '/' + ['remotemedia', 'media', this.id].join('::');
    // },

    saveAttr: function() {
        // RemoteMedia.models.Attribute instance
        var url = this.attr.url('tag', [this.attr.id, this.attr.get('version')]),
            context = this,
            data = this.attributes;

        $.ajax({
            url: url,
            data: data,
            dataType: 'json',
            type: 'POST',
            success: function(response) {
                context.set(response.content);
            }
        });
    },

    // addTag: function(tag) {
    //     var tags = this.get('tags');
    //     tags.push(tag);
    //     this.set({
    //         tags: _.uniq(tags)
    //     }, {
    //         silent: true
    //     });
    //     return this;
    // },

    // removeTag: function(rmTag) {
    //     var tags = _(this.get('tags')).filter(function(tag) {
    //         return tag !== rmTag;
    //     });
    //     this.set({
    //         tags: tags
    //     }, {
    //         silent: true
    //     });
    //     return this;
    // },

    // // Generate thumb url for a given size
    // thumb: function(width, height, filetype) {
    //     filetype = (filetype || 'jpg');
    //     return this.domain() + '/' + width + 'x' + height + '/' + this.id + '.' + filetype;
    // }
});




RemoteMedia.models.MediaCollection = Backbone.Collection.extend({
    model: RemoteMedia.models.Media,

    // Must end in trailing slash
    prefix: '/',

    attr: null,

    total: 0,

    limit: 25,

    remotemediaId: null,

    initialize: function() {
        _.bindAll(this);
    },

    url: function() {
        return ['/ezexceed/ngremotemedia/browse', eZOeGlobalSettings.ez_contentobject_id, this.attr.attributes.id, this.attr.attributes.version].join('/');
    },

    search: function(q, filters) {
        var data = (filters ||  {});
        q && (data.q = q);
        data.limit = this.limit;
        return $.getJSON(this.url(), data, this.onSearch);
    },

    onSearch: function(resp) {
        if (resp && resp.results) {
            this.remotemediaId = resp.remotemediaId;
            this.total = resp.results.total;
            this.reset(resp.results.hits);
            this.trigger('search', resp);
        }
    },

    page: function(q) {
        if (this.length < this.total) {
            var data = {};
            q && (data.q = q);
            data.limit = this.limit;
            data.offset = this.length;
            return $.getJSON(this.url(), data, this.onPage);
        }
    },

    onPage: function(resp) {
        if (resp && 'content' in resp && 'results' in resp.content) {
            this.remotemediaId = resp.content.remotemediaId;
            this.add(resp.content.results.hits);
            this.trigger('page', resp);
        }
    }
});