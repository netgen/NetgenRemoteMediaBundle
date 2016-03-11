/*global NgRemoteMediaShared */
NgRemoteMedia.models = NgRemoteMediaShared.Models();

Backbone.emulateJSON = true;
Backbone.emulateHTTP = true;

NgRemoteMedia.models.Attribute = NgRemoteMedia.models.Attribute.extend({
    scale: function(media) {
        $.getJSON(this.url('scaler', [media]), this.onScale);
    }
});