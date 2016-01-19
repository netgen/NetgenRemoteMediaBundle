$(function() {
    $('.remotemedia-type').each(function() {
        var wrapper = $(this);
        var container = wrapper.find('.remotemedia-buttons');
        if (container.length) {
            var model = new RemoteMedia.models.Attribute({
                id : container.data('id'),
                prefix : container.data('prefix'),
                version : container.data('version')
            });
            var media = new RemoteMedia.models.Media({
                id : container.find('.media-id').val(),
                prefix : container.data('prefix')
            });
            media.attr = model;

            var controller = new RemoteMedia.views.RemoteMedia({
                el : container,
                wrapper : wrapper,
                model : model,
                destination : container.find('.media-id'),
                host : container.find('.media-host'),
                type : container.find('.media-type'),
                ending : container.find('.media-ending')
            }).render();

            var tagger = new RemoteMedia.views.Tagger({
                el : wrapper.find('.tagger'),
                model : media
            }).render();

            var bootstrapData = wrapper.data('bootstrap-media');
            if (bootstrapData) media.set(bootstrapData);

            wrapper.data('objects', {
                media : media,
                tagger : tagger,
                model : model,
                controller : controller
            });
        }
    });
});
