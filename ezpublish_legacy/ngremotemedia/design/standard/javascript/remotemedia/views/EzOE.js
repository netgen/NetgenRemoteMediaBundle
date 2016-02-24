RemoteMedia.views.EzOE = Backbone.View.extend(RemoteMediaShared.ezoe($, RemoteMedia.models.Attribute, RemoteMedia.views.Browser, RemoteMedia.views.Scaler)).extend({
  render_scaler_view: function(options){
    this.render_scaler_view_in_modal(options);
  },

  browser: function() {
      var modal = new RemoteMedia.views.Modal().insert().render();

      this.view = new RemoteMedia.views.Browser({
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