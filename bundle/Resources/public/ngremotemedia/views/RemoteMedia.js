NgRemoteMedia.views.NgRemoteMedia = Backbone.View.extend({
    // Holds current active subview
    view: null,
    destination: null,

    initialize: function(options) {
        options = (options || {});
        this.listenTo(this.model, 'change', this.render);
        return this;
    },

    events: {
        'click .ngremotemedia-remote-file': 'search',
        'click .ngremotemedia-scale': 'scaler',
        'click .ngremotemedia-remove-file': 'remove'
    },

    render: function() {
        var content = this.model.get('content'), html;

        if (content){
            // @todo: ugly hack
            var formId = "#ngremotemedia-buttons-" + this.model.id;
            $(formId + " .media-id").val(content.resourceId);
            $(formId + " .media-url").val(content.secure_url);
            $(formId + " .media-size").val(content.size);
            $(formId + " .media-type").val(content.mediaType);

            var tagsId = '#ngremotemedia-tags-' + this.model.id;
            $(tagsId + " .media-tags").val(content.metaData.tags.join());

            $(formId + " .media-width").val(content.metaData.width);
            $(formId + " .media-height").val(content.metaData.height);

            var thumb = this.thumb(content.secure_url, 600, 600);
            $("#ngremotemedia-preview-" + this.model.id + " img").attr({"src": thumb});

            //html = $('<div />').html(content).find('.ngremotemedia-type').html(); //@todo: wtf did this do?
            //this.$el.html(html);
        }

        this.destination = this.$('.media-id');
        this.renderTags().enableUpload();

        return this;
    },

    // Generate thumb url for a given size
    thumb: function(secure_url, width, height) {
        var url = secure_url.split(/\/v\d+\//);
        return [url[0], 'c_limit,' + 'w_' + width + ',h_' + height, url[1]].join("/");
    },

    renderTags: function() {
        var $tags = this.$('.ngremotemedia-newtags');
        var data = $tags.data();
        $tags.off().select2({
            placeholder: NgRemoteMedia.t('Add tag'),
            tags: true,
            allowClear: true
        });

        return this;
    },

    remove: function(e) {
        e.preventDefault();
        this.destination.attr('value', 'removed');
        this.$('.ngremotemedia-image, .ngremotemedia-scale, .ngremotemedia-remove-file').remove();
        return this;
    },

    changeMedia: function(new_media){
        this.model.change_media(new_media);

        return this;
    },

    enableUpload: function() {
        this.upload = new NgRemoteMedia.views.Upload({
            model: this.model,
            uploaded: function(resp){
                resp && this.model.set(this.model.parse(resp.model_attributes));
                tmp.get('media').variations.reset([]);
            }.bind(this),
            el: this.$el,
            version: this.model.get('version')
        });
        this.upload.render();
        return this;
    },

    search: function() {
        var modal = new NgRemoteMedia.views.Modal().insert().render();

        this.view = new NgRemoteMedia.views.Browser({
            model: this.model,
            collection: this.model.medias,
            onSelect: function(model){ //also used just for close on upload
                modal.close();
                model && this.changeMedia(model);
            }.bind(this),
            el: modal.show().contentEl
        }).render();

        this.model.medias.search(''); //Fetch

    },

    // Open a scaling gui
    scaler: function() {
        var modal = new NgRemoteMedia.views.Modal().insert().render(),
            scaler_view;

        this.model.fetch({
            transform: false,
        }).done(function(){

            scaler_view = new NgRemoteMedia.views.Scaler({
                model: this.model.get('media'),
                el: modal.show().contentEl
            }).render();

            scaler_view.on('saved', function(){
                modal.close();
            })

        }.bind(this));


        modal.on('close', function(){
            scaler_view.close();
        });



    },

    close: function() {
        this.view && this.view.close && this.view.close();
    }
});
