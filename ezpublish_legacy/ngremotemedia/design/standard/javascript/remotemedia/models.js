/*global RemoteMediaShared */
RemoteMedia.models = RemoteMediaShared.Models();

Backbone.emulateJSON = true;
Backbone.emulateHTTP = true;

RemoteMedia.models.Attribute = RemoteMedia.models.Attribute.extend({
    scale: function(media) {
        $.getJSON(this.url('scaler', [media]), this.onScale);
    }
});