(function(){
    var template = NgRemoteMedia.template;

    NgRemoteMedia.views.Modal = Backbone.View.extend({
        id: 'ngremotemedia-modal',

        // To hold a subview
        view: null,

        events: {
            'click .js-close': 'close'
        },

        render: function() {
            this.$el.html(template('modal')).hide();
            this.contentEl = this.$('.in');
            return this;
        },

        insert: function(){
          $(document.body).append(this.$el);
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
})();
