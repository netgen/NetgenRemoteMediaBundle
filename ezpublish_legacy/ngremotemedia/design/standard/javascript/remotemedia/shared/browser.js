window.RemoteMediaShared || (window.RemoteMediaShared = {});

window.RemoteMediaShared.browser = function(UploadView) {
    return {
        tpl: null,
        input: null,

        initialize: function(options) {
            _.bindAll(this);

            this.debounced_search =_.debounce(function(){
                this.collection.search(this.q);
            }, 250);

            options && _.extend(this, _.pick(options, ['onSelect'])); 
            this.collection.on('reset add', this.renderItems);
        },

        events: {
            'keyup .q': 'search',
            'submit .form-search': 'search',
            'click .item a': 'select',
            'click .load-more': 'page'
        },

        keys: {
            'return .q': 'search'
        },

        select: function(e) {
            var model = this.find_model(e);
            eZExceed.stack.pop({
                id: model.id,
                new_image_selected: true,
                model: model.toJSON()
            });
        },

        find_model: function(e){
            e.preventDefault();
            var id = this.$(e.currentTarget).data('id');
            return this.collection.get(id);
        },

        q: '',


        search: function(e) {
            e.preventDefault();
            var q = '';
            if (this.input) {
                q = this.input.val().trim();
            }

            // Only do search if the there are at least 2 characters
            // Do search if query is empty
            if(q.length !== 0 && q.length <= 2 ){return;}

            if (q !== this.q) {
                this.$loader.removeClass('hide');
                this.q = q;
                this.debounced_search();
            }
        },

        render: function() {
            var context = {
                icon: '/extension/ezexceed/design/ezexceed/images/kp/32x32/Pictures.png',
                heading: 'Select media',
                id: this.model.id,
                attribute: this.model.attributes
            };
            this.$el.append(JST.browser(context));

            this.$loader = this.$('img.loader');
            this.$body = this.$('.remotemedia-thumbs');

            this.renderItems(true);
            this.input = this.$('.q');
            this.enableUpload();
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
            data && eZExceed.stack.pop({
                id: data.id,
                new_image_selected: true
            });
        }
    };
};