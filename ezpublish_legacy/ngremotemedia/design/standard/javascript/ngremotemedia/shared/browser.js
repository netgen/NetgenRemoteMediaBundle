window.NgRemoteMediaShared || (window.NgRemoteMediaShared = {});

window.NgRemoteMediaShared.browser = function(UploadView) {
    return {
        tpl: null,
        input: null,

        initialize: function(options) {
            _.bindAll(this);

            this.debounced_search =_.debounce(function(){
                this.collection.search(this.query);
            }, 250);

            options && _.extend(this, _.pick(options, ['onSelect']));

            this.query = {};

            this.collection.on('reset add', this.renderItems);
            this.folders_xhr = $.get(NgRemoteMediaShared.url('/ngremotemedia/folders'));
        },

        events: {
            'keyup .q': 'search',
            'change .ngremotemedia-remote-media-type-select': 'changeMediaType',
            'submit .form-search': 'search',
            'click .item a': 'select',
            'click .load-more': 'page'
        },

        keys: {
            'return .q': 'search'
        },


        changeMediaType: function(e){
          this.query.mediatype = $(e.target).val();
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
                this.$loader.removeClass('hide');
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
                // data: data,
                createTag: function (tag) {
                    return {
                        id: tag.term,
                        text: tag.term,
                        isNew : true
                    };
                }
            }).on('select2:selecting', function (e) {
                console.log(e);
                var d = e.params.args.data;
                if(d.isNew && !confirm(NgRemoteMedia.t('Create new folder?'))){
                    e.preventDefault();
                }else{
                    self.query.folder = d.text;
                    self.collection.search(self.query);
                }
            });

        },

        render: function() {
            var context = {
                icon: '/extension/ngremotemedia/design/standard/images/pictures32x32.png',
                heading: 'Select media',
                id: this.model.id,
                attribute: this.model.attributes
            };
            this.$el.append(JST.browser(context));

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
            this.$('.loader').addClass('hide');
            var html = '';
            if (this.collection.length) {
                html = this.collection.map(function(item) {
                    return JST.item(item.attributes);
                }, this);
            } else {
                html = JST.nohits();
            }


            this.$body[clear ? 'html' : 'append'](html);
            var show_load_more = this.collection.total > this.collection.length;
            this.$('.load-more')[show_load_more ? 'show' : 'hide']();

            return this;
        },

        page: function() {
            this.$('.load-more img').removeClass('hide');
            this.collection.page(this.q);
        },

        enableUpload: function() {
            this.upload = new UploadView({
                model: this.model,
                uploaded: this.uplodedMedia,
                el: this.$el,
                version: this.model.get('version')
            }).render();
            return this;
        },

        uplodedMedia: function(data) {
          this.onSelect(data);
        }
    };
};
