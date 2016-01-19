define(['remotemedia/view', './upload', 'remotemedia/templates/browser', 'remotemedia/templates/item', 'remotemedia/templates/nohits'], function(View, UploadView, Browser, Item, NoHits)
{
    return View.extend({
        tpl : null,
        input : null,

        initialize : function(options)
        {
            options = (options || {});
            _.bindAll(this);

            _.extend(this, _.pick(options, ['onSelect']));
            this.collection.on('reset add', this.renderItems);
        },

        events : {
            'keyup .q' : 'search',
            'submit .form-search' : 'search',
            'click .item a' : 'select',
            'click .load-more' : 'page'
        },

        keys : {
            'return .q' : 'search'
        },

        select : function(e) {
            e.preventDefault();
            var id = this.$(e.currentTarget).data('id');
            var model = this.collection.get(id);
            eZExceed.stack.pop({
                id : id,
                host : model.get('host'),
                type : model.get('type'),
                ending : model.get('scalesTo').ending,
                remotemediaId : this.collection.remotemediaId,
                refresh : true,
                model : model.toJSON()
            });
        },

        q : '',

        search : function(e)
        {
            e.preventDefault();
            var q = '';
            if (this.input) {
                q = this.input.val();
            }
            if (q !== this.q) {
                this.$loader.removeClass('hide');
                this.q = q;
                var collection = this.collection;
                var xhr = _.debounce(function()
                {
                    collection.search(q);
                }, 100)();
            }
        },

        render : function() {
            var context = {
                icon : '/extension/ezexceed/design/ezexceed/images/kp/32x32/Pictures.png',
                heading : 'Select media',
                id : this.model.id,
                attribute : this.model.attributes
            };
            this.$el.append(Browser(context));

            this.$loader = this.$('img.loader');
            this.$body = this.$('.remotemedia-thumbs');

            this.renderItems(true);
            this.input = this.$('.q');
            this.enableUpload();
            return this;
        },

        renderItems : function(clear)
        {
            this.$('.loader').addClass('hide');
            var html = '';
            if (this.collection.length) {
                html = this.collection.map(function(item) {
                    return Item(item.attributes);
                }, this);
            }
            else {
                html = NoHits({});
            }

            if (clear)
                this.$body.html(html);
            else
                this.$body.append(html);

            if (this.collection.total > this.collection.length)
                this.$('.load-more').show();
            else
                this.$('.load-more').hide();

            return this;
        },

        page : function()
        {
            this.$('.load-more img').removeClass('hide');
            this.collection.page(this.$('.q').val());
        },

        enableUpload : function()
        {
            this.upload = new UploadView({
                model : this.model,
                uploaded : this.uplodedMedia,
                el : this.$el,
                version : this.model.get('version'),
                browseContainer : 'remotemedia-browser-local-file-container-' + this.model.id,
                browseButton : 'remotemedia-browser-local-file-' + this.model.id
            }).render();
            return this;
        },

        uplodedMedia : function(data)
        {
            eZExceed.stack.pop({
                id : data.id,
                host : data.host,
                type : data.type,
                ending : data.ending,
                remotemediaId : this.collection.remotemediaId
            });
        }
    });
});
