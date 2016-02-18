$(function() {

    $('.remotemedia-type').each(function() {
        var wrapper = $(this);
        var container = wrapper.find('.remotemedia-buttons');
        if (container.length) {
            
            var bootstrapData = wrapper.data('bootstrap-media');

            var media_attributes = _.extend({}, bootstrapData, {
                prefix : container.data('prefix')
            });

            var model = new RemoteMedia.models.Attribute({
                id : container.data('id'),
                prefix : container.data('prefix'),
                version : container.data('version'),
                media: media_attributes
            }, {parse: true});


            model.medias.id = model.id;
            model.medias.version = model.get('version');

            var controller = new RemoteMedia.views.RemoteMedia({
                el : wrapper,
                model : model
            }).render();


            wrapper.data('objects', {
                model : model,
                controller : controller
            });
        }
    });
});
