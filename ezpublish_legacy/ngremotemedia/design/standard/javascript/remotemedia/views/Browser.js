var UploadView = RemoteMedia.views.Upload;

RemoteMedia.views.Browser = Backbone.View.extend(RemoteMediaShared.browser(UploadView)).extend({
    select: function(e) {
        this.onSelect(this.find_model(e));
    },

    uplodedMedia: function(data) {
        data && this.model.set(this.model.parse(data.model_attributes));
        setTimeout(function(){
          this.onSelect();
        }.bind(this), 200);
    }    
});