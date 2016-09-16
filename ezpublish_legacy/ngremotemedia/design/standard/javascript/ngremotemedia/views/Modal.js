NgRemoteMedia.views.Modal = Backbone.View.extend({
    // el construction information
    tagName: 'div',
    id: 'ngremotemedia-modal',

    // Template for containing data
    template: '<div class="backdrop"/><div class="content"><div class="in"></div></div>',

    // To hold a subview
    view: null,

    initialize: function() {
        _.bindAll(this);
        return this;
    },

    events: {
        'click .close': 'close'
    },

    render: function() {
        this.$el.html(this.template).hide();
        this.contentEl = this.$('.in');
        this.contentEl.before('<a href="#" class="close">X</a>');

        this.delegateEvents();

        return this;
    },

    insert: function(){
      $(document.body).prepend(this.$el);
      return this;
    },

    show: function() {
        this.$el.show();
        return this;
    },

    close: function() {
        this.trigger('close');
        this.$el.hide();
        this.view && this.view.remove();
        this.remove();
    }
});