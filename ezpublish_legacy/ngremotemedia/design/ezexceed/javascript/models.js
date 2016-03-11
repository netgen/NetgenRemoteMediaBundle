define(['backbone', 'jquery-safe'], function(Backbone, $) {

    //Initialize common models
    var Models = NgRemoteMediaShared.Models();

    return {
        media: Models.Media,
        attribute: Models.Attribute,
        collection: Models.MediaCollection
    };
});