RemoteMedia.views.Modal = Backbone.View.extend({
    // el construction information
    tagName : 'div',
    id : 'remotemedia-modal',

    // Template for containing data
    template : '<div class="backdrop"/><div class="content"/>',

    // To hold a subview
    view : null,

    initialize : function(options)
    {
        _.bindAll(this);
        return this;
    },

    events : {
        'click .close' : 'close'
    },

    render : function()
    {
        this.$el.html(this.template).hide();
        this.contentEl = this.$('.content');

        this.delegateEvents();

        return this;
    },

    show : function() {
        this.$el.show();
        return this;
    },

    close : function(e) {
        this.trigger('close');
        this.$el.hide();
        if (this.view !== null)
            this.view.remove();
    }
});

