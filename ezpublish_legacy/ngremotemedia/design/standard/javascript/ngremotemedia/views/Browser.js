var UploadView = NgRemoteMedia.views.Upload;

NgRemoteMedia.views.Browser = Backbone.View.extend(RemoteMediaShared.browser(UploadView)).extend({
    select: function(e) {
        this.onSelect(this.find_model(e));
    },

    uplodedMedia: function(data) {
      this.onSelect(data);        
    }    
});