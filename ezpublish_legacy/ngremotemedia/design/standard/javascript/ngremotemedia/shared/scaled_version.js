window.RemoteMediaShared || (window.RemoteMediaShared = {});

window.RemoteMediaShared.scaled_version = function() {
    return {
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

            this.$el.html(JST.scaledversion(data))
                .attr("version_name", data.name.toLowerCase())
                .attr("id", "eze-ngremotemedia-scale-version-" + data.name.toLowerCase())
                .data('scale', this.model);

            return this;
        }
    };
};