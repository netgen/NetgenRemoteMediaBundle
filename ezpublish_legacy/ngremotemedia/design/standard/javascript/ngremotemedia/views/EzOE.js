NgRemoteMedia.views.EzOE = Backbone.View.extend(NgRemoteMediaShared.ezoe($, NgRemoteMedia.models.Attribute, NgRemoteMedia.views.Browser, NgRemoteMedia.views.Scaler)).extend({
  render_scaler_view: function(options){
    this.render_scaler_view_in_modal(options);
  },

  browser: function() {
      var modal = new NgRemoteMedia.views.Modal().insert().render();

      this.view = new NgRemoteMedia.views.Browser({
          model: this.model,
          collection: this.model.medias,
          onSelect: function(model){ //also used just for close on upload
              modal.close();
              model && this.changeMedia(model);
          }.bind(this),
          el: modal.show().contentEl
      }).render();

      this.model.medias.search(''); //Fetch

  }
  
});