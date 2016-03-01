/*global RemoteMediaShared */
NgRemoteMedia.models = RemoteMediaShared.Models();

Backbone.emulateJSON = true;
Backbone.emulateHTTP = true;

NgRemoteMedia.models.Attribute = NgRemoteMedia.models.Attribute.extend({
    scale: function(media) {
        $.getJSON(this.url('scaler', [media]), this.onScale);
    }
});