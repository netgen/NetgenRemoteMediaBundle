$(function() {

    $('.remotemedia-type').each(function() {
        var wrapper = $(this);
        var container = wrapper.find('.remotemedia-buttons');
        if (container.length) {
            
            var bootstrapData = wrapper.data('bootstrap-media');
            

            var model = new RemoteMedia.models.Attribute({
                id : container.data('id'),
                prefix : container.data('prefix'),
                version : container.data('version')
            });

            //MediaCollection //TODO: optimize this
            model.medias.id = model.id;
            model.medias.version = model.get('version');

            var media = new RemoteMedia.models.Media(_.extend({}, bootstrapData, {
                // id : container.find('.media-id').val(),
                prefix : container.data('prefix'),
                attr: model
            }), {parse: true});
            // media.attr = model;

            var controller = new RemoteMedia.views.RemoteMedia({
                el : wrapper,
                model : model,
                destination : container.find('.media-id'),
                host : container.find('.media-host'),
                type : container.find('.media-type'),
                ending : container.find('.media-ending')
            }).render();

            var tagger = new RemoteMedia.views.Tagger({
                el : wrapper.find('.remotemedia-tags'),
                model : media
            }).render();

            

            wrapper.data('objects', {
                media : media,
                tagger : tagger,
                model : model,
                controller : controller
            });
        }
    });
});
