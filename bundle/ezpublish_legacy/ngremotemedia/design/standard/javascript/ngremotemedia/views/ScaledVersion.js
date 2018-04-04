(function(){
    var template = NgRemoteMedia.template;

    NgRemoteMedia.views.ScaledVersion = Backbone.View.extend({
        tagName: 'li',

        className: function() {
          if(this.model.is_cropped()){
            return 'cropped';
          }
        },

        initialize: function(){
          this.listenTo(this.model, 'change:crop_changed', this.mark_as_changed);
          return this;
        },

        mark_as_changed: function() {
            this.$el.addClass('changed');
        },

        render: function() {
            var data = this.model.data();

            this.$el.html(template('scaledversion', data))
                .attr("version_name", this.model.id)
                .data('model', this.model);

            return this;
        }
    });
})();
