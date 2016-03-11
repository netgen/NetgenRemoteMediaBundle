var UploadView = NgRemoteMedia.views.Upload;

NgRemoteMedia.views.Browser = Backbone.View.extend(NgRemoteMediaShared.browser(UploadView)).extend({
    select: function(e) {
        var model = this.find_model(e);
        this.onSelect({
          id: model.id,
          new_image_selected: !!model,
          model: this.find_model(e)
        });
    },

    uplodedMedia: function(data) {
      this.onSelect(data);        
    }    
});