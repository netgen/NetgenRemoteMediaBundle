var UploadView = RemoteMedia.views.Upload;

RemoteMedia.views.Browser = Backbone.View.extend(RemoteMediaShared.browser(UploadView)).extend({
    select: function(e) {
        this.onSelect(this.find_model(e));
    }
});