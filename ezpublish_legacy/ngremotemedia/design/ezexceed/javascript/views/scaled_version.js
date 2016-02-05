define(['remotemedia/view', 'remotemedia/templates/scaledversion'], function(View, ScaledVersion) {
    return View.extend({
        tagName: 'li',

        initialize: function(options) {
            _.extend(this, _.pick(options, ['media']));
        },

        render: function() {
            var data = _.extend({}, this.model);

            data.width = data.size[0] || 0;
            data.height = data.size[1] || 0;

            var file = this.media.get('file');
            this.model.toSmall = data.toSmall = !(file.width >= data.width && file.height >= data.height);
            this.model.unbounded = !data.width || !data.height; //if any dimension is 0

            this.$el.html(ScaledVersion(data))
                .attr("version_name", data.name.toLowerCase())
                .attr("id", "eze-remotemedia-scale-version-" + data.name.toLowerCase())
                .data('scale', this.model);

            return this;
        }

    });
});