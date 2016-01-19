RemoteMedia.views.Browser = Backbone.View.extend({
    tpl : null,
    $input: null,
    initialize : function(options)
    {
        _.bindAll(this);

        this.listenTo(this.collection, {
            reset: this.renderItems
        });
        this.onSelect = options.onSelect;

        this.tpl = {
            browser : Handlebars.compile($('#tpl-remotemedia-browser').html()),
            item : Handlebars.compile($('#tpl-remotemedia-item').html())
        };
    },

    events : {
        'click button.search' : 'search',
        'submit form.search' : 'search',
        'click .item a' : 'select'
    },

    select : function(e) {
        e.preventDefault();
        var node = $(e.currentTarget).parent();
        this.onSelect({
            id : node.data('id'),
            host : node.data('host'),
            type : node.data('type'),
            ending : node.data('ending')
        });
        this.$('.close').click();
    },

    search : function(e)
    {
        e.preventDefault();
        var $btn = this.$('button.search');
        var prevVal = $btn.text();
        $btn.text('Searching ...').addClass('working');
        this.collection
            .search(this.$input.val())
            .done(function() {
                $btn.text(prevVal).removeClass('working');
            });
    },

    render : function() {
        this.$el.html(
            this.tpl.browser({
                tr : _RemoteMediaTranslations
            })
        );
        this.renderItems();
        this.$input = this.$('[type=text]');
        return this;
    },

    renderItems : function()
    {
        var template = this.tpl.item;
        var views = this.collection.map(function(item) {
            var view = $(template(item.attributes));
            view.data({
                id : item.id,
                host : item.get('host'),
                type : item.get('type'),
                ending : item.get('scalesTo').ending
            });
            return view[0];
        });
        if (views.length > 0) {
            this.$('.body').html(views);
        }

        return this;
    }
});
