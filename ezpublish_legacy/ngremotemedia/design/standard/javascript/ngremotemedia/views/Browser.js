(function() {
    var UploadView = NgRemoteMedia.views.Upload;
    var template = NgRemoteMedia.template;

    NgRemoteMedia.views.Browser = Backbone.View.extend({
        tpl: null,
        input: null,

        initialize: function(options) {
            this.debounced_search =_.debounce(function(){
                this.collection.search(this.query);
            }, 250);

            options && _.extend(this, _.pick(options, ['onSelect']));

            this.query = {};

            this.collection.on('reset add', this.renderItems, this);
            this.folders_xhr = $.get(NgRemoteMediaShared.url('/ngremotemedia/folders'));
        },

        events: {
            'keyup .q': 'search',
            'change .ngremotemedia-remote-media-type-select': 'changeMediaType',
            'change .ngrm-by input': 'changeSearchType',
            'submit .form-search': 'search',
            'click .item a': 'select',
            'click .load-more': 'page'
        },

        keys: {
            'return .q': 'search'
        },


        changeMediaType: function(e){
          this.query.mediatype = $(e.target).val();
          this.$loader.removeClass('ngmr-hide');
          this.collection.reset([]);
          this.renderItems(true);
          this.collection.search(this.query);
          return this;
        },

        changeSearchType: function(e){
          this.query.search_type = $(e.target).val();
          this.$loader.removeClass('ngmr-hide');
          this.collection.reset([]);
          this.renderItems(true);
          this.collection.search(this.query);
          return this;
        },

        select: function(e) {
            var model = this.find_model(e);
            this.onSelect({
              id: model.id,
              new_image_selected: !!model,
              model: this.find_model(e)
            });
        },

        find_model: function(e){
            e.preventDefault();
            var id = this.$(e.currentTarget).data('id');
            return this.collection.get(id);
        },


        search: function(e) {
            e.preventDefault();
            var q = '';
            if (this.input) {
                q = this.input.val().trim();
            }

            // Only do search if the there are at least 2 characters
            // Do search if query is empty
            if(q.length !== 0 && q.length <= 2 ){return;}

            if (q !== this.query.q) {
                this.$loader.removeClass('ngmr-hide');
                this.query.q = q;
                this.debounced_search();
            }
        },


        renderFolderSearch: function() {

            var $search = this.$('.ngremotemedia-remote-folders').off().prop("disabled", true);
            var $loadingOption = $search.find('option.loading');
            var originalText = $loadingOption.text();
            $loadingOption.text('Loading...')
            var data = $search.data();
            var self = this;


            this.folders_xhr.done(function(data){
                var $options = $.map(data, function(item){
                    return $('<option>', {value: item.id, text: item.name})
                });
                $loadingOption.remove();
                $search.append($options);
                $search.prop("disabled", false);
            });

            $search.select2({
                tags: true,
                placeholder: data.placeholderText,
                createTag: function (tag) {
                    return {
                        id: tag.term,
                        text: tag.term,
                        isNew : true
                    };
                }
            }).on('select2:selecting', function (e) {
                var d = e.params.args.data;
                if(d.isNew && !confirm(NgRemoteMedia.t('Create new folder?'))){
                    e.preventDefault();
                }else{
                    self.query.folder = d.id;
                    self.collection.search(self.query);
                }
                self.model.set('folder', self.query.folder);
            });

        },

        render: function() {
            var context = {
                id: this.model.id,
                attribute: this.model.attributes
            };
            this.$el.append(template('browser', context));

            this.$loader = this.$('.loader');
            this.$body = this.$('.ngremotemedia-thumbs');

            this.renderItems(true);
            this.input = this.$('.q');
            this.enableUpload();
            this.renderFolderSearch();
            this.$('.ngremotemedia-remote-media-type-select').select2();
            return this;
        },

        renderItems: function(clear) {
            this.$('.loader').addClass('ngmr-hide');
            var html = '';
            if (this.collection.length) {
                html = this.collection.map(function(item) {
                    return template('item', $.extend({}, item.attributes, {
                        is_image: item.is_image(),
                        is_video: item.is_video(),
                        is_other: item.is_other()
                    }));
                }, this);
            } else {
                html = template('nohits', {
                    loading: clear === true
                });
            }


            this.$body[clear ? 'html' : 'append'](html);
            var show_load_more = this.collection.load_more;
            this.$('.load-more')[show_load_more ? 'show' : 'hide']();

            return this;
        },

        page: function() {
            this.$('.load-more .loader').removeClass('ngmr-hide');
            this.collection.page(this.query);
        },

        enableUpload: function() {
            this.upload = new UploadView({
                model: this.model,
                uploaded: this.uplodedMedia.bind(this),
                el: this.$el,
                version: this.model.get('version')
            }).render();
            return this;
        },

        uplodedMedia: function(data) {
          this.onSelect(data);
        }
    });

})();
