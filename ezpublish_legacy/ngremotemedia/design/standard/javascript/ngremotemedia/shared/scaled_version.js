window.NgRemoteMediaShared || (window.NgRemoteMediaShared = {});

window.NgRemoteMediaShared.scaled_version = function() {
    return {
        tagName: 'li',

        render: function() {
            var data = this.model.data();

            this.$el.html(JST.scaledversion(data))
                .attr("version_name", data.id)
                .data('model', this.model);

            return this;
        }
    };
};
