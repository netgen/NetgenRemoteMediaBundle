window.NgRemoteMediaShared || (window.NgRemoteMediaShared = {});

window.NgRemoteMediaShared.upload = function($, plupload){
  return {
        maxSize: '25mb',

        headers: {
            'Accept': 'application/json, text/javascript, */*; q=0.01'
        },

        initialize: function(options) {
            _.bindAll(this);
            this.options = options;

            this.uploadCallback = options.uploaded;

            return this;
        },

        uploaded: function(up, file, info) {
            this.$('.upload-from-disk').parent().removeClass('uploading');

            if (!info || !info.response || !this.uploadCallback) { return this; }

            var model_attributes;

            try {
                model_attributes = $.parseJSON(info.response);
            } catch (e) {
                alert("Error while uploading file.");
                this.uploadCallback(false);
                return this;
            }


            this.uploadCallback({
                id: model_attributes.id || model_attributes.media.resourceId,
                model_attributes: model_attributes, //For administration
                new_image_selected: true
            });

            return this;
        },

        progress: function(up, file) {
            this.$('.progress').css('width', file.percent + '%');
        },

        added: function(up /*, files*/) {
            up.start();
            this.$('.upload-from-disk').parent().addClass('uploading');
            !this.$('.upload-from-disk').closest('[data-bootstrap]').length && this.$el.after('<div class="backdrop upload-backdrop" />');
            this.trigger('uploading');
        },


        upload_url: function(){
            if (this.model.get('ezoe')) {
                return NgRemoteMediaShared.url('/ngremotemedia/simple_upload');
            }else{
                return NgRemoteMediaShared.url('/ngremotemedia/upload/') +  this.model.get('contentObjectId');
            }
        },

        render: function(/*response*/) {
            // var id = this.model.id !== "ezoe" ? this.model.id : this.model.get('attributeId');
            var id = this.model.id;
            var settings = {
                runtimes: 'html5,flash,html4',
                browse_button: this.$('.upload-from-disk').get(0),
                flash_swf_url: NgRemoteMediaShared.config().plupload_swf,
                max_file_size: this.maxSize,
                url: this.upload_url(),
                multipart_params: {
                    legacy: NgRemoteMediaShared.config().is_admin,
                    AttributeID: id,
                    ContentObjectVersion: this.model.get('version'),
                    http_accept: 'json' //Because of some strange failing when html4 is used
                },
                headers: this.headers
            };

            if ($('#ezxform_token_js').length) {
                // Ugly hack to go with ezformtoken
                settings.multipart_params.ezxform_token = $('#ezxform_token_js').attr('title');
            }

            this.uploader = new plupload.Uploader(settings);
            this.uploader.init();
            this.uploader.bind('FileUploaded', this.uploaded);
            // this.uploader.bind('UploadProgress', this.progress); //NOTE: remove me
            this.uploader.bind('FilesAdded', this.added);
            return this;
        }


  };
};
