$(function() {

    $('.ngremotemedia-type').each(function() {
        var wrapper = $(this);
        var container = wrapper.find('.ngremotemedia-buttons');
        if (!container.length) {return;}

        var model = new NgRemoteMedia.models.Attribute({
            id : container.data('id'),
            version : container.data('version'),
            media: {
                available_variations: wrapper.data('available-variations'),
            },
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

    });
});
