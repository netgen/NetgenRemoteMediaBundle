$(function() {

    $('.ngremotemedia-type').each(function() {
        var wrapper = $(this);
        var container = wrapper.find('.ngremotemedia-buttons');
        console.log(container);
        if (container.length) {

            var bootstrapData = wrapper.data('bootstrap-media');

            var media_attributes = _.extend({}, bootstrapData, {
                prefix : container.data('prefix')
            });

            var model = new NgRemoteMedia.models.Attribute({
                id : container.data('id'),
                prefix : container.data('prefix'),
                version : container.data('version'),
                media: media_attributes,
                contentObjectId: RemoteMediaSettings.ez_contentobject_id
            }, {parse: true});

            var controller = new NgRemoteMedia.views.NgRemoteMedia({
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
