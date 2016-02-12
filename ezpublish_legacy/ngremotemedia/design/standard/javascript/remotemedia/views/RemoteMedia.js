RemoteMedia.views.RemoteMedia = Backbone.View.extend({
    // Holds current active subview
    view: null,
    destination: null,
    host: null,
    type: null,
    wrapper: null,

    container: false,

    initialize: function(options) {
        options = (options || {});
        _.bindAll(this, 'render', 'search', 'close', 'enableUpload', 'changeMedia');

        // DOM node to store selected media id into
        this.destination = options.destination;
        this.host = options.host;
        this.type = options.type;
        this.ending = options.ending;
        this.wrapper = options.wrapper;

        // if ('container' in options) {
        //     this.container = options.container;
        // } else {
        //     // this.container = new RemoteMedia.views.Modal().render();
        //     // this.container.$el.prependTo('body');
        // }
        // // this.container.bind('close', this.close);
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
        // this.container.render();
        this.enableUpload();
        // this.delegateEvents();
        return this;
    },

    remove: function(e) {
        e.preventDefault();
        this.destination.val('');
        this.host.val('');
        this.type.val('');
        this.ending.val('');
        this.wrapper.find('.remotemedia-image').remove();
        this.$('.remotemedia-scale').remove();
        this.$('.remotemedia-remove-file').remove();
        return this;
    },

    changeMedia: function(model) {
        this.destination.val(model.id);

        var url = ["/standard/ngremotemedia/change", RemoteMediaShared.config().currentObjectId, this.model.id, this.model.get('version')].join('/');

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                resource_id: model.id,
                user_id: RemoteMediaShared.config().user_id
            }  
        }).done(this.refresh.bind(this));

        // this.host.val(data.host);
        // this.type.val(data.type);
        // this.ending.val(data.ending);
        // _.delay(this.refresh.bind(this), 1000);
        // this.refresh();
        return this;
    },


    refresh: function(data){
        var html = $(jQuery.parseHTML(data.content.trim())).html();
        this.$el.html(html);
        _.delay(this.enableUpload.bind(this), 100);
    },


    enableUpload: function() {
        this.upload = new RemoteMedia.views.Upload({
            model: this.model,
            uploaded: function(data){
                this.refresh(data.html);
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