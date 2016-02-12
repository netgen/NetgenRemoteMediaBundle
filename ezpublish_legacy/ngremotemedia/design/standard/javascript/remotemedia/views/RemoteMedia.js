RemoteMedia.views.RemoteMedia = Backbone.View.extend({
    // Holds current active subview
    view: null,
    destination: null,

    initialize: function(options) {
        options = (options || {});
        _.bindAll(this, 'render', 'search', 'close', 'enableUpload', 'changeMedia');       
        this.listenTo(this.model, 'change', this.render);
        return this;
    },

    convert_versions: function(versions){
        if(_.isArray(versions)){return versions;}
        return _.map(versions, function(size, name) {
            return {
                size: size.split ? _.map(size.split('x'), function(n){return parseInt(n, 10);}) : size,
                name: name
            };
        });
    },

    events: {
        'click .remotemedia-remote-file': 'search',
        'click .remotemedia-scale': 'scaler',
        'click .remotemedia-remove-file': 'remove'
    },

    render: function() {
        this.model.get('content') && this.$el.html($(this.model.get('content')).html());
        this.destination = this.$('.media-id');
        this.renderTags().enableUpload();
        return this;
    },


    renderTags: function() {
        new RemoteMedia.views.Tagger({
            el: this.$('.remotemedia-tags').off(),
            model: this.model.get('media')
        }).render();
        return this;
    },    

    remove: function(e) {
        e.preventDefault();
        this.destination.attr('value', 'removed');
        this.$('.remotemedia-image, .remotemedia-scale, .remotemedia-remove-file').remove();
        return this;
    },

    changeMedia: function(new_media){
        this.model.change_media(new_media.id);
        return this;
    },




    enableUpload: function() {
        this.upload = new RemoteMedia.views.Upload({
            model: this.model,
            uploaded: function(resp){
                this.model.set(this.model.parse(resp.model_attributes));
            }.bind(this),
            el: this.$el,
            // prefix: this.$el.data('prefix'),
            version: this.model.get('version')
        });
        this.upload.render();
        return this;
    },

    search: function() {
        var modal = new RemoteMedia.views.Modal().insert().render();
        window.tmp = this.model.medias;
        this.view = new RemoteMedia.views.Browser({
            model: this.model,
            collection: this.model.medias,
            onSelect: function(model){
                modal.close();
                this.changeMedia(model);
            }.bind(this),
            el: modal.show().contentEl
        }).render();

        this.model.medias.search(''); //Fetch

    },

    // Open a scaling gui
    scaler: function(e) {
        if (!(this.destination && this.destination.val())) {
            return false;
        }

        var modal = new RemoteMedia.views.Modal().insert().render();

        var node = $(e.currentTarget);

        var available_versions = this.convert_versions(node.data('versions'));
        this.model.set('available_versions', available_versions, {silent: true});

        var settings = {
            mediaId: this.destination.val(),
            //versions: this.convert_versions(node.data('versions')),
            trueSize: node.data('truesize'),
            host: this.host.val(),
            type: this.type.val(),
            model: this.model,
            el: modal.show().contentEl
        };
        this.view = new RemoteMedia.views.Scaler(settings);
        this.model.scale(settings.mediaId);
    },

    close: function() {
        if (this.view && ('close' in this.view)) {
            this.view.close();
        }
    }
});